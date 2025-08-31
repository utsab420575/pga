@extends('layouts.app')

@section('content')
<div class="container-fluid">
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
                <div class="card-header"><b>{{ __('My Applications') }}</b></div>
                <div class="card-body">
              

                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th scope="col">Sl</th>
                          <th scope="col">Program</th>
                          <th scope="col">Department/Institute</th>
                          <th scope="col">Status</th>
                          <th scope="col">Application Type</th>
                          <th scope="col">Applicant ID</th>
                          <th scope="col">Amount</th>
                          <th scope="col">Payment Status</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php($i=1)
                        @foreach($applications as $application)
                            <tr>
                              <th scope="row">{{$i++}}</th>
                              <td>{{$application->degree->degree_name}}</td>
                              <td>{{$application->department->short_name}}</td>
                              <td>{{$application->studenttype->type}}</td>
                              <td>{{$application->applicationtype->type}}</td>
                              <td>{{$application->roll}}</td>
                              <td>{{$application->applicationtype->fee}}</td>
                              <td>@if($application->payment_status === 0)
                                <span style="color:red"><b>Unpaid</b></span>
                                @else
                                <span style="color:green">Paid<br>TRXID: <b>{{$application->payment->trxid}}</b></span>
                                @endif
                              </td>
                              <td>
                                @if($application->edit_per === 1 || $application->payment_status === 0)
                                <a href="{{ URL('edit-application')}}/{{$application->id}}"><button type="button" class="btn btn-danger">Edit</button></a>
                                @endif
                              </td>
                            </tr>
                        @endforeach
                        
                      </tbody>
                    </table>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
