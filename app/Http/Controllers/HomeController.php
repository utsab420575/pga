<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\OtpVerification;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Applicant;
use App\Models\Applicationtype;
use App\Models\Degree;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Studenttype;
use App\Models\Payment;

use Carbon\Carbon;

use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Response;
use Illuminate\Console\View\Components\Alert;
use DateTime;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        if(Auth::user()->user_type == 'applicant')
        {
            if(Auth::user()->phone_verified === 0)
            {
                return Redirect::to('verify-mobile');
            }
            else
            {
                return view('home');
            }
        }
        else
        {
            return view('home');
        }


    }

   /* public function phone_verification()
    {
        return view('applicant.verify-mobile');
    }

    public function phone_verification_submit(Request $request)
    {
        $this->validate($request,[

        'phone' => ['required','unique:users,phone'],
    ]);

        $user= User::find(Auth::user()->id);
        $user->phone=$request->phone;
        $user->phone_verified=1;
        $user->save();
    }*/


    public function phone_verification()
    {
        return view('applicant.phone');
    }

    public function phone_verification_submit(Request $request)
    {
        $this->validate($request,[

            'phone' => ['required','unique:users,phone'],
        ]);

        $user= User::find(Auth::user()->id);
        $user->phone=$request->phone;
        $user->phone_verified=1;
        $user->save();
    }

    public function phone_verify_submit(Request $request)
    {

        //return 'hi';
        $request->validate([
            'phone' => ['required', 'unique:users,phone'],
            'phone_ren' => ['required', 'same:phone'],
        ], [
            'phone_ren.same' => 'Phone number not matched',
        ]);

        $user= User::find(Auth::user()->id);
        $user->phone=$request->phone;
        $user->phone_verified=1;
        $user->save();

        return view('home');
    }

    public function apply_now()
    {
      //return redirect::back()->withErrors('Online application started from 28 May, 2023 at 09.00AM');

        if(Auth::user()->phone_verified === 0)
        {
            return Redirect::to('verify-mobile');
        }

        $degrees = Degree::all();
        $departments = Department::all();
        $studenttypes = Studenttype::all();
        $applicationtypes = Applicationtype::all();
        return view('applicant.apply')->with('degrees',$degrees)->with('departments',$departments)->with('studenttypes',$studenttypes)->with('applicationtypes',$applicationtypes);
    }

    public function apply_now_submit(Request $request)
    {
        $this->validate($request, [
            'degree' => ['required'],
            'department' => ['required'],
            'studenttype' => ['required'],
            'applicationtype' => ['required'],
            'declaration' =>'accepted'
        ]);

        $application = Applicationtype::find($request->applicationtype);

        $checkApplication = Applicant::where('applicationtype_id', $request->applicationtype)
            ->where('department_id', $request->department)
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($checkApplication) {
            return Redirect::back()->withErrors('Already applied in this department');
        } else {
            $applicant = new Applicant;

            if ($application->type == "Admission") {
                $applicant->roll = 100000 + Applicant::where('applicationtype_id', 1)->count() + 1;
            } else {
                $applicant->roll = 200000 + Applicant::where('applicationtype_id', 2)->count() + 1;
            }

            $applicant->payment_status = 0;
            $applicant->edit_per = 0;
            $applicant->department_id = $request->department;
            $applicant->studenttype_id = $request->studenttype;
            $applicant->degree_id = $request->degree;
            $applicant->applicationtype_id = $request->applicationtype;
            $applicant->user_id = Auth::user()->id;
            $applicant->save();

          /*  // ✅ Clone previous applicant data if exists
            $userApplicants = Applicant::where('user_id', Auth::id())->orderBy('id', 'asc')->get();

            if ($userApplicants->count() > 1) {
                $oldApplicantId = $userApplicants->first()->id;   // oldest applicant id
                $newApplicantId = $applicant->id;                 // newly created applicant id

                $this->cloneApplicantData($oldApplicantId, $newApplicantId);
            }*/

            $source = Applicant::where('user_id', Auth::id())
                ->where('id', '!=', $applicant->id)     // exclude the new one
                ->withCount('attachments')              // Laravel will add attachments_count
                ->orderByDesc('attachments_count')      // pick the one with most attachments
                ->first();

            if ($source && $source->attachments_count > 0) {
                $this->cloneApplicantData($source->id, $applicant->id);
            }

            return redirect("application/" . $applicant->id);
        }
    }


    public function application($id)
    {

        $applicant = Applicant::where('id',$id)->where('user_id',Auth::user()->id)->first();
        if($applicant)
        {
            return view('applicant.application')->with('applicant',$applicant);
        }
        else
        {
            return redirect::back()->withErrors('Application not found');
        }
    }

    public function edit_application($id)
    {
        $applicant = Applicant::where('id',$id)->where('user_id',Auth::user()->id)->first();
      	if($applicant->edit_per === 0 && $applicant->payment_status === 1)
        {
          return redirect::back()->withErrors('Already paid');
        }
        if($applicant)
        {
            $degrees = Degree::all();
            $departments = Department::all();
            $studenttypes = Studenttype::all();
            $applicationtypes = Applicationtype::all();
            return view('applicant.edit-application')->with('applicant',$applicant)->with('degrees',$degrees)->with('departments',$departments)->with('studenttypes',$studenttypes)->with('applicationtypes',$applicationtypes);
        }
        else
        {
            return redirect::back()->withErrors('Application not found');
        }

    }

    public function edit_application_submit($id, Request $request)
    {
        $this->validate($request,[
        'degree' => ['required'],
        'department' => ['required'],
        'studenttype' => ['required'],
        'applicationtype' => ['required'],
        'declaration' =>'accepted'
    ]);
      	$application = Applicationtype::find($request->applicationtype);
        $applicant = Applicant::where('id',$id)->where('user_id',Auth::user()->id)->first();
		if($applicant->edit_per === 0 && $applicant->payment_status === 1)
        {
          return redirect::back()->withErrors('Already paid');
        }
        if($applicant)
        {
            $checkApplication = Applicant::where('applicationtype_id',$request->applicationtype)->where('department_id',$request->department)->where('user_id',Auth::user()->id)->where('id', '!=', $applicant->id)->first();

          if($checkApplication)
          {
            return redirect::back()->withErrors('Already applied in this department');
          }
          else
          {
                if($applicant->payment_status === 0)
                {
                    if($application->type == "Admission")
                    {
                      $applicant->roll = 100000+Applicant::where('applicationtype_id',1)->count()+1;
                    }
                    else
                    {
                      $applicant->roll = 200000+Applicant::where('applicationtype_id',2)->count()+1;
                    }
                  $applicant->applicationtype_id = $request->applicationtype;
                }
                $applicant->edit_per = 0;
                $applicant->department_id = $request->department;
                $applicant->studenttype_id = $request->studenttype;
                $applicant->degree_id = $request->degree;

                $applicant->save();

                return redirect("application/".$applicant->id);
            }
        }
        else
        {
            return redirect::back()->withErrors('Application not found');
        }

    }

    public function my_application(){

        $applications = Applicant::where('user_id',Auth::user()->id)->get();
        return view('applicant.my-application')->with('applications',$applications);
    }

    public function how_to_pay(){

        return view('applicant.how-to-pay');
    }

    public function update_password(){
        return view('update-password');
    }

    public function update_password_submit(Request $request){
        $this->validate($request,[
        'password' => ['min:8', 'confirmed'],
        ]);
        $password = User::find(Auth::user()->id);
        $password->password = Hash::make($request->password);
        $password->save();
        return redirect::back()->with('Status','Updated successfully');
    }

  public function payment_report()
    {
        $departments = Department::all();
        $admission_fees = Applicant::where('payment_status',1)->where('applicationtype_id',1)->orderBy('department_id','ASC')->orderBy('roll','ASC')->get();
        $equivalance_fees = Applicant::where('payment_status',1)->where('applicationtype_id',2)->orderBy('department_id','ASC')->orderBy('roll','ASC')->get();

        return view('payment-report')->with('departments',$departments)->with('admission_fees',$admission_fees)->with('equivalance_fees',$equivalance_fees);
    }


    //clone applicant(all row copy from elibility applicant to new applicant)
    protected function cloneApplicantData($oldApplicantId, $newApplicantId)
    {
        // ✅ Copy basic_infos
        $basicInfo = \DB::table('basic_infos')->where('applicant_id', $oldApplicantId)->first();
        if ($basicInfo) {
            $data = (array) $basicInfo;
            unset($data['id']);
            $data['applicant_id'] = $newApplicantId;
            \DB::table('basic_infos')->insert($data);
        }

        // ✅ Copy eligibility_degree
        $eligibilityDegrees = \DB::table('eligibility_degrees')->where('applicant_id', $oldApplicantId)->get();
        foreach ($eligibilityDegrees as $degree) {
            $data = (array) $degree;
            unset($data['id']);
            $data['applicant_id'] = $newApplicantId;
            \DB::table('eligibility_degrees')->insert($data);
        }

        // ✅ Copy education_info
        $educationInfos = \DB::table('education_infos')->where('applicant_id', $oldApplicantId)->get();
        foreach ($educationInfos as $edu) {
            $data = (array) $edu;
            unset($data['id']);
            $data['applicant_id'] = $newApplicantId;
            \DB::table('education_infos')->insert($data);
        }

        // ✅ Copy attachments + files (if needed)
        $this->cloneAttachmentsWithFiles($oldApplicantId, $newApplicantId);
    }



    protected function cloneAttachmentsWithFiles(int $oldApplicantId, int $newApplicantId): void
    {
        $attachments = Attachment::where('applicant_id', $oldApplicantId)->get();

        $todayFolder = now()->format('Y-m-d');         // e.g. 2025-09-04
        $destDirRel  = "attachments/{$todayFolder}";   // relative (public/)
        $destDirAbs  = public_path($destDirRel);       // absolute

        // ensure daily folder exists
        if (!is_dir($destDirAbs)) {
            mkdir($destDirAbs, 0777, true);
        }

        foreach ($attachments as $a) {
            $srcAbs = public_path($a->file);
            if (!is_file($srcAbs)) {
                // original file missing -> skip
                continue;
            }

            $ext   = strtolower(pathinfo($srcAbs, PATHINFO_EXTENSION));
            $base  = pathinfo($srcAbs, PATHINFO_FILENAME);
            $parts = explode('_', $base);

            // drop first 5 tokens: [oldId, type, YYYYMMDD, HHMMSS, usec]
            $tailParts = count($parts) > 5 ? array_slice($parts, 5) : $parts;
            $tailSafe  = preg_replace('/[^A-Za-z0-9_\-]/', '_', implode('_', $tailParts));

            $newName = $newApplicantId . '_' . $a->attachment_type_id . '_' . now()->format('Ymd_His_u');
            if ($tailSafe !== '') {
                $newName .= '_' . $tailSafe;
            }
            $newName .= '.' . $ext;

            $destAbs = $destDirAbs . DIRECTORY_SEPARATOR . $newName;

            // copy the file
            File::copy($srcAbs, $destAbs);

            // create new DB row pointing to the copied file
            Attachment::create([
                'file'               => $destDirRel . '/' . $newName, // relative path from public/
                'attachment_type_id' => $a->attachment_type_id,
                'applicant_id'       => $newApplicantId,
            ]);
        }
    }



    //mobile verification
    public function verify_mobile()
    {
        $code = Setting::find(1);
        return view('applicant.phone-verification')->with('code',$code->google_auth_api);
    }

    public function verify_mobile_submit(Request $request)
    {
        // Generate random password for new user
        $settings = Setting::latest()->first();
        $password = substr(str_shuffle(str_repeat($x='23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ', ceil(8/strlen($x)) )),1,8);

        // Step 1: Check if mobile already exists
        $check_exists = User::where('phone',$request->numb)->first();
        if($check_exists)
        {
            // Already registered → stop here
            return redirect::back()->withErrors("Already registerd with this mobile no.");
        }

        // Step 2: Check if OTP exists for this mobile & code
        $otp_record = OTPVerification::where('mobile_number', $request->numb)
            ->where('otp', $request->code)
            ->orderBy('created_at', 'desc') // take latest OTP
            ->first();

        if (!$otp_record) {
            // OTP not found → stop here
            return response()->json(['success' => false, 'message' => 'Invalid OTP or OTP not found for this mobile number.'], 400);
        }
        else
        {
            // Step 3: OTP valid → proceed to register new user
            $mobileNumber = $request->numb;

            // Remove '+' sign if present
            $mobileNumber = str_replace('+', '', $mobileNumber);

            // Remove '88' prefix if present
            if (substr($mobileNumber, 0, 2) === '88') {
                $mobileNumber = substr($mobileNumber, 2);
            }

            // Create new User
            $user= Auth::user();
            //$user->name='Applicant';
           // $user->email=$mobileNumber."@admission.duet.ac.bd";
            $user->phone=$mobileNumber;
           // $user->password=Hash::make($password);
            $user->user_type='applicant';
            $user->phone_verified=1;
            $user->save();

          /*  // Step 4: Create new Userpin
            $total_user = Userpin::all();
            $userpin= new Userpin;
            $userpin->pin=10000+$total_user->count()+1;
            $userpin->password=$password;
            $userpin->payment_status=0;
            $userpin->ssc_api_count=0;
            $userpin->reg_steps=1;
            $userpin->user_id=$user->id;
            $userpin->save();*/

            // Step 5: Prepare SMS for login credentials
            $to = $user->mobile;
            if (substr($to, 0, 2) !== '88') {
                $to = '88' . $to; // Ensure starts with 88
            }

           // $text = "Hello Dear,\nYour Payment ID: {$userpin->pin}. Mobile No: {$user->mobile} and password: {$password}\nBest Regards,\nCoordinator\nAdmission Committee {$settings->admission_title}, DUET";

            // Step 6: Send SMS
           // $smsSent = $this->send_sms($to, $text);

            // Step 7: Login the user immediately
            Auth::login($user);

            // Step 8: Respond success
            return response()->json(['success' => true, 'message' => 'Registration successful, logged in.']);
        }
    }



    public function sentverifyotp(Request $request)
    {
        Log::info("sentverifyotp() called", ['request' => $request->all()]);
/*
        $secretKey = '6LcvlO4UAAAAAFYAQ5CsRFoqIpXMjU0DFarOOm4b';
        $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$request->gresp;
        Log::info("Prepared reCAPTCHA verification URL", ['verifyUrl' => $verifyUrl]);

        // Verify reCAPTCHA
        $response = file_get_contents($verifyUrl);
        Log::info("Received reCAPTCHA response", ['raw_response' => $response]);

        $responseData = json_decode($response);
        Log::info("Decoded reCAPTCHA response", ['responseData' => $responseData]);

        if (!$responseData->success) {
            $errors = implode(', ', $responseData->{'error-codes'});
            Log::warning("reCAPTCHA verification failed", ['errors' => $errors]);
            return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed: '.$errors], 400);
        }*/

        // Generate a 4-digit OTP
        $otp_no = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        Log::info("Generated OTP", ['otp' => $otp_no]);

        // Get the phone number of the authenticated user
        $phoneNumber = str_replace('+', '', $request->id);
        Log::info("Processed phone number", ['phoneNumber' => $phoneNumber]);

        // Prepare the data to send the OTP
        $postdata = [
            'authkey' => 'G0W3KDI6G3KSW2',
            'mobile' => $phoneNumber,
            'text' => 'নম্বর যাচাইয়ের জন্য আপনার ওটিপি হলো ' . $otp_no
        ];
        Log::info("Prepared SMS API request", ['url' => 'https://sms.duetbd.org/api/send-sms', 'postdata' => $postdata]);

        $url = 'https://sms.duetbd.org/api/send-sms';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        Log::info("Executing cURL request to SMS API");
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info("cURL executed", ['status' => $status, 'raw_response' => $response]);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            Log::error("cURL error occurred", ['error' => $error_msg]);
            curl_close($ch);
            return response()->json(['success' => false, 'message' => 'Failed to send OTP', 'error' => $error_msg], 500);
        }

        curl_close($ch);
        Log::info("cURL closed successfully");

        $response_data = json_decode($response, true);
        Log::info("Decoded SMS API response", ['response_data' => $response_data]);

        if ($status == 200 && isset($response_data['response']['status']) && strtolower($response_data['response']['status']) == 'success') {
            Log::info("SMS API reported success, storing OTP in database", ['otp' => $otp_no, 'mobile' => $phoneNumber]);

            OTPVerification::create([
                'otp' => $otp_no,
                'mobile_number' => $phoneNumber,
            ]);

            Log::info("OTP stored successfully in database");
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'otp' => $otp_no
            ]);
        } else {
            $error_message = isset($response_data['error_message']) ? $response_data['error_message'] : 'Failed to send OTP';
            Log::warning("SMS API failed", ['status' => $status, 'error_message' => $error_message, 'response_data' => $response_data]);
            return response()->json(['success' => false, 'message' => $error_message], $status);
        }
    }

    private function send_sms($to, $text)
    {
        Log::info("send_sms() called", ['to' => $to, 'text' => $text]);

        // Step 1: Prepare SMS API call
        $authKey = 'G0W3KDI6G3KSW2';
        $url = "https://sms.duetbd.org/api/send-sms";
        $postData = [
            'authkey' => $authKey,
            'mobile'  => $to,
            'text'    => $text,
        ];

        Log::info("Prepared SMS API request", ['url' => $url, 'postData' => $postData]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        Log::info("cURL executed", ['status' => $status, 'raw_response' => $response]);

        // Step 2: Error handling
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            Log::error("cURL error in send_sms()", ['error' => $error_msg]);
            curl_close($ch);
            return response()->json(['success' => false, 'message' => 'Failed to send OTP', 'error' => $error_msg], 500);
        }

        curl_close($ch);

        // Step 3: Decode response
        $response_data = json_decode($response, true);
        Log::info("Decoded SMS API response", ['response_data' => $response_data]);

        // Step 4: Success check
        if ($status == 200 && isset($response_data['status']) && $response_data['status'] == 'success') {
            Log::info("SMS sent successfully", ['mobile' => $to]);
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        } else {
            $error_message = isset($response_data['error_message']) ? $response_data['error_message'] : 'Failed to send OTP';
            Log::warning("SMS sending failed", [
                'status' => $status,
                'error_message' => $error_message,
                'response_data' => $response_data
            ]);
            return response()->json(['success' => false, 'message' => $error_message], $status);
        }
    }






}
