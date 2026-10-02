<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use App\Models\AttachmentRequirement;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ApplicationPostgraduateController extends Controller
{
    public function create($applicantId)
    {
        $applicant = Applicant::with([
            'basicInfo',            // hasOne
            'eligibilityDegree',    // hasOne (your single-row eligibility)
            'educationInfos',       // hasMany
            'theses',               // hasMany
            'publications',         // hasMany
            'jobExperiences',       // hasMany
            'references',           // hasMany
            'attachments.type',     // hasMany + belongsTo type
        ])->findOrFail($applicantId);

        // Only the owner (unless admin)
        if (auth()->user()->user_type === 'applicant' && $applicant->user_id !== auth()->id()) {
            abort(403, 'You are not allowed to access this page.');
        }

        /*// Business rule: paid Admission applications only
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 1)) {
                return back()->withErrors('This page is available only for paid Admission applications.');
            }
        }*/

        // Business rule: paid Admission applications only
        if (auth()->user()->user_type === 'applicant') {
            // ✅ If payment record not found or payment_status != 1 → block
            if (!$applicant->payment || $applicant->payment_status != 1 || $applicant->applicationtype_id != 1) {
                return back()->withErrors('You must complete payment first before accessing this page.');
            }
        }


        if (
            ((auth()->user()->user_type === 'head') || (auth()->user()->user_type === 'admin')) && ((int)$applicant->applicationtype_id === 1)){

            /*
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
            }*/
        }

        //Not remove this code;
        /*if(Auth::user()->user_type === 'applicant'){
            // 🔒 Deadline check from settings
            $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)

            if ($setting && $setting->end_date) {
                $deadline = Carbon::parse($setting->end_date)->endOfDay();

                if (now()->gt($deadline)) {
                    // You can include the date to be clear
                    return back()->withErrors('Application date is over. Deadline was: '.$deadline->toDateString());
                }
            }else{
                return back()->withErrors('Setting Table Data Not Found');
            }
        }*/


        // ✅ Deadline: applicants only, with bypass for (final_submit=0 && admission_approve=0 && payment_status=1)
         if (Auth::user()->user_type === 'applicant') {
             $bypassDeadline =
                 ((int)$applicant->final_submit === 0) &&
                 ((int)$applicant->admission_approve === 0) &&
                 ((int)$applicant->payment_status === 1);

             if (!$bypassDeadline) {
                 $setting  = Setting::latest('id')->first();
                 $lastDate = $applicant->applicationtype_id == 1 ? ($setting?->end_date) : ($setting?->eligibility_last_date);

                 if (!$lastDate) {
                     //return response()->json(['message' => 'Setting Table Data Not Found. Contact ICT-CELL.'], 403);
                     return back()->withErrors('Setting Table Data Not Found');
                 }

                 $deadline = Carbon::parse($lastDate)->endOfDay();
                 if (now()->gt($deadline)) {
                     return back()->withErrors('Application date is over. Deadline was: '.$deadline->toDateString());
                     //return response()->json(['message' => 'Submission deadline has passed. You cannot upload new files.'], 403);
                 }
             }
         }



        return view('applicant.application_postgraduate_master_form', [
            'applicant'          => $applicant,

            // already used in your blade
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegree'  => $applicant->eligibilityDegree,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::offeredFor($applicant),
            'requiredTypeIds'    => AttachmentRequirement::requiredTypeIdsFor($applicant),

            // new datasets
            'theses'             => $applicant->theses,
            'publications'       => $applicant->publications,
            'jobExperiences'     => $applicant->jobExperiences,
            'references'         => $applicant->references,
        ]);
    }

    public function preview($applicantId)
    {
        $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)
        $applicant = Applicant::with([
            'user',
            'department.faculty',
            'degree',
            'studenttype',
            'applicationtype',
            'basicInfo',
            'educationInfos',
            'theses',
            'publications',
            'jobExperiences',
            'references',
            'attachments.type',
            'eligibilityDegree',
            'payment'
        ])->findOrFail($applicantId);

        // Security check - only owner can preview (unless admin)
        if (auth()->user()->user_type === 'applicant' && $applicant->user_id !== auth()->id()) {
            abort(403, 'You are not allowed to access this page.');
        }

        // Force mobile verification first
        if (auth()->user()->user_type === 'applicant') {
            // ✅ Phone Verified
            if ((int)auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }
        
        // Business rule: only for paid Admission applications
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 1)) {
                return back()->withErrors('Preview is available only for paid Admission applications.');
            }
        }

        //  Does the logged-in user have ANY approved eligibility?
        $hasEligibility = Applicant::where('user_id', auth()->id())
            ->where('eligibility_approve', 1)
            ->exists();

        return view('applicant.preview_admission_form', [
            'applicant' => $applicant,
            'setting' => $setting,
            'hasEligibility' => $hasEligibility,
        ]);
    }

    public function eligibility($applicantId)
    {
        $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)
        $applicant = Applicant::with([
            'user',
            'department.faculty',
            'degree',
            'studenttype',
            'applicationtype',
            'basicInfo',
            'educationInfos',
            'theses',
            'publications',
            'jobExperiences',
            'references',
            'attachments.type',
            'eligibilityDegree',
            'payment'
        ])->findOrFail($applicantId);

        // Security check - only owner can preview (unless admin)
        if (auth()->user()->user_type === 'applicant' && $applicant->user_id !== auth()->id()) {
            abort(403, 'You are not allowed to access this page.');
        }

        // Force mobile verification first
        if (auth()->user()->user_type === 'applicant') {
            // ✅ Phone Verified
            if ((int)auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        // Business rule: only for paid Admission applications
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 2)) {
                return back()->withErrors('Preview is available only for paid Admission applications.');
            }
        }

        return view('applicant.preview_eligibility_form', [
            'applicant' => $applicant,
            'setting' => $setting,
        ]);
    }
}