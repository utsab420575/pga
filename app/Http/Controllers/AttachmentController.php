<?php
// app/Http/Controllers/AttachmentController.php
namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function index()
    {
        $items = Attachment::latest()->with(['type','applicant'])->paginate(20);
        return view('attachment.index', compact('items'));
    }

    public function create()
    {
        return view('attachment.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|max:5120',
            'attachment_type_id' => 'required|exists:attachment_types,id',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('attachments', 'public');
        }

        Attachment::create($data);
        return redirect()->route('attachment.all')->with('success', 'Attachment uploaded.');
    }

    public function show($id)
    {
        $item = Attachment::with(['type','applicant'])->findOrFail($id);
        return view('attachment.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Attachment::findOrFail($id);
        return view('attachment.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Attachment::findOrFail($id);
        $data = $request->validate([
            'file' => 'nullable|file|max:5120',
            'attachment_type_id' => 'required|exists:attachment_types,id',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('attachments', 'public');
        }

        $item->update($data);
        return redirect()->route('attachment.all')->with('success', 'Attachment updated.');
    }

    public function destroy($id)
    {
        $item = Attachment::findOrFail($id);
        $item->delete();
        return redirect()->route('attachment.all')->with('success', 'Attachment deleted.');
    }
}
