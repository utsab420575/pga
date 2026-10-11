<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AttachmentRequirement;
use App\Models\AttachmentType;
use Illuminate\Http\Request;

class FinalSubmitController extends Controller
{
    /**
     * Display a listing of applicants with their final submission and data fillup status.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $status = $request->get('status'); // 'all', '1', '0'
        $type   = $request->get('type');   // 'all', '1', '2'

        $query = Applicant::with([
            'user',
            'department',
            'degree',
            'applicationtype',
            'basicInfo',
            'educationInfos',
            'references',
            'attachments',
            'eligibilityDegree',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search)
                      ->orWhere('roll', 'like', "%{$search}%");
                } else {
                    $q->where('roll', 'like', "%{$search}%");
                }
                $q->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        if ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('final_submit', (int) $status);
        }

        if ($type !== null && $type !== '' && $type !== 'all') {
            $query->where('applicationtype_id', (int) $type);
        }

        $items = $query->latest('id')
            ->paginate(25)
            ->withQueryString();

        // Check data fillup completeness for each row according to its application type
        $items->getCollection()->transform(function ($applicant) {
            $applicant->fillup_errors = $this->checkApplicantData($applicant);
            $applicant->is_fillup_complete = empty($applicant->fillup_errors);
            return $applicant;
        });

        return view('admin.settings.final_submit.index', compact('items', 'search', 'status', 'type'));
    }

    /**
     * Undo final submission for an applicant (final_submit = 0).
     * This re-enables form editing for the applicant.
     */
    public function undo(Applicant $applicant)
    {
        $applicant->final_submit = 0;
        $applicant->save();

        $identifier = $applicant->roll ? "Roll: {$applicant->roll} (ID #{$applicant->id})" : "ID #{$applicant->id}";

        return redirect()->back()->with(
            'success',
            "Final submission undone for Applicant {$identifier}. The applicant can now edit their application again."
        );
    }

    /**
     * Make final submission by admin after validating data fillup.
     * Follows the validation checks of /final-submit/eligibility and /final-submit/application.
     */
    public function submit(Request $request, Applicant $applicant)
    {
        $force = $request->boolean('force');
        $errors = $this->checkApplicantData($applicant);

        if (!empty($errors) && !$force) {
            return redirect()->back()->with(
                'error',
                "Cannot make final submit for Applicant ID #{$applicant->id}. Missing required data: " . implode(' | ', $errors)
            );
        }

        $applicant->final_submit = 1;
        $applicant->save();

        $identifier = $applicant->roll ? "Roll: {$applicant->roll} (ID #{$applicant->id})" : "ID #{$applicant->id}";
        $extraMsg   = ($force && !empty($errors)) ? " (forced by admin override)" : "";

        return redirect()->back()->with(
            'success',
            "Applicant {$identifier} has been successfully marked as Final Submitted{$extraMsg}."
        );
    }

    /**
     * Validate data fillup for an applicant according to application type.
     */
    public function checkApplicantData(Applicant $applicant): array
    {
        if ((int) $applicant->applicationtype_id === 2) {
            return $this->checkEligibilityData($applicant);
        }

        return $this->checkApplicationData($applicant);
    }

