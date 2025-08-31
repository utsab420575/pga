<!-- Admin Template -->
@extends('layouts.app')
@section('css')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <!-- Action Message -->
                @if (count($errors) > 0)
                    @foreach ($errors->all() as $error)
                        <p class="alert alert-danger">{{ $error }}</p>
                    @endforeach
                @endif
                @if (session('Status'))
                    <p class="alert alert-info">{{ session('Status') }}</p>
                @endif
                <!-- End Action Message -->
                <div class="card-body">
                    @if (Auth::user()->user_type == 'admin')
                        <div class="row">
                            <div class="col-md-3">

                                <div class="wrimagecard wrimagecard-topimage"> <a href="#">
                                        <div class="wrimagecard-topimage_header backcolorUser">
                                            <center><i class="fa-solid fa-users fa-3x iconcolorUser"></i></center>
                                        </div>
                                        <div class="wrimagecard-topimage_title">
                                            <div align="center">
                                                <h5>View Users</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">

                                <div class="wrimagecard wrimagecard-topimage"><a href="#">
                                        <div class="wrimagecard-topimage_header backcolorTemplate">
                                            <center><i class="fa-solid fa-layer-group fa-3x iconcolorTemplate"></i>
                                            </center>
                                        </div>
                                        <div class="wrimagecard-topimage_title">
                                            <div align="center">
                                                <h5>View Templates</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">

                                <div class="wrimagecard wrimagecard-topimage"><a href="#">
                                        <div class="wrimagecard-topimage_header backcolorCategory">
                                            <center><i class="fa-solid fa-list fa-3x iconcolorCategory"></i></center>
                                        </div>
                                        <div class="wrimagecard-topimage_title">
                                            <div align="center">
                                                <h5>View Categories</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3">

                                <div class="wrimagecard wrimagecard-topimage"><a href="#">
                                        <div class="wrimagecard-topimage_header backcolorFont">
                                            <center><i class="fa-solid fa-font fa-3x iconcolorFont"></i></center>
                                        </div>
                                        <div class="wrimagecard-topimage_title">
                                            <div align="center">
                                                <h5>View Fonts</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row pt-3">
                        <div class="col-md-3">

                            <div class="wrimagecard wrimagecard-topimage"><a href="{{URL::to('apply-now')}}">
                                    <div class="wrimagecard-topimage_header backcolorLogo">
                                        <center><i class="fa-solid fa-paper-plane fa-3x iconcolorLogo"></i></center>
                                    </div>
                                    <div class="wrimagecard-topimage_title">
                                        <div align="center">
                                            <h5>Apply Now</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">

                            <div class="wrimagecard wrimagecard-topimage"><a href="{{URL::to('my-application')}}">
                                    <div class="wrimagecard-topimage_header backcolorUser">
                                        <center><i class="fa-solid fa-table-list fa-3x iconcolorAuthsign"></i></center>
                                    </div>
                                    <div class="wrimagecard-topimage_title">
                                        <div align="center">
                                            <h5>My Applications</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">

                            <div class="wrimagecard wrimagecard-topimage"><a href="{{URL::to('how-to-pay')}}">
                                    <div class="wrimagecard-topimage_header backcolorCreateID">
                                        <center><i class="fa-regular fa-credit-card fa-3x iconcolorCreateID"></i>
                                        </center>
                                    </div>
                                    <div class="wrimagecard-topimage_title">
                                        <div align="center">
                                            <h5>How to Pay?</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-3">

                            <div class="wrimagecard wrimagecard-topimage">
                                <a href="{{URL::to('notice')}}">
                                    <div class="wrimagecard-topimage_header backcolorViewID">
                                        <center><i class="fa-solid fa-bell fa-3x iconcolorViewID"></i></center>
                                    </div>
                                    <div class="wrimagecard-topimage_title">
                                        <div align="center">
                                            <h5>Notices</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>
@endsection
