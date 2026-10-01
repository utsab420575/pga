<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\OtpVerification;
use App\Models\Setting;
use App\Models\UserAttachment;
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
use Illuminate\Validation\Rule;

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
        // Force mobile verification first
        if (auth()->user()->user_type === 'applicant') {
            // ✅ Phone Verified
            if ((int)auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        //  Deadline check from settings
        $setting = Setting::query()->orderByDesc('id')->first(); // or where('session', current)

        if ($setting && $setting->end_date) {
            $deadline = \Illuminate\Support\Carbon::parse($setting->end_date)->endOfDay();

            if (now()->gt($deadline)) {
                // You can include the date to be clear
                return back()->withErrors('Application date is over. Deadline was: '.$deadline->toDateString());
            }
        }else{
            return back()->withErrors('Setting Table Data Not Found');
        }


        // Block if user has an eligibility application (type=2) not yet approved
        $hasPendingEligibility = Applicant::where('user_id', Auth::id())
            ->where('applicationtype_id', 2)
            ->where(function ($q) {
                $q->whereNull('eligibility_approve')
                    ->orWhere('eligibility_approve', 0);
            })
            ->exists();

        if ($hasPendingEligibility) {
            return redirect()->back()
                ->withErrors('You have to eligibility approval for apply application');
            // (If you prefer a "success/error" flash key instead, use ->with('error', '...'))
        }






        // Eligibility approved?
        $hasApprovalEligibility = Applicant::where('user_id', Auth::id())
            ->where('applicationtype_id', 2)
            ->where('eligibility_approve', 1)
            ->exists();

        // Load dropdown data
        $degrees         = Degree::all();
        $departments     = Department::all();
        $studenttypes    = Studenttype::all();
        $applicationtypes= Applicationtype::all();
        $deptDegreeMap   = Department::with('degrees')
            ->get()
            ->mapWithKeys(fn($d) => [$d->id => $d->degrees->pluck('id')->toArray()])
            ->toArray();

        return view('applicant.apply', compact('degrees','departments','studenttypes','applicationtypes','hasApprovalEligibility','deptDegreeMap'));
    }


    // ============================================================
    // LANDING PAGE — shows available application type cards
    // Cards are hidden (not shown) when their window is not active
    // ============================================================
    public function apply_landing()
    {
        // Phone verification guard
        if (auth()->user()->user_type === 'applicant') {
            if ((int) auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        $setting = Setting::orderByDesc('id')->first();
        $now     = now();

        // Eligibility window dates
        $eligStart = ($setting && $setting->eligibility_start_date)
            ? Carbon::parse($setting->eligibility_start_date)->startOfDay() : null;
        $eligEnd   = ($setting && $setting->eligibility_last_date)
            ? Carbon::parse($setting->eligibility_last_date)->endOfDay()    : null;

        // Admission window dates
        $admissionStart = ($setting && $setting->start_date)
            ? Carbon::parse($setting->start_date)->startOfDay() : null;
        $admissionEnd   = ($setting && $setting->end_date)
            ? Carbon::parse($setting->end_date)->endOfDay()     : null;

        // Decide which card to show (completely hidden if window not active)
        $showEligibilityCard = $eligStart && $eligEnd && $now->between($eligStart, $eligEnd);
        $showAdmissionCard   = $admissionStart && $admissionEnd && $now->between($admissionStart, $admissionEnd);

        return view('applicant.apply_landing', compact(
            'showEligibilityCard', 'showAdmissionCard',
            'eligStart', 'eligEnd',
            'admissionStart', 'admissionEnd'
        ));
    }


    // ============================================================
    // ELIGIBILITY APPLICATION FORM — type 2, private university only
    // ============================================================
    public function apply_eligibility()
    {
        // Phone verification guard
        if (auth()->user()->user_type === 'applicant') {
            if ((int) auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        $userId  = Auth::id();
        $setting = Setting::orderByDesc('id')->first();
        $now     = now();

        // Check eligibility window
        $eligStart = ($setting && $setting->eligibility_start_date)
            ? Carbon::parse($setting->eligibility_start_date)->startOfDay() : null;
        $eligEnd   = ($setting && $setting->eligibility_last_date)
            ? Carbon::parse($setting->eligibility_last_date)->endOfDay()    : null;

        if (!$eligStart || !$eligEnd) {
            return back()->withErrors('Eligibility application settings not configured. Please contact the administrator.');
        }

        if ($now->lt($eligStart)) {
            return back()->withErrors(
                'The Eligibility application window has not started yet. It opens on ' . $eligStart->format('d M Y') . '.'
            );
        }
        if ($now->gt($eligEnd)) {
            return back()->withErrors(
                'The Eligibility application deadline has passed. The window was '
                . $eligStart->format('d M Y') . ' – ' . $eligEnd->format('d M Y') . '.'
            );
        }

        // Block if already has a pending eligibility application
        $hasPendingEligibility = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->where(function ($q) {
                $q->whereNull('eligibility_approve')->orWhere('eligibility_approve', 0);
            })
            ->exists();

        if ($hasPendingEligibility) {
            return back()->withErrors('You already have a pending eligibility application. Please wait for a decision.');
        }

        // Block if already has an approved eligibility
        $hasApprovedEligibility = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->where('eligibility_approve', 1)
            ->exists();

        if ($hasApprovedEligibility) {
            return back()->withErrors('Your eligibility is already approved. Please go to Admission Application.');
        }

        // Block if already has an admission application
        $hasAdmissionApplication = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 1)
            ->exists();

        if ($hasAdmissionApplication) {
            return back()->withErrors('You already have an admission application. You cannot apply for eligibility now.');
        }

        // Load dropdowns & dynamic department-degree mapping from DB
        $degrees       = Degree::all();
        $departments   = Department::all();
        $studenttypes  = Studenttype::all();
        $deptDegreeMap = Department::with('degrees')
            ->get()
            ->mapWithKeys(fn($d) => [$d->id => $d->degrees->pluck('id')->toArray()])
            ->toArray();

        return view('applicant.apply_eligibility', compact(
            'degrees', 'departments', 'studenttypes', 'deptDegreeMap',
            'eligStart', 'eligEnd'
        ));
    }


    // ============================================================
    // ADMISSION APPLICATION FORM — type 1
    // For: public university / approved eligibility / previously approved
    // ============================================================
    public function apply_admission()
    {
        // Phone verification guard
        if (auth()->user()->user_type === 'applicant') {
            if ((int) auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        $userId  = Auth::id();
        $setting = Setting::orderByDesc('id')->first();
        $now     = now();

        // Check admission window
        $admissionStart = ($setting && $setting->start_date)
            ? Carbon::parse($setting->start_date)->startOfDay() : null;
        $admissionEnd   = ($setting && $setting->end_date)
            ? Carbon::parse($setting->end_date)->endOfDay()     : null;

        if (!$admissionStart || !$admissionEnd) {
            return back()->withErrors('Admission application settings not configured. Please contact the administrator.');
        }

        if ($now->lt($admissionStart)) {
            return back()->withErrors(
                'The Admission application window has not started yet. It opens on ' . $admissionStart->format('d M Y') . '.'
            );
        }
        if ($now->gt($admissionEnd)) {
            return back()->withErrors(
                'The Admission application deadline has passed. The window was '
                . $admissionStart->format('d M Y') . ' – ' . $admissionEnd->format('d M Y') . '.'
            );
        }

        // Check if user has an existing eligibility application
        $eligibilityApp = Applicant::with('department')
            ->where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->first();

        if ($eligibilityApp) {
            // If not approved, block admission application until Department Head approves
            if ((int) $eligibilityApp->eligibility_approve !== 1) {
                $deptName = $eligibilityApp->department ? $eligibilityApp->department->full_name : 'your applied department';
                return redirect()->route('apply-now')->withErrors(
                    "You have already applied for eligibility application. Please take approval from Department Head ({$deptName})."
                );
            }
        }

        // If eligibility application exists and is approved
        $hasApprovalEligibility = (bool) ($eligibilityApp && (int) $eligibilityApp->eligibility_approve === 1);

        // Load dropdowns & dynamic department-degree mapping from DB
        $degrees          = Degree::all();
        $departments      = Department::all();
        $studenttypes     = Studenttype::all();
        $applicationtypes = Applicationtype::all();
        $deptDegreeMap    = Department::with('degrees')
            ->get()
            ->mapWithKeys(fn($d) => [$d->id => $d->degrees->pluck('id')->toArray()])
            ->toArray();

        return view('applicant.apply_admission', compact(
            'degrees', 'departments', 'studenttypes', 'applicationtypes', 'deptDegreeMap',
            'hasApprovalEligibility',
            'admissionStart', 'admissionEnd'
        ));
    }


    public function apply_now_submit(Request $request)
    {
        // Step 1: Validate form input (must match your Blade form)
        $this->validate($request, [
            'degree'          => ['required'],
            'department'      => ['required'],
            'studenttype'     => ['required'],
            'applicationtype' => ['required'],
            'declaration'     => 'accepted',

            // Required only when previously_eligible
            'prev_eligibility_file' => [
                Rule::requiredIf($request->university_type === 'previously_eligible'),
                'file','mimes:pdf,jpg,jpeg,png','max:2048'
            ],

        ]);

        if (auth()->user()->user_type === 'applicant') {
            // ✅ Phone Verified
            if ((int)auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }

        $appType = (int) $request->applicationtype; // 1 = Admission, 2 = Eligibility
        $userId  = Auth::id();

        // Step 2: Load latest settings (for date windows)
        $setting = Setting::query()->orderByDesc('id')->first();
        if (!$setting) {
            return back()->withErrors('Setting Table Data Not Found');
        }

        $now = now();

        // Admission application window
        $admissionStart = $setting->start_date
            ? \Illuminate\Support\Carbon::parse($setting->start_date)->startOfDay() : null;
        $admissionEnd = $setting->end_date
            ? \Illuminate\Support\Carbon::parse($setting->end_date)->endOfDay() : null;

        // Eligibility application window
        $eligStart = $setting->eligibility_start_date
            ? \Illuminate\Support\Carbon::parse($setting->eligibility_start_date)->startOfDay() : null;
        $eligEnd = $setting->eligibility_last_date
            ? \Illuminate\Support\Carbon::parse($setting->eligibility_last_date)->endOfDay() : null;

        $canAdmission   = $admissionStart && $admissionEnd && $now->between($admissionStart, $admissionEnd);
        $canEligibility = $eligStart && $eligEnd && $now->between($eligStart, $eligEnd);

        // 🔹 Step 3: Enforce time windows for Admission / Eligibility
        if ($appType === 1 && !$canAdmission) {
            if ($admissionStart && $now->lt($admissionStart)) {
                $msg = 'The Admission application window has not started yet.'
                    . ' It opens on ' . $admissionStart->format('d M Y') . '.';
            } elseif ($admissionEnd && $now->gt($admissionEnd)) {
                $msg = 'The Admission application deadline has passed.'
                    . ' The window was ' . $admissionStart->format('d M Y')
                    . ' – ' . $admissionEnd->format('d M Y') . '.';
            } else {
                $msg = 'The Admission application window is not currently available.';
            }
            return back()->withErrors($msg);
        }
        if ($appType === 2 && !$canEligibility) {
            if ($eligStart && $now->lt($eligStart)) {
                $msg = 'The Eligibility application window has not started yet.'
                    . ' It opens on ' . $eligStart->format('d M Y') . '.';
            } elseif ($eligEnd && $now->gt($eligEnd)) {
                $msg = 'The Eligibility application deadline has passed.'
                    . ' The window was ' . $eligStart->format('d M Y')
                    . ' – ' . $eligEnd->format('d M Y') . '.';
            } else {
                $msg = 'The Eligibility application window is not currently available.';
            }
            return back()->withErrors($msg);
        }

        // 🔹 Step 4: Extra rules & state checks

        //  If any eligibility application already exists, block another one
        if ($appType === 2) {
            $hasAnyEligibility = Applicant::where('user_id', $userId)
                ->where('applicationtype_id', 2)
                ->exists();

            if ($hasAnyEligibility) {
                return back()->withErrors('You already have an eligibility application; you cannot submit another.');
            }
        }

        // Check if user has an existing eligibility application
        $existingEligibility = Applicant::with('department')
            ->where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->first();

        $hasPendingEligibility = $existingEligibility && (int) $existingEligibility->eligibility_approve !== 1;

        // If Admission requested but eligibility is not approved → block with Department Head message
        if ($appType === 1 && $hasPendingEligibility) {
            $deptName = $existingEligibility->department ? $existingEligibility->department->full_name : 'your applied department';
            return redirect()->route('apply-now')->withErrors(
                "You have already applied for eligibility application. Please take approval from Department Head ({$deptName})."
            );
        }

        // If Eligibility requested but one is pending → block
        if ($appType === 2 && $hasPendingEligibility) {
            return back()->withErrors('You already have an eligibility application pending approval. Please wait for a decision.');
        }

        // If Eligibility requested but already approved → block
        if ($appType === 2) {
            $hasApprovedEligibility = Applicant::where('user_id', $userId)
                ->where('applicationtype_id', 2)
                ->where('eligibility_approve', 1)
                ->exists();

            if ($hasApprovedEligibility) {
                return back()->withErrors('You already have eligibility approval. Please proceed to admission application.');
            }

            // If Admission already exists, block creating eligibility
            $hasAdmissionApplication = Applicant::where('user_id', $userId)
                ->where('applicationtype_id', 1)
                ->exists();

            if ($hasAdmissionApplication) {
                return back()->withErrors('You already have an admission application. You cannot apply for eligibility now.');
            }
        }

        // 🔹 Step 5: Prevent duplicate: same user + same type + same department
        $duplicate = Applicant::where('applicationtype_id', $appType)
            ->where('department_id', $request->department)
            ->where('user_id', $userId)
            ->first();

        if ($duplicate) {
            return Redirect::back()->withErrors('Already applied in this department');
        }

        // 🔹 Step 6: Create new application (inside transaction for safety)
        $application = Applicationtype::find($appType);
        if (!$application) {
            return back()->withErrors('Invalid application type.');
        }


        //return 'hi';
        DB::beginTransaction();
        try {
            // Generate unique roll no. based on application type
            $base = ($application->type === "Admission" || $appType == 1) ? 100000 : 200000;

            // Fetch current maximum numeric roll for this application type with row locking
            $maxRoll = Applicant::where('applicationtype_id', $appType)
                ->whereRaw("roll REGEXP '^[0-9]+$'")
                ->lockForUpdate()
                ->max(DB::raw('CAST(roll AS UNSIGNED)'));

            $roll = ($maxRoll && $maxRoll >= $base) ? ($maxRoll + 1) : ($base + 1);

            // Double check uniqueness across all applicants to prevent any collision
            while (Applicant::where('roll', (string) $roll)->lockForUpdate()->exists()) {
                $roll++;
            }

            // Save applicant record
            $applicant = new Applicant;
            $applicant->roll               = (string) $roll;
            $applicant->payment_status     = 0;
            $applicant->edit_per           = 0;
            $applicant->department_id      = $request->department;
            $applicant->studenttype_id     = $request->studenttype;
            $applicant->degree_id          = $request->degree;
            $applicant->applicationtype_id = $appType;
            $applicant->user_id            = $userId;
            $applicant->save();

            // 🔹 Step 7: Clone attachments from the applicant with the most attachments (if any)
            $source = Applicant::where('user_id', $userId)
                ->where('id', '!=', $applicant->id)
                ->withCount('attachments')
                ->orderByDesc('attachments_count')
                ->first();

            if ($source && $source->attachments_count > 0) {
                $this->cloneApplicantData($source->id, $applicant->id);
            }





            // inside your try { ... } block, AFTER $applicant->save();
            $storedPublicRelativePath = null;

            if ($request->university_type === 'previously_eligible' && $request->hasFile('prev_eligibility_file')) {
                $file = $request->file('prev_eligibility_file');

                // Basic whitelist (you already validate; this is an extra guard)
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                    throw new \RuntimeException('Invalid file type.');
                }

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeBase     = preg_replace('/[^A-Za-z0-9_-]/', '', $originalName) ?: 'file';
                $today        = now()->format('Y-m-d');

                // Folder under public/
                $subdir     = "user_attachments/{$today}";
                $uploadPath = public_path($subdir);

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }

                // e.g. 42_prev_eligibility_20251018_153045_123456_filename.pdf
                $filename = $userId . '_prev_eligibility_' . now()->format('Ymd_His_u') . '_' . $safeBase . '.' . $extension;

                // Move into public/…
                $file->move($uploadPath, $filename);

                // Save relative path (so you can use asset($path) later)
                $storedPublicRelativePath = "{$subdir}/{$filename}";

                // DB insert
                UserAttachment::create([
                    'user_id'     => $userId,
                    'type'        => 3, // previous_eligibility
                    'file'        => $storedPublicRelativePath, // <-- public relative path
                    'title'       => 'Previously approved eligibility',
                    'attachments' => 'previous_eligibility_proof',
                ]);
            }


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create application: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors('Failed to create application. Please try again.');
        }

        // 🔹 Step 8: Redirect user to their new application form
        return redirect("application/" . $applicant->id);
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
        $applicant = Applicant::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$applicant) {
            return back()->withErrors('Application not found');
        }

        // Edit only if payment==0 AND final_submit==0
        if ((int)$applicant->payment_status === 1 || (int)$applicant->final_submit === 1) {
            return back()->withErrors('Editing not allowed after payment or final submission.');
        }

        $degrees          = Degree::all();
        $departments      = Department::all();
        $studenttypes     = Studenttype::all();
        $applicationtypes = Applicationtype::all();

        // Same flag your apply blade/JS expects
        $hasApprovalEligibility = Applicant::where('user_id', Auth::id())
            ->where('applicationtype_id', 2)
            ->where('eligibility_approve', 1)
            ->exists();

        return view('applicant.edit-application', compact(
            'applicant', 'degrees', 'departments', 'studenttypes', 'applicationtypes', 'hasApprovalEligibility'
        ));
    }

    public function edit_application_submit($id, Request $request)
    {
        $applicant = Applicant::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$applicant) {
            return back()->withErrors('Application not found');
        }

        // Edit only if payment==0 AND final_submit==0
        if ((int)$applicant->payment_status === 1 || (int)$applicant->final_submit === 1) {
            return back()->withErrors('Editing not allowed after payment or final submission.');
        }

        // Validate (university_type is request-only; used for gating)
        $this->validate($request, [
            'degree'          => ['required'],
            'department'      => ['required'],
            'studenttype'     => ['required'],
            'applicationtype' => ['required'],
            'university_type' => ['required', 'in:private,public'],
            'declaration'     => ['accepted'],
        ]);

        $userId       = Auth::id();
        $newAppType   = (int) $request->applicationtype;          // target type from form (1=Admission, 2=Eligibility)
        $oldAppType   = (int) $applicant->applicationtype_id;     // current stored type
        $changingType = $newAppType !== $oldAppType;

        // Load settings (windows) like apply_now_submit
        $setting = Setting::query()->orderByDesc('id')->first();
        if (!$setting) {
            return back()->withErrors('Setting Table Data Not Found');
        }

        $now = now();

        $admissionStart = $setting->start_date
            ? \Illuminate\Support\Carbon::parse($setting->start_date)->startOfDay() : null;
        $admissionEnd = $setting->end_date
            ? \Illuminate\Support\Carbon::parse($setting->end_date)->endOfDay() : null;

        $eligStart = $setting->eligibility_start_date
            ? \Illuminate\Support\Carbon::parse($setting->eligibility_start_date)->startOfDay() : null;
        $eligEnd = $setting->eligibility_last_date
            ? \Illuminate\Support\Carbon::parse($setting->eligibility_last_date)->endOfDay() : null;

        $canAdmission   = $admissionStart && $admissionEnd && $now->between($admissionStart, $admissionEnd);
        $canEligibility = $eligStart && $eligEnd && $now->between($eligStart, $eligEnd);

        // Enforce time windows for the TARGET type (even if only department/degree changed)
        if ($newAppType === 1 && !$canAdmission) {
            if ($admissionStart && $now->lt($admissionStart)) {
                $msg = 'The Admission application window has not started yet.'
                    . ' It opens on ' . $admissionStart->format('d M Y') . '.';
            } elseif ($admissionEnd && $now->gt($admissionEnd)) {
                $msg = 'The Admission application deadline has passed.'
                    . ' The window was ' . $admissionStart->format('d M Y')
                    . ' – ' . $admissionEnd->format('d M Y') . '.';
            } else {
                $msg = 'The Admission application window is not currently available.';
            }
            return back()->withErrors($msg);
        }
        if ($newAppType === 2 && !$canEligibility) {
            if ($eligStart && $now->lt($eligStart)) {
                $msg = 'The Eligibility application window has not started yet.'
                    . ' It opens on ' . $eligStart->format('d M Y') . '.';
            } elseif ($eligEnd && $now->gt($eligEnd)) {
                $msg = 'The Eligibility application deadline has passed.'
                    . ' The window was ' . $eligStart->format('d M Y')
                    . ' – ' . $eligEnd->format('d M Y') . '.';
            } else {
                $msg = 'The Eligibility application window is not currently available.';
            }
            return back()->withErrors($msg);
        }

        // Eligibility rule flags (same as apply)
        $hasPendingEligibility = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->where(function ($q) {
                $q->whereNull('eligibility_approve')->orWhere('eligibility_approve', 0);
            })
            ->exists();

        $hasApprovedEligibility = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 2)
            ->where('eligibility_approve', 1)
            ->exists();

        $hasAdmissionApplication = Applicant::where('user_id', $userId)
            ->where('applicationtype_id', 1)
            ->exists();

        // Apply the same “extra rules & state checks” against the TARGET type
        if ($newAppType === 1 /* Admission */) {
            // Block if there is a pending eligibility
            if ($hasPendingEligibility) {
                return back()->withErrors('Your eligibility application is pending approval. Please wait before applying for admission.');
            }
            // (Approved eligibility is fine; admission may proceed as per your rules)
        }

        if ($newAppType === 2 /* Eligibility */) {
            // Only one eligibility application per user
            $hasAnyEligibility = Applicant::where('user_id', $userId)
                ->where('applicationtype_id', 2)
                ->where('id', '!=', $applicant->id)   // exclude the one being edited
                ->exists();
            if ($hasAnyEligibility) {
                return back()->withErrors('You already have an eligibility application; you cannot submit another.');
            }
            // Block if another eligibility is pending (excluding this one)
            if ($hasPendingEligibility && $oldAppType !== 2) { // changing to eligibility while another pending exists
                return back()->withErrors('You already have an eligibility application pending approval. Please wait for a decision.');
            }
            // Block if eligibility already approved
            if ($hasApprovedEligibility && $oldAppType !== 2) {
                return back()->withErrors('You already have eligibility approval. Please proceed to admission application.');
            }
            // Block creating eligibility if admission application exists
            if ($hasAdmissionApplication && $oldAppType !== 2) {
                return back()->withErrors('You already have an admission application. You cannot apply for eligibility now.');
            }
        }

        // Private/Public gating (same as your blade JS)
        $hasApprovalEligibility = $hasApprovedEligibility; // reuse computed flag
        $uniType = $request->input('university_type');     // not stored
        $allowedAppTypeIds = $uniType === 'private'
            ? ($hasApprovalEligibility ? [1] : [2])
            : [1];

        if (!in_array($newAppType, $allowedAppTypeIds, true)) {
            return back()->withErrors('Selected application type is not allowed for the chosen university type.');
        }

        // Prevent duplicate: same user + same department + same app type (exclude this record)
        $duplicate = Applicant::where('user_id', $userId)
            ->where('department_id', $request->department)
            ->where('applicationtype_id', $newAppType)
            ->where('id', '!=', $applicant->id)
            ->first();

        if ($duplicate) {
            return back()->withErrors('Already applied in this department');
        }

        // ✅ Update fields (no roll generation in edit)
        $applicant->department_id      = $request->department;
        $applicant->studenttype_id     = $request->studenttype;
        $applicant->degree_id          = $request->degree;
        $applicant->applicationtype_id = $newAppType;

        // If you still use edit_per to disable further edits after this save, keep:
        $applicant->edit_per = 0;

        $applicant->save();

        return redirect("application/" . $applicant->id);
    }

    public function my_application(){
        if (auth()->user()->user_type === 'applicant') {
            // ✅ Phone Verified
            if ((int)auth()->user()->phone_verified === 0) {
                return Redirect::to('verify-mobile');
            }
        }
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

        // Admission
        $admission_fees = Applicant::with(['department', 'user', 'payment'])
            ->select('applicants.*')
            ->leftJoin('payments', 'payments.applicant_id', '=', 'applicants.id')
            ->where('applicants.payment_status', 1)
            ->where('applicants.applicationtype_id', 1)
            ->orderBy('payments.amount', 'ASC')
            ->orderBy('applicants.department_id', 'ASC')
            ->orderBy('applicants.roll', 'ASC')
            ->get();

        // Equivalence
        $equivalance_fees = Applicant::with(['department', 'user', 'payment'])
            ->select('applicants.*')
            ->leftJoin('payments', 'payments.applicant_id', '=', 'applicants.id')
            ->where('applicants.payment_status', 1)
            ->where('applicants.applicationtype_id', 2)
            ->orderBy('payments.amount', 'ASC')
            ->orderBy('applicants.department_id', 'ASC')
            ->orderBy('applicants.roll', 'ASC')
            ->get();

        // Sum amounts from payments (safe if some applicants have no payment)
        $admission_total_amount = $admission_fees->sum(function ($item) {
            return optional($item->payment)->amount ?? 0;
        });

        $equivalance_total_amount = $equivalance_fees->sum(function ($item) {
            return optional($item->payment)->amount ?? 0;
        });


        // ==== METHOD/BANK-WISE SUMMARY ====

        // Admission grouped by bankname
        $admission_method_summaries = Payment::select(
            'bankname',
            DB::raw('COUNT(*) as applications'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereIn('applicant_id', $admission_fees->pluck('id'))
            ->groupBy('bankname')
            ->get();

        // Equivalance grouped by bankname
        $equivalance_method_summaries = Payment::select(
            'bankname',
            DB::raw('COUNT(*) as applications'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereIn('applicant_id', $equivalance_fees->pluck('id'))
            ->groupBy('bankname')
            ->get();





        return view('payment-report')
            ->with('departments', $departments)
            ->with('admission_fees', $admission_fees)
            ->with('equivalance_fees', $equivalance_fees)
            ->with('admission_total_amount', $admission_total_amount)
            ->with('equivalance_total_amount', $equivalance_total_amount)
            ->with('admission_method_summaries', $admission_method_summaries)
            ->with('equivalance_method_summaries', $equivalance_method_summaries);

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

        // ✅ Copy references (if any)
        $refs = \DB::table('references')
            ->where('applicant_id', $oldApplicantId)
            ->get();

        if ($refs->isNotEmpty()) {
            $rows = [];
            foreach ($refs as $ref) {
                $data = (array) $ref;      // cast row object to array
                unset($data['id']);        // new PK will be generated
                $data['applicant_id'] = $newApplicantId; // reassign owner
                $rows[] = $data;
            }
            \DB::table('references')->insert($rows); // bulk insert
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
        return view('applicant.phone-verification')->with('code', $code->google_auth_api ?? '');
    }

    /**
     * Check if mobile number already exists for another user
     */
    public function check_mobile_exists(Request $request)
    {
        $phone = $request->phone ?? $request->id ?? $request->numb;
        $rawNumber = preg_replace('/[^0-9]/', '', (string)$phone);
        $mobile11 = substr($rawNumber, -11);

        if (strlen($mobile11) !== 11) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 11-digit mobile number.'
            ], 422);
        }

        $existingUser = User::where(function($q) use ($mobile11) {
            $q->where('phone', $mobile11)
              ->orWhere('phone', 'like', '%' . $mobile11);
        })
        ->when(Auth::check(), function($q) {
            $q->where('id', '!=', Auth::id());
        })
        ->first();

        if ($existingUser) {
            return response()->json([
                'exists' => true,
                'message' => 'Your mobile number already exists.'
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'Mobile number is available.'
        ]);
    }

    public function verify_mobile_submit(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Your session has expired. Please login again.'
            ], 401);
        }

        $rawNumber = preg_replace('/[^0-9]/', '', (string)$request->numb);
        $mobileNumber = substr($rawNumber, -11);

        if (strlen($mobileNumber) !== 11) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number.'
            ], 422);
        }

        // Step 1: Check if mobile already exists for another user
        $check_exists = User::where(function($q) use ($mobileNumber) {
            $q->where('phone', $mobileNumber)
              ->orWhere('phone', 'like', '%' . $mobileNumber);
        })
        ->where('id', '!=', Auth::id())
        ->first();

        if ($check_exists) {
            return response()->json([
                'success' => false,
                'exists' => true,
                'message' => 'Your mobile number already exists.'
            ], 400);
        }

        // Step 2: Check if OTP exists for this mobile & code
        $otp_record = OTPVerification::where(function($q) use ($request, $mobileNumber) {
                $q->where('mobile_number', $request->numb)
                  ->orWhere('mobile_number', $mobileNumber)
                  ->orWhere('mobile_number', '88' . $mobileNumber);
            })
            ->where('otp', $request->code)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp_record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP or OTP not found for this mobile number.'
            ], 400);
        }

        // Step 3: OTP valid → update user profile
        try {
            $user = Auth::user();
            $user->phone = $mobileNumber;
            $user->user_type = 'applicant';
            $user->phone_verified = 1;
            $user->save();

            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Phone verified successfully.'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                return response()->json([
                    'success' => false,
                    'exists' => true,
                    'message' => 'Your mobile number already exists.'
                ], 400);
            }
            Log::error("Database error during mobile verification", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please try again.'
            ], 500);
        }
    }

    public function sentverifyotp(Request $request)
    {
        Log::info("sentverifyotp() called", ['request' => $request->all()]);

        $rawNumber = preg_replace('/[^0-9]/', '', (string)$request->id);
        $mobile11 = substr($rawNumber, -11);

        if (strlen($mobile11) !== 11) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number. Must be 11 digits.'
            ], 422);
        }

        // Check if mobile already exists in users table
        $existingUser = User::where(function($q) use ($mobile11) {
            $q->where('phone', $mobile11)
              ->orWhere('phone', 'like', '%' . $mobile11);
        })
        ->when(Auth::check(), function($q) {
            $q->where('id', '!=', Auth::id());
        })
        ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'exists' => true,
                'message' => 'Your mobile number already exists.'
            ], 400);
        }

        // Generate a 4-digit OTP
        $otp_no = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        Log::info("Generated OTP", ['otp' => $otp_no]);

        // Process phone number for SMS API (must be 8801XXXXXXXXX)
        $phoneNumber = '88' . $mobile11;
        Log::info("Processed phone number", ['phoneNumber' => $phoneNumber]);

        // Prepare the data to send the OTP
        $postdata = [
            'authkey' => 'G0W3KDI6G3KSW2',
            'mobile' => $phoneNumber,
            'text' => 'নম্বর যাচাইয়ের জন্য আপনার ওটিপি হলো ' . $otp_no . ', PGA, DUET'
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
            return response()->json(['success' => false, 'message' => $error_message], $status > 0 ? $status : 400);
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


    //approve eligibility work
    public function approve_eligibility(Request $request)
    {
        $user = auth()->user();

        // Base query: show only submitted & not-yet-approved by default
        $q = Applicant::with([
            'department:id,short_name',
            'user:id,name,phone',
            'payment:trxid,paymentdate,amount,method,applicant_id',
        ])

            ->where('payment_status', 1)
            ->where('applicationtype_id',2);

        // Role-based visibility
        if ($user->user_type === 'head') {
            // Assumes you store the head's department id in the session.
            // Replace with your own mapping if different.
            $headDeptId = $user->department_id;
            $q->where('department_id', $headDeptId);
        } // admins see all

        $applicants = $q->orderBy('department_id')->orderBy('roll')->get();

        // For the header/filter display only
        $departments = Department::orderBy('short_name')->get();

        //return $applicants->count();

        return view('head.approve-eligibility', compact('applicants', 'departments'));
    }

    //approve admission work
    public function approve_admission(Request $request)
    {
        $user = auth()->user();

        // Base query: show only submitted & not-yet-approved by default
        $q = Applicant::with([
            'department:id,short_name',
            'user:id,name,phone',
            'payment:trxid,paymentdate,amount,method,applicant_id',
        ])

            ->where('payment_status', 1)
            ->where('applicationtype_id',1);

        // Role-based visibility
        if ($user->user_type === 'head') {
            // Assumes you store the head's department id in the session.
            // Replace with your own mapping if different.
            $headDeptId = $user->department_id;
            $q->where('department_id', $headDeptId);
        } // admins see all

        $applicants = $q->orderBy('department_id')->orderBy('roll')->get();

        // For the header/filter display only
        $departments = Department::orderBy('short_name')->get();

        //return $applicants->count();

        // Build a per-user status map: [ user_id => 1 or 0 ]
        $eligMap = Applicant::query()
            ->select('user_id')
            ->selectRaw('COUNT(*) AS total_apps')
            ->selectRaw('MAX(applicationtype_id = 2) AS has_type2')
            ->selectRaw('MAX(applicationtype_id = 2 AND eligibility_approve = 1) AS ok2')
            ->groupBy('user_id')
        ->get()
        ->mapWithKeys(function ($row) {
            // Include only users with >1 apps and at least one type=2 app
            if ((int)$row->total_apps <= 1) return [];
            if ((int)$row->has_type2 !== 1) return [];
            // If included, value is 1 if any type=2 app is elig-approved, else 0
            return [$row->user_id => (int)$row->ok2];
        });
        //return $eligMap;

        return view('head.approve-admission', compact('applicants', 'departments','eligMap'));
    }






}