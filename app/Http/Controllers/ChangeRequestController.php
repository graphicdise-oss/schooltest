<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeRequest;

class ChangeRequestController extends Controller
{
  public function create()
{
    $prefill = session('change_request_preview', []);
    return view('change-request.form', compact('prefill'));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'requester_name'    => 'required|string|max:255',
            'department'        => 'required|string|max:255',
            'request_date'      => 'required|date',
            'priority'          => 'required|in:normal,urgent,critical',
            'module_name'       => 'required|string|max:255',
            'operation_types'   => 'nullable|array',
            'operation_types.*' => 'in:new_feature,bug_fix,optimization,ui_ux',
            'objective'         => 'required|string',
            'fix_link'          => 'nullable|string|max:1000',
        ]);

        $data['operation_types'] = $data['operation_types'] ?? [];

        if ($request->input('action') === 'preview') {
            session(['change_request_preview' => $data]);
            return redirect()->route('change-request.preview');
        }

        $record = ChangeRequest::create($data);
        return redirect()->route('change-request.show', $record->id)->with('saved', true);
    }

    public function preview()
    {
        $data = session('change_request_preview');
        if (!$data) return redirect()->route('change-request.create');
        return view('change-request.show', ['data' => $data, 'isPreview' => true]);
    }

    public function show($id)
    {
        $record = ChangeRequest::findOrFail($id);
        return view('change-request.show', ['data' => $record, 'isPreview' => false]);
    }

    public function history()
    {
        $records = ChangeRequest::orderByDesc('created_at')->paginate(15);
        return view('change-request.history', compact('records'));
    }

    public function destroy($id)
    {
        ChangeRequest::findOrFail($id)->delete();
        return redirect()->route('change-request.history')->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }
}