@extends('layouts.app')
@section('css')
    <style>
        .form-card{border:0;border-radius:1rem;box-shadow:0 14px 30px rgba(18,38,63,.06)}
        .card-header{border-bottom:0;background:linear-gradient(90deg,#f8f9fa,#fff);font-weight:600}
        .label-req::after{content:" *";color:#dc3545;font-weight:700}
        .help{font-size:.85rem;color:#6c757d}
        .radio-deck{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem}
        .radio-tile{border:1px solid #e9ecef;border-radius:.75rem;padding:.75rem 1rem;display:flex;align-items:center;gap:.6rem;background:#fff;transition:.2s;cursor:pointer}
        .radio-tile:hover{border-color:#dfe3e7;box-shadow:0 6px 16px rgba(0,0,0,.05)}
        .radio-tile .fa{font-size:1.1rem;opacity:.8}
        .radio-tile input{margin-top:2px}
        #prev-eligibility-block{border:1px dashed #dfe3e7;border-radius:.75rem;padding:1rem;background:#fcfcfd}
        .sticky-submit{position:sticky;bottom:0;background:#fff;padding:.75rem 0;border-top:1px solid #f1f3f5}
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-12">

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
                    <div class="card form-card" style="margin-top: 15px;">
                        <div class="card-header">{{ __('Basic Information') }}</div>

                        <div class="card-body">
                            @csrf

                            {{-- University Type --}}
                            <div class="form-group">
                                <label class="label-req">{{ __('University Type') }}</label>
                                <div class="radio-deck mt-2">
                                    <label class="radio-tile">
                                        <input class="form-check-input" type="radio" name="university_type" id="uni_private" value="private"
                                               {{ old('university_type') === 'private' ? 'checked' : '' }} required>
                                        <i class="fa fa-university"></i>
                                        <span>Private University</span>
                                    </label>

                                    <label class="radio-tile">
                                        <input class="form-check-input" type="radio" name="university_type" id="uni_public" value="public"
                                               {{ old('university_type') === 'public' ? 'checked' : '' }} required>
                                        <i class="fa fa-landmark"></i>
                                        <span>Public University</span>
                                    </label>

                                    <label class="radio-tile">
                                        <input class="form-check-input" type="radio" name="university_type" id="uni_prev_eligible" value="previously_eligible"
                                               {{ old('university_type') === 'previously_eligible' ? 'checked' : '' }} required>
                                        <i class="fa fa-landmark"></i>
                                        <span>Previously Approved Eligibility</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Program Details --}}
                            <div class="form-group row">
                                <label for="department" class="col-md-4 col-form-label text-md-right label-req">{{ __('Department/Institute') }}</label>
                                <div class="col-md-6">
                                    <select id="department" class="form-control" name="department" required>
                                        <option value="" selected>-Select department-</option>
                                        @foreach($departments as $department)
                                            <option value="{{$department->id}}">{{$department->short_name}}</option>
                                        @endforeach
                                    </select>
                                    <small class="help">Pick the department/institute you’re applying to.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="degree" class="col-md-4 col-form-label text-md-right label-req">{{ __('Program Applied For') }}</label>
                                <div class="col-md-6">
                                    <select id="degree" class="form-control" name="degree" required>
                                        <option value="" selected>-Select program-</option>
                                        @foreach($degrees as $degree)
                                            <option value="{{$degree->id}}">{{$degree->degree_name}}</option>
                                        @endforeach
                                    </select>
                                    <small class="help">Options filter automatically based on your selected department.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="studenttype" class="col-md-4 col-form-label text-md-right label-req">{{ __('Status') }}</label>
                                <div class="col-md-6">
                                    <select id="studenttype" class="form-control" name="studenttype" required>
                                        <option value="" selected>-Select program status-</option>
                                        @foreach($studenttypes as $studenttype)
                                            <option value="{{$studenttype->id}}">{{$studenttype->type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Application Type (filled by JS) --}}
                            <div class="form-group row">
                                <label for="applicationtype" class="col-md-4 col-form-label text-md-right label-req">{{ __('Application Type') }}</label>
                                <div class="col-md-6">
                                    <select id="applicationtype" class="form-control" name="applicationtype" required>
                                        <option value="" selected>-Select application type-</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Previously-Eligible Proof --}}
                            <div id="prev-eligibility-block" class="mt-3" style="display:none;">
                                <div class="form-group row">
                                    <label class="col-md-4 col-form-label text-md-right label-req">Upload Approval Proof (PDF/JPG/PNG)</label>
                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="prev_eligibility_file" accept=".pdf,.jpg,.jpeg,.png" class="custom-file-input" id="prev_eligibility_file">
                                            <label class="custom-file-label" for="prev_eligibility_file">Choose file...</label>
                                        </div>
                                        <small class="help">Max 10 MB. Upload your previous eligibility approval document.</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-4 col-form-label text-md-right label-req">Confirmation</label>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <input type="checkbox" name="prev_eligibility_confirm" id="prev_eligibility_confirm" class="mr-2">
                                        <label for="prev_eligibility_confirm" class="mb-0">I declare that my eligibility was previously approved.</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Declaration --}}
                            <div class="form-group row">
                                <label for="declaration" class="col-md-4 col-form-label text-md-right label-req">{{ __('Declaration') }}</label>
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="declaration" name="declaration" class="custom-control-input" required>
                                        <label class="custom-control-label" for="declaration">
                                            I declare that the information provided in this form is correct, true and complete to the best of my knowledge and belief. If any information is found false, incorrect, and incomplete or if any ineligibility is detected before or after the examination, any legal action can be taken against me by the authority including the cancellation of my candidature.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sticky-submit">
                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-success px-4">
                                        {{ __('Submit') }}
                                    </button>
                                </div>
                            </div>
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
            7: [5,6],        // IPE (Industrial and Production Engineering) → no degree mapped yet
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


    {{--for showing options based on private/public university--}}
{{--    <script>
        const allApplicationTypes = @json($applicationtypes);
        const oldUniversityType = @json(old('university_type'));
        const oldApplicationType = @json(old('applicationtype'));
        const hasEligibilityApproval = @json($hasApprovalEligibility);

        const appTypeSelect = document.getElementById('applicationtype');
        const uniPrivate   = document.getElementById('uni_private');
        const uniPublic    = document.getElementById('uni_public');

        function fillApplicationTypes(universityType) {
            if (!appTypeSelect) return;

            appTypeSelect.innerHTML = '<option value="">-Select application type-</option>';

            let allowedIds = [];
            if (universityType === 'private') {
                allowedIds = hasEligibilityApproval ? [1] : [2];
            } else if (universityType === 'public') {
                allowedIds = [1]; // <-- fixed
            }


            allApplicationTypes.forEach(item => {
                if (allowedIds.includes(item.id)) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.type;
                    appTypeSelect.appendChild(opt);
                }
            });

            if (oldApplicationType && allowedIds.includes(Number(oldApplicationType))) {
                appTypeSelect.value = String(oldApplicationType);
            }
        }

        if (uniPrivate) uniPrivate.addEventListener('change', () => fillApplicationTypes('private'));
        if (uniPublic) uniPublic.addEventListener('change', () => fillApplicationTypes('public'));

        // Init on page load
        if (oldUniversityType === 'private') {
            if (uniPrivate) uniPrivate.checked = true;
            fillApplicationTypes('private');
        } else if (oldUniversityType === 'public') {
            if (uniPublic) uniPublic.checked = true;
            fillApplicationTypes('public');
        }
    </script>--}}

    <script>
        // Bootstrap 4 custom-file input label update
        document.addEventListener('change', function(e){
            if(e.target && e.target.classList.contains('custom-file-input')){
                const label = e.target.nextElementSibling;
                if (label) label.textContent = e.target.files.length ? e.target.files[0].name : 'Choose file...';
            }
        });
    </script>

    <script>
        const allApplicationTypes   = @json($applicationtypes);
        const oldUniversityType     = @json(old('university_type'));
        const oldApplicationType    = @json(old('applicationtype'));
        const hasEligibilityApproval= @json($hasApprovalEligibility);

        const appTypeSelect   = document.getElementById('applicationtype');
        const uniPrivate      = document.getElementById('uni_private');
        const uniPublic       = document.getElementById('uni_public');
        const uniPrevEligible = document.getElementById('uni_prev_eligible');
        const prevBlock       = document.getElementById('prev-eligibility-block');

        function togglePrevBlock(show) {
            if (!prevBlock) return;
            prevBlock.style.display = show ? 'block' : 'none';
        }

        function fillApplicationTypes(universityType) {
            if (!appTypeSelect) return;
            appTypeSelect.innerHTML = '<option value="">-Select application type-</option>';

            let allowedIds = [];
            if (universityType === 'private') {
                allowedIds = hasEligibilityApproval ? [1] : [2];
                togglePrevBlock(false);
            } else if (universityType === 'public') {
                allowedIds = [1];
                togglePrevBlock(false);
            } else if (universityType === 'previously_eligible') {
                // ✅ As requested: allowId = 1 (Admission)
                allowedIds = [1];
                togglePrevBlock(true);
            }

            allApplicationTypes.forEach(item => {
                if (allowedIds.includes(item.id)) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.type;
                    appTypeSelect.appendChild(opt);
                }
            });

            if (oldApplicationType && allowedIds.includes(Number(oldApplicationType))) {
                appTypeSelect.value = String(oldApplicationType);
            }
        }

        if (uniPrivate)      uniPrivate.addEventListener('change', () => fillApplicationTypes('private'));
        if (uniPublic)       uniPublic.addEventListener('change', () => fillApplicationTypes('public'));
        if (uniPrevEligible) uniPrevEligible.addEventListener('change', () => fillApplicationTypes('previously_eligible'));

        // Init on page load (handle old() preselection)
        if (oldUniversityType === 'private' && uniPrivate) {
            uniPrivate.checked = true; fillApplicationTypes('private');
        } else if (oldUniversityType === 'public' && uniPublic) {
            uniPublic.checked = true; fillApplicationTypes('public');
        } else if (oldUniversityType === 'previously_eligible' && uniPrevEligible) {
            uniPrevEligible.checked = true; fillApplicationTypes('previously_eligible');
        }
    </script>

@endsection
