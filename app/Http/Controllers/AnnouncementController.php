<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRecipient;
use App\Models\Academic\Level;
use App\Models\Academic\ClassSection;
use App\Models\Academic\StudentSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // รายการประกาศทั้งหมด + ปุ่มสร้างใหม่
    public function index()
    {
        $announcements = Announcement::withCount('recipients')
            ->with(['recipients' => fn($q) => $q->whereNotNull('read_at')])
            ->orderByDesc('created_at')
            ->paginate(20);

        $levels = Level::orderBy('sort_order')->get();
        $sections = ClassSection::real()->with('level')
            ->whereHas('semester', fn($q) => $q->where('is_current', true))
            ->orderBy('level_id')->orderBy('section_number')->get();

        return view('announcements.index', compact('announcements', 'levels', 'sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'message_type'  => 'nullable|string|max:100',
            'requires_ack'  => 'nullable|boolean',
            'target_type'   => 'required|in:all,level,section',
            'target_level_id'   => 'nullable|exists:levels,level_id',
            'target_section_id' => 'nullable|exists:class_sections,section_id',
            'body'          => 'required|string',
            'attachment'    => 'nullable|image|max:5120',
        ], [
            'title.required' => 'กรุณากรอกชื่อเรื่อง',
            'body.required'  => 'กรุณากรอกรายละเอียด',
            'attachment.image' => 'ไฟล์แนบต้องเป็นรูปภาพเท่านั้น',
            'attachment.max'   => 'ไฟล์แนบต้องไม่เกิน 5MB',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement = Announcement::create([
            'title'             => $data['title'],
            'message_type'      => $data['message_type'] ?? null,
            'send_type'         => 'ส่งทันที',
            'requires_ack'      => $request->boolean('requires_ack'),
            'delivery_mode'     => 'รายกลุ่ม',
            'target_type'       => $data['target_type'],
            'target_level_id'   => $data['target_type'] === 'level' ? $data['target_level_id'] : null,
            'target_section_id' => $data['target_type'] === 'section' ? $data['target_section_id'] : null,
            'body'              => $data['body'],
            'attachment_path'   => $attachmentPath,
            'created_by'        => Auth::user()->thai_firstname ?? 'system',
        ]);

        $studentIds = $this->resolveRecipientStudentIds($data['target_type'], $data['target_level_id'] ?? null, $data['target_section_id'] ?? null);
        $now = now();
        $rows = $studentIds->map(fn($sid) => [
            'announcement_id' => $announcement->announcement_id,
            'student_id'      => $sid,
            'created_at'      => $now,
            'updated_at'      => $now,
        ])->all();
        if ($rows) {
            AnnouncementRecipient::insert($rows);
        }

        return redirect()->route('announcements.show', $announcement->announcement_id)
            ->with('success', 'ส่งประกาศสำเร็จ ถึงผู้ปกครอง ' . count($rows) . ' คน');
    }

    // รายละเอียดประกาศ + ตารางสถานะการอ่าน/รับทราบรายคน
    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);

        $recipients = AnnouncementRecipient::with(['student.studentSections' => function ($q) {
                $q->where('status', 'กำลังศึกษา')->with('classSection.level')->latest('id');
            }])
            ->where('announcement_id', $id)
            ->get()
            ->map(function ($r) {
                $section = $r->student?->studentSections?->first()?->classSection;
                return (object) [
                    'student_id'   => $r->student_id,
                    'student_code' => $r->student?->student_code ?? '-',
                    'name'         => trim(($r->student?->thai_prefix ?? '') . ($r->student?->thai_firstname ?? '') . ' ' . ($r->student?->thai_lastname ?? '')),
                    'section_name' => $section?->full_name ?? '-',
                    'read_at'      => $r->read_at,
                    'acknowledged_at' => $r->acknowledged_at,
                ];
            })
            ->sortBy('section_name')
            ->values();

        return view('announcements.show', compact('announcement', 'recipients'));
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        $levels = Level::orderBy('sort_order')->get();
        $sections = ClassSection::real()->with('level')
            ->whereHas('semester', fn($q) => $q->where('is_current', true))
            ->orderBy('level_id')->orderBy('section_number')->get();

        return view('announcements.edit', compact('announcement', 'levels', 'sections'));
    }

    // แก้ได้เฉพาะเนื้อหา (หัวข้อ/ประเภท/รายละเอียด/ไฟล์แนบ/ต้องรับทราบไหม) — ไม่แก้กลุ่มผู้รับ
    // เพราะรายชื่อผู้รับถูกล็อกไว้ตั้งแต่ตอนส่งแล้ว เปลี่ยนทีหลังจะทำให้ประวัติการอ่าน/รับทราบไม่ตรงกับผู้รับจริง
    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'message_type' => 'nullable|string|max:100',
            'requires_ack' => 'nullable|boolean',
            'body'         => 'required|string',
            'attachment'   => 'nullable|image|max:5120',
        ], [
            'title.required' => 'กรุณากรอกชื่อเรื่อง',
            'body.required'  => 'กรุณากรอกรายละเอียด',
        ]);

        if ($request->hasFile('attachment')) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement->update([
            'title'        => $data['title'],
            'message_type' => $data['message_type'] ?? null,
            'requires_ack' => $request->boolean('requires_ack'),
            'body'         => $data['body'],
            'attachment_path' => $data['attachment_path'] ?? $announcement->attachment_path,
        ]);

        return redirect()->route('announcements.show', $id)->with('success', 'แก้ไขประกาศสำเร็จ');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }
        $announcement->recipients()->delete();
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'ลบประกาศสำเร็จ');
    }

    // หานักเรียนที่ "กำลังศึกษา" อยู่จริงตามกลุ่มเป้าหมายที่เลือก (ทั้งหมด/ระดับชั้น/ห้องเดียว)
    private function resolveRecipientStudentIds(string $targetType, ?int $levelId, ?int $sectionId)
    {
        $query = StudentSection::where('status', 'กำลังศึกษา');

        if ($targetType === 'section' && $sectionId) {
            $query->where('section_id', $sectionId);
        } elseif ($targetType === 'level' && $levelId) {
            $query->whereHas('classSection', fn($q) => $q->where('level_id', $levelId));
        }

        return $query->pluck('student_id')->unique()->values();
    }
}
