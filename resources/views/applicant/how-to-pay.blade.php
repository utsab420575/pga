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
                <div class="card-header"><b>{{ __('How to pay?') }}</b></div>
                <div class="card-body" align="center">
                    <p align="justify">If you face any kind of difficulties after payment, please contact with "ictcell@duet.ac.bd" with the following supporting documents.<br><br>1. Screenshot of the transaction<br>2. Applicant ID<br><br>We will take necessary steps within few hours.</p>
                    <img src="{{asset('public/nagad.png')}}" class="img-thumbnail" alt="Cinque Terre">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
