@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-user-lock text-primary mr-2"></i>Applicant Passwords</h1>
        <form method="GET" action="{{ route('admin.passwords.applicant') }}" class="form-inline">
            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm mr-1"
                   placeholder="Roll, name, email or phone">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            @if($search !== '')
                <a href="{{ route('admin.passwords.applicant') }}" class="btn btn-secondary btn-sm ml-1">Clear</a>
            @endif
        </form>
    </div>

    @include('admin.settings._flash')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Roll</th>
                        <th>Final Submit</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Password</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td>
                                @php
                                    $applicantRel = $item->applicant ?? $item->applicants ?? null;
                                    if ($applicantRel instanceof \Illuminate\Support\Collection) {
                                        $rolls = $applicantRel->pluck('roll')->filter()->unique()->values();
                                    } elseif (is_object($applicantRel) && isset($applicantRel->roll)) {
                                        $rolls = collect([$applicantRel->roll])->filter();
                                    } else {
                                        $rolls = collect();
                                    }
                                @endphp
                                @if($rolls->isNotEmpty())
                                    {{ $rolls->implode(', ') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $applicants = $item->applicant ?? $item->applicants ?? null;
                                    if ($applicants instanceof \Illuminate\Support\Collection) {
                                        $apps = $applicants;
                                    } elseif (is_object($applicants)) {
                                        $apps = collect([$applicants]);
                                    } else {
                                        $apps = collect();
                                    }
                                @endphp
                                @forelse($apps as $app)
                                    @if((int)($app->final_submit ?? 0) === 1)
                                        <span class="badge badge-success">Submitted</span>
                                    @else
                                        <span class="badge badge-secondary">Not Submitted</span>
                                    @endif
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->phone ?? '—' }}</td>
                            <td><code>{{ $item->plain_pass ?? '—' }}</code></td>
                            <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No applicants found.</td></tr>
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
