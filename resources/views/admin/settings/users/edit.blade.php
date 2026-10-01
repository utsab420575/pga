@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-user-edit mr-2"></i>Edit User #{{ $item->id }}</h5>
                </div>
                <div class="card-body">
                    @include('admin.settings._flash')
                    <form action="{{ route('admin.users.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.settings.users._form')
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
