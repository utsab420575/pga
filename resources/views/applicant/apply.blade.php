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
                <form method="POST" action="{{ URL('apply-now-submit')}}" enctype="multipart/form-data">
                    <div class="card" style="margin-top: 15px;">

                        <div class="card-header">{{ __('Basic Information') }}</div>

                        <div class="card-body">

                            @csrf

                            <div class="form-group row">
                                <label for="department" class="col-md-4 col-form-label text-md-right">{{ __('Department/Institute [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="department" class="form-control" name="department" required>
                                        <option value="" selected>-Select department-</option>
                                        @foreach($departments as $department)
                                            <option value="{{$department->id}}">{{$department->short_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="degree" class="col-md-4 col-form-label text-md-right">{{ __('Program Applied For [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="degree" class="form-control" name="degree" required>
                                        <option value="" selected>-Select program-</option>
                                        @foreach($degrees as $degree)
                                            <option value="{{$degree->id}}">{{$degree->degree_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="studenttype" class="col-md-4 col-form-label text-md-right">{{ __('Program Status [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="studenttype" class="form-control" name="studenttype" required>
                                        <option value="" selected>-Select program status-</option>
                                        @foreach($studenttypes as $studenttype)
                                            <option value="{{$studenttype->id}}">{{$studenttype->type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="applicationtype" class="col-md-4 col-form-label text-md-right">{{ __('Application Type [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="applicationtype" class="form-control" name="applicationtype" required>
                                        <option value="" selected>-Select application type-</option>
                                        @foreach($applicationtypes as $applicationtype)
                                            <option value="{{$applicationtype->id}}">{{$applicationtype->type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="declaration" class="col-md-4 col-form-label text-md-right">{{ __('Declaration [*]') }}</label>
                                <div class="col-md-6">
                                    <p align="justify">
                                        <input type="checkbox" name="declaration" required>
                                        I declare that the information provided in this form is correct, true and complete to the best of my knowledge and belief. If any information is found false, incorrect, and incomplete or if any ineligibility is detected before or after the examination, any legal action can be taken against me by the authority including the cancellation of my candidature.
                                    </p>
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
        // 🔹 Define mapping of department_id => allowed degree_ids
        /*  1 = PGD
            2 = M Sc.
            3 = M Sc. in WEM
            4 = M in WEM
            5 = M Engg.
            6 = M Sc. Engg.
            7 = M Phil.
            8 = Ph. D*/
        const deptDegreeMap = {
            1: [5,6,8],   // CE (Civil Engineering) → M Engg., M Sc. Engg., Ph. D
            2: [5,6,8],   // EEE (Electrical & Electronic Engineering) → M Engg., M Sc. Engg., Ph. D
            3: [5,6,8],   // ME (Mechanical Engineering) → M Engg., M Sc. Engg., Ph. D
            4: [5,6,8],   // CSE (Computer Science and Engineering) → M Engg., M Sc. Engg., Ph. D
            5: [5,6,8],   // TE (Textile Engineering) → M Engg., M Sc. Engg., Ph. D
            6: [5,6],     // FE (Food Engineering) → M Engg., M Sc. Engg.
            7: [],        // IPE (Industrial and Production Engineering) → no degree mapped yet
            8: [1,3,4],   // IWE (Institute of Water and Environment) → PGD, M Sc. in WEM, M in WEM
            9: [5,6,1],   // IICT (Institute of Information and Communication Technology) → M Engg., M Sc. Engg., PGD
            10: [5,6,1],  // IEE (Institute of Energy Engineering) → M Engg., M Sc. Engg., PGD
            11: [2,7,8],  // Chemistry → M Sc., M Phil., Ph. D
            12: [2,7,8],       // Mathematics → M Sc., M Phil., Ph. D
            13: [2,7,8],  // Physics → M Sc., M Phil., Ph. D
        };


        // 🔹 Store all degree options initially
        let allDegrees = @json($degrees);

        // 🔹 When department changes, filter degrees
        document.getElementById('department').addEventListener('change', function() {
            let deptId = this.value;
            let degreeSelect = document.getElementById('degree');

            // reset degree select
            degreeSelect.innerHTML = '<option value="">-Select program-</option>';

            if (deptDegreeMap[deptId]) {
                let allowedIds = deptDegreeMap[deptId];
                allDegrees.forEach(degree => {
                    if (allowedIds.includes(degree.id)) {
                        let opt = document.createElement('option');
                        opt.value = degree.id;
                        opt.textContent = degree.degree_name;
                        degreeSelect.appendChild(opt);
                    }
                });
            }
        });
    </script>
@endsection
