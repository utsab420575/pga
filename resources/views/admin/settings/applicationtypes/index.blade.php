@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fas fa-file-alt text-primary mr-2"></i>Application Types</h1>
        <a href="{{ route('admin.applicationtypes.create') }}" class="btn btn-primary btn-sm">
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
                        <th>Type</th>
                        <th>Fee (BDT)</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ number_format($item->fee, 2) }}</td>
                            <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.applicationtypes.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.applicationtypes.destroy', $item->id) }}" class="btn btn-danger btn-sm delete ml-1">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No records found.</td></tr>
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
