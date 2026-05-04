<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    public function create(Request $request)
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('id')->get();
        $personnels = Personnel::orderBy('thai_firstname')->get();
        $selectedPersonnelId = $request->get('personnel_id');

        return view('leave.request_form', compact('leaveTypes', 'personnels', 'selectedPersonnelId'));
    }

 public function store(Request $request)
{
    $request->validate([
        'leave_type_key' => 'required',
        'start_date'     => 'required|date',
        'end_date'       => 'required|date|after_or_equal:start_date',
        'num_days'       => 'required|numeric|min:0.5',
        'requester_id'   => 'required',
    ], [
        'leave_type_key.required' => 'กรุณาเลือกประเภทการลา',
        'start_date.required'     => 'กรุณาระบุวันที่เริ่มลา',
        'end_date.required'       => 'กรุณาระบุวันที่สิ้นสุด',
        'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มลา',
        'num_days.required'       => 'กรุณาระบุจำนวนวัน',
        'requester_id.required'   => 'กรุณาเลือกผู้ยื่นคำร้อง',
    ]);

    LeaveRequest::create([
        'leave_type_key'      => $request->leave_type_key,
        'request_date'        => now(),
        'start_date'          => $request->start_date,
        'end_date'            => $request->end_date,
        'num_days'            => $request->num_days,
        'leave_period'        => $request->leave_period ?? 'ทั้งวัน',
        'requester_id'        => $request->requester_id,
        'reviewer_id'         => $request->requester_id, // ใช้ตัวเองชั่วคราว หรือจะเอาออกก็ได้
        'status'              => 'รอการอนุมัติ',
        'reason'              => $request->reason,
        'contact_house'       => $request->contact_house,
        'contact_road'        => $request->contact_road,
        'contact_subdistrict' => $request->contact_subdistrict,
        'contact_district'    => $request->contact_district,
        'contact_province'    => $request->contact_province,
        'contact_phone'       => $request->contact_phone,
        'approver1_id'        => $request->approver1_id,
        'approver2_id'        => $request->approver2_id,
    ]);

    return redirect()->route('leave.personnel.show', $request->requester_id)
        ->with('success', 'ส่งใบลาสำเร็จ รอการอนุมัติ');
}

    public function show($id)
    {
        $request   = LeaveRequest::with(['leaveType','requester','reviewer','approver1','approver2'])->findOrFail($id);
        $personnel = $request->requester;

        // สถิติการลาในปีนี้
        $yearAD = now()->year;
        $stats  = LeaveRequest::where('requester_id', $request->requester_id)
            ->whereYear('start_date', $yearAD)
            ->where('status', 'อนุมัติ')
            ->select('leave_type_key', DB::raw('SUM(num_days) as total'))
            ->groupBy('leave_type_key')
            ->pluck('total', 'leave_type_key');

        return view('leave.request_show', compact('request', 'personnel', 'stats'));
    }

    public function print($id)
    {
        $request   = LeaveRequest::with(['leaveType','requester','reviewer','approver1','approver2'])->findOrFail($id);
        $personnel = $request->requester;

        $yearAD = $request->start_date->year;
        $stats  = LeaveRequest::where('requester_id', $request->requester_id)
            ->whereYear('start_date', $yearAD)
            ->where('status', 'อนุมัติ')
            ->select('leave_type_key', DB::raw('SUM(num_days) as total'))
            ->groupBy('leave_type_key')
            ->pluck('total', 'leave_type_key');

        return view('leave.request_print', compact('request', 'personnel', 'stats'));
    }

    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $personnelId  = $leaveRequest->requester_id;
        $leaveRequest->delete();
        return redirect()->route('leave.personnel.show', $personnelId)->with('success', 'ลบรายการลาสำเร็จ');
    }

    public function updateStatus(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $request->validate(['status' => 'required|in:อนุมัติ,ไม่อนุมัติ,รอการอนุมัติ']);
        $leaveRequest->update([
            'status'           => $request->status,
            'note'             => $request->note,
            'approver1_comment'=> $request->approver1_comment,
            'approver1_date'   => $request->approver1_date,
            'approver2_comment'=> $request->approver2_comment,
            'approver2_date'   => $request->approver2_date,
        ]);
        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }
}