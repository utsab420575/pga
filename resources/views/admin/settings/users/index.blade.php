@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1 class="h3 mb-0"><i class="fas fa-users-cog text-primary mr-2"></i>Users</h1>
        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline mr-2">
                <select name="user_type" class="form-control form-control-sm mr-1">
                    <option value="">All types</option>
                    @foreach($userTypes as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm mr-1"
                       placeholder="Name, email or phone">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if($search !== '' || $type)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm ml-1">Clear</a>
                @endif
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-user-plus mr-1"></i> Add User
            </a>
        </div>
    </div>

    @include('admin.settings._flash')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Phone Verified</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->phone ?? '—' }}</td>
                            <td>
                                @if($item->phone_verified)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($item->user_type) }}</td>
                            <td>{{ optional($item->department)->short_name ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
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
