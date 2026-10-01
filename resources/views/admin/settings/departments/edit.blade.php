@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Department</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.departments.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="full_name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="full_name" name="full_name"
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   value="{{ old('full_name', $item->full_name) }}" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="short_name">Short Name <span class="text-danger">*</span></label>
                            <input type="text" id="short_name" name="short_name"
                                   class="form-control @error('short_name') is-invalid @enderror"
                                   value="{{ old('short_name', $item->short_name) }}" required>
                            @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="faculty_id">Faculty <span class="text-danger">*</span></label>
                            <select id="faculty_id" name="faculty_id"
                                    class="form-control @error('faculty_id') is-invalid @enderror" required>
                                <option value="">— Select Faculty —</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}"
                                        {{ old('faculty_id', $item->faculty_id) == $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->faculty_name }} ({{ $faculty->short_name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
