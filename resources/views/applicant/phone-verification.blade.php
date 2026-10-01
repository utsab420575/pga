@extends('layouts.app')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                @if(count($errors)>0)
                    @foreach($errors->all() as $error)
                        <p class="alert alert-danger">{{$error}}</p>
                    @endforeach
                @endif

                @if(session('Status'))
                    <p class="alert alert-info">{{session('Status')}}</p>
                @endif

                <div class="card mt-5">

                    <div class="card-header">{{ __('Verify Your Phone Number') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ URL('phone-verification-submit')}}">
                            @csrf
                            <div class="card">
                                <div class="card-header">{{ __('First Enter Your Phone Number') }}</div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Phone Number [*]') }}</label>

                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">+88</span> <!-- Country code as prefix -->
                                                <input type="tel"
                                                       class="form-control @error('phone') is-invalid @enderror"
                                                       id="phone"
                                                       name="phone"
                                                       value="{{ old('phone') }}"
                                                       placeholder="e.g. 01XXXXXXXXX"
                                                       minlength="11"
                                                       maxlength="11"
                                                       required
                                                       autofocus
                                                       pattern="[0-9]*"
                                                       inputmode="numeric"
                                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                                            </div>

                                            @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                              <strong>{{ $message }}</strong>
                                          </span>
                                            @enderror
                                        </div>


                                    </div>
                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right"></label>

                                        <div class="col-md-6">
                                            <div id="recaptcha-container"></div>
                                        </div>
                                    </div>

                                    <div id="myDIVsend" align="center" style="display: none;">
                                        <img src="{{asset('load.gif')}}" alt="Loading...">
                                    </div>

                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right"></label>

                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-primary" onclick="phoneAuth();">
                                                <i class="fas fa-paper-plane mr-1"></i> Send Code
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <br><br>
                            <div class="card">
                                <div class="card-header">{{ __('Verify with Verification Code') }}</div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Enter Verification Code [*]') }}</label>

                                        <div class="col-md-6">
                                            <input id="verificationCode" type="text" class="form-control" name="verificationCode" value="{{ old('verificationCode') }}" required="" >

                                            @error('verificationCode')
                                             <span class="invalid-feedback" role="alert">
                                                 <strong>{{ $message }}</strong>
                                             </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div id="myDIV" align="center" style="display: none;">
                                        <img src="{{asset('load.gif')}}" alt="Loading...">
                                    </div>
                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right"></label>

                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success" onclick="codeverify();">
                                                <i class="fas fa-check-circle mr-1"></i> Verify Code & Next
                                            </button>
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
@section('script')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script src="https://www.gstatic.com/firebasejs/6.0.2/firebase.js"></script>
    <script type="text/javascript">
        var numb = '';
        var sent_otp = '';
        var check = 0;

        console.log("⚡ Script loaded.");

        // Firebase config
        var firebaseConfig = {
            apiKey: "AIzaSyA3E80LldZKJJXE00O9-6DWAUtxeKadUM0",
            authDomain: "test-notification-2cc00.firebaseapp.com",
            databaseURL: "https://test-notification-2cc00.firebaseio.com",
            projectId: "test-notification-2cc00",
            storageBucket: "test-notification-2cc00.appspot.com",
            messagingSenderId: "909713642086",
            appId: "1:909713642086:web:5129fc58137f302d1353a7"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        console.log("✅ Firebase initialized.");

        window.onload = function () {
            console.log("🌍 Window loaded. Calling render().");
            render();
        };

        function render() {
            console.log("🖌 Rendering Firebase reCAPTCHA...");
            if (document.getElementById('recaptcha-container')) {
                try {
                    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                        size: 'normal',
                        callback: function(response) {
                            console.log("✅ Firebase reCAPTCHA solved:", response);
                        },
                        'expired-callback': function() {
                            console.warn("⚠️ Firebase reCAPTCHA expired.");
                        }
                    });
                    recaptchaVerifier.render().then(function(widgetId) {
                        console.log("✅ Firebase reCAPTCHA rendered with ID:", widgetId);
                    }).catch(function(err){
                        console.error("❌ Error rendering Firebase reCAPTCHA:", err);
                    });
                } catch(e) {
                    console.error("Firebase reCAPTCHA init failed", e);
                }
            }
        }


        function phoneAuth() {
            console.log("📱 phoneAuth() called.");

            var rawPhone = document.getElementById('phone').value.trim();

            if (rawPhone === '') {
                console.warn("⚠️ Phone number empty.");
                Swal.fire({
                    icon: 'warning',
                    title: 'Phone Required',
                    text: 'Please enter your mobile number.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                document.getElementById("phone").focus();
                return false;
            }

            if (rawPhone.length !== 11) {
                console.warn("⚠️ Phone number invalid length:", rawPhone.length);
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Number',
                    text: 'Mobile number must be 11 digits long.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                document.getElementById("phone").focus();
                return false;
            }

            var xc = document.getElementById("myDIVsend");
            xc.style.display = "block";

            // Step 1: Check if mobile already exists before sending OTP
            $.ajax({
                url: '{{ route('check-mobile-exists') }}',
                method: 'POST',
                data: { phone: rawPhone },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(checkRes) {
                    if (checkRes.exists) {
                        xc.style.display = "none";
                        Swal.fire({
                            icon: 'warning',
                            title: 'Already Registered',
                            text: 'Your mobile number already exists.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Step 2: Validate reCAPTCHA if rendered
                    var recaptchaResponse = '';
                    if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.getResponse === 'function') {
                        try {
                            recaptchaResponse = grecaptcha.getResponse();
                        } catch(e) {}
                    }

                    if (recaptchaResponse.length === 0 && document.getElementById('recaptcha-container')) {
                        xc.style.display = "none";
                        Swal.fire({
                            icon: 'warning',
                            title: 'Verification Required',
                            text: 'Please complete the reCAPTCHA verification.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }

                    var number = "88" + rawPhone;
                    numb = number;
                    console.log("➡️ Phone number prepared:", number);

                    // Step 3: Send OTP via AJAX
                    console.log("🚀 Sending AJAX request to sentverifyotp...");
                    $.ajax({
                        url: '{{ route('sentverifyotp') }}',
                        data: { id: number, gresp: recaptchaResponse },
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            console.log("✅ OTP sent response:", response);
                            check = 1;
                            xc.style.display = "none";
                            Swal.fire({
                                icon: 'success',
                                title: 'Code Sent',
                                text: 'Verification code has been sent to your mobile number.',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'OK'
                            });
                            $('#verificationCode').focus();
                        },
                        error: function(xhr) {
                            console.error("❌ Error sending OTP:", xhr);
                            xc.style.display = "none";
                            if (xhr.responseJSON && xhr.responseJSON.exists) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Already Registered',
                                    text: 'Your mobile number already exists.',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to send verification code.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error Sending Code',
                                    text: msg,
                                    confirmButtonColor: '#d33',
                                    confirmButtonText: 'OK'
                                });
                            }
                        }
                    });
                },
                error: function(err) {
                    xc.style.display = "none";
                    if (err.responseJSON && err.responseJSON.exists) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Already Registered',
                            text: 'Your mobile number already exists.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        var errorMsg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Error validating mobile number.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }

        function codeverify() {
            console.log("🔎 codeverify() called.");

            var code = document.getElementById('verificationCode').value.trim();
            console.log("➡️ Entered code:", code, " Phone:", numb);

            if (code === '') {
                console.warn("⚠️ No code entered.");
                Swal.fire({
                    icon: 'warning',
                    title: 'Required',
                    text: 'Please enter verification code.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                document.getElementById('verificationCode').focus();
                return false;
            }

            if (typeof check === 'undefined' || check == 0) {
                console.warn("⚠️ Check flag not set. OTP was not requested.");
                Swal.fire({
                    icon: 'warning',
                    title: 'Action Required',
                    text: 'Please click "Send Code" first to receive your verification code.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            var x = document.getElementById("myDIV");
            x.style.display = "block";

            console.log("🚀 Sending AJAX request to verify-mobile-submit...");
            $.ajax({
                type: "POST",
                url: '{{ route('verify-mobile-submit') }}',
                data: { code: code, numb: numb },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log("✅ Code verification response:", response);
                    x.style.display = "none";
                    if (response.success) {
                        console.log("🎉 Verification success, redirecting...");
                        Swal.fire({
                            icon: 'success',
                            title: 'Verified!',
                            text: 'Mobile number verified successfully.',
                            confirmButtonColor: '#28a745',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.replace("home");
                        });
                    } else {
                        if (response.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Already Registered',
                                text: 'Your mobile number already exists.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Verification Failed',
                                text: response.message || 'Verification failed. Please try again.',
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                },
                error: function(error) {
                    console.error("❌ Error verifying code:", error);
                    x.style.display = "none";
                    if (error.responseJSON && error.responseJSON.exists) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Already Registered',
                            text: 'Your mobile number already exists.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        var msg = (error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : 'Invalid verification code or server error.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Verification Error',
                            text: msg,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }
    </script>
@endsection

