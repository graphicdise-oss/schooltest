<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Curriculum;
use App\Models\ExamRoom;
use Illuminate\Http\Request;

class ExamRoomController extends Controller
{
    public function index(Request $request)
    {
        $curriculumNames = Curriculum::where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values();

        $query = ExamRoom::query();

        if ($request->filled('curriculum_name')) {
            $query->where('curriculum_name', $request->curriculum_name);
        }

        if ($request->filled('search')) {
            $query->where('room_name', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('curriculum_name')->orderBy('room_name')->get();

        return view('academic.exam_room_index', compact('rooms', 'curriculumNames'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'curriculum_name' => 'nullable|string|max:255',
            'room_name'       => 'required|string|max:255',
            'capacity'        => 'required|integer|min:1',
        ]);

        ExamRoom::create($request->only('curriculum_name', 'room_name', 'capacity'));

        return back()->with('success', 'เพิ่มห้องสอบสำเร็จ');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'curriculum_name' => 'nullable|string|max:255',
            'room_name'       => 'required|string|max:255',
            'capacity'        => 'required|integer|min:1',
        ]);

        ExamRoom::findOrFail($id)->update($request->only('curriculum_name', 'room_name', 'capacity'));

        return back()->with('success', 'แก้ไขห้องสอบสำเร็จ');
    }

    public function destroy($id)
    {
        ExamRoom::findOrFail($id)->delete();
        return back()->with('success', 'ลบห้องสอบสำเร็จ');
    }
}
