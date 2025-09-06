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
                                            <button type="button" onclick="phoneAuth();">Send Code</button>
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
                                        <img src="public/load.gif">
                                    </div>
                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right"></label>

                                        <div class="col-md-6">
                                            <button type="button" onclick="codeverify();">Verify Code & Next</button>
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
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                size: 'normal',   // can be 'invisible' if you want auto
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
        }


        function phoneAuth() {
            console.log("📱 phoneAuth() called.");

            var number = "88" + document.getElementById('phone').value;
            numb = number;
            console.log("➡️ Phone number prepared:", number);

            var xc = document.getElementById("myDIVsend");
            if (xc.style.display === "none") {
                xc.style.display = "block";
                console.log("⏳ Loader shown.");
            } else {
                xc.style.display = "none";
                console.log("❌ Loader toggled off unexpectedly.");
            }

            if (number === '' || number === '88') {
                console.warn("⚠️ Phone number empty.");
                alert("Please enter your mobile number");
                document.getElementById("phone").focus();
                xc.style.display = "none";
                return false;
            }
            if (number.length != 13) {
                console.warn("⚠️ Phone number invalid length:", number.length);
                alert("Mobile number must be 11 digits long.");
                document.getElementById("phone").focus();
                xc.style.display = "none";
                return false;
            }

            var recaptchaResponse = grecaptcha.getResponse();
            console.log("🔑 reCAPTCHA response token:", recaptchaResponse);

            if (recaptchaResponse.length === 0) {
                console.warn("⚠️ reCAPTCHA not solved.");
                alert("Please complete the reCAPTCHA verification");
                xc.style.display = "none";
                return false;
            }

            console.log("🚀 Sending AJAX request to sentverifyotp...");
            $.ajax({
                url: '{{ URL('sentverifyotp') }}',
                data: { id: number, gresp: recaptchaResponse },
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log("✅ OTP sent response:", response);
                    check = 1;
                    alert("Message sent");
                    $('#verificationCode').focus();
                    xc.style.display = "none";
                },
                error: function(error) {
                    console.error("❌ Error sending OTP:", error);
                    alert('Error: ' + error.responseJSON.message);
                    xc.style.display = "none";
                }
            });
        }

        function codeverify() {
            console.log("🔎 codeverify() called.");

            var code = document.getElementById('verificationCode').value;
            console.log("➡️ Entered code:", code, " Phone:", numb);

            if (code === '') {
                console.warn("⚠️ No code entered.");
                alert("Please enter verification code");
                return false;
            }

            if (typeof check === 'undefined' || check == 0) {
                console.warn("⚠️ Check flag not set. OTP was not requested.");
                alert("Please try to get the code again.");
                return false;
            }

            var x = document.getElementById("myDIV");
            if (x.style.display === "none") {
                x.style.display = "block";
                console.log("⏳ Loader shown for code verification.");
            } else {
                x.style.display = "none";
                console.log("❌ Loader toggled off unexpectedly.");
            }

            console.log("🚀 Sending AJAX request to verify-mobile-submit...");
            $.ajax({
                type: "POST",
                url: 'verify-mobile-submit',
                data: { code: code, numb: numb },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log("✅ Code verification response:", response);
                    if (response.success) {
                        console.log("🎉 Verification success, redirecting...");
                        window.location.replace("home");
                    } else {
                        console.warn("⚠️ Verification failed response:", response);
                        alert('Verification failed. Please try again.');
                    }
                },
                error: function(error) {
                    console.error("❌ Error verifying code:", error);
                    alert('Error: ' + error.responseJSON.message);
                    x.style.display = "none";
                }
            });
        }
    </script>
@endsection

