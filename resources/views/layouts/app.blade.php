<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 4.1.3 (CDN) -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
          crossorigin="anonymous">

    @yield('css')
</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navbar"
                aria-controls="main-navbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="main-navbar">
            <!-- Left -->
            <ul class="navbar-nav mr-auto">
                <!-- add left links if needed -->
            </ul>

            <!-- Right -->
            <ul class="navbar-nav ml-auto">
                @guest
                    <li class="nav-item mr-2">
                        <a class="nav-link btn btn-success text-white" href="{{ route('login') }}">
                            {{ __('Login') }}
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link btn btn-info text-white" href="{{ url('register') }}">
                                {{ __('Create Applicant Account') }}
                            </a>
                        </li>
                    @endif
                @else
                    @if (Auth::user()->user_type == 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="studentsDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Students
                            </a>
                            <div class="dropdown-menu" aria-labelledby="studentsDropdown">
                                <a class="dropdown-item" href="{{ url('select-department-update-student-status') }}">Admin Status</a>
                                <a class="dropdown-item" href="{{ url('select-department-view-student-status') }}">View Status</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('payment-report') }}">Accounts Report</a>
                        </li>
                    @endif

                    @if (Auth::user()->user_type == 'applicant')
                        <li class="nav-item"><a class="nav-link" href="{{ url('home') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('notice') }}">Notice</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('apply-now') }}">Apply</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('my-application') }}">My Applications</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('how-to-pay') }}">How to Pay?</a></li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="{{ url('change-password') }}">Change Password</a>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<!-- Push content below fixed navbar -->
<div class="container" style="padding-top: 70px;">
    @yield('content')
</div>

<!-- JS (CDN): jQuery -> Popper -> Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>

@yield('script')
</body>
</html>
