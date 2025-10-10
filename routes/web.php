<?php

use App\Http\Controllers\ApplicationPostgraduateController;
use App\Http\Controllers\EligibilityApprovalController;
use App\Http\Controllers\FinalSubmitController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Models\Notice;


use App\Http\Controllers\BasicInfoController;
use App\Http\Controllers\EducationInfoController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\JobExperienceController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\AttachmentTypeController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\EligibilityDegreeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\EligibilityVerificationController;

use App\Http\Controllers\PgaPaymentApiController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*Route::get('/', fn () => view('welcome'))->name('welcome');*/

Route::get('/', function () {
    $notices = Notice::orderByDesc('date')->orderByDesc('id')->get();
    return view('welcome', compact('notices'));
})->name('welcome');

Route::get('/reg', function () {
    // Use the back() helper and pass an errors bag (works with $errors in views)
    return back()->withErrors(['notice' => 'Online application started from 28 May, 2023 at 09.00AM']);
})->name('reg');


Route::get('/notice', function () {
    $notices = Notice::orderByDesc('date')->orderByDesc('id')->get();
    return view('notice', compact('notices'));
})->name('notice');


// Auth routes provided by laravel/ui (installed in Step 2)
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');



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
    ->middleware('roles:admin,applicant,head')
    ->name('change-password');

Route::post('change-password-submit', [HomeController::class, 'update_password_submit'])
    ->middleware('roles:admin,applicant,head')
    ->name('change-password-submit');

Route::get('payment-report', [HomeController::class, 'payment_report'])
    ->middleware('roles:admin')
    ->name('payment-report');


