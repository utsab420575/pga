<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'))->name('welcome');

Route::get('/reg', function () {
    // Use the back() helper and pass an errors bag (works with $errors in views)
    return back()->withErrors(['notice' => 'Online application started from 28 May, 2023 at 09.00AM']);
})->name('reg');

Route::get('/notice', fn () => view('notice'))->name('notice');

// Auth routes provided by laravel/ui (installed in Step 2)
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('phone-verification', [HomeController::class, 'phone_verification'])
    ->middleware('roles:applicant')
    ->name('phone-verification');

Route::post('phone-verification-submit', [HomeController::class, 'phone_verification_submit'])
    ->middleware('roles:applicant')
    ->name('phone-verification-submit');

Route::get('apply-now', [HomeController::class, 'apply_now'])
    ->middleware('roles:applicant')
    ->name('apply-now');

Route::post('apply-now-submit', [HomeController::class, 'apply_now_submit'])
    ->middleware('roles:applicant')
    ->name('apply-now-submit');

Route::get('my-application', [HomeController::class, 'my_application'])
    ->middleware('roles:applicant')
    ->name('my-application');

Route::get('edit-application/{id}', [HomeController::class, 'edit_application'])
    ->middleware('roles:applicant')
    ->whereNumber('id')
    ->name('edit-application');

Route::post('edit-application-submit/{id}', [HomeController::class, 'edit_application_submit'])
    ->middleware('roles:applicant')
    ->whereNumber('id')
    ->name('edit-application-submit');

Route::get('how-to-pay', [HomeController::class, 'how_to_pay'])
    ->middleware('roles:applicant')
    ->name('how-to-pay');

Route::get('application/{id}', [HomeController::class, 'application'])
    ->middleware('roles:applicant')
    ->whereNumber('id')
    ->name('application');

Route::get('change-password', [HomeController::class, 'update_password'])
    ->middleware('roles:admin,applicant')
    ->name('change-password');

Route::post('change-password-submit', [HomeController::class, 'update_password_submit'])
    ->middleware('roles:admin,applicant')
    ->name('change-password-submit');

Route::get('payment-report', [HomeController::class, 'payment_report'])
    ->middleware('roles:admin')
    ->name('payment-report');
