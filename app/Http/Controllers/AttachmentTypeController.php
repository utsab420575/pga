<?php
// app/Http/Controllers/AttachmentTypeController.php
namespace App\Http\Controllers;

use App\Models\AttachmentType;
use Illuminate\Http\Request;

class AttachmentTypeController extends Controller
{
    public function index()
    {
        $items = AttachmentType::latest()->paginate(20);
        return view('attachment_type.index', compact('items'));
    }

    public function create()
    {
        return view('attachment_type.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|boolean',
            'required' => 'nullable|boolean',
        ]);

        $data['status'] = $data['status'] ?? false;
        $data['required'] = $data['required'] ?? false;

        AttachmentType::create($data);
        return redirect()->route('attachment_type.all')->with('success', 'Attachment type created.');
    }

    public function show($id)
    {
        $item = AttachmentType::findOrFail($id);
        return view('attachment_type.show', compact('item'));
    }

    public function edit($id)
    {
        $item = AttachmentType::findOrFail($id);
        return view('attachment_type.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = AttachmentType::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|boolean',
            'required' => 'nullable|boolean',
        ]);

        $data['status'] = $data['status'] ?? false;
        $data['required'] = $data['required'] ?? false;

        $item->update($data);
        return redirect()->route('attachment_type.all')->with('success', 'Attachment type updated.');
    }

    public function destroy($id)
    {
        $item = AttachmentType::findOrFail($id);
        $item->delete();
        return redirect()->route('attachment_type.all')->with('success', 'Attachment type deleted.');
    }
}
