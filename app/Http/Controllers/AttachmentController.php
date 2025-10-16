<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Attachment;
use App\Models\AttachmentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $applicant = Applicant::findOrFail($request->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }
        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot upload.');
        }



        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot upload.');
        }

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        /*if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }*/

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

        $applicant = Applicant::findOrFail($request->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }
        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot update.');
        }
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot update.');
        }

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        /*if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }*/

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
        $applicant = Applicant::findOrFail($item->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return back()->withErrors('Forbidden.');
        }
        if ($applicant->final_submit == 1) {
            return back()->withErrors('Final submission already done. Cannot delete.');
        }
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return back()->withErrors('Submission deadline has passed. Cannot delete.');
        }

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        /*if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }*/

        // full path in public folder
        $filePath = public_path($item->file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $item->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }

    /**
     * AJAX single-file upload
     */
    public function ajaxUpload(Request $request)
    {
        $request->validate([
            'attachment_type_id' => 'required|exists:attachment_types,id',
            'applicant_id'       => 'required|exists:applicants,id',
            'file'               => 'required|file',
            'title'              => 'nullable|string|max:255',
        ]);

        $typeId    = (int) $request->attachment_type_id;
        $applicant = Applicant::findOrFail($request->applicant_id);

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        if ($applicant->final_submit == 1) {
            return response()->json(['message' => 'Final submission already done. You cannot upload new files.'], 403);
        }

        //this can be useful,not delete this code
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
        }

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
       /* if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }*/

        // File validation
        // File validation with dimensions
        if ($typeId == 1) {
            // Photo (Passport size)
            $request->validate([
                'file' => 'mimes:jpg,jpeg,png,webp,gif|max:500|dimensions:width=300,height=300',
            ]);
        } elseif ($typeId == 2) {
            // Signature
            $request->validate([
                'file' => 'mimes:jpg,jpeg,png,webp,gif|max:500|dimensions:width=300,height=80',
            ]);
        } else {
            // Other documents (PDFs only)
            $request->validate([
                'file' => 'mimes:pdf|max:10240',
            ]);
        }

        // File handling
        $file          = $request->file('file');
        $extension     = strtolower($file->getClientOriginalExtension());
        $originalName  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase      = preg_replace('/[^A-Za-z0-9_-]/', '', $originalName);
        $today         = now()->format('Y-m-d');
        $uploadPath    = public_path("attachments/{$today}");

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $filename = $applicant->id . '_' . $typeId . '_' . now()->format('Ymd_His_u') . '_' . $safeBase . '.' . $extension;
        $file->move($uploadPath, $filename);

        $dbPath = "attachments/{$today}/{$filename}";

        $attachment = new Attachment();
        $attachment->file = $dbPath;
        $attachment->attachment_type_id = $typeId;
        $attachment->applicant_id = $applicant->id;
        if ($attachment->isFillable('title')) {
            $attachment->title = $request->input('title') ?: optional(AttachmentType::find($typeId))->title;
        }
        $attachment->save();


        // ✅ If photo or signature, also update in basic_infos
        $basic = $applicant->basicInfo; // relation should exist (hasOne)
        if ($basic) {
            if ($typeId == 1) {
                $basic->photo = $dbPath; // assumes you have `photo` column
            } elseif ($typeId == 2) {
                $basic->sign = $dbPath; // assumes you have `signature` column
            }
            $basic->save();
        }

        $type = AttachmentType::find($typeId);
        $url  = asset($attachment->file);
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
     */
    public function ajaxDelete(Attachment $attachment, Request $request)
    {
        $applicant = Applicant::find($attachment->applicant_id);
        if (!$applicant) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // ✅ Four Conditions
        if (Auth::user()->user_type === 'applicant' && $applicant->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        if ($applicant->final_submit == 1) {
            return response()->json(['message' => 'Final submission already done. You cannot delete files.'], 403);
        }
        $setting = Setting::latest()->first();;
        $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
        if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
            return response()->json(['message' => 'Submission deadline has passed. You cannot delete files.'], 403);
        }

        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && eligibility_approve=0 && payment_status=1)
        /*if (Auth::user()->user_type === 'applicant') {
            $bypassDeadline =
                ((int)$applicant->final_submit === 0) &&
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1);

            if (!$bypassDeadline) {
                $setting  = Setting::latest('id')->first();
                $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                if (!$lastDate) {
                    return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                }

                $deadline = Carbon::parse($lastDate)->endOfDay();
                if (now()->gt($deadline)) {
                    return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                }
            }
        }*/

        $fullPath = public_path($attachment->file);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $attachment->delete();
        return response()->json(['ok' => true]);
    }
}
