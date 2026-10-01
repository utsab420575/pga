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
                <div class="card-body text-center">
                    <p class="text-justify">
                        If you face any kind of difficulties after payment, please contact with 
                        <b>pg.admission@duet.ac.bd</b> with the following supporting documents.<br><br>
                        1. Screenshot of the transaction<br>
                        2. Applicant ID<br><br>
                        We will take necessary steps within few hours.
                    </p>

                    <div class="row mt-4">
                        <div class="col-12 mb-3">
                            <div class="card shadow-sm border-0">
                                <img src="{{ asset('bkash_web.jpg') }}" 
                                     class="card-img-top img-fluid rounded" 
                                     alt="bKash Payment">
                                <div class="card-body p-2">
                                    <h6 class="text-center">bKash Payment</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="card shadow-sm border-0">
                                <img src="{{ asset('agrani.jpg') }}" 
                                     class="card-img-top img-fluid rounded" 
                                     alt="Agrani Bank Payment">
                                <div class="card-body p-2">
                                    <h6 class="text-center">Agrani Bank Payment</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection