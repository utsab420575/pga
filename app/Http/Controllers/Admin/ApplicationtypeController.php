<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicationtype;
use Illuminate\Http\Request;

class ApplicationtypeController extends Controller
{
    public function index()
    {
        $items = Applicationtype::latest()->paginate(20);
        return view('admin.settings.applicationtypes.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.applicationtypes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|max:255',
            'fee'  => 'required|numeric|min:0',
        ]);

        Applicationtype::create($data);
        return redirect()->route('admin.applicationtypes.index')
            ->with('success', 'Application type created successfully.');
    }

    public function edit($id)
    {
        $item = Applicationtype::findOrFail($id);
        return view('admin.settings.applicationtypes.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Applicationtype::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|string|max:255',
            'fee'  => 'required|numeric|min:0',
        ]);

        $item->update($data);
        return redirect()->route('admin.applicationtypes.index')
            ->with('success', 'Application type updated successfully.');
    }

    public function destroy($id)
    {
        $item = Applicationtype::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.applicationtypes.index')
            ->with('success', 'Application type deleted successfully.');
    }
}
