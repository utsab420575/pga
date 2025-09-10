@extends('layouts.app')

@section('content')
<div class="container">

  <!--<div class="row justify-content-center">
    <div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
  <strong><i class="fa fa-warning"></i>Payment Instruction for Candidates:</strong> <marquee><p style="font-family: Impact; font-size: 18pt">Pay order/ DD is not required if anyone pay through Nagad online payment. <a target="_blank" href="https://career.duetbd.org/public/nagad.png">Click here to know how to pay with Nagad</a></p></marquee>
</div>
    </div>-->

    <div class="row justify-content-center">
        <div class="col-md-12">

  @if(count($errors)>0)
          @foreach($errors->all() as $error)
        <p class="alert alert-danger">{{$error}}</p>
          @endforeach
        @endif

        @if(session('Status'))
        <p class="alert alert-info">{{session('Status')}}</p>
        @endif
            <div class="card">
                <div class="card-header"><b>{{ __('Application Guidelines') }}</b></div>
                <div class="card-body">
                    <table class="table table-striped">
                      <tbody>

                      <tr>
                          <td align="left">
                              Step 1. If you don't have any applicant account then click
                              <a href="{{ URL::to('register') }}"><b>"Create Applicant Account"</b></a><br><br>

                              Step 2. Then verify your contact number.<br><br>

                              Step 3. To apply click <b>"Apply Now"</b>, provide all required information, and click the <b>"Submit"</b> button.<br><br>

                              Step 4. Pay the applicable fee based on your application type
                              (e.g., Eligibility Form or Admission Form).<br><br>

                              Step 5. Log back into your account, go to the <b>"My Application"</b> section,
                              and click on either <b>"Submit Eligibility Form"</b> or <b>"Submit Admission Form"</b>
                              depending on your application type.<br><br>

                              Step 6. Fill in all required fields and upload necessary documents,
                              then click <b>"Final Submission"</b> to complete the process.
                          </td>
                      </tr>

                      <tr>
                          <td align="center"><a href="{{route('login')}}"><b> Login</b></a> (if you already have an applicant account)</td>
                        </tr>
                        <tr>
                          <td align="center"><br>Enquiry<br>
                            Email: <a href="mailto:reg_duet@duet.ac.bd"><b>pg.admission@duet.ac.bd</b></a>
                          </td>

                        </tr>
                        <tr>
                        <td align="center"><br>Notice<br>
                            Admission Notice: <a href="{{ asset('public/notice1.pdf') }}"><b>Download</b></a>
                          </td>

                        </tr>
                        <tr>
                        <td align="center"><br>Form<br>
                            {{--Application for admission to Postgraduate Program: <a href="{{ asset('public/admission.doc') }}"><b>Download</b></a><br>
                            Application for Eligibility Verification of Obtained Degree: <a href="{{ asset('public/eligibility.doc') }}"><b>Download</b></a><br>--}}
                            No Objection Certificate (NOC): <a href="{{ asset('public/noc.doc') }}"><b>Download</b></a>
                          </td></tr>
                      </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
