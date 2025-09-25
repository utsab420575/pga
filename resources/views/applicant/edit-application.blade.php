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

                <form method="POST" action="{{ URL('edit-application-submit') }}/{{$applicant->id}}" enctype="multipart/form-data">
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-header">{{ __('Basic Information') }}</div>

                        <div class="card-body">
                            @csrf

                            {{-- University Type (request-only; inferred default from current applicationtype) --}}
                            @php
                                $paid = (int)$applicant->payment_status === 1;
                                $currentAppTypeId = (int)($applicant->applicationtype->id ?? 0); // 1=Admission, 2=Eligibility
                                $inferredUniType = $currentAppTypeId === 2 ? 'private' : 'public';
                            @endphp
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">{{ __('University Type [*]') }}</label>
                                <div class="col-md-6 d-flex align-items-center">
                                    <div class="form-check mr-3">
                                        <input class="form-check-input" type="radio" name="university_type" id="uni_private" value="private"
                                               {{ old('university_type', $inferredUniType) === 'private' ? 'checked' : '' }}
                                               {{ $paid ? 'disabled' : '' }} required>
                                        <label class="form-check-label" for="uni_private">Private University</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="university_type" id="uni_public" value="public"
                                               {{ old('university_type', $inferredUniType) === 'public' ? 'checked' : '' }}
                                               {{ $paid ? 'disabled' : '' }} required>
                                        <label class="form-check-label" for="uni_public">Public University</label>
                                    </div>
                                    @if($paid)
                                        {{-- disabled inputs don't submit: keep the value --}}
                                        <input type="hidden" name="university_type" value="{{ old('university_type', $inferredUniType) }}">
                                    @endif
                                </div>
                            </div>

                            {{-- Department --}}
                            <div class="form-group row">
                                <label for="department" class="col-md-4 col-form-label text-md-right">{{ __('Department/Institute [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="department" class="form-control" name="department" required>
                                        <option value="{{ $applicant->department->id }}" selected>{{ $applicant->department->short_name }} *</option>
                                        @foreach($departments as $department)
                                            @if($department->id !== $applicant->department->id)
                                                <option value="{{$department->id}}">{{$department->short_name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Degree --}}
                            <div class="form-group row">
                                <label for="degree" class="col-md-4 col-form-label text-md-right">{{ __('Program applied for [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="degree" class="form-control" name="degree" required>
                                        <option value="{{ $applicant->degree->id }}" selected>{{ $applicant->degree->degree_name }} *</option>
                                        @foreach($degrees as $degree)
                                            @if($degree->id !== $applicant->degree->id)
                                                <option value="{{$degree->id}}">{{$degree->degree_name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Student Status --}}
                            <div class="form-group row">
                                <label for="studenttype" class="col-md-4 col-form-label text-md-right">{{ __('Status [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="studenttype" class="form-control" name="studenttype" required>
                                        <option value="{{ $applicant->studenttype->id }}" selected>{{ $applicant->studenttype->type }} *</option>
                                        @foreach($studenttypes as $studenttype)
                                            @if($studenttype->id !== $applicant->studenttype->id)
                                                <option value="{{$studenttype->id}}">{{$studenttype->type}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Application Type (disabled after payment; JS fills options) --}}
                            <div class="form-group row">
                                <label for="applicationtype" class="col-md-4 col-form-label text-md-right">{{ __('Application Type [*]') }}</label>
                                <div class="col-md-6">
                                    <select id="applicationtype" class="form-control" name="applicationtype" {{ $paid ? 'disabled' : '' }} required>
                                        <option value="" selected>-Select application type-</option>
                                        @if($currentAppTypeId)
                                            <option value="{{ $currentAppTypeId }}" selected>{{ $applicant->applicationtype->type }}</option>
                                        @endif
                                    </select>
                                    @if($paid)
                                        <input type="hidden" name="applicationtype" value="{{ $currentAppTypeId }}">
                                        <small class="text-muted d-block mt-1">Application Type is locked after payment.</small>
                                    @endif
                                </div>
                            </div>

                            {{-- Declaration --}}
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
                            <button type="submit" class="btn btn-success">{{ __('Submit') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('script')
    {{-- Dept → Degree filtering (same map as apply) --}}
    <script type="text/javascript">
        /* 1=PGD, 2=M Sc., 3=M Sc. in WEM, 4=M in WEM, 5=M Engg., 6=M Sc. Engg., 7=M Phil., 8=Ph. D */
        const deptDegreeMap = {
            1:[5,6,8],2:[5,6,8],3:[5,6,8],4:[5,6,8],5:[5,6,8],
            6:[5,6],7:[5,6],8:[1,3,4],9:[5,6,1],10:[5,6,1],
            11:[2,7,8],12:[2,7,8],13:[2,7,8],
        };
        const allDegrees = @json($degrees);
        const degreeSelect = document.getElementById('degree');
        const deptSelect   = document.getElementById('department');

        function refillDegrees(deptId, keepSelectedId = {{ (int)$applicant->degree_id }}) {
            degreeSelect.innerHTML = '<option value="">-Select program-</option>';
            if (deptDegreeMap[deptId]) {
                const allowed = deptDegreeMap[deptId];
                allDegrees.forEach(d => {
                    if (allowed.includes(d.id)) {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.degree_name;
                        if (Number(keepSelectedId) === Number(d.id)) opt.selected = true;
                        degreeSelect.appendChild(opt);
                    }
                });
            }
        }
        // Initial fill honoring current selection
        refillDegrees({{ (int)$applicant->department_id }});
        deptSelect.addEventListener('change', function() { refillDegrees(Number(this.value), null); });
    </script>

    {{-- AppType filtering by University Type (request-only; mirrors apply) --}}
    <script>
        const allApplicationTypes    = @json($applicationtypes);
        const hasEligibilityApproval = @json($hasApprovalEligibility);
        const appTypeSelect          = document.getElementById('applicationtype');
        const uniPrivate             = document.getElementById('uni_private');
        const uniPublic              = document.getElementById('uni_public');
        const currentAppTypeId       = {{ (int)($applicant->applicationtype->id ?? 0) }};
        const paid                   = {{ (int)$applicant->payment_status }} === 1;

        function allowedIdsFor(uniType){
            if (uniType === 'private') return hasEligibilityApproval ? [1] : [2];
            if (uniType === 'public')  return [1];
            return [];
        }
        function currentUniType(){
            if (uniPrivate && uniPrivate.checked) return 'private';
            if (uniPublic  && uniPublic.checked)  return 'public';
            return null;
        }
        function fillApplicationTypes(force=null){
            if (!appTypeSelect) return;
            const uniType = force || currentUniType();
            const allowed = allowedIdsFor(uniType);
            const selectedCandidate = Number({{ (int)old('applicationtype') ?: $currentAppTypeId }});

            appTypeSelect.innerHTML = '<option value="">-Select application type-</option>';
            allApplicationTypes.forEach(item => {
                if (allowed.includes(item.id)) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.type;
                    if (selectedCandidate === Number(item.id)) opt.selected = true;
                    appTypeSelect.appendChild(opt);
                }
            });
        }

        // Init on load using inferred radios
        const initType = ({{ $currentAppTypeId }} === 2) ? 'private' : 'public';
        fillApplicationTypes(initType);

        // Rebind changes when not paid
        if (!paid) {
            if (uniPrivate) uniPrivate.addEventListener('change', () => fillApplicationTypes('private'));
            if (uniPublic)  uniPublic .addEventListener('change', () => fillApplicationTypes('public'));
        }
    </script>
@endsection
