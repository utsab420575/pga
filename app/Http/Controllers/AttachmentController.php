<?php
// app/Http/Controllers/AttachmentController.php
namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // full path in public folder
        $filePath = public_path($item->file);

        // delete file if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // delete DB record
        $item->delete();

        return redirect()->back()->with('success', 'Deleted successfully');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
            'attachment_type_id' => 'required|exists:attachment_types,id',
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        //extra valiation for type 4(passport size picture) and 8(signature)
        foreach ($request->file('files') as $file) {
            $attachmentTypeId = $request->attachment_type_id;
            $extension = strtolower($file->getClientOriginalExtension());

            // Extra validation for type 4 (photo 300x300)
            if ($attachmentTypeId == 4) {
                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    return back()->withErrors(['files' => 'Only JPG/PNG allowed']);
                }
                [$width, $height] = getimagesize($file);
                if ($width != 300 || $height != 300) {
                    return back()->withErrors(['files' => 'Image must be 300x300 pixels']);
                }
            }

            // Extra validation for type 8 (signature 250x50)
            if ($attachmentTypeId == 8) {
                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    return back()->withErrors(['files' => 'Only JPG/PNG allowed']);
                }
                [$width, $height] = getimagesize($file);
                if ($width != 250 || $height != 50) {
                    return back()->withErrors(['files' => 'Image must be 250x50 pixels']);
                }
            }
        }

        $today = now()->format('Y-m-d'); // folder name: 2025-09-04
        $uploadPath = public_path("attachments/{$today}");

        // make directory if not exists
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        foreach ($request->file('files') as $file) {
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = $request->applicant_id . '_' .
                $request->attachment_type_id . '_' .
                now()->format('Ymd_His_u') . '_' .
                preg_replace('/[^A-Za-z0-9_-]/', '', $originalName) . // remove spaces/special chars
                '.' . $extension;

            // move file to public folder
            $file->move($uploadPath, $filename);

            // save path in DB (relative to public)
            $dbPath = "attachments/{$today}/{$filename}";

            Attachment::create([
                'file' => $dbPath,
                'attachment_type_id' => $request->attachment_type_id,
                'applicant_id' => $request->applicant_id,
            ]);
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

}
