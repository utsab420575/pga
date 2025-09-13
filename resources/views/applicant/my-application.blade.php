@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">

                {{-- Error Messages --}}
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <p class="alert alert-danger mb-2">{{ $error }}</p>
                    @endforeach
                @endif

                {{-- Status Message --}}
                @if(session('Status'))
                    <p class="alert alert-info">{{ session('Status') }}</p>
                @endif

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <b>{{ __('My Applications') }}</b>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table  align-middle mb-0">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Sl</th>
                                    <th scope="col">Program</th>
                                    <th scope="col">Department/Institute</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Application Type</th>
                                    <th scope="col">Applicant ID</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Payment Status</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($i = 1)
                                @foreach($applications as $application)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $application->degree->degree_name }}</td>
                                        <td>{{ $application->department->short_name }}</td>
                                        <td>{{ $application->studenttype->type }}</td>
                                        <td>{{ $application->applicationtype->type }}</td>
                                        <td><span class="badge badge-secondary">{{ $application->roll }}</span></td>
                                        <td>{{ number_format($application->applicationtype->fee) }}</td>
                                        <td>
                                            @if($application->payment_status == 0)
                                                <span class="badge badge-danger">Unpaid</span>
                                            @else
                                                <span class="badge badge-success">Paid</span>
                                                <br>
                                                <small class="text-muted">TRXID:
                                                    <b>{{ $application->payment->trxid }}</b></small>
                                            @endif
                                        </td>
                                        <td class="text-center">

                                            {{-- ✏️ Edit button (allowed if edit_per=1 or unpaid) --}}
                                            @if($application->edit_per == 1 || $application->payment_status == 0)
                                                <a href="{{ url('edit-application/'.$application->id) }}"
                                                   class="btn btn-warning btn-sm mb-1"
                                                   target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            @endif

                                            {{--  Submit or View form button --}}
                                            @if($application->payment_status == 1 && in_array((int)$application->applicationtype_id, [1, 2], true))

                                                @if($application->final_submit == 1)
                                                    {{-- ✅ Already submitted → show View button --}}
                                                    <a href="{{ (int)$application->applicationtype_id === 1
                                                            ? route('applicant.preview.admission.form', $application->id)
                                                            : route('applicant.preview.eligibility.form', $application->id) }}"
                                                       class="btn btn-primary btn-sm mb-1"
                                                       target="_blank" rel="noopener noreferrer">
                                                        <i class="fas fa-eye"></i>
                                                        {{ (int)$application->applicationtype_id === 1 ? 'View Application' : 'View Eligibility Form' }}
                                                    </a>
                                                @else
                                                    {{-- 📝 Not yet submitted → show Submit button --}}
                                                    <a href="{{ (int)$application->applicationtype_id === 1
                                                            ? url('applicant/application-postgraduate-form/'.$application->id)
                                                            : url('applicant/eligibility-form/'.$application->id) }}"
                                                       class="btn btn-success btn-sm mb-1"
                                                       target="_blank" rel="noopener noreferrer">
                                                        <i class="fas fa-paper-plane"></i>
                                                        {{ (int)$application->applicationtype_id === 1 ? 'Submit Application' : 'Submit Eligibility Form' }}
                                                    </a>
                                                @endif

                                            @endif

                                            {{-- 🖨 Print button (after final submit) --}}
                                            @if($application->final_submit == 1)
                                                @if($application->applicationtype_id == 1)
                                                    <a href="{{ route('applicant.preview.admission.form', $application->id) }}"
                                                       class="btn btn-info btn-sm mb-1"
                                                       target="_blank">
                                                        <i class="fas fa-print"></i> Print Application
                                                    </a>
                                                @elseif($application->applicationtype_id == 2)
                                                    <a href="{{ route('applicant.preview.eligibility.form', $application->id) }}"
                                                       class="btn btn-info btn-sm mb-1"
                                                       target="_blank">
                                                        <i class="fas fa-print"></i> Print Eligibility Form
                                                    </a>
                                                @endif
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
