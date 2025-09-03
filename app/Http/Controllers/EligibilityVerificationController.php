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
            'eligibilityDegrees',
            'educationInfos',
            'attachments.type',
        ])->findOrFail($applicantId);

        //return $applicant;

        // If you want to restrict to owner unless admin:
        if (auth()->user()->user_type === 'applicant' && $applicant->user_id !== auth()->id()) {
            //return redirect()->back()->with('error', 'You are not allowed to access this page.');
            abort(403, 'You are not allowed to access this page.');
        }

        return view('applicant.eligibility_master_form', [
            'applicant'          => $applicant,
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegrees' => $applicant->eligibilityDegrees,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::orderBy('title')->get(),
        ]);
    }
}
