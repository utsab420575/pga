@extends('layouts.app')
@section('content')
@php
    $show = fn ($v) => is_array($v) ? json_encode($v) : (is_bool($v) ? ($v ? 'true' : 'false') : ($v ?? 'null'));
    $badge = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'cloned' => 'info'];
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-history text-info mr-2"></i>Activity Log</h1>
    </div>

    <form method="GET" action="{{ route('admin.activity_logs.index') }}" class="card card-body shadow-sm mb-3">
        <div class="form-row">
            <div class="col-md-2 mb-2">
                <input type="text" name="applicant" value="{{ $filters['applicant'] ?? '' }}" class="form-control form-control-sm" placeholder="Applicant ID / Roll">
            </div>
            <div class="col-md-2 mb-2">
                <select name="table" class="form-control form-control-sm">
                    <option value="">All tables</option>
                    @foreach($tables as $t)
                        <option value="{{ $t }}" @selected(($filters['table'] ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="event" class="form-control form-control-sm">
                    <option value="">All events</option>
                    @foreach(['created', 'updated', 'deleted', 'cloned'] as $e)
                        <option value="{{ $e }}" @selected(($filters['event'] ?? '') === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 mb-2">
                <input type="number" name="user" value="{{ $filters['user'] ?? '' }}" class="form-control form-control-sm" placeholder="User ID">
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm" title="From">
            </div>
            <div class="col-md-2 mb-2">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm" title="To">
            </div>
            <div class="col-md-1 mb-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i></button>
            </div>
        </div>
        @if(array_filter($filters))
            <div><a href="{{ route('admin.activity_logs.index') }}" class="small">Clear filters</a></div>
        @endif
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Time</th>
                        <th>Event</th>
                        <th>Table / Row</th>
                        <th>Applicant</th>
                        <th>By</th>
                        <th>Changes</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $new = $item->properties['attributes'] ?? [];
                            $old = $item->properties['old'] ?? [];
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $item->created_at?->format('d M Y h:i:s A') }}</td>
                            <td><span class="badge badge-{{ $badge[$item->event] ?? 'secondary' }}">{{ $item->event }}</span></td>
                            <td class="text-nowrap">{{ $item->log_name }} #{{ $item->subject_id }}</td>
                            <td>
                                @if($item->applicant_id)
                                    <a href="{{ route('admin.activity_logs.index', ['applicant' => $item->applicant_id]) }}">#{{ $item->applicant_id }}</a>
                                @else — @endif
                            </td>
                            <td class="text-nowrap">
                                @if($item->causer)
                                    {{ $item->causer->name }}
                                    <small class="text-muted d-block">{{ $item->causer->user_type }} · #{{ $item->causer_id }}</small>
                                @else
                                    <span class="text-muted">system</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($item->event === 'updated')
                                    @foreach($new as $field => $value)
                                        <div><strong>{{ $field }}</strong>:
                                            <span class="text-danger">{{ $show($old[$field] ?? null) }}</span> →
                                            <span class="text-success">{{ $show($value) }}</span>
                                        </div>
                                    @endforeach
                                @elseif($item->event === 'cloned')
                                    Cloned from applicant
                                    <a href="{{ route('admin.activity_logs.index', ['applicant' => $item->properties['from_applicant_id'] ?? '']) }}">#{{ $item->properties['from_applicant_id'] ?? '?' }}</a>
                                @else
                                    <details>
                                        <summary>{{ count($new ?: $old) }} fields</summary>
                                        @foreach(($new ?: $old) as $field => $value)
                                            <div><strong>{{ $field }}</strong>: {{ $show($value) }}</div>
                                        @endforeach
                                    </details>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $item->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No activity found.</td></tr>
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
