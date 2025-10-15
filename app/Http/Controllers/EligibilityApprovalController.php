<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EligibilityApprovalController extends Controller
{
    public function toggle(Request $request, Applicant $applicant)
    {
        // role-based guard: heads can only act on their own department
        $user = auth()->user();

        if ($user->user_type != 'head') {
                abort(403, 'You are not allowed to update this applicant.');
        }
        if ($user->user_type === 'head') {
            if (!$user->department_id || $applicant->department_id !== $user->department_id) {
                abort(403, 'You are not allowed to update this applicant.');
            }
        }

        // optional: only allow toggle if final_submit + payment done
        if ($applicant->final_submit != 1 || $applicant->payment_status != 1) {
            return response()->json(['ok' => false, 'msg' => 'Applicant is not eligible to be approved yet.'], 422);
        }

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

        // toggle value
        $applicant->eligibility_approve = $applicant->eligibility_approve ? 0 : 1;
        $applicant->save();

        $approved = (int) $applicant->eligibility_approve === 1;

        return response()->json([
            'ok'       => true,
            'approved' => $approved,
            'label'    => $approved ? 'Undo' : 'Approve Eligibility',
            'class'    => $approved ? 'btn-danger' : 'btn-success',
            'id'       => $applicant->id,
        ]);
    }
}
