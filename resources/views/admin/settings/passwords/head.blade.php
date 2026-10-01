@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-key text-warning mr-2"></i>Password Reset for Head</h1>
    </div>

    @include('admin.settings._flash')

    <div class="alert alert-info">
        Reset password will be set to <strong>12345678</strong>.
    </div>

    <form id="headResetForm" method="POST" action="{{ route('admin.passwords.head.reset') }}">
        @csrf
        <input type="hidden" name="mode" id="resetMode" value="selected">

        <div class="mb-3">
            <button type="button" class="btn btn-warning btn-sm" id="resetSelectedBtn">
                <i class="fas fa-user-check mr-1"></i> Reset Selected
            </button>
            <button type="button" class="btn btn-danger btn-sm ml-1" id="resetAllBtn">
                <i class="fas fa-users mr-1"></i> Reset All Heads
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><input type="checkbox" class="row-check" name="ids[]" value="{{ $item->id }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->phone ?? '—' }}</td>
                                <td>{{ optional($item->department)->short_name ?? '—' }}</td>
                                <td>{{ $item->updated_at ? $item->updated_at->format('d M Y h:i A') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No head users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('headResetForm');
    var mode = document.getElementById('resetMode');
    var checks = document.querySelectorAll('.row-check');

    document.getElementById('checkAll').addEventListener('change', function () {
        var on = this.checked;
        checks.forEach(function (c) { c.checked = on; });
    });

    document.getElementById('resetSelectedBtn').addEventListener('click', function () {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (n === 0) { alert('Please select at least one head.'); return; }
        if (!confirm('Reset password to 12345678 for ' + n + ' selected head(s)?')) return;
        mode.value = 'selected';
        form.submit();
    });

    document.getElementById('resetAllBtn').addEventListener('click', function () {
        if (!confirm('Reset password to 12345678 for ALL heads?')) return;
        mode.value = 'all';
        form.submit();
    });
});
</script>
@endsection
