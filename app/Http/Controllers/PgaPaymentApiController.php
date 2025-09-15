<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

// Models
use App\Models\Applicant;
use App\Models\Applicationtype;
use App\Models\Setting;

class PgaPaymentApiController extends Controller
{
    //
    public function bkashPull(Request $request)
    {

        // 1) Validate parameters
        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code"    => 406,
                "status"  => "error",
                "msg"     => "Mandatory field missing",
                "details" => $validator->errors(),
            ]);
        }

        $authkey  = $request->input('authkey');
        $examRoll = $request->input('exam_roll');
        $today    = Carbon::today();

        // 2) Authentication check (fixed md5 hash)
        //for pga authkey is: UXZPQ6OEJOL2J5J8P44E6D9CJ2HS
        if (md5($authkey) !== 'ea737681fab0183ca42dca52400f20bf') {
            return response()->json([
                "code"    => 403,
                "status"  => "error",
                "msg"     => "Access Denied!",
                "details" => "Authentication failed",
            ]);
        }

        try {


            // 3) Find applicant
            $applicant = Applicant::where('roll', $examRoll)->first();
            if (!$applicant) {
                return response()->json([
                    "code"   => 404,
                    "status" => "ok",
                    "msg"    => "exam_roll not found",
                ]);
            }

            // 4) Get application type + fee
            $applicationTypeId = (int) $applicant->applicationtype_id;
            $applicationType   = Applicationtype::find($applicationTypeId);

            if (!$applicationType) {
                return response()->json([
                    "code"   => 404,
                    "status" => "ok",
                    "msg"    => "application type not found",
                ]);
            }

            // 5) Settings
            $settings = Setting::orderByDesc('id')->first();
            if (!$settings) {
                return response()->json([
                    "code"  => 500,
                    "status"=> "error",
                    "msg"   => "Server Error",
                ]);
            }

            // 6) Deadline checks
            if ($applicationTypeId === 1) {
                // Admission
                if (!$settings->end_date || $today->gt(Carbon::parse($settings->end_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "ok",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            } elseif ($applicationTypeId === 2) {
                // Eligibility
                if (!$settings->eligibility_last_date || $today->gt(Carbon::parse($settings->eligibility_last_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "ok",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            }

            // 7) Already paid check
            if ($applicant->payment_status == 1) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // Redundant safety check against payments table
            $alreadyPaid = Payment::where('applicant_id', $applicant->id)->exists();

            if ($alreadyPaid) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // ✅ Test case override
            if (in_array($examRoll, ['100001', '200001'])) {
                return response()->json([
                    "code"           => 200,
                    "status"         => "ok",
                    "msg"            => "Success",
                    "applicant_name" => "Test Applicant",
                    "exam_roll"      => $examRoll,
                    "amount"         => "5",
                ]);
            }

            // 8) Success → unpaid applicant
            $fee  = (float) $applicationType->fee;
            $name = $applicant->user->name;

            return response()->json([
                "code"           => 200,
                "status"         => "ok",
                "msg"            => "Success",
                "applicant_name" => $name ?? "Unknown",
                "exam_roll"      => $applicant->roll,
                "amount"         => (string) $fee,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "code"   => 500,
                "status" => "error",
                "msg"    => "Server Error",
            ]);
        }
    }


    public function bkashPush(Request $request)
    {
        // 1) Validate parameters
        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
            'txID'      => 'required|string',
            'txdate'    => 'required|date',
            'amount'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code"    => 406,
                "status"  => "error",
                "msg"     => "Mandatory Field missing",
                "details" => $validator->errors(),
            ]);
        }

        $authkey   = $request->input('authkey');
        $examRoll  = $request->input('exam_roll');
        $txID      = $request->input('txID');
        $txDate    = $request->input('txdate');
        $amount    = (float) $request->input('amount');
        $today     = Carbon::today();

        // 2) Auth check (hash of G66WG6OEJOL2J5J8P44E6D9CJ2HS)
        if (md5($authkey) !== 'ea737681fab0183ca42dca52400f20bf') {
            return response()->json([
                "code"    => 403,
                "status"  => "error",
                "msg"     => "Access Denied!",
                "details" => "Authentication failed",
            ]);
        }

        try {

            // 3) Find applicant
            $applicant = Applicant::where('roll', $examRoll)->first();
            if (!$applicant) {
                return response()->json([
                    "code"   => 404,
                    "status" => "error",
                    "msg"    => "exam_roll not found",
                ]);
            }



            // 4) Get application type + fee
            $applicationType = Applicationtype::find($applicant->applicationtype_id);
            if (!$applicationType) {
                return response()->json([
                    "code"   => 404,
                    "status" => "error",
                    "msg"    => "application type not found",
                ]);
            }

            // 5) Deadline check
            $settings = Setting::orderByDesc('id')->first();
            if ($applicationType->id === 1) {
                if (!$settings->end_date || $today->gt(Carbon::parse($settings->end_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "error",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            } elseif ($applicationType->id === 2) {
                if (!$settings->eligibility_last_date || $today->gt(Carbon::parse($settings->eligibility_last_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "error",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            }

            // 6) Already paid check
            if ($applicant->payment_status == 1) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }
            $alreadyPaid = Payment::where('applicant_id', $applicant->id)->exists();
            if ($alreadyPaid) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // 7) Check trxid duplicate
            $trxExists = Payment::where('trxid', $txID)->exists();
            if ($trxExists) {
                return response()->json([
                    "code"   => 435,
                    "status" => "error",
                    "msg"    => "Transaction ID already exists",
                ]);
            }


            // ✅ Test case override
            if (in_array($examRoll, ['100001', '200001'])) {
                if ($amount != 5) {
                    return response()->json([
                        "code"   => 438,
                        "status" => "error",
                        "msg"    => "Amount must be exactly 5 in test mode",
                    ]);
                }

                // 9) Mark applicant as paid
                $applicant->payment_status = 1;
                $applicant->save();

                // Simulate insert (skip DB if you want)
                Payment::create([
                    'trxid'        => $txID,
                    'paymentdate'  => $txDate,
                    'amount'       => $amount,
                    'method'       => 'Online',
                    'bankname'     => 'Bkash',
                    'applicant_id' => $applicant->id,
                ]);

                return response()->json([
                    "code"   => 200,
                    "status" => "ok",
                    "msg"    => "Success (Test Mode)",
                ]);
            }

            // 8) Amount check
            $fee = (float) $applicationType->fee;
            if ($amount < $fee) {
                return response()->json([
                    "code"    => 438,
                    "status"  => "error",
                    "msg"     => "The amount is not sufficient.",
                    "details" => ["amount" => ["The amount is not sufficient."]],
                ]);
            } elseif ($amount > $fee) {
                return response()->json([
                    "code"    => 438,
                    "status"  => "error",
                    "msg"     => "The amount exceeds the required fee.",
                    "details" => ["amount" => ["The amount exceeds the required fee."]],
                ]);
            }

            // 9) Mark applicant as paid
            $applicant->payment_status = 1;
            $applicant->save();

            // 10) Insert into payments
            Payment::create([
                'trxid'        => $txID,
                'paymentdate'  => $txDate,
                'amount'       => $amount,
                'method'       => 'Online',
                'bankname'     => 'Bkash',
                'applicant_id' => $applicant->id,
            ]);

            return response()->json([
                "code"   => 200,
                "status" => "ok",
                "msg"    => "Success",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "code"   => 500,
                "status" => "error",
                "msg"    => "Server Error",
            ]);
        }
    }


    public function agraniPull(Request $request)
    {

        // 1) Validate parameters
        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code"    => 406,
                "status"  => "error",
                "msg"     => "Mandatory field missing",
                "details" => $validator->errors(),
            ]);
        }

        $authkey  = $request->input('authkey');
        $examRoll = $request->input('exam_roll');
        $today    = Carbon::today();

        // 2) Authentication check (fixed md5 hash)
        //for pga authkey is: UXZPQ6OEJOL2J5J8P44E6D9CJ2HS
        if (md5($authkey) !== '722d04592d25056bfb6172324bfbd521') {
            return response()->json([
                "code"    => 403,
                "status"  => "error",
                "msg"     => "Access Denied!",
                "details" => "Authentication failed",
            ]);
        }

        try {


            // 3) Find applicant
            $applicant = Applicant::where('roll', $examRoll)->first();
            if (!$applicant) {
                return response()->json([
                    "code"   => 404,
                    "status" => "ok",
                    "msg"    => "exam_roll not found",
                ]);
            }

            // 4) Get application type + fee
            $applicationTypeId = (int) $applicant->applicationtype_id;
            $applicationType   = Applicationtype::find($applicationTypeId);

            if (!$applicationType) {
                return response()->json([
                    "code"   => 404,
                    "status" => "ok",
                    "msg"    => "application type not found",
                ]);
            }

            // 5) Settings
            $settings = Setting::orderByDesc('id')->first();
            if (!$settings) {
                return response()->json([
                    "code"  => 500,
                    "status"=> "error",
                    "msg"   => "Server Error",
                ]);
            }

            // 6) Deadline checks
            if ($applicationTypeId === 1) {
                // Admission
                if (!$settings->end_date || $today->gt(Carbon::parse($settings->end_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "ok",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            } elseif ($applicationTypeId === 2) {
                // Eligibility
                if (!$settings->eligibility_last_date || $today->gt(Carbon::parse($settings->eligibility_last_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "ok",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            }

            // 7) Already paid check
            if ($applicant->payment_status == 1) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // Redundant safety check against payments table
            $alreadyPaid = Payment::where('applicant_id', $applicant->id)->exists();

            if ($alreadyPaid) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // ✅ Test case override
            if (in_array($examRoll, ['100001', '200001'])) {
                return response()->json([
                    "code"           => 200,
                    "status"         => "ok",
                    "msg"            => "Success",
                    "applicant_name" => "Test Applicant",
                    "exam_roll"      => $examRoll,
                    "amount"         => "5",
                ]);
            }

            // 8) Success → unpaid applicant
            $fee  = (float) $applicationType->fee;
            $name = $applicant->user->name;

            return response()->json([
                "code"           => 200,
                "status"         => "ok",
                "msg"            => "Success",
                "applicant_name" => $name ?? "Unknown",
                "exam_roll"      => $applicant->roll,
                "amount"         => (string) $fee,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "code"   => 500,
                "status" => "error",
                "msg"    => "Server Error",
            ]);
        }
    }


    public function agraniPush(Request $request)
    {
        // 1) Validate parameters
        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
            'txID'      => 'required|string',
            'txdate'    => 'required|date',
            'amount'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code"    => 406,
                "status"  => "error",
                "msg"     => "Mandatory Field missing",
                "details" => $validator->errors(),
            ]);
        }

        $authkey   = $request->input('authkey');
        $examRoll  = $request->input('exam_roll');
        $txID      = $request->input('txID');
        $txDate    = $request->input('txdate');
        $amount    = (float) $request->input('amount');
        $today     = Carbon::today();

        // 2) Auth check (hash of G66WG6OEJOL2J5J8P44E6D9CJ2HS)
        if (md5($authkey) !== '722d04592d25056bfb6172324bfbd521') {
            return response()->json([
                "code"    => 403,
                "status"  => "error",
                "msg"     => "Access Denied!",
                "details" => "Authentication failed",
            ]);
        }

        try {

            // 3) Find applicant
            $applicant = Applicant::where('roll', $examRoll)->first();
            if (!$applicant) {
                return response()->json([
                    "code"   => 404,
                    "status" => "error",
                    "msg"    => "exam_roll not found",
                ]);
            }



            // 4) Get application type + fee
            $applicationType = Applicationtype::find($applicant->applicationtype_id);
            if (!$applicationType) {
                return response()->json([
                    "code"   => 404,
                    "status" => "error",
                    "msg"    => "application type not found",
                ]);
            }

            // 5) Deadline check
            $settings = Setting::orderByDesc('id')->first();
            if ($applicationType->id === 1) {
                if (!$settings->end_date || $today->gt(Carbon::parse($settings->end_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "error",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            } elseif ($applicationType->id === 2) {
                if (!$settings->eligibility_last_date || $today->gt(Carbon::parse($settings->eligibility_last_date))) {
                    return response()->json([
                        "code"   => 437,
                        "status" => "error",
                        "msg"    => "Payment deadline has passed",
                    ]);
                }
            }

            // 6) Already paid check
            if ($applicant->payment_status == 1) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }
            $alreadyPaid = Payment::where('applicant_id', $applicant->id)->exists();
            if ($alreadyPaid) {
                return response()->json([
                    "code"   => 436,
                    "status" => "error",
                    "msg"    => "Already paid",
                ]);
            }

            // 7) Check trxid duplicate
            $trxExists = Payment::where('trxid', $txID)->exists();
            if ($trxExists) {
                return response()->json([
                    "code"   => 435,
                    "status" => "error",
                    "msg"    => "Transaction ID already exists",
                ]);
            }


            // ✅ Test case override
            if (in_array($examRoll, ['100001', '200001'])) {
                if ($amount != 5) {
                    return response()->json([
                        "code"   => 438,
                        "status" => "error",
                        "msg"    => "Amount must be exactly 5 in test mode",
                    ]);
                }

                // 9) Mark applicant as paid
                $applicant->payment_status = 1;
                $applicant->save();

                // Simulate insert (skip DB if you want)
                Payment::create([
                    'trxid'        => $txID,
                    'paymentdate'  => $txDate,
                    'amount'       => $amount,
                    'method'       => 'Online',
                    'bankname'     => 'Agrani',
                    'applicant_id' => $applicant->id,
                ]);

                return response()->json([
                    "code"   => 200,
                    "status" => "ok",
                    "msg"    => "Success (Test Mode)",
                ]);
            }

            // 8) Amount check
            $fee = (float) $applicationType->fee;
            if ($amount < $fee) {
                return response()->json([
                    "code"    => 438,
                    "status"  => "error",
                    "msg"     => "The amount is not sufficient.",
                    "details" => ["amount" => ["The amount is not sufficient."]],
                ]);
            } elseif ($amount > $fee) {
                return response()->json([
                    "code"    => 438,
                    "status"  => "error",
                    "msg"     => "The amount exceeds the required fee.",
                    "details" => ["amount" => ["The amount exceeds the required fee."]],
                ]);
            }

            // 9) Mark applicant as paid
            $applicant->payment_status = 1;
            $applicant->save();

            // 10) Insert into payments
            Payment::create([
                'trxid'        => $txID,
                'paymentdate'  => $txDate,
                'amount'       => $amount,
                'method'       => 'Online',
                'bankname'     => 'Agrani',
                'applicant_id' => $applicant->id,
            ]);

            return response()->json([
                "code"   => 200,
                "status" => "ok",
                "msg"    => "Success",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "code"   => 500,
                "status" => "error",
                "msg"    => "Server Error",
            ]);
        }
    }

}
