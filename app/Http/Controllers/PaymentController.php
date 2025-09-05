<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * MD5(authkey) expected from gateway (keep in env/config ideally).
     */
    private const GATEWAY_AUTH_MD5 = '5d8aedc15fb8b50a7606af923be87f62';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Legacy helper: now returns a JSON response instead of echo/exit.
     * Keep the same name/signature for drop-in compatibility.
     */
    public static function myCustomFunction()
    {
        // If you truly need to hard-stop, you could still exit.
        // Prefer returning a response from callers instead.
        return response()->json(['status' => 'error', 'msg' => 'Payment date over']);
    }

    /**
     * Small helper to validate gateway auth.
     */
    protected function hasValidGatewayAuth(?string $authkey): bool
    {
        $incoming = md5((string) $authkey);
        return hash_equals(self::GATEWAY_AUTH_MD5, $incoming);
    }

    /**
     * Check if applicant exists and whether a payment record exists.
     * Expects: authkey, exam_roll
     */
    public function bkash_check(Request $request)
    {
        // If you need to close payments globally, return the same legacy payload:
        // return self::myCustomFunction();

        if (!$this->hasValidGatewayAuth($request->input('authkey'))) {
            return response()->json(['status' => 'error', 'msg' => 'Access Denied!']);
        }

        $request->validate([
            'exam_roll' => ['required', 'string'],
        ]);

        $applicant = Applicant::where('roll', $request->input('exam_roll'))->first();

        if (!$applicant) {
            return response()->json(['status' => 'error', 'msg' => 'Applicant not found']);
        }

        $payment = Payment::where('applicant_id', $applicant->id)->first();

        if ($payment) {
            return response()->json(['status' => 'ok', 'msg' => 'Found']);
        }

        return response()->json(['status' => 'ok', 'msg' => 'Not Found']);
    }

    /**
     * Pull applicant details (name, fee) if not already paid.
     * Expects: authkey, exam_roll
     */
    public function bkash_pull(Request $request)
    {

        return 'hi';
        // return self::myCustomFunction();

        if (!$this->hasValidGatewayAuth($request->input('authkey'))) {
            return response()->json(['status' => 'error', 'msg' => 'Access Denied!']);
        }

        $request->validate([
            'exam_roll' => ['required', 'string'],
        ]);

        $applicationId = $request->input('exam_roll');

        $candidate = Applicant::with(['user', 'applicationtype'])
            ->where('roll', $applicationId)
            ->first();

        if (!$candidate) {
            return response()->json(['status' => 'error', 'msg' => 'Candidate Not Found']);
        }

        $alreadyPaid = Payment::where('applicant_id', $candidate->id)->exists();
        if ($alreadyPaid) {
            return response()->json(['status' => 'ok', 'msg' => 'Already Paid']);
        }

        return response()->json([
            'status'         => 'ok',
            'msg'            => 'Success',
            'applicant_name' => optional($candidate->user)->name,
            'exam_roll'      => (string) $applicationId,
            'amount'         => optional($candidate->applicationtype)->fee,
        ]);
    }

    /**
     * Push (record) a payment.
     * Expects: authkey, exam_roll, amount, txID, txdate
     */
    public function bkash_push(Request $request)
    {
        // return self::myCustomFunction();

        if (!$this->hasValidGatewayAuth($request->input('authkey'))) {
            return response()->json(['status' => 'error', 'msg' => 'Access Denied!']);
        }

        $request->validate([
            'exam_roll' => ['required', 'string'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'txID'      => ['required', 'string'],
            'txdate'    => ['required', 'string'], // tighten format if needed
        ]);

        // Prevent duplicate by trxid
        if (Payment::where('trxid', $request->input('txID'))->exists()) {
            return response()->json(['status' => 'error', 'msg' => 'Duplicate Entry']);
        }

        $applicant = Applicant::where('roll', $request->input('exam_roll'))->first();
        if (!$applicant) {
            return response()->json(['status' => 'error', 'msg' => 'Applicant not found']);
        }

        $payment = new Payment();
        $payment->trxid        = (string) $request->input('txID');
        $payment->paymentdate  = (string) $request->input('txdate');
        $payment->amount       = (float)  $request->input('amount');
        $payment->method       = 'online';
        $payment->bankname     = 'Bkash';
        $payment->applicant_id = $applicant->id;
        $payment->save();

        return response()->json(['status' => 'ok', 'msg' => 'Success']);
    }
}
