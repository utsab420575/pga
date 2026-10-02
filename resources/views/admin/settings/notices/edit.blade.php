@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Notice</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.notices.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date"
                                       class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', $item->date ? \Carbon\Carbon::parse($item->date)->format('Y-m-d') : '') }}">
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-8">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $item->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="details">Details</label>
                            <textarea id="details" name="details" rows="4"
                                      class="form-control @error('details') is-invalid @enderror">{{ old('details', $item->details) }}</textarea>
                            @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="file">
                                Attachment <small class="text-muted">(Leave blank to keep current file)</small>
                            </label>
                            @if($item->file)
                                <p class="mb-1">
                                    <small>Current:
                                        <a href="{{ asset($item->file) }}" target="_blank" class="text-info">
                                            <i class="fas fa-file mr-1"></i>View file
                                        </a>
                                    </small>
                                </p>
                            @endif
                            <input type="file" id="file" name="file"
                                   class="form-control-file @error('file') is-invalid @enderror"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.notices.index') }}" class="btn btn-secondary">
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
