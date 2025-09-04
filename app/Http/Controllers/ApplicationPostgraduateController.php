<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use Illuminate\Http\Request;

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

        return view('applicant.application_postgraduate_master_form', [
            'applicant'          => $applicant,

            // already used in your blade
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegree'  => $applicant->eligibilityDegree,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::orderBy('title')->get(),

            // new datasets
            'theses'             => $applicant->theses,
            'publications'       => $applicant->publications,
            'jobExperiences'     => $applicant->jobExperiences,
            'references'         => $applicant->references,
        ]);
    }
}
