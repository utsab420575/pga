<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        // Business rule: paid Admission applications only
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 1)) {
                return back()->withErrors('This page is available only for paid Admission applications.');
            }
        }

        // 🔒 Deadline check from settings
        $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)

        if ($setting && $setting->end_date) {
            $deadline = Carbon::parse($setting->end_date)->endOfDay();

            if (now()->gt($deadline)) {
                // You can include the date to be clear
                return back()->withErrors('Application date is over. Deadline was: '.$deadline->toDateString());
            }
        }


        return view('applicant.application_postgraduate_master_form', [
            'applicant'          => $applicant,

            // already used in your blade
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegree'  => $applicant->eligibilityDegree,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::orderBy('id')->get(),

            // new datasets
            'theses'             => $applicant->theses,
            'publications'       => $applicant->publications,
            'jobExperiences'     => $applicant->jobExperiences,
            'references'         => $applicant->references,
        ]);
    }
}
