<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
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
            'passport_no',
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



        //Attachments
        // ✅ 5. Check attachments by type
        $attachments = $applicant->attachments;

        // Count attachments per type
        $typeCounts = $attachments->groupBy('attachment_type_id')->map->count();

        // --- Type 1: Recent photograph
        if (($typeCounts[1] ?? 0) < 1) {
            return back()->withErrors('You must upload at least 1 recent passport-size photograph.');
        }

        // --- Type 2: Signature
        if (($typeCounts[2] ?? 0) < 1) {
            return back()->withErrors('You must upload at least 1 signature.');
        }

        // --- Type 3: SSC Certificate
        if (($typeCounts[3] ?? 0) < 1) {
            return back()->withErrors('You must upload your SSC or equivalent certificate.');
        }

        // --- Type 4: HSC/Diploma Certificate
        if (($typeCounts[4] ?? 0) < 1) {
            return back()->withErrors('You must upload your HSC or equivalent / Diploma certificate.');
        }

        // --- Type 5: BSc Certificate
        if (($typeCounts[5] ?? 0) < 1) {
            return back()->withErrors('You must upload your BSc certificate.');
        }

        // --- Type 7: SSC Mark Sheet
        if (($typeCounts[7] ?? 0) < 1) {
            return back()->withErrors('You must upload your SSC or equivalent mark sheet/grade sheet/transcript.');
        }

        // --- Type 8: HSC/Diploma Mark Sheet
        if (($typeCounts[8] ?? 0) < 1) {
            return back()->withErrors('You must upload your HSC or equivalent / Diploma mark sheet/grade sheet/transcript.');
        }

        // --- Type 9: MSc Mark Sheet
        if (($typeCounts[9] ?? 0) < 1) {
            return back()->withErrors('You must upload your BSC or equivalent mark sheet/grade sheet/transcript.');
        }



        // --- Type 14: Detailed Syllabus
        if (($typeCounts[13] ?? 0) < 1) {
            return back()->withErrors('You must upload the detailed syllabus mentioning all course contents.');
        }

        // --- Type 13: NID/Birth Certificate
        if (($typeCounts[14] ?? 0) < 1) {
            return back()->withErrors('You must upload your National ID card or Birth Certificate.');
        }








        // ✅ Mark applicant as finally submitted
        $applicant->final_submit = 1;
        $applicant->save();

        return back()->with('success', 'Your application has been finally submitted.');
    }

    public function submitApplication(Request $request, Applicant $applicant)
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


        //Attachments
        // ✅ 5. Check attachments by type
        $attachments = $applicant->attachments;

        // Count attachments per type
        $typeCounts = $attachments->groupBy('attachment_type_id')->map->count();

        // --- Type 1: Recent photograph
        if (($typeCounts[1] ?? 0) < 1) {
            return back()->withErrors('You must upload at least 1 recent passport-size photograph.');
        }

        // --- Type 2: Signature
        if (($typeCounts[2] ?? 0) < 1) {
            return back()->withErrors('You must upload at least 1 signature.');
        }

        // --- Type 3: SSC Certificate
        if (($typeCounts[3] ?? 0) < 1) {
            return back()->withErrors('You must upload your SSC or equivalent certificate.');
        }

        // --- Type 4: HSC/Diploma Certificate
        if (($typeCounts[4] ?? 0) < 1) {
            return back()->withErrors('You must upload your HSC or equivalent / Diploma certificate.');
        }

        // --- Type 5: BSc Certificate
        if (($typeCounts[5] ?? 0) < 1) {
            return back()->withErrors('You must upload your BSc certificate.');
        }

        // --- Type 7: SSC Mark Sheet
        if (($typeCounts[7] ?? 0) < 1) {
            return back()->withErrors('You must upload your SSC or equivalent mark sheet/grade sheet/transcript.');
        }

        // --- Type 8: HSC/Diploma Mark Sheet
        if (($typeCounts[8] ?? 0) < 1) {
            return back()->withErrors('You must upload your HSC or equivalent / Diploma mark sheet/grade sheet/transcript.');
        }

        // --- Type 9: BSC Mark Sheet
        if (($typeCounts[9] ?? 0) < 1) {
            return back()->withErrors('You must upload your BSC or equivalent mark sheet/grade sheet/transcript.');
        }

        // --- Type 9: Testimonial
        if (($typeCounts[11] ?? 0) < 1) {
            return back()->withErrors('You must upload your Testimonial.');
        }






        // ✅ Mark applicant as finally submitted
        $applicant->final_submit = 1;
        $applicant->save();

        return back()->with('success', 'Your application has been finally submitted.');
    }
}
