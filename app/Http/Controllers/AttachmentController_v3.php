<?php
// app/Http/Controllers/AttachmentController.php
namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Attachment;
use App\Models\AttachmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttachmentController_v3 extends Controller
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


    //no need this upload() ; this is handle by ajax upload , delete
   /* public function upload(Request $request)
    {
        // Basic fields
        $request->validate([
            'attachment_type_id' => 'required|exists:attachment_types,id',
            'applicant_id'       => 'required|exists:applicants,id',
            'files'              => 'required|array',
            'files.*'            => 'file', // further constrained below
        ]);

        $typeId = (int) $request->attachment_type_id;

        // Build per-file validation rules based on type
        $perFileRules = [];
        $perFileMessages = [];

        if (in_array($typeId, [6, 10], true)) {
            // 6: photo (<= 500KB), 10: signature (<= 500KB)
            // allow only images
            $perFileRules['files.*'] = 'mimes:jpg,jpeg,png|max:500';

            // Optional: dimension checks (uncomment if you want exact sizes)
            //  - type 6: exactly 300x300
            //  - type 10: exactly 250x50
            // You can enforce with getimagesize below after validation.
            $perFileMessages = [
                'files.*.mimes' => 'Only JPG/PNG images are allowed for this attachment type.',
                'files.*.max'   => 'Each image must not exceed 500KB.',
            ];
        } else {
            // All other types: PDF only, <= 2MB
            $perFileRules['files.*'] = 'mimes:pdf|max:2048';
            $perFileMessages = [
                'files.*.mimes' => 'Only PDF files are allowed for this attachment type.',
                'files.*.max'   => 'Each PDF must not exceed 2MB.',
            ];
        }

        // Validate files with the dynamic rules
        validator($request->all(), $perFileRules, $perFileMessages)->validate();



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
    }*/


    /**
     * AJAX single-file upload
     * Expects: attachment_type_id, applicant_id, file (single), optional title
     * Returns JSON: { id, type_id, type_title, title, url, is_image }
     */
    public function ajaxUpload(Request $request)
    {

        //return $request;
        // 1. Basic validation for incoming fields
        $request->validate([
            'attachment_type_id' => 'required|exists:attachment_types,id', // must exist in DB
            'applicant_id'       => 'required|exists:applicants,id',       // must exist in DB
            'file'               => 'required|file',                       // single file required
            'title'              => 'nullable|string|max:255',             // optional title
        ]);

        $typeId     = (int) $request->attachment_type_id;
        $applicant  = Applicant::findOrFail($request->applicant_id);

        // 2. Authorization check
        // Applicants can only upload for themselves
        // Admins can upload for any applicant
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }


        // 3. Per-type validation rules (match your existing `upload` method)
        if (in_array($typeId, [1, 2], true)) {
            // For type 6 (photo) or type 10 (signature)
            // Only allow image files, max size 500 KB
            $request->validate([
                'file' => 'mimes:jpg,jpeg,png,webp,gif|max:500',
            ], [
                'file.mimes' => 'Only image files (JPG/PNG/WEBP/GIF) are allowed for this type.',
                'file.max'   => 'Image must not exceed 500KB.',
            ]);
        } else {
            // For all other types: PDF only, max size 10 MB
            $request->validate([
                'file' => 'mimes:pdf|max:10240',
            ], [
                'file.mimes' => 'Only PDF files are allowed for this attachment type.',
                'file.max'   => 'PDF must not exceed 10MB.',
            ]);
        }

        // 4. Build a safe file name and target directory
        $file          = $request->file('file'); // UploadedFile instance
        $extension     = strtolower($file->getClientOriginalExtension()); // e.g. pdf, jpg
        $originalName  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase      = preg_replace('/[^A-Za-z0-9_-]/', '', $originalName); // strip special chars
        $today         = now()->format('Y-m-d'); // e.g. 2025-09-06
        $uploadPath    = public_path("attachments/{$today}"); // public/attachments/2025-09-06

        // Ensure folder exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Construct filename pattern: applicantId_typeId_timestamp_originalname.ext
        $filename = $applicant->id . '_' .
            $typeId . '_' .
            now()->format('Ymd_His_u') . '_' .
            $safeBase . '.' . $extension;

        // 5. Move file from temp to our uploads folder
        $file->move($uploadPath, $filename);

        // Store relative DB path (used with asset())
        $dbPath = "attachments/{$today}/{$filename}";

        // 6. Save in DB
        $attachment = new Attachment();
        $attachment->file = $dbPath;
        $attachment->attachment_type_id = $typeId;
        $attachment->applicant_id = $applicant->id;
        // If your attachments table has a `title` column, save it
        if ($attachment->isFillable('title')) {
            // use given title if not empty, else fallback to type title
            $attachment->title = $request->input('title') ?: optional(AttachmentType::find($typeId))->title;
        }
        $attachment->save();

        // 7. Build JSON response for the front-end
        $type = AttachmentType::find($typeId); // get type info
        $url  = asset($attachment->file);      // full URL to file

        // Check if file is an image (for preview vs. "View" link)
        $isImage = Str::endsWith($dbPath, ['.jpg', '.jpeg', '.png', '.webp', '.gif']);

        return response()->json([
            'id'         => $attachment->id,
            'type_id'    => $typeId,
            'type_title' => $type->title ?? 'N/A',
            'title'      => $attachment->title ?? null,
            'url'        => $url,
            'is_image'   => $isImage,
        ]);
    }


    /**
     * AJAX delete attachment
     * Route model bound: {attachment}
     */
    /**
     * AJAX delete attachment
     * Route model bound: {attachment}
     */
    public function ajaxDelete(Attachment $attachment, Request $request)
    {
        // 1. Load applicant for authorization
        $applicant = Applicant::find($attachment->applicant_id);
        if (!$applicant) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // Applicants can only delete their own attachments
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // 2. Delete the physical file if it exists
        $fullPath = public_path($attachment->file);
        if (is_file($fullPath)) {
            @unlink($fullPath); // suppress errors with @
        }

        // 3. Delete the DB row
        $attachment->delete();

        // 4. Return success JSON for JS to update the table
        return response()->json(['ok' => true]);
    }

}
