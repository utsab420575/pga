@extends('layouts.app')

@section('css')
    <style>
        .stat-card { border: 0; border-radius: 8px; box-shadow: 0 6px 16px rgba(46, 61, 73, 0.12); }
        .stat-card .stat-icon { font-size: 2.2rem; opacity: .85; }
        .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: .85rem; text-transform: uppercase; letter-spacing: .04em; }
        .table thead th { white-space: nowrap; }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h3>Dashboard</h3>
                <small class="text-muted">
                    {{ optional(auth()->user()->department)->full_name ?? 'Unknown Dept' }}
                </small>
            </div>
        </div>

        {{-- Statistics, application type wise (2 = Eligibility first, then 1 = Admission Test) --}}
        @foreach($applicationtypes as $type)
            @php
                $paidCount   = $byType[$type->id]['paid']->count();
                $unpaidCount = $byType[$type->id]['unpaid']->count();
            @endphp
            <h5 class="mb-2">{{ $type->type }}</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">{{ $paidCount + $unpaidCount }}</div>
                                <div class="stat-label">Total Applicants</div>
                            </div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">{{ $paidCount }}</div>
                                <div class="stat-label">Paid Applicants</div>
                            </div>
                            <i class="fas fa-check-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">{{ $unpaidCount }}</div>
                                <div class="stat-label">Unpaid Applicants</div>
                            </div>
                            <i class="fas fa-times-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Application type wise statistics + lists --}}
        @foreach($applicationtypes as $type)
            @php
                $paid   = $byType[$type->id]['paid'];
                $unpaid = $byType[$type->id]['unpaid'];
            @endphp

            <div class="card mb-4 mt-2">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <strong>{{ $type->type }}</strong>
                    <div>
                        <span class="badge badge-primary">Total: {{ $paid->count() + $unpaid->count() }}</span>
                        <span class="badge badge-success">Paid: {{ $paid->count() }}</span>
                        <span class="badge badge-danger">Unpaid: {{ $unpaid->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#paid-{{ $type->id }}" role="tab">
                                Paid ({{ $paid->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#unpaid-{{ $type->id }}" role="tab">
                                Unpaid ({{ $unpaid->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        @foreach(['paid' => $paid, 'unpaid' => $unpaid] as $status => $rows)
                            <div class="tab-pane fade {{ $status === 'paid' ? 'show active' : '' }}"
                                 id="{{ $status }}-{{ $type->id }}" role="tabpanel">
                                @if($rows->isEmpty())
                                    <div class="alert alert-info mb-0">No {{ $status }} applicants found.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0">
                                            <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Applicant Roll</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                @if($status === 'paid')
                                                    <th>Payment Date</th>
                                                    <th>Final Submit</th>
                                                @else
                                                    <th>Applied On</th>
                                                @endif
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($rows as $row)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $row->roll }}</td>
                                                    <td>{{ optional($row->user)->name }}</td>
                                                    <td>{{ optional($row->user)->phone }}</td>
                                                    @if($status === 'paid')
                                                        <td>
                                                            {{ optional($row->payment)->paymentdate
                                                                ? \Carbon\Carbon::parse($row->payment->paymentdate)->format('d M Y')
                                                                : '-' }}
                                                        </td>
                                                        <td>
                                                            @if((int)($row->final_submit ?? 0) === 1)
                                                                <span class="text-success">Yes</span>
                                                            @else
                                                                <span class="text-danger">No</span>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td>{{ $row->created_at ? $row->created_at->format('d M Y') : '-' }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
