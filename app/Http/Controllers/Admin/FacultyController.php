<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $items = Faculty::latest()->paginate(20);
        return view('admin.settings.faculties.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.faculties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'faculty_name' => 'required|string|max:255',
            'short_name'   => 'required|string|max:255',
        ]);

        Faculty::create($data);
        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty created successfully.');
    }

    public function edit($id)
    {
        $item = Faculty::findOrFail($id);
        return view('admin.settings.faculties.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Faculty::findOrFail($id);
        $data = $request->validate([
            'faculty_name' => 'required|string|max:255',
            'short_name'   => 'required|string|max:255',
        ]);

        $item->update($data);
        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty updated successfully.');
    }

    public function destroy($id)
    {
        $item = Faculty::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.faculties.index')
            ->with('success', 'Faculty deleted successfully.');
    }
}