    /**
     * Validation rules matching FinalSubmitController::submitEligibility
     */
    protected function checkEligibilityData(Applicant $applicant): array
    {
        $errors = [];

        // 1. Basic info
        $basic = $applicant->basicInfo;
        if (!$basic) {
            $errors[] = 'Basic Information is not completed.';
            return $errors;
        }

        $requiredBasicFields = [
            'full_name_block_letter' => 'Full Name (Block Letters)',
            'f_name'                 => "Father's Name",
            'm_name'                 => "Mother's Name",
            'per_address'            => 'Permanent Address',
            'pre_address'            => 'Present Address',
            'dob'                    => 'Date of Birth',
            'nationality'            => 'Nationality',
            'nid'                    => 'NID',
            'religion'               => 'Religion',
            'gender'                 => 'Gender',
            'marital_status'         => 'Marital Status',
        ];

        foreach ($requiredBasicFields as $field => $label) {
            if (empty($basic->$field)) {
                $errors[] = "Basic Info '{$label}' is required.";
            }
        }

        // 2. Eligibility degree
        $eligibility = $applicant->eligibilityDegree;
        if (!$eligibility) {
            $errors[] = 'Eligibility Degree information is missing.';
        } else {
            $requiredEligibilityFields = [
                'degree'          => 'Degree',
                'institute'       => 'Institute',
                'country'         => 'Country',
                'cgpa'            => 'CGPA',
                'date_graduation' => 'Date of Graduation',
                'duration'        => 'Duration',
                'total_credit'    => 'Total Credit',
                'mode'            => 'Mode of Study',
                'uni_status'      => 'University Status',
                'url'             => 'University URL',
            ];

            foreach ($requiredEligibilityFields as $field => $label) {
                if (empty($eligibility->$field)) {
                    $errors[] = "Eligibility Degree '{$label}' is required.";
                }
            }
        }

        // 3. Education Info (at least 3)
        if ($applicant->educationInfos->isEmpty()) {
            $errors[] = 'Education Information is missing.';
        } elseif ($applicant->educationInfos->count() < 3) {
            $errors[] = 'At least 3 Education Information entries are required (has ' . $applicant->educationInfos->count() . ').';
        }

        // 4. Required Attachments
        if ($attError = $this->missingAttachmentsError($applicant)) {
            $errors[] = $attError;
        }

        return $errors;
    }

    /**
     * Validation rules matching FinalSubmitController::submitApplication
     */
    protected function checkApplicationData(Applicant $applicant): array
    {
        $errors = [];

        // 1. Basic info
        $basic = $applicant->basicInfo;
        if (!$basic) {
            $errors[] = 'Basic Information is not completed.';
            return $errors;
        }

        $requiredBasicFields = [
            'full_name'      => 'Full Name',
            'bn_name'        => 'Name in Bangla',
            'f_name'         => "Father's Name",
            'm_name'         => "Mother's Name",
            'g_income'       => 'Guardian Income',
            'per_address'    => 'Permanent Address',
            'pre_address'    => 'Present Address',
            'dob'            => 'Date of Birth',
            'nationality'    => 'Nationality',
            'nid'            => 'NID',
            'religion'       => 'Religion',
            'gender'         => 'Gender',
            'marital_status' => 'Marital Status',
        ];

        foreach ($requiredBasicFields as $field => $label) {
            if (empty($basic->$field)) {
                $errors[] = "Basic Info '{$label}' is required.";
            }
        }

        // 2. Education info (at least 3)
        if ($applicant->educationInfos->isEmpty()) {
            $errors[] = 'Education Information is missing.';
        } elseif ($applicant->educationInfos->count() < 3) {
            $errors[] = 'At least 3 Education Information entries are required (has ' . $applicant->educationInfos->count() . ').';
        }

        // 3. References (at least 2)
        if ($applicant->references->count() < 2) {
            $errors[] = 'At least 2 References are required (has ' . $applicant->references->count() . ').';
        }

        // 4. Required Attachments
        if ($attError = $this->missingAttachmentsError($applicant)) {
            $errors[] = $attError;
        }

        return $errors;
    }

    /**
     * Lists missing required attachment types for the applicant.
     */
    protected function missingAttachmentsError(Applicant $applicant): ?string
    {
        $uploaded = $applicant->attachments->pluck('attachment_type_id')->map(fn ($id) => (int) $id)->all();
        $missing  = array_diff(AttachmentRequirement::requiredTypeIdsFor($applicant), $uploaded);

        if (empty($missing)) {
            return null;
        }

        $titles = AttachmentType::whereIn('id', $missing)->orderBy('id')->pluck('title')->map(fn ($t) => trim($t));
        return 'Missing required attachment(s): ' . $titles->implode('; ') . '.';
    }
}
