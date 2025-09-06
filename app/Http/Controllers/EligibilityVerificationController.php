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

        // Business rule: page opens only if payment_status=1 AND applicationtype_id=1
        // (If your column is literally `application_type_id`, change the property name below.)
        if (auth()->user()->user_type === 'applicant') {
            if (!($applicant->payment_status == 1 && $applicant->applicationtype_id == 2)) {
                return back()->withErrors('This page is available only for paid Admission applications.');
                // Or: abort(403, 'This page is available only for paid Admission applications.');
            }
        }


        // 🔒 Deadline check from settings
        $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)

        if ($setting && $setting->eligibility_last_date) {
            $deadline = Carbon::parse($setting->eligibility_last_date)->endOfDay();

            if (now()->gt($deadline)) {
                // You can include the date to be clear
                return back()->withErrors('Application date is over. Deadline was: '.$deadline->toDateString());
            }
        }else{
            return back()->withErrors('Setting Table Data Not Found,Contact With ICT-CELL');
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
