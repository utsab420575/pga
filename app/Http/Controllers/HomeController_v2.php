<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

class HomeController_v2 extends Controller
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
                return Redirect::to('phone-verification');
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

    public function phone_verification()
    {
        return view('applicant.phone-verification');
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

    public function apply_now()
    {
      //return redirect::back()->withErrors('Online application started from 28 May, 2023 at 09.00AM');

        if(Auth::user()->phone_verified === 0)
        {
            return Redirect::to('phone-verification');
        }

        $degrees = Degree::all();
        $departments = Department::all();
        $studenttypes = Studenttype::all();
        $applicationtypes = Applicationtype::all();
        return view('applicant.apply')->with('degrees',$degrees)->with('departments',$departments)->with('studenttypes',$studenttypes)->with('applicationtypes',$applicationtypes);
    }

    public function apply_now_submit(Request $request)
    {
        $this->validate($request,[
        'degree' => ['required'],
        'department' => ['required'],
        'studenttype' => ['required'],
        'applicationtype' => ['required'],
        'declaration' =>'accepted'
    ]);

      	$application = Applicationtype::find($request->applicationtype);
        $checkApplication = Applicant::where('applicationtype_id',$request->applicationtype)->where('department_id',$request->department)->where('user_id',Auth::user()->id)->first();

        if($checkApplication)
        {
            return redirect::back()->withErrors('Already applied in this department');
        }
        else
        {
            $applicant = new Applicant;
            if($application->type == "Admission")
            {
                $applicant->roll = 100000+Applicant::where('applicationtype_id',1)->count()+1;
            }
            else
            {
                $applicant->roll = 200000+Applicant::where('applicationtype_id',2)->count()+1;
            }
            //$applicant->roll = 100000+Applicant::count()+1;
            $applicant->payment_status = 0;
            $applicant->edit_per = 0;
            $applicant->department_id = $request->department;
            $applicant->studenttype_id = $request->studenttype;
            $applicant->degree_id = $request->degree;
            $applicant->applicationtype_id = $request->applicationtype;
            $applicant->user_id = Auth::user()->id;
            $applicant->save();

            return redirect("application/".$applicant->id);
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


}
