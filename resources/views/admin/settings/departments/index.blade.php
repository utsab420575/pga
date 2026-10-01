@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-building text-success mr-2"></i>Departments</h1>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
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
                        <th>Short Name</th>
                        <th>Full Name</th>
                        <th>Faculty</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td><span class="badge badge-success">{{ $item->short_name }}</span></td>
                            <td>{{ $item->full_name }}</td>
                            <td>{{ optional($item->faculty)->faculty_name ?? '—' }}</td>
                            <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.departments.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.departments.destroy', $item->id) }}" class="btn btn-danger btn-sm delete ml-1">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No records found.</td></tr>
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
