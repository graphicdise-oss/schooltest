<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\StudentSection;
use App\Models\Pp2Setting;
use Illuminate\Http\Request;

class Pp2Controller extends Controller
{
    public function index(Request $request)
    {
        $settings = Pp2Setting::first();
        $studentSections = collect();

        if ($request->filled('search')) {
            $s = $request->search;
            $studentSections = StudentSection::with(['student', 'classSection.level'])
                ->whereHas('student', fn($q) =>
                    $q->where('student_code', 'like', "%{$s}%")
                      ->orWhere('thai_firstname', 'like', "%{$s}%")
                      ->orWhere('thai_lastname', 'like', "%{$s}%")
                )
                ->get();
        }

        return view('student.pp2_index', compact('settings', 'studentSections'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'school_name'   => 'nullable|string|max:255',
            'province'      => 'nullable|string|max:255',
            'affiliation'   => 'nullable|string|max:255',
            'director_name' => 'nullable|string|max:255',
        ]);

        $settings = Pp2Setting::first();
        if ($settings) {
            $settings->update($data);
        } else {
            Pp2Setting::create($data);
        }

        return redirect()->route('pp2.index')->with('success', 'บันทึกการตั้งค่าเรียบร้อย');
    }

    public function print($studentSectionId)
    {
        $studentSection = StudentSection::with([
            'student',
            'classSection.level',
        ])->findOrFail($studentSectionId);

        $settings = Pp2Setting::first();
        $doc = null; // ไม่มีเลขที่เอกสาร ใช้ค่า default ในหน้า print

        return view('student.pp2_print', compact('studentSection', 'settings', 'doc'));
    }
}
