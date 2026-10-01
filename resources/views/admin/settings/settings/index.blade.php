@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-sliders-h text-secondary mr-2"></i>System Settings</h1>
        <a href="{{ route('admin.settings.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Add New
        </a>
    </div>

    @include('admin.settings._flash')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Session</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Last Payment Date</th>
                        <th>Eligibility Start</th>
                        <th>Eligibility Last Date</th>
                        <th>Last Eligibility Payment</th>
                        <th>Eligibility Approval</th>
                        <th>Admission Approval</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td><span class="badge badge-primary">{{ $item->session ?? '—' }}</span></td>
                            <td>{{ $item->start_date ? $item->start_date->format('d M Y') : '—' }}</td>
                            <td>{{ $item->end_date ? $item->end_date->format('d M Y') : '—' }}</td>
                            <td>{{ $item->last_payment_date ? \Carbon\Carbon::parse($item->last_payment_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $item->eligibility_start_date ? \Carbon\Carbon::parse($item->eligibility_start_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $item->eligibility_last_date ? $item->eligibility_last_date->format('d M Y') : '—' }}</td>
                            <td>{{ $item->last_eligibility_payment_date ? \Carbon\Carbon::parse($item->last_eligibility_payment_date)->format('d M Y') : '—' }}</td>
                            <td>
                                @if($item->eligibility_approval_start_date && $item->eligibility_approval_last_date)
                                    <small>{{ \Carbon\Carbon::parse($item->eligibility_approval_start_date)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($item->eligibility_approval_last_date)->format('d M Y') }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($item->admission_approval_start_date && $item->admission_approval_last_date)
                                    <small>{{ \Carbon\Carbon::parse($item->admission_approval_start_date)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($item->admission_approval_last_date)->format('d M Y') }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.settings.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.settings.destroy', $item->id) }}" class="btn btn-danger btn-sm delete ml-1">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No settings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
