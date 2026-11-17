<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Applicationtype;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * PGA - Shared Pull / Push for all payment gateways.
 *
 * Copy to: app/Http/Controllers/GatewayPaymentController.php
 *
 * Called by the CENTRAL payment system, e.g.
 *   POST /bkash_pull, /agrani_pull, /upay_pull  → paymentPull()
 *   POST /bkash_push, /agrani_push, /upay_push  → paymentPush()
 *
 * Business rules and response messages are the same as
 * PgaPaymentApiController bkashPull / bkashPush (agrani* were identical
 * except bankname and an active test mode). Gateway-specific values
 * (bankname, log prefix, test rolls) come from config/payment_gateways.php.
 */
class GatewayPaymentController extends Controller
{
    // -------------------------------------------------------
    // Shared PULL
    // Central asks: "how much does this applicant owe?"
    // -------------------------------------------------------
    public function paymentPull(Request $request)
    {
        // Gateway from the route default, not from the request
        $gateway   = $this->gatewayConfig($request->route('gateway'));
        $logPrefix = $gateway['log'] ?? 'Gateway';

        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 406,
                'status'  => 'error',
                'msg'     => 'Mandatory field missing',
                'details' => $validator->errors(),
            ]);
        }

        $examRoll = $request->input('exam_roll');
        $today    = Carbon::today();

        if (!$this->authenticate($gateway, $request->input('authkey'))) {
            return $this->accessDenied();
        }

        try {
            $applicant = Applicant::where('roll', $examRoll)->first();

            // Central moves to the next child system only on code 404
            if (!$applicant) {
                return response()->json([
                    'code'   => 404,
                    'status' => 'ok',
                    'msg'    => 'exam_roll not found',
                ]);
            }

            // 1 = Admission, 2 = Eligibility; the fee comes from the type
            $applicationTypeId = (int) $applicant->applicationtype_id;
            $applicationType   = Applicationtype::find($applicationTypeId);

            if (!$applicationType) {
                return response()->json([
                    'code'   => 500,
                    'status' => 'ok',
                    'msg'    => 'application type not found',
                ]);
            }

            $settings = Setting::orderByDesc('id')->first();

            if (!$settings) {
                return response()->json([
                    'code'   => 500,
                    'status' => 'error',
                    'msg'    => 'Server Error',
                ]);
            }

            if ($this->deadlinePassed($applicationTypeId, $settings, $today)) {
                return response()->json([
                    'code'   => 437,
                    'status' => 'ok',
                    'msg'    => 'Payment deadline has passed',
                ]);
            }

            if ($this->isAlreadyPaid($applicant)) {
                return $this->alreadyPaid();
            }

            // Test mode (only for gateways that list test rolls in config)
            if ($this->isTestRoll($gateway, $examRoll)) {
                return response()->json([
                    'code'           => 200,
                    'status'         => 'ok',
                    'msg'            => 'Success',
                    'applicant_name' => 'Test Applicant',
                    'exam_roll'      => $examRoll,
                    'amount'         => '5',
                ]);
            }

            $fee  = (float) $applicationType->fee;
            $name = $applicant->user->name;

            return response()->json([
                'code'           => 200,
                'status'         => 'ok',
                'msg'            => 'Success',
                'applicant_name' => $name ?? 'Unknown',
                'exam_roll'      => $applicant->roll,
                'amount'         => (string) $fee,
            ]);

        } catch (\Exception $e) {
            Log::error("{$logPrefix} PULL Exception:", [
                'message'   => $e->getMessage(),
                'exam_roll' => $examRoll,
            ]);
            return $this->serverError();
        }
    }

    // -------------------------------------------------------
    // Shared PUSH
    // Central says: "this applicant paid, save it".
    // -------------------------------------------------------
    public function paymentPush(Request $request)
    {
        // Gateway from the route default, not from the request
        $gateway   = $this->gatewayConfig($request->route('gateway'));
        $logPrefix = $gateway['log'] ?? 'Gateway';

        $validator = Validator::make($request->all(), [
            'authkey'   => 'required|string',
            'exam_roll' => 'required|string',
            'txID'      => 'required|string',
            'txdate'    => 'required|date',
            'amount'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 406,
                'status'  => 'error',
                'msg'     => 'Mandatory Field missing',
                'details' => $validator->errors(),
            ]);
        }

        $examRoll = $request->input('exam_roll');
        $txID     = $request->input('txID');
        $txDate   = $request->input('txdate');
        $amount   = (float) $request->input('amount');
        $today    = Carbon::today();

        if (!$this->authenticate($gateway, $request->input('authkey'))) {
            return $this->accessDenied();
        }

        try {
            $applicant = Applicant::where('roll', $examRoll)->first();

            if (!$applicant) {
                return response()->json([
                    'code'   => 404,
                    'status' => 'error',
                    'msg'    => 'exam_roll not found',
                ]);
            }

            $applicationType = Applicationtype::find($applicant->applicationtype_id);

            if (!$applicationType) {
                return response()->json([
                    'code'   => 500,
                    'status' => 'error',
                    'msg'    => 'application type not found',
                ]);
            }

            // Same as old Push: type id taken from the Applicationtype row
            $settings = Setting::orderByDesc('id')->first();

            if ($this->deadlinePassed($applicationType->id, $settings, $today)) {
                return response()->json([
                    'code'   => 437,
                    'status' => 'error',
                    'msg'    => 'Payment deadline has passed',
                ]);
            }

            if ($this->isAlreadyPaid($applicant)) {
                return $this->alreadyPaid();
            }

            if (Payment::where('trxid', $txID)->exists()) {
                return $this->duplicateTx();
            }

            $testMode = $this->isTestRoll($gateway, $examRoll);

            if ($testMode) {
                // Test rolls must pay exactly 5
                if ($amount != 5) {
                    return response()->json([
                        'code'   => 438,
                        'status' => 'error',
                        'msg'    => 'Amount must be exactly 5 in test mode',
                    ]);
                }
            } else {
                // Amount must be exactly the fee
                $fee = (float) $applicationType->fee;

                if ($amount < $fee) {
                    return response()->json([
                        'code'    => 438,
                        'status'  => 'error',
                        'msg'     => 'The amount is not sufficient.',
                        'details' => ['amount' => ['The amount is not sufficient.']],
                    ]);
                } elseif ($amount > $fee) {
                    return response()->json([
                        'code'    => 438,
                        'status'  => 'error',
                        'msg'     => 'The amount exceeds the required fee.',
                        'details' => ['amount' => ['The amount exceeds the required fee.']],
                    ]);
                }
            }

            // Mark paid + save payment together (all or nothing).
            // Returns null on success, or an error response.
            $error = DB::transaction(function () use ($applicant, $txID, $txDate, $amount, $gateway) {

                // Lock this applicant's row so two simultaneous requests cannot
                // both pass the checks above and pay twice.
                $locked = Applicant::where('id', $applicant->id)->lockForUpdate()->first();

                if (!$locked || $locked->payment_status == 1
                    || Payment::where('applicant_id', $locked->id)->exists()) {
                    return $this->alreadyPaid();
                }

                if (Payment::where('trxid', $txID)->exists()) {
                    return $this->duplicateTx();
                }

                $locked->payment_status = 1;
                $locked->save();

                Payment::create([
                    'trxid'        => $txID,
                    'paymentdate'  => $txDate,
                    'amount'       => $amount,
                    'method'       => 'Online',
                    'bankname'     => $gateway['bankname'],     // e.g. Bkash (from config)
                    'applicant_id' => $locked->id,
                ]);

                return null;
            });

            if ($error !== null) {
                return $error;
            }

            Log::info("{$logPrefix} PUSH Success:", ['exam_roll' => $examRoll, 'txID' => $txID]);

            return response()->json([
                'code'   => 200,
                'status' => 'ok',
                'msg'    => $testMode ? 'Success (Test Mode)' : 'Success',
            ]);

        } catch (\Exception $e) {
            Log::error("{$logPrefix} PUSH Exception:", [
                'message'   => $e->getMessage(),
                'exam_roll' => $examRoll,
                'txID'      => $txID,
            ]);
            return $this->serverError();
        }
    }

    // -------------------------------------------------------
    // Helpers (PGA business rules, same as PgaPaymentApiController)
    // -------------------------------------------------------

    // config/payment_gateways.php → gateways.{name}; null if unknown
    private function gatewayConfig(?string $gatewayKey): ?array
    {
        if (!$gatewayKey) {
            return null;
        }

        $config = config('payment_gateways.gateways.' . $gatewayKey);

        return is_array($config) ? $config : null;
    }

    // Central → PGA authentication. Fails if the gateway is not configured.
    private function authenticate(?array $gateway, $authkey): bool
    {
        $hash = config('payment_gateways.central_auth_hash');

        if ($gateway === null || empty($hash)) {
            return false;
        }

        return hash_equals((string) $hash, md5((string) $authkey));
    }

    // Payment window check. Returns true (→ 437) when payment is NOT allowed today:
    //   Type 1 (Admission):   start_date             … end_date
    //   Type 2 (Eligibility): eligibility_start_date … eligibility_last_date
    // No end date set counts as closed. No start date set = no start limit.
    // Other types: no check.
    private function deadlinePassed($typeId, $settings, Carbon $today): bool
    {
        if ($typeId === 1) {
            return !$settings->last_payment_date
                || $today->gt(Carbon::parse($settings->last_payment_date))
                || ($settings->start_date && $today->lt(Carbon::parse($settings->start_date)));
        }

        if ($typeId === 2) {
            return !$settings->last_eligibility_payment_date
                || $today->gt(Carbon::parse($settings->last_eligibility_payment_date))
                || ($settings->eligibility_start_date && $today->lt(Carbon::parse($settings->eligibility_start_date)));
        }

        return false;
    }

    // Paid flag on the applicant, or any payment row for the applicant
    private function isAlreadyPaid($applicant): bool
    {
        return $applicant->payment_status == 1
            || Payment::where('applicant_id', $applicant->id)->exists();
    }

    private function isTestRoll(?array $gateway, $examRoll): bool
    {
        return in_array($examRoll, $gateway['test_rolls'] ?? []);
    }

    private function accessDenied()
    {
        return response()->json([
            'code'    => 403,
            'status'  => 'error',
            'msg'     => 'Access Denied!',
            'details' => 'Authentication failed',
        ]);
    }

    private function alreadyPaid()
    {
        return response()->json([
            'code'   => 436,
            'status' => 'error',
            'msg'    => 'Already paid',
        ]);
    }

    private function duplicateTx()
    {
        return response()->json([
            'code'   => 438,
            'status' => 'error',
            'msg'    => 'Transaction ID already exists',
        ]);
    }

    private function serverError()
    {
        return response()->json([
            'code'   => 500,
            'status' => 'error',
            'msg'    => 'Server Error',
        ]);
    }
}
