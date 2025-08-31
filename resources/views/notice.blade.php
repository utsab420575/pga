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
                            <tr>
                              <th scope="col">1</th>
                              <td scope="col">23-05-2023</td>
                              <td scope="col">Admission Notice</td>
                              <td scope="col">Download to view</td>
                              <td scope="col"><a href="{{ asset('public/notice.pdf') }}" class="btn btn-primary">Download</a></td>
                            </tr>                    
                      </tbody>
                    </table>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
