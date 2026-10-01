@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Degree</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.degrees.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="degree_name">Degree Name <span class="text-danger">*</span></label>
                            <input type="text" id="degree_name" name="degree_name"
                                   class="form-control @error('degree_name') is-invalid @enderror"
                                   value="{{ old('degree_name', $item->degree_name) }}" required>
                            @error('degree_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.degrees.index') }}" class="btn btn-secondary">
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
