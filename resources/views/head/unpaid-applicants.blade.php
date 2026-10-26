@extends('layouts.app')

@section('css')
    <style>
        .table thead th { white-space: nowrap; }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h3>{{ $title }} &mdash; Unpaid Applicants</h3>
                <small class="text-muted">
                    @if(auth()->user()->user_type === 'admin')
                        Showing: All Departments
                    @else
                        Showing: {{ optional(auth()->user()->department)->full_name ?? 'Unknown Dept' }}
                    @endif
                </small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="alert alert-warning py-2 mb-0">
                    Listing applicants with <strong>Payment = Not Done</strong>.
                </div>
            </div>
        </div>

        @php
            $grouped = $applicants->groupBy(function ($a) {
                return optional($a->department)->short_name ?? 'Unknown';
            });
        @endphp

        @forelse($grouped as $deptName => $rows)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Department/Institute: {{ $deptName }}</strong>
                    <span class="badge badge-secondary">{{ $rows->count() }} applicant(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Department</th>
                                <th>Applicant Roll</th>
                                <th>Name</th>
                                <th>Applied On</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($row->department)->short_name ?? '-' }}</td>
                                    <td>{{ $row->roll }}</td>
                                    <td>
                                        {{ optional($row->user)->name }} <br>
                                        {{ optional($row->user)->phone }}
                                    </td>
                                    <td>{{ $row->created_at ? $row->created_at->format('d M Y') : '-' }}</td>
                                    <td><span class="badge badge-danger">Unpaid</span></td>
                                    <td></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                No unpaid applicants found.
            </div>
        @endforelse
    </div>
@endsection
