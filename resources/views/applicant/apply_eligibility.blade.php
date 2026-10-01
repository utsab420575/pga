@extends('layouts.app')

@section('css')
<style>
    .form-card        { border:0; border-radius:1rem; box-shadow:0 14px 30px rgba(18,38,63,.06) }
    .card-header-elig { border-bottom:0; background:linear-gradient(90deg,#1a9e8f,#0d7a6d); color:#fff; font-weight:600; border-radius:1rem 1rem 0 0 !important; }
    .label-req::after { content:" *"; color:#dc3545; font-weight:700 }
    .help             { font-size:.85rem; color:#6c757d }
    .sticky-submit    { position:sticky; bottom:0; background:#fff; padding:.75rem 0; border-top:1px solid #f1f3f5 }
    .window-info      { background:#f0faf8; border:1px solid #c6f0ea; border-radius:.6rem; padding:.6rem 1rem; font-size:.88rem; color:#1a6a60; margin-bottom:1.2rem; }
    .window-info i    { margin-right:.4rem; }
    .type-badge       { display:inline-block; background:#e6f7f5; color:#0d7a6d; font-weight:700; font-size:.82rem; padding:.25rem .7rem; border-radius:5px; border:1px solid #c6f0ea; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            {{-- Error / status messages  we can see--}}
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

                {{-- Hidden: type is always Eligibility (2), university always private --}}
                <input type="hidden" name="university_type"  value="private">
                <input type="hidden" name="applicationtype"  value="2">

                <div class="card form-card" style="margin-top:15px">

                    {{-- Card Header --}}
                    <div class="card-header card-header-elig d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-clipboard-check mr-2"></i>Eligibility Application</span>
                        <span class="type-badge">Private University</span>
                    </div>

                    <div class="card-body">

                        {{-- Application Window Info --}}
                        <div class="window-info">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Application Window:</strong>
                            {{ $eligStart->format('d M Y') }} &ndash; {{ $eligEnd->format('d M Y') }}
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
                            <div class="col-md-8 offset-md-4 d-flex align-items-center gap-3">
                                <a href="{{ route('apply-now') }}" class="btn btn-outline-secondary mr-3">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit Eligibility Application
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
    // ── Department → Degree filtering (dynamically loaded from DB) ──
    const deptDegreeMap = @json($deptDegreeMap);

    let allDegrees = @json($degrees);
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
