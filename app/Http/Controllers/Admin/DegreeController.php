<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Degree;
use Illuminate\Http\Request;

class DegreeController extends Controller
{
    public function index()
    {
        $items = Degree::latest()->paginate(20);
        return view('admin.settings.degrees.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.degrees.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'degree_name' => 'required|string|max:255',
        ]);

        Degree::create($data);
        return redirect()->route('admin.degrees.index')
            ->with('success', 'Degree created successfully.');
    }

    public function edit($id)
    {
        $item = Degree::findOrFail($id);
        return view('admin.settings.degrees.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Degree::findOrFail($id);
        $data = $request->validate([
            'degree_name' => 'required|string|max:255',
        ]);

        $item->update($data);
        return redirect()->route('admin.degrees.index')
            ->with('success', 'Degree updated successfully.');
    }

    public function destroy($id)
    {
        $item = Degree::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.degrees.index')
            ->with('success', 'Degree deleted successfully.');
    }
}
