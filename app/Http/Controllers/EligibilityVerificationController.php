<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttachmentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        /* // Business rule: page opens only if payment_status=1 AND applicationtype_id=1
         // (If your column is literally `application_type_id`, change the property name below.)
         if (auth()->user()->user_type === 'applicant') {
             if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 2)) {
                 return back()->withErrors('This page is available only for paid Admission applications.');
                 // Or: abort(403, 'This page is available only for paid Admission applications.');
             }
         }*/

        // Business rule: paid Admission applications only
        if (auth()->user()->user_type === 'applicant') {
            // ✅ If payment record not found or payment_status != 2 → block
            if (!$applicant->payment || $applicant->payment_status != 1 || $applicant->applicationtype_id != 2) {
                return back()->withErrors('You must complete payment first before accessing this page.');
            }
        }


        // 🔒 Deadline check: APPLICANTS ONLY (admins/heads skip)
        if (auth()->user()->user_type === 'applicant') {

            // ✅ Bypass deadline ONLY if:
            //    1) eligibility not approved yet
            //    2) payment completed
            //    3) not final submitted
            $canBypassDeadline =
                ((int)$applicant->eligibility_approve === 0) &&
                ((int)$applicant->payment_status === 1) &&
                ((int)$applicant->final_submit === 0);

            if (!$canBypassDeadline) {
                // 🛑 Everyone else → enforce deadline
                $setting = Setting::query()->latest('id')->first();

                if (!$setting || !$setting->eligibility_last_date) {
                    return back()->withErrors('Setting Table Data Not Found, Contact ICT-CELL');
                }

                $deadline = \Carbon\Carbon::parse($setting->eligibility_last_date)->endOfDay();

                if (now()->gt($deadline)) {
                    return back()->withErrors('Application date is over. Deadline was: ' . $deadline->toDateString());
                }
            }
        }


        return view('applicant.eligibility_master_form', [
            'applicant'          => $applicant,
            'basicInfo'          => $applicant->basicInfo,
            'eligibilityDegree' => $applicant->eligibilityDegree,
            'educationInfos'     => $applicant->educationInfos,
            'attachments'        => $applicant->attachments,
            'attachmentTypes'    => AttachmentType::orderBy('id')->get(),
        ]);
    }
}