// BASIC INFO
Route::prefix('basic_info')->name('basic_info.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [BasicInfoController::class, 'index'])->name('all');
    Route::get('add',            [BasicInfoController::class, 'create'])->name('add');
    Route::post('add',           [BasicInfoController::class, 'store'])->name('store');
    Route::get('view/{id}',      [BasicInfoController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [BasicInfoController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [BasicInfoController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [BasicInfoController::class, 'destroy'])->name('delete');
});

// EDUCATION INFO
Route::prefix('education_info')->name('education_info.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [EducationInfoController::class, 'index'])->name('all');
    Route::get('add',            [EducationInfoController::class, 'create'])->name('add');
    Route::post('add',           [EducationInfoController::class, 'store'])->name('store');
    Route::get('show/{id}',      [EducationInfoController::class, 'show'])->name('show');
    Route::get('edit/{id}',      [EducationInfoController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [EducationInfoController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [EducationInfoController::class, 'destroy'])->name('delete');
});

// THESES
Route::prefix('thesis')->name('thesis.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [ThesisController::class, 'index'])->name('all');
    Route::get('add',            [ThesisController::class, 'create'])->name('add');
    Route::post('add',           [ThesisController::class, 'store'])->name('store');
    Route::get('view/{id}',      [ThesisController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [ThesisController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [ThesisController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [ThesisController::class, 'destroy'])->name('delete');
});

// PUBLICATIONS
Route::prefix('publication')->name('publication.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [PublicationController::class, 'index'])->name('all');
    Route::get('add',            [PublicationController::class, 'create'])->name('add');
    Route::post('add',           [PublicationController::class, 'store'])->name('store');
    Route::get('view/{id}',      [PublicationController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [PublicationController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [PublicationController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [PublicationController::class, 'destroy'])->name('delete');
});

// JOB EXPERIENCES
Route::prefix('job_experience')->name('job_experience.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [JobExperienceController::class, 'index'])->name('all');
    Route::get('add',            [JobExperienceController::class, 'create'])->name('add');
    Route::post('add',           [JobExperienceController::class, 'store'])->name('store');
    Route::get('view/{id}',      [JobExperienceController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [JobExperienceController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [JobExperienceController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [JobExperienceController::class, 'destroy'])->name('delete');
});

// REFERENCES
Route::prefix('reference')->name('reference.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [ReferenceController::class, 'index'])->name('all');
    Route::get('add',            [ReferenceController::class, 'create'])->name('add');
    Route::post('add',           [ReferenceController::class, 'store'])->name('store');
    Route::get('view/{id}',      [ReferenceController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [ReferenceController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [ReferenceController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [ReferenceController::class, 'destroy'])->name('delete');
});

// ATTACHMENT TYPES
Route::prefix('attachment_type')->name('attachment_type.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [AttachmentTypeController::class, 'index'])->name('all');
    Route::get('add',            [AttachmentTypeController::class, 'create'])->name('add');
    Route::post('add',           [AttachmentTypeController::class, 'store'])->name('store');
    Route::get('view/{id}',      [AttachmentTypeController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [AttachmentTypeController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [AttachmentTypeController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [AttachmentTypeController::class, 'destroy'])->name('delete');
});

// ATTACHMENTS
Route::prefix('attachments')->name('attachments.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [AttachmentController::class, 'index'])->name('all');
    Route::get('add',            [AttachmentController::class, 'create'])->name('add');
    Route::post('add',           [AttachmentController::class, 'store'])->name('store');
    Route::get('view/{id}',      [AttachmentController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [AttachmentController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [AttachmentController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [AttachmentController::class, 'destroy'])->name('delete');

    //for upload and delete
    Route::post('upload',  [AttachmentController::class, 'upload'])->name('upload');
    Route::delete('{id}',  [AttachmentController::class, 'destroy'])->name('delete');
});

// ELIGIBILITY DEGREES
Route::prefix('eligibility_degree')->name('eligibility_degree.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [EligibilityDegreeController::class, 'index'])->name('all');
    Route::get('add',            [EligibilityDegreeController::class, 'create'])->name('add');
    Route::post('add',           [EligibilityDegreeController::class, 'store'])->name('store');
    Route::get('view/{id}',      [EligibilityDegreeController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [EligibilityDegreeController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [EligibilityDegreeController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [EligibilityDegreeController::class, 'destroy'])->name('delete');
});

// SETTINGS
Route::prefix('setting')->name('setting.')->middleware('roles:admin,applicant')->group(function () {
    Route::get('all',            [SettingController::class, 'index'])->name('all');
    Route::get('add',            [SettingController::class, 'create'])->name('add');
    Route::post('add',           [SettingController::class, 'store'])->name('store');
    Route::get('view/{id}',      [SettingController::class, 'show'])->name('view');
    Route::get('edit/{id}',      [SettingController::class, 'edit'])->name('edit');
    Route::put('update/{id}',    [SettingController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [SettingController::class, 'destroy'])->name('delete');
});

Route::get('applicant/eligibility-form/{id}', [EligibilityVerificationController::class, 'create'])
    ->name('applicant.eligibility_master_form')
    ->middleware(['auth','roles:admin,applicant,head']);

Route::get('applicant/application-postgraduate-form/{id}', [ApplicationPostgraduateController::class, 'create'])
    ->name('applicant.application_postgraduate_master_form')
    ->middleware(['auth','roles:admin,applicant,head']);

Route::post('/attachments/ajax-upload', [AttachmentController::class, 'ajaxUpload'])
    ->name('attachments.ajaxUpload')
    ->middleware(['auth','roles:admin,applicant']);

Route::delete('/attachments/{attachment}/ajax-delete', [AttachmentController::class, 'ajaxDelete'])
    ->name('attachments.ajaxDelete')
    ->middleware(['auth','roles:admin,applicant']);


//not working
Route::post('bkash_pull', [PaymentController::class, 'bkash_pull'])->name('bkash_pull');
Route::post('bkash_push', [PaymentController::class, 'bkash_push'])->name('bkash_push');
Route::post('bkash_check', [PaymentController::class, 'bkash_check'])->name('bkash_check');

// mobile number verification(working)
Route::get('/verify-mobile', [HomeController::class, 'verify_mobile'])->name('verify-mobile');
Route::post('/verify-mobile-submit', [HomeController::class, 'verify_mobile_submit'])->name('verify-mobile-submit');

Route::post('/sentverifyotp', [HomeController::class, 'sentverifyotp'])->name('sentverifyotp');

//no need
/*Route::get('phone-verification', [HomeController::class, 'phone_verification'])
    ->middleware('roles:applicant')
    ->name('phone-verification');

Route::post('phone-verification-submit', [HomeController::class, 'phone_verification_submit'])
    ->middleware('roles:applicant')
    ->name('phone-verification-submit');

Route::post('phone-verify-submit', [HomeController::class, 'phone_verify_submit'])
    ->middleware('roles:applicant')
    ->name('phone-verify-submit');*/


Route::post('/final-submit/eligibility/{applicant}', [FinalSubmitController::class, 'submitEligibility'])
    ->name('final.submit.eligibility')
    ->middleware('roles:applicant');

Route::post('/final-submit/application/{applicant}', [FinalSubmitController::class, 'submitApplication'])
    ->name('final.submit.application')
    ->middleware('roles:applicant');

Route::get('applicant/preview-admission-form/{applicant}', [ApplicationPostgraduateController::class, 'preview'])
    ->name('applicant.preview.admission.form')
    ->middleware('auth');

Route::get('applicant/preview-eligibility-form/{applicant}', [ApplicationPostgraduateController::class, 'eligibility'])
    ->name('applicant.preview.eligibility.form')
    ->middleware('auth');


//Payment Route
Route::get('/bkash-pull', [PgaPaymentApiController::class, 'bkashPull']);
Route::get('/bkash-push', [PgaPaymentApiController::class, 'bkashPush']);
Route::get('/agrani-pull', [PgaPaymentApiController::class, 'agraniPull']);
Route::get('/agrani-push', [PgaPaymentApiController::class, 'agraniPush']);


//showing full page for approve
Route::get('approve-eligibility', [HomeController::class, 'approve_eligibility'])
    ->middleware('roles:admin,head')
    ->name('approve-eligibility');

//approve individual applicant using sweetalert
Route::post('approve-eligibility/{applicant}', [EligibilityApprovalController::class, 'toggle'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-eligibility.toggle');
