@extends('layouts.app')

@section('css')
<style>
    .form-card          { border:0; border-radius:1rem; box-shadow:0 14px 30px rgba(18,38,63,.06) }
    .card-header-adm    { border-bottom:0; background:linear-gradient(90deg,#4361ee,#2d46c7); color:#fff; font-weight:600; border-radius:1rem 1rem 0 0 !important; }
    .label-req::after   { content:" *"; color:#dc3545; font-weight:700 }
    .help               { font-size:.85rem; color:#6c757d }
    .sticky-submit      { position:sticky; bottom:0; background:#fff; padding:.75rem 0; border-top:1px solid #f1f3f5 }
    .window-info        { background:#eef2ff; border:1px solid #c7d2fe; border-radius:.6rem; padding:.6rem 1rem; font-size:.88rem; color:#3730a3; margin-bottom:1.2rem; }
    .window-info i      { margin-right:.4rem; }
    .radio-deck         { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:.75rem }
    .radio-tile         { border:1px solid #e9ecef; border-radius:.75rem; padding:.75rem 1rem; display:flex; align-items:center; gap:.6rem; background:#fff; transition:.2s; cursor:pointer }
    .radio-tile:hover   { border-color:#c7d2fe; box-shadow:0 6px 16px rgba(0,0,0,.05) }
    .radio-tile .fa     { font-size:1.1rem; opacity:.8 }
    .radio-tile input   { margin-top:2px }
    #prev-eligibility-block { border:1px dashed #c7d2fe; border-radius:.75rem; padding:1rem; background:#f8f9ff; margin-top:.75rem }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            {{-- Error / status messages --}}
            @if(count($errors) > 0)
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endforeach
            @endif
            @if(session('Status'))
                <div class="alert alert-info">{{ session('Status') }}</div>
            @endif

            <form method="POST" action="{{ url('apply-now-submit') }}" enctype="multipart/form-data">
                @csrf

                {{-- Hidden: type is always Admission (1) --}}
                <input type="hidden" name="applicationtype" value="1">

                <div class="card form-card" style="margin-top:15px">

                    {{-- Card Header --}}
                    <div class="card-header card-header-adm d-flex align-items-center">
                        <i class="fas fa-university mr-2"></i>Admission Application
                    </div>

                    <div class="card-body">

                        {{-- Application Window Info --}}
                        <div class="window-info">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Application Window:</strong>
                            {{ $admissionStart->format('d M Y') }} &ndash; {{ $admissionEnd->format('d M Y') }}
                        </div>

                        {{-- University Type --}}
                        <div class="form-group">
                            <label class="label-req">University Type</label>
                            <div class="radio-deck mt-2">

                                {{-- Public University — always shown --}}
                                <label class="radio-tile">
                                    <input class="form-check-input" type="radio"
                                           name="university_type" id="uni_public" value="public"
                                           {{ old('university_type') === 'public' ? 'checked' : '' }} required>
                                    <i class="fa fa-landmark text-primary"></i>
                                    <span>Public University</span>
                                </label>

                                {{-- Private (Eligibility Approved) — shown only if approved --}}
                                @if($hasApprovalEligibility)
                                <label class="radio-tile">
                                    <input class="form-check-input" type="radio"
                                           name="university_type" id="uni_private" value="private"
                                           {{ old('university_type') === 'private' ? 'checked' : '' }}>
                                    <i class="fa fa-university text-success"></i>
                                    <span>Private University <small class="text-success">(Eligibility Approved)</small></span>
                                </label>
                                @endif

                                {{-- Previously Approved Eligibility — always shown --}}
                                <label class="radio-tile">
                                    <input class="form-check-input" type="radio"
                                           name="university_type" id="uni_prev_eligible" value="previously_eligible"
                                           {{ old('university_type') === 'previously_eligible' ? 'checked' : '' }}>
                                    <i class="fa fa-check-circle text-warning"></i>
                                    <span>Previously Approved Eligibility</span>
                                </label>

                            </div>
                        </div>

                        {{-- Previously-Eligible Proof Upload (shown only when selected) --}}
                        <div id="prev-eligibility-block" style="display:none;">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right label-req">
                                    Upload Approval Proof (PDF / JPG / PNG)
                                </label>
                                <div class="col-md-6">
                                    <div class="custom-file">
                                        <input type="file" name="prev_eligibility_file"
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               class="custom-file-input" id="prev_eligibility_file">
                                        <label class="custom-file-label" for="prev_eligibility_file">Choose file...</label>
                                    </div>
                                    <small class="help">Max 10 MB. Upload your previous eligibility approval document.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right label-req">Confirmation</label>
                                <div class="col-md-6 d-flex align-items-center">
                                    <input type="checkbox" name="prev_eligibility_confirm"
                                           id="prev_eligibility_confirm" class="mr-2">
                                    <label for="prev_eligibility_confirm" class="mb-0">
                                        I declare that my eligibility was previously approved.
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Department --}}
                        <div class="form-group row">
                            <label for="department" class="col-md-4 col-form-label text-md-right label-req">
                                Department / Institute
                            </label>
                            <div class="col-md-6">
                                <select id="department" class="form-control" name="department" required>
                                    <option value="">— Select Department —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help">Pick the department/institute you are applying to.</small>
                            </div>
                        </div>

                        {{-- Degree --}}
                        <div class="form-group row">
                            <label for="degree" class="col-md-4 col-form-label text-md-right label-req">
                                Program Applied For
                            </label>
                            <div class="col-md-6">
                                <select id="degree" class="form-control" name="degree" required>
                                    <option value="">— Select Program —</option>
                                    @foreach($degrees as $degree)
                                        <option value="{{ $degree->id }}" {{ old('degree') == $degree->id ? 'selected' : '' }}>
                                            {{ $degree->degree_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help">Options filter automatically based on your selected department.</small>
                            </div>
                        </div>

                        {{-- Student Type --}}
                        <div class="form-group row">
                            <label for="studenttype" class="col-md-4 col-form-label text-md-right label-req">
                                Student Status
                            </label>
                            <div class="col-md-6">
                                <select id="studenttype" class="form-control" name="studenttype" required>
                                    <option value="">— Select Status —</option>
                                    @foreach($studenttypes as $st)
                                        <option value="{{ $st->id }}" {{ old('studenttype') == $st->id ? 'selected' : '' }}>
                                            {{ $st->type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Declaration --}}
                        <div class="form-group row">
                            <label for="declaration" class="col-md-4 col-form-label text-md-right label-req">
                                Declaration
                            </label>
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="declaration" name="declaration"
                                           class="custom-control-input" required>
                                    <label class="custom-control-label" for="declaration">
                                        I declare that the information provided in this form is correct, true
                                        and complete to the best of my knowledge and belief. If any information
                                        is found false, incorrect, or incomplete, or if any ineligibility is
                                        detected before or after the examination, any legal action can be taken
                                        against me by the authority including the cancellation of my candidature.
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /card-body --}}

                    {{-- Submit --}}
                    <div class="sticky-submit">
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4 d-flex align-items-center">
                                <a href="{{ route('apply-now') }}" class="btn btn-outline-secondary mr-3">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit Admission Application
                                </button>
                            </div>
                        </div>
                    </div>

                </div>{{-- /card --}}
            </form>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // ── Previously-Eligible block toggle ──
    const prevBlock       = document.getElementById('prev-eligibility-block');
    const uniPrevEligible = document.getElementById('uni_prev_eligible');
    const uniPublic       = document.getElementById('uni_public');
    const uniPrivate      = document.getElementById('uni_private');

    function togglePrevBlock() {
        if (!prevBlock) return;
        prevBlock.style.display = (uniPrevEligible && uniPrevEligible.checked) ? 'block' : 'none';
    }

    if (uniPrevEligible) uniPrevEligible.addEventListener('change', togglePrevBlock);
    if (uniPublic)       uniPublic.addEventListener('change', togglePrevBlock);
    if (uniPrivate)      uniPrivate.addEventListener('change', togglePrevBlock);

    // Restore on validation error
    togglePrevBlock();

    // ── Bootstrap 4 custom-file label ──
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('custom-file-input')) {
            const label = e.target.nextElementSibling;
            if (label) label.textContent = e.target.files.length ? e.target.files[0].name : 'Choose file...';
        }
    });

    // ── Department → Degree filtering (dynamically loaded from DB) ──
    const deptDegreeMap = @json($deptDegreeMap);

    let allDegrees  = @json($degrees);
    const oldDept   = "{{ old('department') }}";
    const oldDegree = "{{ old('degree') }}";

    function filterDegrees(deptId) {
        const sel = document.getElementById('degree');
        sel.innerHTML = '<option value="">— Select Program —</option>';
        if (deptDegreeMap[deptId]) {
            deptDegreeMap[deptId].forEach(id => {
                const d = allDegrees.find(x => x.id === id);
                if (d) {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.degree_name;
                    if (String(d.id) === oldDegree) opt.selected = true;
                    sel.appendChild(opt);
                }
            });
        }
    }

    document.getElementById('department').addEventListener('change', function () {
        filterDegrees(parseInt(this.value));
    });

    // Restore on validation error
    if (oldDept) filterDegrees(parseInt(oldDept));
</script>
@endsection
