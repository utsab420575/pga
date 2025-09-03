<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use Illuminate\Http\Request;

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

        // Business rule: page opens only if payment_status=1 AND applicationtype_id=1
        // (If your column is literally `application_type_id`, change the property name below.)
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 2)) {
                return back()->withErrors('This page is available only for paid Admission applications.');
                // Or: abort(403, 'This page is available only for paid Admission applications.');
            }
        }


        return view('applicant.eligibility_master_form', [
            'applicant'          => $applicant,
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegree' => $applicant->eligibilityDegree,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::orderBy('title')->get(),
        ]);
    }
}
