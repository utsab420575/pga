<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Degree;
use Illuminate\Http\Request;

class DepartmentDegreeController extends Controller
{
    /**
     * List all departments with their currently assigned degrees.
     */
    public function index()
    {
        $departments = Department::with(['degrees', 'faculty'])->orderBy('id')->get();

        return view('admin.settings.department_degrees.index', compact('departments'));
    }

    /**
     * Show the edit form for one department's degree assignments.
     */
    public function edit($id)
    {
        $department     = Department::with('degrees')->findOrFail($id);
        $allDegrees     = Degree::orderBy('id')->get();
        $assignedIds    = $department->degrees->pluck('id')->toArray();

        return view('admin.settings.department_degrees.edit', compact(
            'department', 'allDegrees', 'assignedIds'
        ));
    }

    /**
     * Sync the selected degrees for a department.
     * Laravel sync() adds new, removes unchecked — one clean call.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        // degree_ids is an array of checked degree IDs; default empty array if none checked
        $selectedIds = $request->input('degree_ids', []);

        $department->degrees()->sync($selectedIds);

        return redirect()
            ->route('admin.department_degrees.index')
            ->with('success', 'Degree assignments updated for ' . $department->short_name . '.');
    }
}
