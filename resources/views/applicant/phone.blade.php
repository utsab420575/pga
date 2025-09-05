@extends('layouts.app')

@section('css')
<script src="{{ asset('public/js/jquery-3.1.1.min.js') }}"></script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if(count($errors) > 0)
                @foreach($errors->all() as $error)
                    <p class="alert alert-danger">{{ $error }}</p>
                @endforeach
            @endif

            @if(session('Status'))
                <p class="alert alert-info">{{ session('Status') }}</p>
            @endif

            <div class="card">
                <div class="card-header">{{ __('Verify Your Phone Number') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ URL('phone-verify-submit') }}">
                        @csrf
                        <div class="card">
                            <div class="card-header">{{ __('First Enter Your Phone Number') }}</div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Phone Number [*]') }}</label>
                                    <div class="col-md-6">
                                        <input id="phone" type="text" class="form-control" name="phone" value="{{ old('phone') }}" required placeholder="e.g. +8801XXXXXXXXX" autofocus>
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="phone_ren" class="col-md-4 col-form-label text-md-right">{{ __('Re-enter Phone Number [*]') }}</label>
                                    <div class="col-md-6">
                                        <input id="phone_ren" type="text" class="form-control" name="phone_ren" value="{{ old('phone_ren') }}" required placeholder="e.g. +8801XXXXXXXXX" autofocus>
                                        @error('phone_ren')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="submit" class="col-md-4 col-form-label text-md-right"></label>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
