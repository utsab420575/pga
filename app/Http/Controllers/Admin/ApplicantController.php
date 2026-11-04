<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Applicationtype;
use App\Models\Degree;
use App\Models\Department;
use App\Models\Studenttype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApplicantController extends Controller
{
    const UNIVERSITY_TYPES = [
        'public'              => 'Public',
        'private'             => 'Private',
        'previously_eligible' => 'Previously Eligible',
    ];

    public function index(Request $request)
    {
        $search  = trim((string) $request->get('search'));
        $type    = $request->get('type');
        $payment = $request->get('payment');

        $items = Applicant::with(['user', 'department', 'degree', 'studenttype', 'applicationtype'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    if (ctype_digit($search)) {
                        $q->where('id', (int) $search)->orWhere('roll', 'like', "%{$search}%");
                    } else {
                        $q->where('roll', 'like', "%{$search}%");
                    }
                    $q->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%")
                           ->orWhere('phone', 'like', "%{$search}%");
                    });
                });
            })
            ->when(in_array($type, ['1', '2'], true), fn ($q) => $q->where('applicationtype_id', (int) $type))
            ->when(in_array($payment, ['0', '1'], true), fn ($q) => $q->where('payment_status', (int) $payment))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.settings.applicants.index', [
            'items'             => $items,
            'search'            => $search,
            'type'              => $type,
            'payment'           => $payment,
            'departments'       => Department::orderBy('short_name')->get(['id', 'short_name', 'full_name']),
            'degrees'           => Degree::orderBy('degree_name')->get(['id', 'degree_name']),
            'studenttypes'      => Studenttype::orderBy('id')->get(['id', 'type']),
            'applicationtypes'  => Applicationtype::orderBy('id')->get(['id', 'type']),
            'universityTypes'   => self::UNIVERSITY_TYPES,
            'departmentDegrees' => DB::table('department_degree')->get(['department_id', 'degree_id'])
                ->groupBy('department_id')
                ->map(fn ($rows) => $rows->pluck('degree_id')->map(fn ($id) => (int) $id)->values()),
        ]);
    }

    public function update(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        $data = $request->validate([
            'roll'                => ['required', 'string', 'max:255', Rule::unique('applicants', 'roll')->ignore($applicant->id)],
            'applicationtype_id'  => 'required|exists:applicationtypes,id',
            'department_id'       => 'required|exists:departments,id',
            'degree_id'           => 'required|exists:degrees,id',
            'studenttype_id'      => 'required|exists:studenttypes,id',
            'university_type'     => ['nullable', Rule::in(array_keys(self::UNIVERSITY_TYPES))],
            'payment_status'      => 'required|in:0,1',
            'final_submit'        => 'required|in:0,1',
            'edit_per'            => 'required|in:0,1',
            'eligibility_approve' => 'required|in:0,1',
            'admission_approve'   => 'required|in:0,1',
        ]);
        $data['university_type'] = ($data['university_type'] ?? null) ?: null;

        $this->checkBusinessRules($applicant, $data);

        $applicant->fill($data);

        if (!$applicant->isDirty()) {
            return response()->json(['ok' => true, 'msg' => 'No changes to save.']);
        }

        $applicant->save(); // LogsApplicantActivity records who changed what

        return response()->json([
            'ok'  => true,
            'msg' => "Applicant Roll {$applicant->roll} (ID #{$applicant->id}) updated successfully.",
        ]);
    }

    /**
     * Same rules applicants follow in edit_application_submit. Only checked when the
     * values change, so an old record can still have its other fields corrected.
     */
    protected function checkBusinessRules(Applicant $applicant, array $data): void
    {
        $deptChanged   = (int) $data['department_id'] !== (int) $applicant->department_id;
        $degreeChanged = (int) $data['degree_id'] !== (int) $applicant->degree_id;
        $typeChanged   = (int) $data['applicationtype_id'] !== (int) $applicant->applicationtype_id;

        if (($deptChanged || $degreeChanged) && !DB::table('department_degree')
                ->where('department_id', $data['department_id'])
                ->where('degree_id', $data['degree_id'])
                ->exists()) {
            throw ValidationException::withMessages([
                'degree_id' => 'This degree is not offered by the selected department.',
            ]);
        }

        if (($deptChanged || $typeChanged) && Applicant::where('user_id', $applicant->user_id)
                ->where('department_id', $data['department_id'])
                ->where('applicationtype_id', $data['applicationtype_id'])
                ->where('id', '!=', $applicant->id)
                ->exists()) {
            throw ValidationException::withMessages([
                'department_id' => 'This user already has an application of this type in the selected department.',
            ]);
        }
    }
}
