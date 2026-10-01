@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus mr-2"></i>Add Faculty</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.faculties.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="faculty_name">Faculty Name <span class="text-danger">*</span></label>
                            <input type="text" id="faculty_name" name="faculty_name"
                                   class="form-control @error('faculty_name') is-invalid @enderror"
                                   value="{{ old('faculty_name') }}" placeholder="e.g. Faculty of Engineering" required>
                            @error('faculty_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="short_name">Short Name <span class="text-danger">*</span></label>
                            <input type="text" id="short_name" name="short_name"
                                   class="form-control @error('short_name') is-invalid @enderror"
                                   value="{{ old('short_name') }}" placeholder="e.g. FE" required>
                            @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.faculties.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
