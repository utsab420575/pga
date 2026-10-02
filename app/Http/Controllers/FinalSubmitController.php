<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentRequirement;
use App\Models\AttachmentType;
use Illuminate\Http\Request;

class FinalSubmitController extends Controller
{
    public function submitEligibility(Request $request, Applicant $applicant)
    {
        $request->validate([
            'confirm' => 'accepted',
            'declaration' => 'accepted',
        ]);

        // ✅ 1. Check if basic info exists
        $basic = $applicant->basicInfo;
        if (!$basic) {
            return back()->withErrors('Please complete your Basic Information before final submission.');
        }

        // ✅ 2. Ensure required basic info fields are not empty
        $requiredFields = [
            'full_name_block_letter',
            'f_name',
            'm_name',
            /*'passport_no',*/
            'per_address',
            'pre_address',
            'dob',
            'nationality',
            'nid',
            'religion',
            'gender',
            'marital_status',
        ];

        foreach ($requiredFields as $field) {
            if (empty($basic->$field)) {
                return back()->withErrors("Please complete your Basic Information field: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }
        //EligibilityDegree
        // ✅ 4. Check eligibility degree
        $eligibility = $applicant->eligibilityDegree;
        if (!$eligibility) {
            return back()->withErrors('Please complete your Eligibility Degree information before final submission.');
        }

        $requiredEligibilityFields = [
            'degree',
            'institute',
            'country',
            'cgpa',
            'date_graduation',
            'duration',
            'total_credit',
            'mode',
            'uni_status',
            'url',
        ];

        foreach ($requiredEligibilityFields as $field) {
            if (empty($eligibility->$field)) {
                return back()->withErrors("Please complete your Eligibility Degree field: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }


        //Education Info
        if ($applicant->educationInfos->isEmpty()) {
            return back()->withErrors('Please provide at least one Education Information entry.');
        }

        // ✅ 3. Check education info
        if ($applicant->educationInfos->count() < 3) {
            return back()->withErrors('Please provide at least 3 Education Information entries.');
        }



        // ✅ 5. Required attachments (rules in attachment_requirements table)
        if ($error = $this->missingAttachmentsError($applicant)) {
            return back()->withErrors($error);
        }

        // ✅ Mark applicant as finally submitted
        $applicant->final_submit = 1;
        $applicant->save();

        return back()->with('success', 'Your application has been finally submitted.');
    }

    public function submitApplication(Request $request, Applicant $applicant)
    {
        //return $applicant;

        $request->validate([
            'confirm' => 'accepted',
            'declaration' => 'accepted',
        ]);

        // ✅ 1. Check if basic info exists
        $basic = $applicant->basicInfo;
        if (!$basic) {
            return back()->withErrors('Please complete your Basic Information before final submission.');
        }

        // ✅ 2. Ensure required basic info fields are not empty
        $requiredFields = [
            'full_name',
            'bn_name',
            'f_name',
            'm_name',
            'g_income',
            'per_address',
            'pre_address',
            'dob',
            'nationality',
            'nid',
            'religion',
            'gender',
            'marital_status',
        ];

        foreach ($requiredFields as $field) {
            if (empty($basic->$field)) {
                return back()->withErrors("Please complete your Basic Information field: " . ucfirst(str_replace('_', ' ', $field)));
            }
        }




        //Education Info
        if ($applicant->educationInfos->isEmpty()) {
            return back()->withErrors('Please provide at least one Education Information entry.');
        }

        // ✅ 3. Check education info
        if ($applicant->educationInfos->count() < 3) {
            return back()->withErrors('Please provide at least 3 Education Information entries.');
        }

        //Thesis/publication/job not required


        //minimum one reference
        //Education Info
        if ($applicant->references->count() < 2) {
            return back()->withErrors('Please provide at least two Reference.');
        }

        // ✅ 3. Check education info
        if ($applicant->educationInfos->count() < 3) {
            return back()->withErrors('Please provide at least 3 Education Information entries.');
        }


        // ✅ 5. Required attachments (rules in attachment_requirements table)
        if ($error = $this->missingAttachmentsError($applicant)) {
            return back()->withErrors($error);
        }

        // ✅ Mark applicant as finally submitted
        $applicant->final_submit = 1;
        $applicant->save();

        return redirect()->route('home')->with('success', 'Your application has been finally submitted.');

        return back()->with('success', 'Your application has been finally submitted.');
    }

    /** Lists every required attachment type the applicant has not uploaded yet, or null if complete. */
    private function missingAttachmentsError(Applicant $applicant): ?string
    {
        $uploaded = $applicant->attachments->pluck('attachment_type_id')->map(fn ($id) => (int) $id)->all();
        $missing  = array_diff(AttachmentRequirement::requiredTypeIdsFor($applicant), $uploaded);

        if (empty($missing)) {
            return null;
        }

        $titles = AttachmentType::whereIn('id', $missing)->orderBy('id')->pluck('title')->map(fn ($t) => trim($t));
        return 'Please upload the following required document(s): ' . $titles->implode('; ') . '.';
    }
}
