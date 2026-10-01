<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttachmentType;
use Illuminate\Http\Request;

class AttachmentTypeController extends Controller
{
    public function index()
    {
        $items = AttachmentType::latest()->paginate(20);
        return view('admin.settings.attachment_types.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.attachment_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'rules'    => 'nullable|string',
            'status'   => 'nullable|boolean',
            'required' => 'nullable|boolean',
        ]);

        $data['status']   = isset($data['status'])   ? 1 : 0;
        $data['required'] = isset($data['required']) ? 1 : 0;

        AttachmentType::create($data);
        return redirect()->route('admin.attachment_types.index')
            ->with('success', 'Attachment type created successfully.');
    }

    public function edit($id)
    {
        $item = AttachmentType::findOrFail($id);
        return view('admin.settings.attachment_types.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = AttachmentType::findOrFail($id);
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'rules'    => 'nullable|string',
            'status'   => 'nullable|boolean',
            'required' => 'nullable|boolean',
        ]);

        $data['status']   = $request->has('status')   ? 1 : 0;
        $data['required'] = $request->has('required') ? 1 : 0;

        $item->update($data);
        return redirect()->route('admin.attachment_types.index')
            ->with('success', 'Attachment type updated successfully.');
    }

    public function destroy($id)
    {
        $item = AttachmentType::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.attachment_types.index')
            ->with('success', 'Attachment type deleted successfully.');
    }
}
