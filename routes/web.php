<?php

use App\Http\Controllers\AdmissionApprovalController;
use App\Http\Controllers\ApplicationPostgraduateController;
use App\Http\Controllers\EligibilityApprovalController;
use App\Http\Controllers\FinalSubmitController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MaintenanceController;
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

// Admin\Settings controllers
use App\Http\Controllers\Admin\ApplicationtypeController as AdminApplicationtypeController;
use App\Http\Controllers\Admin\AttachmentTypeController as AdminAttachmentTypeController;
use App\Http\Controllers\Admin\DegreeController as AdminDegreeController;
use App\Http\Controllers\Admin\FacultyController as AdminFacultyController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\StudenttypeController as AdminStudenttypeController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\DepartmentDegreeController as AdminDepartmentDegreeController;
use App\Http\Controllers\Admin\GithubDeployController;
use App\Http\Controllers\Admin\PasswordController as AdminPasswordController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;

use App\Http\Controllers\PgaPaymentApiController;
use Illuminate\Support\Facades\Artisan;



Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return 'All caches cleared';
});

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



Route::get('apply-now', [HomeController::class, 'apply_landing'])
    ->middleware('roles:applicant')
    ->name('apply-now');

// Eligibility Application form page (private university — type 2)
Route::get('apply-eligibility', [HomeController::class, 'apply_eligibility'])
    ->middleware('roles:applicant')
    ->name('apply-eligibility');

// Admission Application form page (public / approved / previously approved — type 1)
Route::get('apply-admission', [HomeController::class, 'apply_admission'])
    ->middleware('roles:applicant')
    ->name('apply-admission');

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
Route::post('/check-mobile-exists', [HomeController::class, 'check_mobile_exists'])->name('check-mobile-exists');

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

