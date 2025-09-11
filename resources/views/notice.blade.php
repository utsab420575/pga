@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                @if(count($errors)>0)
                    @foreach($errors->all() as $error)
                        <p class="alert alert-danger">{{$error}}</p>
                    @endforeach
                @endif

                @if(session('Status'))
                    <p class="alert alert-info">{{session('Status')}}</p>
                @endif
                <div class="card">
                    <div class="card-header"><b>{{ __('Notices') }}</b></div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Sl</th>
                                <th scope="col">Date</th>
                                <th scope="col">Title</th>
                                <th scope="col">Details</th>
                                <th scope="col">File</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($notices as $index => $notice)
                                    <tr>
                                        <th scope="row">{{ $index + 1 }}</th>
                                        <td>{{ $notice->date?->format('d-m-Y') }}</td>
                                        <td>{{ $notice->title }}</td>
                                        <td>{{ $notice->details ?? '—' }}</td>
                                        <td>
                                            @if($notice->file)
                                                <a href="{{ asset($notice->file) }}" class="btn btn-primary"
                                                   target="_blank">Download</a>
                                            @else
                                                <span class="text-muted">No file</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No notices available.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
