<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    public function create()
    {
        return view('change-request.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'requester_name'  => 'required|string|max:255',
            'department'      => 'required|string|max:255',
            'request_date'    => 'required|date',
            'priority'        => 'required|in:normal,urgent,critical',
            'module_name'     => 'required|string|max:255',
            'operation_types' => 'nullable|array',
            'operation_types.*' => 'in:new_feature,bug_fix,optimization,ui_ux',
            'objective'       => 'required|string',
            'fix_link'        => 'nullable|string|max:1000',
        ]);

        $data['operation_types'] = $data['operation_types'] ?? [];

        session(['change_request' => $data]);

        return redirect()->route('change-request.show');
    }

    public function show()
    {
        $data = session('change_request');

        if (!$data) {
            return redirect()->route('change-request.create');
        }

        return view('change-request.show', compact('data'));
    }
}
