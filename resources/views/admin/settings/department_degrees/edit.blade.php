@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>Edit Allowed Degrees for:
                        <strong>{{ $department->short_name }}</strong>
                        <small class="text-dark">({{ $department->full_name }})</small>
                    </h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')

                    <form action="{{ route('admin.department_degrees.update', $department->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <p class="text-muted mb-3">
                            Select all degrees/programs that are offered by <strong>{{ $department->full_name }}</strong>.
                        </p>

                        <div class="row">
                            @foreach($allDegrees as $deg)
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox p-2 border rounded">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="degree_{{ $deg->id }}"
                                               name="degree_ids[]"
                                               value="{{ $deg->id }}"
                                               {{ in_array($deg->id, $assignedIds) ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="degree_{{ $deg->id }}">
                                            {{ $deg->degree_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                            <a href="{{ route('admin.department_degrees.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i> Save Mapping
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
