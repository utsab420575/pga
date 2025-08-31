@extends('layouts.app')

@section('css')
<script src="{{ asset('public/js/jquery-3.1.1.min.js') }}"></script>
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

            <div class="card">

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
                                        <input id="phone" type="phone" class="form-control" name="phone" value="{{ old('phone') }}" required="" placeholder="e.g. +8801XXXXXXXXX" autofocus>

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
    // Your web app's Firebase configuration
    var firebaseConfig = {
        apiKey: "AIzaSyA3E80LldZKJJXE00O9-6DWAUtxeKadUM0",
        authDomain: "test-notification-2cc00.firebaseapp.com",
        databaseURL: "https://test-notification-2cc00.firebaseio.com",
        projectId: "test-notification-2cc00",
        storageBucket: "test-notification-2cc00.appspot.com",
        messagingSenderId: "909713642086",
        appId: "1:909713642086:web:5129fc58137f302d1353a7"
    };
    var check = 0;
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    window.onload=function () {
      render();
    };
    function render() {
        window.recaptchaVerifier=new firebase.auth.RecaptchaVerifier('recaptcha-container');
        recaptchaVerifier.render();
    }
    function phoneAuth() {
        //get the number
        
        var number=document.getElementById('phone').value;
        if(number=='')
        {
            alert("Please enter your mobile number");
            document.getElementById("phone").focus();
            return false;
        }
        //phone number authentication function of firebase
        //it takes two parameter first one is number,,,second one is recaptcha
        firebase.auth().signInWithPhoneNumber(number,window.recaptchaVerifier).then(function (confirmationResult) {
            //s is in lowercase
            window.confirmationResult=confirmationResult;
            coderesult=confirmationResult;
            console.log(coderesult);
            alert("Message sent");
            check = 1;
            $('#verificationCode').focus();
        }).catch(function (error) {
            alert(error.message);
        });
    }
    function codeverify() {
        
        var code=document.getElementById('verificationCode').value;
        if(code==''){
            alert("Please enter verification code");
            return false;
        }
            
        if(check==0)
        {
            alert("Please try to get the code again.");
            return false;
        }

        var x = document.getElementById("myDIV");
          if (x.style.display === "none") {
            x.style.display = "block";
          } else {
            x.style.display = "none";
          }
        coderesult.confirm(code).then(function (result) {
            var user=result.user;
            var phone=result.user.phoneNumber;
            var data = { phone : phone };
              $.ajax({
                type: "POST",
                url: 'phone-verification-submit',
                data: data,
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                  window.location.replace("home");
                },
                error: function() {
                  alert('Duplicate Phone Number!! Try with another phone number.');
                  x.style.display = "none";
                }
              });
            console.log(user);
        }).catch(function (error) {
            alert(error.message);
            x.style.display = "none";
        });
    }
</script>
@endsection