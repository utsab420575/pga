@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus mr-2"></i>Add Application Type</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.applicationtypes.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <input type="text" id="type" name="type" class="form-control @error('type') is-invalid @enderror"
                                   value="{{ old('type') }}" placeholder="e.g. Regular, International" required>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="fee">Fee (BDT) <span class="text-danger">*</span></label>
                            <input type="number" id="fee" name="fee" class="form-control @error('fee') is-invalid @enderror"
                                   value="{{ old('fee', 0) }}" step="0.01" min="0" required>
                            @error('fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.applicationtypes.index') }}" class="btn btn-secondary">
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
