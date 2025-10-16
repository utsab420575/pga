<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EligibilityVerificationController extends Controller
{
    public function create($applicantId)
    {
        $applicant = Applicant::with([
            'basicInfo',
            'eligibilityDegree',
            'educationInfos',
            'attachments.type',
        ])->findOrFail($applicantId);

        //return $applicant;

        // If you want to restrict to owner unless admin:
        if (auth()->user()->user_type === 'applicant' && $applicant->user_id !== auth()->id()) {
            //return redirect()->back()->with('error', 'You are not allowed to access this page.');
            abort(403, 'You are not allowed to access this page.');
        }

        /* // Business rule: page opens only if payment_status=1 AND applicationtype_id=1
         // (If your column is literally `application_type_id`, change the property name below.)
         if (auth()->user()->user_type === 'applicant') {
             if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 2)) {
                 return back()->withErrors('This page is available only for paid Admission applications.');
                 // Or: abort(403, 'This page is available only for paid Admission applications.');
             }
         }*/

        // Business rule: paid Admission applications only
        if (auth()->user()->user_type === 'applicant') {
            // ✅ If payment record not found or payment_status != 2 → block
            if (!$applicant->payment || $applicant->payment_status != 1 || $applicant->applicationtype_id != 2) {
                return back()->withErrors('You must complete payment first before accessing this page.');
            }
        }


        if(Auth::user()->user_type === 'head' && $applicant->applicationtype_id == 2){
            // ✅ Date gate: allow ONLY if now() < settings.start_date
            $setting = Setting::query()->latest('id')->first();
            if (!$setting || !$setting->start_date) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Settings missing start date. Contact ICT-CELL.'
                ], 422);
            }

            // Compare using start of day so the whole start_date is blocked
            $start = Carbon::parse($setting->start_date)->startOfDay();
            if (! now()->lt($start)) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Approval window is closed. Allowed only before: '.$start->toDateString()
                ], 422);
            }
        }
        if(Auth::user()->user_type === 'head' && $applicant->applicationtype_id == 1){
            // ✅ Date gate: allow ONLY if now() < settings.start_date
            $setting = Setting::query()->latest('id')->first();
            if (!$setting || !$setting->start_date) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Settings missing start date. Contact ICT-CELL.'
                ], 422);
            }

            // Compare using start of day so the whole start_date is blocked
            $start = Carbon::parse($setting->last_date)->startOfDay();
            if (! now()->lt($start)) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Approval window is closed. Allowed only before: '.$start->toDateString()
                ], 422);
            }
        }

        if(Auth::user()->user_type === 'applicant'){
            $setting = Setting::latest()->first();
            $lastDate = $applicant->applicationtype_id == 1 ? $setting?->end_date : $setting?->eligibility_last_date;
            if ($lastDate && now()->toDateString() > \Carbon\Carbon::parse($lastDate)->toDateString()) {
                return back()->withErrors('Submission deadline has passed. Cannot update.');
            }
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


        return view('applicant.eligibility_master_form', [
            'applicant' => $applicant,
            'basicInfo' => $applicant->basicInfo,
            'eligibilityDegree' => $applicant->eligibilityDegree,
            'educationInfos' => $applicant->educationInfos,
            'attachments' => $applicant->attachments,
            'attachmentTypes' => AttachmentType::orderBy('id')->get(),
        ]);
    }
}
