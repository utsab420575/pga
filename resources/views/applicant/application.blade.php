@extends('layouts.app')

@section('content')
<div class="container">
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
                <div class="card-header"><b>{{ __('Online Application Summary') }}</b></div>
                <div class="card-body" align="center">
                    <table class="table table-hover table-striped">

                        <tbody>
                          <tr>
                            <td width="50%" align="right">Program applied for:</td>
                            <td><b>{{$applicant->degree->degree_name}}</b></td>
                          </tr>
                          <tr>
                            <td align="right">Department/Institute:</td>
                            <td><b>{{$applicant->department->short_name}}</b></td>
                          </tr>
                          <tr>
                            <td align="right">Status:</td>
                            <td><b>{{$applicant->studenttype->type}}</b></td>
                          </tr>
                          <tr>
                            <td align="right">Application Type:</td>
                            <td><b>{{$applicant->applicationtype->type}}</b></td>
                          </tr>
                          <tr>
                            <td align="right">Your Applicant ID:</td>
                            <td><b>{{$applicant->roll}}</b></td>
                          </tr>
                          <tr>
                            <td align="right">Fees:</td>
                            <td><b>{{$applicant->applicationtype->fee}}.tk</b></td>
                          </tr>
                        </tbody>
                      </table>
                    <br>
                    <a href="{{ URL('my-application')}}"><button type="button" class="btn btn-primary">My Applications</button></a> <a href="{{ URL('how-to-pay')}}"><button type="button" class="btn btn-primary">Pay Now</button></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
