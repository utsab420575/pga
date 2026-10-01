<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index()
    {
        $items = Notice::latest('date')->paginate(20);
        return view('admin.settings.notices.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'    => 'nullable|date',
            'title'   => 'required|string|max:255',
            'details' => 'nullable|string',
            'file'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('notices', 'public');
            $data['file'] = $path;
        }

        Notice::create($data);
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit($id)
    {
        $item = Notice::findOrFail($id);
        return view('admin.settings.notices.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Notice::findOrFail($id);
        $data = $request->validate([
            'date'    => 'nullable|date',
            'title'   => 'required|string|max:255',
            'details' => 'nullable|string',
            'file'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if it exists
            if ($item->file) {
                Storage::disk('public')->delete($item->file);
            }
            $path = $request->file('file')->store('notices', 'public');
            $data['file'] = $path;
        } else {
            unset($data['file']);
        }

        $item->update($data);
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy($id)
    {
        $item = Notice::findOrFail($id);
        if ($item->file) {
            Storage::disk('public')->delete($item->file);
        }
        $item->delete();
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }
}
