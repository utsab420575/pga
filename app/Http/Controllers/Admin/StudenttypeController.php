<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Studenttype;
use Illuminate\Http\Request;

class StudenttypeController extends Controller
{
    public function index()
    {
        $items = Studenttype::latest()->paginate(20);
        return view('admin.settings.studenttypes.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.studenttypes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        Studenttype::create($data);
        return redirect()->route('admin.studenttypes.index')
            ->with('success', 'Student type created successfully.');
    }

    public function edit($id)
    {
        $item = Studenttype::findOrFail($id);
        return view('admin.settings.studenttypes.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Studenttype::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|string|max:255',
        ]);

        $item->update($data);
        return redirect()->route('admin.studenttypes.index')
            ->with('success', 'Student type updated successfully.');
    }

    public function destroy($id)
    {
        $item = Studenttype::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.studenttypes.index')
            ->with('success', 'Student type deleted successfully.');
    }
}
