<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $items = Department::with('faculty')->latest()->paginate(20);
        return view('admin.settings.departments.index', compact('items'));
    }

    public function create()
    {
        $faculties = Faculty::orderBy('faculty_name')->get();
        return view('admin.settings.departments.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'short_name' => 'required|string|max:255',
            'full_name'  => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        Department::create($data);
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit($id)
    {
        $item      = Department::findOrFail($id);
        $faculties = Faculty::orderBy('faculty_name')->get();
        return view('admin.settings.departments.edit', compact('item', 'faculties'));
    }

    public function update(Request $request, $id)
    {
        $item = Department::findOrFail($id);
        $data = $request->validate([
            'short_name' => 'required|string|max:255',
            'full_name'  => 'required|string|max:255',
            'faculty_id' => 'required|exists:faculties,id',
        ]);

        $item->update($data);
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $item = Department::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
