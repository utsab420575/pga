@extends('layouts.app')
@section('css')
<style>
    @media print {
        .pagebreak { page-break-before: always; }
    }
</style>
@endsection
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12" align="center">
            <img class="img-responsive" src="{{asset('public/logo.png')}}" width="120px" alt="Logo">
            <h2 class="mt-2">Postgraduate Admission 2021-2022</h2>
            <h2>Dhaka University of Engineering & Technology, Gazipur</h2>
            <h4>Gazipur - 1707</h4>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-md-12" align="center">
            <h3>Application Summary</h3><br>
        </div>
    </div>
    <div class="row justify-content-center">
    <div class="col-auto">
      <table class="table table-responsive">
        <thead>
            <tr>
                <th>Department</th>
                <th>Admission</th>
                <th>Equivalance</th>
            </tr>
                    @php($total = 0)
                    @foreach($departments as $department)
                    <tr>
                        <td>{{$department->short_name}}</td>
                        <td>{{$admission_fees->where('department_id',$department->id)->count()}}</td>
                        <td>{{$equivalance_fees->where('department_id',$department->id)->count()}}</td>
                    </tr>
                    @endforeach
                  <tr>
                    <th>Total Application</th>
                    <th>{{$admission_fees->count()}}</th>
                    <th>{{$equivalance_fees->count()}}</th>
                  </tr>
                </thead>
      </table>
    </div>
  </div>
    <div class="row mt-5">
        <div class="col-md-12" align="center">
            <h3>Payment Summary</h3><br>
        </div>
    </div>                
    <div class="row justify-content-center">
        <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <th>Application Type</th>
                        <th>Method</th>
                        <th>Number of Application</th>
                        <th>Payment</th>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Admission</td>
                        <td>Nagad</td>
                        <td>{{$admission_fees->count()}}</td>
                        <td>{{$admission_fees->count()*1500}}</td>
                      </tr>
                    <tr>
                        <td>Equivalance</td>
                        <td>Nagad</td>
                        <td>{{$equivalance_fees->count()}}</td>
                        <td>{{$equivalance_fees->count()*3000}}</td>
                      </tr>
                    </tbody>
                  </table>
            
        </div>
    </div>
    <div class="row">
        <div class="col-md-12" align="center">
            <p>This is a system generated report from Undergraduate Admission System.
<br>Date & Time: {{date("Y/m/d h:i:sa")}}
<br>Powered By: ICT Cell, DUET</p>
        </div>
    </div>
    <div class="pagebreak"> </div>
    <div id="content">
    <div class="row justify-content-center">
        <div class="col-md-12">
              <p>Payment information of admission applications:</p>            
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>SL</th>
                    <th>Department/Institute</th>
                    <th>Application Roll</th>
                    <th>Name</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                    @php($i = 1)
                    @foreach($admission_fees as $admission_fee)
                                <tr>
                                    <td>{{$i++}}</td>
                                    <td>{{$admission_fee->department->short_name}}</td>
                                    <td>{{$admission_fee->roll}}</td>
                                    <td>{{$admission_fee->user->name}}</td>
                                    <td>{{$admission_fee->payment->trxid}}</td>
                                    <td>{{$admission_fee->payment->paymentdate}}</td>
                                    <td>Nagad</td>
                                    <td>{{$admission_fee->payment->amount}}</td>
                              </tr>
                    @endforeach
                </tbody>
              </table>
            
        </div>
    </div>
    </div>

    <div class="pagebreak"> </div>
    <div id="content">
    <div class="row justify-content-center">
        <div class="col-md-12">
              <p>Payment information of equivalance applications:</p>            
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>SL</th>
                    <th>Department/Institute</th>
                    <th>Application Roll</th>
                    <th>Name</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                    @php($i = 1)
                    

                    @foreach($equivalance_fees as $equivalance_fee)
                                <tr>
                                    <td>{{$i++}}</td>
                                    <td>{{$equivalance_fee->department->short_name}}</td>
                                    <td>{{$equivalance_fee->roll}}</td>
                                    <td>{{$equivalance_fee->user->name}}</td>
                                    <td>{{$equivalance_fee->payment->trxid}}</td>
                                    <td>{{$equivalance_fee->payment->paymentdate}}</td>
                                    <td>Nagad</td>
                                    <td>{{$equivalance_fee->payment->amount}}</td>
                              </tr>
                    @endforeach

                </tbody>
              </table>
            
        </div>
    </div>
    </div>

</div>
@endsection
@section('script')

@endsection