@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus mr-2"></i>Add System Setting</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.settings.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="session">Session</label>
                            <input type="text" id="session" name="session"
                                   class="form-control @error('session') is-invalid @enderror"
                                   value="{{ old('session') }}" placeholder="e.g. 2025-2026">
                            @error('session')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-muted font-weight-bold border-bottom pb-1 mt-3">Application Period</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}">
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}">
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="last_payment_date">Last Payment Date</label>
                            <input type="date" id="last_payment_date" name="last_payment_date"
                                   class="form-control @error('last_payment_date') is-invalid @enderror"
                                   value="{{ old('last_payment_date') }}">
                            @error('last_payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-muted font-weight-bold border-bottom pb-1 mt-3">Eligibility Period</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="eligibility_start_date">Eligibility Start Date</label>
                                <input type="date" id="eligibility_start_date" name="eligibility_start_date"
                                       class="form-control @error('eligibility_start_date') is-invalid @enderror"
                                       value="{{ old('eligibility_start_date') }}">
                                @error('eligibility_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="eligibility_last_date">Eligibility Last Date</label>
                                <input type="date" id="eligibility_last_date" name="eligibility_last_date"
                                       class="form-control @error('eligibility_last_date') is-invalid @enderror"
                                       value="{{ old('eligibility_last_date') }}">
                                @error('eligibility_last_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="last_eligibility_payment_date">Last Eligibility Payment Date</label>
                            <input type="date" id="last_eligibility_payment_date" name="last_eligibility_payment_date"
                                   class="form-control @error('last_eligibility_payment_date') is-invalid @enderror"
                                   value="{{ old('last_eligibility_payment_date') }}">
                            @error('last_eligibility_payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <h6 class="text-muted font-weight-bold border-bottom pb-1 mt-3">Eligibility Approval Period (Department Head)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="eligibility_approval_start_date">Eligibility Approval Start Date</label>
                                <input type="date" id="eligibility_approval_start_date" name="eligibility_approval_start_date"
                                       class="form-control @error('eligibility_approval_start_date') is-invalid @enderror"
                                       value="{{ old('eligibility_approval_start_date') }}">
                                @error('eligibility_approval_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="eligibility_approval_last_date">Eligibility Approval Last Date</label>
                                <input type="date" id="eligibility_approval_last_date" name="eligibility_approval_last_date"
                                       class="form-control @error('eligibility_approval_last_date') is-invalid @enderror"
                                       value="{{ old('eligibility_approval_last_date') }}">
                                @error('eligibility_approval_last_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-muted font-weight-bold border-bottom pb-1 mt-3">Admission Approval Period (Department Head)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="admission_approval_start_date">Admission Approval Start Date</label>
                                <input type="date" id="admission_approval_start_date" name="admission_approval_start_date"
                                       class="form-control @error('admission_approval_start_date') is-invalid @enderror"
                                       value="{{ old('admission_approval_start_date') }}">
                                @error('admission_approval_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="admission_approval_last_date">Admission Approval Last Date</label>
                                <input type="date" id="admission_approval_last_date" name="admission_approval_last_date"
                                       class="form-control @error('admission_approval_last_date') is-invalid @enderror"
                                       value="{{ old('admission_approval_last_date') }}">
                                @error('admission_approval_last_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h6 class="text-muted font-weight-bold border-bottom pb-1 mt-3">API Configuration</h6>
                        <div class="form-group">
                            <label for="sms_api">SMS API Key</label>
                            <textarea id="sms_api" name="sms_api" rows="2"
                                      class="form-control @error('sms_api') is-invalid @enderror">{{ old('sms_api') }}</textarea>
                            @error('sms_api')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="google_auth_api">Google Auth API</label>
                            <textarea id="google_auth_api" name="google_auth_api" rows="2"
                                      class="form-control @error('google_auth_api') is-invalid @enderror">{{ old('google_auth_api') }}</textarea>
                            @error('google_auth_api')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
