<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave\LeaveRequest;
use App\Models\Leave\LeaveType;
use App\Models\Personne\Personnel;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function show($id)
    {
        $request   = LeaveRequest::with(['leaveType', 'requester', 'reviewer'])->findOrFail($id);
        $personnel = $request->requester;

        return view('leave.request_show', compact('request', 'personnel'));
    }

    public function print($id)
    {
        $request   = LeaveRequest::with(['leaveType', 'requester', 'reviewer'])->findOrFail($id);
        $personnel = $request->requester;

        return view('leave.request_print', compact('request', 'personnel'));
    }

    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $personnelId  = $leaveRequest->requester_id;
        $leaveRequest->delete();

        return redirect()->route('leave.personnel.show', $personnelId)
            ->with('success', 'ลบรายการลาสำเร็จ');
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('id')->get();
        $personnels = Personnel::orderBy('thai_firstname')->get();

        return view('leave.request_form', compact('leaveTypes', 'personnels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_key' => 'required',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'num_days'       => 'required|numeric|min:0.5',
            'requester_id'   => 'required',
            'reviewer_id'    => 'required',
        ], [
            'leave_type_key.required' => 'กรุณาเลือกประเภทการลา',
            'start_date.required'     => 'กรุณาระบุวันที่เริ่มลา',
            'end_date.required'       => 'กรุณาระบุวันที่สิ้นสุด',
            'end_date.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มลา',
            'num_days.required'       => 'กรุณาระบุจำนวนวัน',
            'requester_id.required'   => 'กรุณาเลือกผู้ยื่นคำร้อง',
            'reviewer_id.required'    => 'กรุณาเลือกผู้ตรวจสอบ',
        ]);

        LeaveRequest::create([
            'leave_type_key' => $request->leave_type_key,
            'request_date'   => now(),
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'num_days'       => $request->num_days,
            'requester_id'   => $request->requester_id,
            'reviewer_id'    => $request->reviewer_id,
            'status'         => 'รอการอนุมัติ',
            'reason'         => $request->reason,
        ]);

        return redirect()->route('leave.personnel.show', $request->requester_id)
            ->with('success', 'บันทึกรายการลาสำเร็จ');
    }

    public function updateStatus(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $request->validate(['status' => 'required|in:อนุมัติ,ไม่อนุมัติ,รอการอนุมัติ']);

        $leaveRequest->update([
            'status' => $request->status,
            'note'   => $request->note,
        ]);

        return back()->with('success', 'อัปเดตสถานะสำเร็จ');
    }
}
