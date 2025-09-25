@extends('layouts.app')
@section('css')
@endsection
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
            <div class="card-body" align="center">
                Apply for Postgraduate Program in DUET, Gazipur
            </div>
            <form method="POST" action="{{ URL('edit-application-submit')}}/{{$applicant->id}}" enctype="multipart/form-data">
            <div class="card" style="margin-top: 15px;">

                <div class="card-header">{{ __('Basic Information') }}</div>

                <div class="card-body">
                    
                    @csrf
                    
                    <div class="form-group row">
                     <label for="degree" class="col-md-4 col-form-label text-md-right">{{ __('Program applied for [*]') }}</label>
                        <div class="col-md-6">                            
                        <select id="degree" class="form-control" name="degree" required="">
                            <option value="{{$applicant->degree->id}}" selected>{{$applicant->degree->degree_name}} *</option>
                            @foreach($degrees as $degree)
                                <option value="{{$degree->id}}">{{$degree->degree_name}}</option>
                            @endforeach
                         </select>
                         </div>                                  
                    </div>

                    <div class="form-group row">
                     <label for="department" class="col-md-4 col-form-label text-md-right">{{ __('Department/Institute [*]') }}</label>
                        <div class="col-md-6">                            
                        <select id="department" class="form-control" name="department" required="">
                            <option value="{{$applicant->department->id}}" selected>{{$applicant->department->short_name}} *</option>
                            @foreach($departments as $department)
                                <option value="{{$department->id}}">{{$department->short_name}}</option>
                            @endforeach
                         </select>
                         </div>                                  
                    </div>

                    <div class="form-group row">
                     <label for="studenttype" class="col-md-4 col-form-label text-md-right">{{ __('Status [*]') }}</label>
                        <div class="col-md-6">                            
                        <select id="studenttype" class="form-control" name="studenttype" required="">
                            <option value="{{$applicant->studenttype->id}}" selected>{{$applicant->studenttype->type}} *</option>
                            @foreach($studenttypes as $studenttype)
                                <option value="{{$studenttype->id}}">{{$studenttype->type}}</option>
                            @endforeach
                         </select>
                         </div>                                  
                    </div>
					@if($applicant->payment_status === 1)
                    <div class="form-group row">
                     <label for="applicationtype" class="col-md-4 col-form-label text-md-right">{{ __('Application Type [*]') }}</label>
                        <div class="col-md-6">                            
                        <select id="applicationtype" class="form-control" name="applicationtype" required="">
                            <option value="{{$applicant->applicationtype->id}}" selected>{{$applicant->applicationtype->type}} *</option>
                            @foreach($applicationtypes as $applicationtype)
                                <option value="{{$applicationtype->id}}">{{$applicationtype->type}}</option>
                            @endforeach
                         </select>
                         </div>                                  
                    </div>
					@endif
                    <div class="form-group row">
                     <label for="declaration" class="col-md-4 col-form-label text-md-right">{{ __('Declaration [*]') }}</label>
                        <div class="col-md-6">                            
                         <p align="justify"> <input type="checkbox" name="declaration" required> I declare that the information provided in this form is correct, true and complete to the best of my knowledge and belief. If any information is found false, incorrect, and incomplete or if any ineligibility is detected before or after the examination, any legal action can be taken against me by the authority including the cancellation of my candidature.</p>
                         </div>                                  
                    </div>
                        
                </div>

                        

            </div>

                <div class="form-group row mb-0" style="padding-top: 10px;">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-success">
                            {{ __('Submit') }}
                        </button>

                     
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
$('#myCheck').change(function(){
  if($(this).is(':checked'))   
  {
    $('#mySelect').prop('disabled','disabled')
    $('#mySelect').val("");
  }
  else
  {
    $('#mySelect').prop('disabled',false)
  }
    
})
</script>
@endsection