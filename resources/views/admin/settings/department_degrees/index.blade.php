@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">
            <i class="fas fa-project-diagram mr-2" style="color: #6f42c1;"></i>Department &ndash; Degree Mapping
        </h1>
    </div>

    @include('admin.settings._flash')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Short Name</th>
                        <th>Department Full Name</th>
                        <th>Faculty</th>
                        <th>Allowed Degrees</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge badge-success">{{ $dept->short_name }}</span></td>
                            <td>{{ $dept->full_name }}</td>
                            <td>{{ optional($dept->faculty)->faculty_name ?? '—' }}</td>
                            <td>
                                @forelse($dept->degrees as $deg)
                                    <span class="badge badge-info mr-1 mb-1">{{ $deg->degree_name }}</span>
                                @empty
                                    <span class="text-muted small">No degrees mapped</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.department_degrees.edit', $dept->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit Degrees
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