// Download documents for eligibility
Route::get('approve-eligibility/download', [EligibilityApprovalController::class, 'downloadDepartment'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-eligibility.download');

// Download Excel/CSV for Eligibility Applicant List
Route::get('approve-eligibility/export', [EligibilityApprovalController::class, 'exportDepartmentInfo'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-eligibility.export');


//giving approval to admission applicant
//showing full page for approve
Route::get('approve-admission', [HomeController::class, 'approve_admission'])
    ->middleware('roles:admin,head')
    ->name('approve-admission');

//approve individual applicant using sweetalert
Route::post('approve-admission/{applicant}', [AdmissionApprovalController::class, 'toggle'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-admission.toggle');


//check student
Route::get('pgaStudentCheck', [PgaPaymentApiController::class, 'pgaStudentCheck']);



//Fill Photo Signature
Route::get('maintenance/backfill-photo-sign', [MaintenanceController::class, 'backfillPhotoSign'])
    ->middleware(['auth','roles:admin']);


//download documents
Route::get('approve-admission/download', [AdmissionApprovalController::class, 'downloadDepartment'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-admission.download');

// Download Excel for Applicant List
Route::get('approve-admission/export', [AdmissionApprovalController::class, 'exportDepartmentInfo'])
    ->middleware(['auth','roles:admin,head'])
    ->name('approve-admission.export');


// ============================================================
// ADMIN SETTINGS PANEL — Full CRUD for 8 entities
// ============================================================
Route::prefix('admin/settings')->middleware(['auth', 'roles:admin'])->group(function () {

    // Application Types
    Route::get('applicationtypes',              [AdminApplicationtypeController::class, 'index'])->name('admin.applicationtypes.index');
    Route::get('applicationtypes/create',       [AdminApplicationtypeController::class, 'create'])->name('admin.applicationtypes.create');
    Route::post('applicationtypes',             [AdminApplicationtypeController::class, 'store'])->name('admin.applicationtypes.store');
    Route::get('applicationtypes/{id}/edit',    [AdminApplicationtypeController::class, 'edit'])->name('admin.applicationtypes.edit');
    Route::put('applicationtypes/{id}',         [AdminApplicationtypeController::class, 'update'])->name('admin.applicationtypes.update');
    Route::get('applicationtypes/{id}/delete',  [AdminApplicationtypeController::class, 'destroy'])->name('admin.applicationtypes.destroy');

    // Attachment Types
    Route::get('attachment_types',              [AdminAttachmentTypeController::class, 'index'])->name('admin.attachment_types.index');
    Route::get('attachment_types/create',       [AdminAttachmentTypeController::class, 'create'])->name('admin.attachment_types.create');
    Route::post('attachment_types',             [AdminAttachmentTypeController::class, 'store'])->name('admin.attachment_types.store');
    Route::get('attachment_types/{id}/edit',    [AdminAttachmentTypeController::class, 'edit'])->name('admin.attachment_types.edit');
    Route::put('attachment_types/{id}',         [AdminAttachmentTypeController::class, 'update'])->name('admin.attachment_types.update');
    Route::get('attachment_types/{id}/delete',  [AdminAttachmentTypeController::class, 'destroy'])->name('admin.attachment_types.destroy');

    // Degrees
    Route::get('degrees',              [AdminDegreeController::class, 'index'])->name('admin.degrees.index');
    Route::get('degrees/create',       [AdminDegreeController::class, 'create'])->name('admin.degrees.create');
    Route::post('degrees',             [AdminDegreeController::class, 'store'])->name('admin.degrees.store');
    Route::get('degrees/{id}/edit',    [AdminDegreeController::class, 'edit'])->name('admin.degrees.edit');
    Route::put('degrees/{id}',         [AdminDegreeController::class, 'update'])->name('admin.degrees.update');
    Route::get('degrees/{id}/delete',  [AdminDegreeController::class, 'destroy'])->name('admin.degrees.destroy');

    // Faculties
    Route::get('faculties',              [AdminFacultyController::class, 'index'])->name('admin.faculties.index');
    Route::get('faculties/create',       [AdminFacultyController::class, 'create'])->name('admin.faculties.create');
    Route::post('faculties',             [AdminFacultyController::class, 'store'])->name('admin.faculties.store');
    Route::get('faculties/{id}/edit',    [AdminFacultyController::class, 'edit'])->name('admin.faculties.edit');
    Route::put('faculties/{id}',         [AdminFacultyController::class, 'update'])->name('admin.faculties.update');
    Route::get('faculties/{id}/delete',  [AdminFacultyController::class, 'destroy'])->name('admin.faculties.destroy');

    // Notices
    Route::get('notices',              [AdminNoticeController::class, 'index'])->name('admin.notices.index');
    Route::get('notices/create',       [AdminNoticeController::class, 'create'])->name('admin.notices.create');
    Route::post('notices',             [AdminNoticeController::class, 'store'])->name('admin.notices.store');
    Route::get('notices/{id}/edit',    [AdminNoticeController::class, 'edit'])->name('admin.notices.edit');
    Route::put('notices/{id}',         [AdminNoticeController::class, 'update'])->name('admin.notices.update');
    Route::get('notices/{id}/delete',  [AdminNoticeController::class, 'destroy'])->name('admin.notices.destroy');

    // Settings
    Route::get('settings',              [AdminSettingController::class, 'index'])->name('admin.settings.index');
    Route::get('settings/create',       [AdminSettingController::class, 'create'])->name('admin.settings.create');
    Route::post('settings',             [AdminSettingController::class, 'store'])->name('admin.settings.store');
    Route::get('settings/{id}/edit',    [AdminSettingController::class, 'edit'])->name('admin.settings.edit');
    Route::put('settings/{id}',         [AdminSettingController::class, 'update'])->name('admin.settings.update');
    Route::get('settings/{id}/delete',  [AdminSettingController::class, 'destroy'])->name('admin.settings.destroy');

    // Student Types
    Route::get('studenttypes',              [AdminStudenttypeController::class, 'index'])->name('admin.studenttypes.index');
    Route::get('studenttypes/create',       [AdminStudenttypeController::class, 'create'])->name('admin.studenttypes.create');
    Route::post('studenttypes',             [AdminStudenttypeController::class, 'store'])->name('admin.studenttypes.store');
    Route::get('studenttypes/{id}/edit',    [AdminStudenttypeController::class, 'edit'])->name('admin.studenttypes.edit');
    Route::put('studenttypes/{id}',         [AdminStudenttypeController::class, 'update'])->name('admin.studenttypes.update');
    Route::get('studenttypes/{id}/delete',  [AdminStudenttypeController::class, 'destroy'])->name('admin.studenttypes.destroy');

    // Departments
    Route::get('departments',              [AdminDepartmentController::class, 'index'])->name('admin.departments.index');
    Route::get('departments/create',       [AdminDepartmentController::class, 'create'])->name('admin.departments.create');
    Route::post('departments',             [AdminDepartmentController::class, 'store'])->name('admin.departments.store');
    Route::get('departments/{id}/edit',    [AdminDepartmentController::class, 'edit'])->name('admin.departments.edit');
    Route::put('departments/{id}',         [AdminDepartmentController::class, 'update'])->name('admin.departments.update');
    Route::get('departments/{id}/delete',  [AdminDepartmentController::class, 'destroy'])->name('admin.departments.destroy');

    // Department-Degree Mapping
    Route::get('department_degrees',          [AdminDepartmentDegreeController::class, 'index'])->name('admin.department_degrees.index');
    Route::get('department_degrees/{id}/edit',[AdminDepartmentDegreeController::class, 'edit'])->name('admin.department_degrees.edit');
    Route::put('department_degrees/{id}',     [AdminDepartmentDegreeController::class, 'update'])->name('admin.department_degrees.update');

    // Passwords
    Route::get('password/head',        [AdminPasswordController::class, 'headIndex'])->name('admin.passwords.head');
    Route::post('password/head/reset', [AdminPasswordController::class, 'headReset'])->name('admin.passwords.head.reset');
    Route::get('password/applicant',   [AdminPasswordController::class, 'applicantIndex'])->name('admin.passwords.applicant');

    // Activity Log
    Route::get('activity-log', [AdminActivityLogController::class, 'index'])->name('admin.activity_logs.index');
});

// ============================================================
// ADMIN — GitHub Deploy (pull latest code from GitHub)
// ============================================================
Route::prefix('admin/github-deploy')->middleware(['auth', 'roles:admin'])->group(function () {
    Route::get('/',      [GithubDeployController::class, 'index'])->name('admin.github_deploy.index');
    Route::post('fetch', [GithubDeployController::class, 'fetch'])->name('admin.github_deploy.fetch');
    Route::post('pull',  [GithubDeployController::class, 'pull'])->name('admin.github_deploy.pull');
});
