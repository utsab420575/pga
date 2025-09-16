@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <style>
        .card + .card { margin-top: 1rem; }
        .table-sm td, .table-sm th { padding: .35rem; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">

                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <p class="alert alert-danger">{{ $error }}</p>
                    @endforeach
                @endif
                @if(session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="mb-3">
                    <h4 class="mb-0">Application For Eligibility Verification of Obtained Degree</h4>
                    <small class="text-muted">Applicant Name: <b>{{ $applicant->user->name }}</b> &nbsp;|&nbsp; Roll: <b>{{ $applicant->roll }}</b></small>
                    <input type="hidden" id="applicant_id" value="{{ $applicant->id }}">
                </div>

                {{-- CARD 1: Basic Info --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Basic Information</b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#basicInfoModal">
                                Add / Update
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($basicInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                <tr><th width="25%">Applicant Name (Block Letter)</th><td>{{ $basicInfo->full_name_block_letter}}</td></tr>
                                <tr><th>Father's Name</th><td>{{ $basicInfo->f_name }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $basicInfo->m_name }}</td></tr>
                                <tr><th>National ID</th><td>{{ $basicInfo->nid }}</td></tr>
                                <tr><th>Nationality</th><td>{{ $basicInfo->nationality }}</td></tr>
                                <tr><th>Date of Birth</th><td>{{ optional($basicInfo->dob)->format('Y-m-d') }}</td></tr>
                                <tr><th>Religion</th><td>{{ $basicInfo->religion }}</td></tr>
                                <tr><th>Gender</th><td>{{ $basicInfo->gender }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ $basicInfo->marital_status }}</td></tr>
                                <tr><th>Passport Number</th><td>{{ $basicInfo->passport_no }}</td></tr>
                                <tr><th>Present Address</th><td>{{ $basicInfo->pre_address }}</td></tr>
                                <tr><th>Permanent Address</th><td>{{ $basicInfo->per_address }}</td></tr>
                                </tbody>
                            </table>
                        @else
                            <em>No basic info yet.</em>
                        @endif
                    </div>
                </div>

                {{-- CARD 2: Eligibility Degree --}}
                <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><b>Particulars of Obtained Degree for which Eligibility is required</b></span>
                            @if($applicant->final_submit != 1)
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#eligibilityModal">
                                    {{ $eligibilityDegree ? 'Update' : 'Add' }}
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($eligibilityDegree)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Degree</th><th>Institute</th><th>Country</th><th>CGPA</th><th>Grad. Date</th>
                                            <th>Duration</th><th>Total Credit</th><th>Mode</th><th>Period</th><th>Uni Status</th><th>Web-link</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>{{ $eligibilityDegree->degree }}</td>
                                            <td>{{ $eligibilityDegree->institute }}</td>
                                            <td>{{ $eligibilityDegree->country }}</td>
                                            <td>{{ $eligibilityDegree->cgpa }}</td>
                                            <td>{{ optional($eligibilityDegree->date_graduation)->format('Y-m-d') }}</td>
                                            <td>{{ $eligibilityDegree->duration}}</td>
                                            <td>{{ $eligibilityDegree->total_credit }}</td>
                                            <td>{{ $eligibilityDegree->mode }}</td>
                                            <td>{{ $eligibilityDegree->period }}</td>
                                            <td>{{ $eligibilityDegree->uni_status }}</td>
                                            <td>{{ $eligibilityDegree->url }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <em>No eligibility degree added.</em>
                            @endif
                        </div>
                    </div>

                {{-- CARD 3: Education Info (you can add multiple) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Education Info</b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#educationModal">
                                Add
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Degree</th>
                                    <th>Institute</th>
                                    <th>Year</th>
                                    <th>Field</th>
                                    <th>CGPA</th>
                                    <th style="width:110px">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($educationInfos as $ei)
                                    <tr>
                                        <td>{{ $ei->degree }}</td>
                                        <td>{{ $ei->institute }}</td>
                                        <td>{{ $ei->year_of_passing }}</td>
                                        <td>{{ $ei->field }}</td>
                                        <td>{{ $ei->cgpa }}</td>
                                        <td>
                                            {{--here we send data to modal using link--}}
                                            <a
                                                href="#"
                                                class="btn btn-outline-primary btn-sm ei-edit"
                                                data-update-url="{{ route('education_info.update', $ei->id) }}"
                                                data-degree="{{ $ei->degree }}"
                                                data-institute="{{ $ei->institute }}"
                                                data-year_of_passing="{{ $ei->year_of_passing }}"
                                                data-field="{{ $ei->field }}"
                                                data-cgpa="{{ $ei->cgpa }}"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a
                                                href="{{ route('education_info.delete', $ei->id) }}"
                                                class="btn btn-outline-danger btn-sm ei-delete"
                                                data-delete-url="{{ route('education_info.delete', $ei->id) }}"
                                            >
                                                <i class="fas fa-trash-alt mr-1" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><em>No education info yet.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                {{-- CARD 8: Attachments (by type, with previews) --}}
                {{-- CARD X: Quick Upload (AJAX, single file, toaster feedback) --}}
                @php
                    // Filter out specific attachment types (like 5,7,8,9)
                    // so they don’t appear in the quick upload selection.
                    $selectableTypes = $attachmentTypes->reject(fn($t) => in_array($t->id, [11,12]));
                @endphp

                <div class="card">
                    {{-- Card header with title and "Add" button (opens modal) --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Upload all necessary documents</b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#quickUploadModal">Add</button>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" id="quickUploadTable">
                                <thead>
                                <tr>
                                    <th style="width: 14rem;">Type</th>   {{-- Shows attachment type --}}
                                    <th>Title</th>                        {{-- File title --}}
                                    <th style="width: 12rem;">File</th>   {{-- File preview or link --}}
                                    <th style="width: 9rem;">Action</th>  {{-- Action buttons (delete, etc.) --}}
                                </tr>
                                </thead>
                                <tbody id="quickUploadTbody">
                                {{-- Loop through existing attachments and display them --}}
                                @foreach(($attachments ?? collect())->sortBy('attachment_type_id') as $file)
                                    @php
                                        // Detect if file is an image (for inline preview)
                                        $isImage = \Illuminate\Support\Str::endsWith(
                                            strtolower($file->file),
                                            ['.jpg','.jpeg','.png','.webp','.gif']
                                        );

                                        // Use the saved title if available; otherwise fallback to filename
                                        $displayTitle = $file->title ?? basename($file->file);

                                        // Get the type title from relation (if exists), otherwise show N/A
                                        $typeTitle = optional($file->type)->title ?? 'N/A';

                                        // Build the full file URL
                                        $url = asset($file->file);
                                    @endphp

                                    <tr data-id="{{ $file->id }}">
                                        {{-- File type --}}
                                        <td>{{ $typeTitle }}</td>

                                        {{-- Title or filename --}}
                                        <td>{{ $displayTitle }}</td>

                                        {{-- Preview or link --}}
                                        <td class="text-center">
                                            @if($isImage)
                                                {{-- Inline image preview for images --}}
                                                <img src="{{ $url }}" alt="image"
                                                     style="max-width: 120px; max-height: 80px; border:1px solid #ddd; border-radius:6px;">
                                            @else
                                                {{-- "View" button for non-images (opens file in new tab) --}}
                                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-info btn-sm">View</a>
                                            @endif
                                        </td>

                                        {{-- Action buttons --}}
                                        <td class="text-center">
                                            {{-- Delete button triggers AJAX delete via route --}}
                                            <button class="btn btn-danger btn-sm q-del"
                                                    data-delete-url="{{ route('attachments.ajaxDelete', $file->id) }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- JS will dynamically append new rows here after successful upload --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>






                {{-- CARD: Final Submit --}}
                @if($applicant->final_submit == 1)
                    <div class="alert alert-success d-flex align-items-center mt-3">
                        <i class="fas fa-check-circle fa-2x mr-2"></i>
                        <div>
                            <strong>Application is submitted successfully.</strong>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('final.submit.eligibility', $applicant->id) }}">
                        @csrf

                        {{-- CARD: Declaration --}}
                        <div class="card mt-4">
                            <div class="card-header">
                                <b>Declaration</b>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="declarationCheckbox" name="declaration" required>
                                    <label class="form-check-label" for="declarationCheckbox">
                                        I declare that the information provided in this form is correct, true and complete to the best of my knowledge and belief.
                                        If any information is found false, incorrect, or incomplete, or if any ineligibility is detected before or after the examination,
                                        any legal action can be taken against me by the authority including the cancellation of my application.
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- CARD: Final Submit --}}
                        <div class="card mt-4">
                            <div class="card-header">
                                <b>Final Submission</b>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirmCheckbox" name="confirm" required>
                                    <label class="form-check-label" for="confirmCheckbox">
                                        I confirm that I have submitted all of the required documents and information.
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Are you sure? After final submission, you may not be able to edit further?')">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                @endif



                    {{--empty div--}}
                <div class=" mt-5 mb-5"></div>

            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Basic Info Modal --}}
    {{-- Basic Info Modal --}}
    <div class="modal fade" id="basicInfoModal" tabindex="-1" role="dialog" aria-labelledby="basicInfoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content"  id="basicInfoForm"
                  method="POST"
                  action="{{ $basicInfo ? route('basic_info.update', $basicInfo->id) : route('basic_info.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($basicInfo) @method('PUT') @endif

                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="basicInfoLabel">Basic Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Applicant's Name (In Block Letter)</label><span class="text-danger">*</span>
                            <input
                                type="text"
                                name="full_name_block_letter"
                                class="form-control text-uppercase"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();"
                                maxlength="255"
                                value="{{ old('full_name_block_letter', $basicInfo->full_name_block_letter ?? '') }}"
                                placeholder="E.g., MD RAHIM UDDIN"
                                required
                            >
                        </div>
                        {{-- <div class="form-group col-md-6">
                          <label>Bangla Name</label><span class="text-danger">*</span>
                          <input type="text" name="bn_name" class="form-control"
                                 value="{{ old('bn_name', $basicInfo->bn_name ?? '') }}">
                        </div> --}}
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Father's Name</label><span class="text-danger">*</span>
                            <input type="text" name="f_name" class="form-control"
                                   value="{{ old('f_name', $basicInfo->f_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mother's Name</label><span class="text-danger">*</span>
                            <input type="text" name="m_name" class="form-control"
                                   value="{{ old('m_name', $basicInfo->m_name ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>National ID</label><span class="text-danger">*</span>
                            <input type="text" name="nid" class="form-control"
                                   value="{{ old('nid', $basicInfo->nid ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nationality</label><span class="text-danger">*</span>
                            <input type="text" name="nationality" class="form-control"
                                   value="{{ old('nationality', $basicInfo->nationality ?? '') }}"
                                   placeholder="Bangladeshi"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Date of Birth</label><span class="text-danger">*</span>
                            <input type="date" name="dob" class="form-control"
                                   value="{{ old('dob', optional($basicInfo->dob ?? null)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Religion</label><span class="text-danger">*</span>
                            @php $religionOld = old('religion', $basicInfo->religion ?? ''); @endphp
                            <select name="religion" class="form-control" required>
                                <option value="">--select--</option>
                                <option value="Islam"   {{ $religionOld==='Islam'   ? 'selected':'' }}>Islam</option>
                                <option value="Hindu"   {{ $religionOld==='Hindu'   ? 'selected':'' }}>Hindu</option>
                                <option value="Cristan" {{ $religionOld==='Cristan' ? 'selected':'' }}>Cristan</option>
                                <option value="Baudda"  {{ $religionOld==='Baudda'  ? 'selected':'' }}>Baudda</option>
                                <option value="Others"  {{ $religionOld==='Others'  ? 'selected':'' }}>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Gender</label><span class="text-danger">*</span>
                            @php $genderOld = old('gender', $basicInfo->gender ?? ''); @endphp
                            <select class="form-control" name="gender" required>
                                <option value="">--select--</option>
                                <option value="Male"   {{ $genderOld==='Male'?'selected':'' }}>Male</option>
                                <option value="Female" {{ $genderOld==='Female'?'selected':'' }}>Female</option>
                                <option value="Other"  {{ $genderOld==='Other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Marital Status</label><span class="text-danger">*</span>
                            @php $msOld = old('marital_status', $basicInfo->marital_status ?? ''); @endphp
                            <select class="form-control" name="marital_status" required>
                                <option value="">--select--</option>
                                <option value="Single"   {{ $msOld==='Single'?'selected':'' }}>Single</option>
                                <option value="Married"  {{ $msOld==='Married'?'selected':'' }}>Married</option>
                                <option value="Divorced" {{ $msOld==='Divorced'?'selected':'' }}>Divorced</option>
                                <option value="Widowed"  {{ $msOld==='Widowed'?'selected':'' }}>Widowed</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Passport Number </label><span class="text-mute"> (If any)</span>
                            <input type="text" name="passport_no" class="form-control"
                                   value="{{ old('passport_no', $basicInfo->passport_no ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        {{-- g_incode & field_of_interest intentionally hidden per your comment --}}
                    </div>

                    @php
                        $preText = $basicInfo->pre_address ?? '';
                        $perText = $basicInfo->per_address ?? '';

                        // returns the value after "Label:" on the matching line
                        function addr_pick($text, $label) {
                            if (!$text) return '';
                            foreach (preg_split("/\r\n|\n|\r/", $text) as $line) {
                                [$k,$v] = array_pad(explode(':', $line, 2), 2, '');
                                if (strcasecmp(trim($k), $label) === 0) {
                                    return trim($v);
                                }
                            }
                            return '';
                        }
                    @endphp

                    <div class="form-row">
                        {{-- Present Address --}}
                        <div class="col-md-6">
                            <label class="mb-2"><b>Present Address</b></label><span class="text-danger">*</span>

                            <div class="form-group mb-2">
                                <small>Holding No</small>
                                <input type="text" name="pre_holding_no" class="form-control"
                                       value="{{ old('pre_holding_no', addr_pick($preText, 'Holding No')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Village / Road No</small>
                                <input type="text" name="pre_village_road" class="form-control"
                                       value="{{ old('pre_village_road', addr_pick($preText, 'Village/Road')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Post Office</small>
                                <input type="text" name="pre_post_office" class="form-control"
                                       value="{{ old('pre_post_office', addr_pick($preText, 'Post Office')) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="pre_upazila_thana" class="form-control"
                                       value="{{ old('pre_upazila_thana', addr_pick($preText, 'Upazila/Thana')) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="pre_district" class="form-control"
                                       value="{{ old('pre_district', addr_pick($preText, 'District')) }}" required>
                            </div>

                            {{-- Hidden field that actually gets submitted (required as before) --}}
                            <input type="hidden" name="pre_address" required>
                        </div>

                        {{-- Permanent Address --}}
                        <div class="col-md-6">
                            <label class="mb-2 d-flex justify-content-start align-items-center">
                                <b>Permanent Address</b><span class="text-danger">*</span>
                                <div class="form-check ml-3">
                                    <input type="checkbox" class="form-check-input" id="sameAsPresent">
                                    <label class="form-check-label small" for="sameAsPresent">Same as Present</label>
                                </div>
                            </label>

                            <div class="form-group mb-2">
                                <small>Holding No</small>
                                <input type="text" name="per_holding_no" class="form-control"
                                       value="{{ old('per_holding_no', addr_pick($perText, 'Holding No')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Village / Road No</small>
                                <input type="text" name="per_village_road" class="form-control"
                                       value="{{ old('per_village_road', addr_pick($perText, 'Village/Road')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Post Office</small>
                                <input type="text" name="per_post_office" class="form-control"
                                       value="{{ old('per_post_office', addr_pick($perText, 'Post Office')) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="per_upazila_thana" class="form-control"
                                       value="{{ old('per_upazila_thana', addr_pick($perText, 'Upazila/Thana')) }}" required>
                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="per_district" class="form-control"
                                       value="{{ old('per_district', addr_pick($perText, 'District')) }}" required>
                            </div>

                            <input type="hidden" name="per_address" required>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">{{ $basicInfo ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>



    {{-- Eligibility Degree Modal --}}
    <div class="modal fade" id="eligibilityModal" tabindex="-1" role="dialog" aria-labelledby="eligibilityLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content"
                  method="POST"
                  action="{{ $eligibilityDegree ? route('eligibility_degree.update', $eligibilityDegree->id) : route('eligibility_degree.store') }}">
                @csrf
                @if($eligibilityDegree) @method('PUT') @endif

                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="eligibilityLabel">{{ $eligibilityDegree ? 'Update Eligibility Degree' : 'Add Eligibility Degree' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Name of the degree</label><span class="text-danger">*</span>
                            <input type="text" name="degree" class="form-control" required
                                   value="{{ old('degree', $eligibilityDegree->degree ?? '') }}"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Name of the University/Institution</label><span class="text-danger">*</span>
                            <input type="text" name="institute" class="form-control" required
                                   value="{{ old('institute', $eligibilityDegree->institute ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Country from which the degree obtained </label><span class="text-danger">*</span>
                            <input type="text" name="country" class="form-control" required
                                   value="{{ old('country', $eligibilityDegree->country ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>CGPA/GPA/Class</label><span class="text-danger">*</span>
                            <input type="number" step="0.01" name="cgpa" class="form-control" required
                                   value="{{ old('cgpa', $eligibilityDegree->cgpa ?? '') }}">
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Date of Graduation</label><span class="text-danger">*</span>
                            <input type="date" name="date_graduation" class="form-control" required
                                   value="{{ old('date_graduation', optional($eligibilityDegree->date_graduation ?? null)->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Duration of the program</label><span class="text-danger">*</span>
                            <input type="number" name="duration" class="form-control" placeholder="e.g., 4" required
                                   value="{{ old('duration', $eligibilityDegree->duration ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>Total credit earned</label><span class="text-danger">*</span>
                            <input type="number" step="0.01" name="total_credit" class="form-control" required
                                   value="{{ old('total_credit', $eligibilityDegree->total_credit ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mode of Study </label><span class="text-danger">*</span>
                            @php $modeOld = old('mode', $eligibilityDegree->mode ?? ''); @endphp
                            <select name="mode" class="form-control" required>
                                <option value="">--select--</option>
                                <option {{ $modeOld==='Full-time' ? 'selected':'' }}>Full-time</option>
                                <option {{ $modeOld==='Part-Time' ? 'selected':'' }}>Part-Time</option>
                                <option {{ $modeOld==='Distance learning' ? 'selected':'' }}>Distance learning</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>
                                <b>Period of Stay Abroad</b>
                            </label>
                            <small class="form-text text-muted">
                                For foreign degree, mention your entry & exit dates.
                                In case of Distance Learning, write <em>“Not Applicable”</em>.
                            </small>
                            <input type="text" name="period" class="form-control mt-1"
                                   placeholder="e.g., Jan 2018 – Dec 2022 or Not Applicable"
                                   value="{{ old('period', $eligibilityDegree->period ?? '') }}">
                        </div>

                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>University Status</label><span class="text-danger">*</span>
                            @php $uniOld = old('uni_status', $eligibilityDegree->uni_status ?? ''); @endphp
                            <select name="uni_status" class="form-control" required>
                                <option value="">--select--</option>
                                <option {{ $uniOld==='Public' ? 'selected':'' }}>Public</option>
                                <option {{ $uniOld==='Private' ? 'selected':'' }}>Private</option>
                                <option {{ $uniOld==='International' ? 'selected':'' }}>International</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>University Web Link</label><span class="text-danger">*</span>
                            <input type="url" name="url" class="form-control" placeholder="https://..." required
                                   value="{{ old('url', $eligibilityDegree->url ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">{{ $eligibilityDegree ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Education Info Modal --}}
    {{-- Education Info Modal --}}
    <div class="modal fade" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="educationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="educationForm" class="modal-content" method="POST" action="{{ route('education_info.store') }}">
                @csrf
                <input type="hidden" id="ei_method" name="_method" value="POST">
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="educationLabel"><span id="ei_modal_title">Add Education Info</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group"><label>Degree/Certificate</label><span class="text-danger">*</span><input type="text" name="degree" class="form-control" required></div>
                    <div class="form-group"><label>University/Institute/ Board</label><span class="text-danger">*</span><input type="text" name="institute" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Year of Passing</label><span class="text-danger">*</span><input type="number" name="year_of_passing" class="form-control" min="1900" max="2100" required></div>
                        <div class="form-group col-md-6"><label>Discipline/Field</label><span class="text-danger">*</span><input type="text" name="field" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>CGPA/Class/ Division</label><span class="text-danger">*</span><input type="number" step="0.01" name="cgpa" class="form-control"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="ei_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>




    {{-- Attachment Modal --}}
    <div class="modal fade" id="quickUploadModal" tabindex="-1" role="dialog" aria-labelledby="quickUploadLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            {{-- form inside modal for uploading single file --}}
            <form id="quickUploadForm" class="modal-content" enctype="multipart/form-data">
                @csrf
                {{-- hidden applicant_id field --}}
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="quickUploadLabel">Add Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    {{-- dropdown to select attachment type --}}
                    <div class="form-group">
                        <label>Attachment Type</label><span class="text-danger">*</span>
                        <select name="attachment_type_id" class="form-control" required>
                            <option value="">-- select --</option>
                            @foreach($selectableTypes as $t)
                                <option value="{{ $t->id }}" data-rule="{{ e($t->rules) }}">{{ $t->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- optional title input --}}
                    <div class="form-group">
                        <label>Title of File</label><span class="text-danger">*</span>
                        <input type="text" name="title" class="form-control" maxlength="255" placeholder="e.g., Title" required>
                    </div>

                    {{-- file chooser (single file only) --}}
                    <div class="form-group">
                        <label>Choose File</label><span class="text-danger">*</span><span class="text-danger">*</span>
                        <input type="file" name="file" class="form-control-file" accept="image/*,.pdf" required>
                        {{--<small class="text-muted d-block mt-1">Single PDF is accepted.”</small>--}}
                    </div>


                    <div class="form-group">
                        <label class="mb-1 d-flex align-items-center">
                            <span class="mr-2 text-secondary">Rules for selected type</span>
                            <small class="text-muted"></small>
                        </label><span class="text-danger">*</span>

                        {{-- shown/hidden dynamically based on selection --}}
                        <div id="typeRuleBox" class="alert alert-warning mb-0" style="display:none;">
                            <div id="typeRuleContent"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- submit button with spinner --}}
                    <button id="quickUploadSubmit" type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm mr-1 d-none" id="quickUploadSpinner"></span>
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>



@endsection


{{-- hidden DELETE form for Education Info data delete --}}
<form id="eiDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>


@section('script')
   {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>--}}


   {{-- sweealert--}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (event) {
                const link = event.target.closest('.ei-delete');
                if (!link) return;

                event.preventDefault();

                const deleteUrl = link.getAttribute('data-delete-url') || link.getAttribute('href');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('eiDeleteForm');
                        form.setAttribute('action', deleteUrl);
                        form.submit(); // <-- sends POST with _method=DELETE
                    }
                });
            });
        });
    </script>


    {{-- this is for education info --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.ei-edit');
            if (!link) return;
            e.preventDefault();

            const f = document.getElementById('educationForm');
            f.setAttribute('action', link.dataset.updateUrl);
            document.getElementById('ei_method').value = 'PUT';
            document.getElementById('ei_modal_title').textContent = 'Update Education Info';
            document.getElementById('ei_submit_btn').textContent  = 'Update';

            f.querySelector('[name=degree]').value          = link.dataset.degree || '';
            f.querySelector('[name=institute]').value       = link.dataset.institute || '';
            f.querySelector('[name=year_of_passing]').value = link.dataset.year_of_passing || '';
            f.querySelector('[name=field]').value           = link.dataset.field || '';
            f.querySelector('[name=cgpa]').value            = link.dataset.cgpa || '';

            $('#educationModal').modal('show');
        });
    </script>

   <script>
       document.getElementById('sameAsPresent').addEventListener('change', function() {
           const fields = ['holding_no','village_road','post_office','upazila_thana','district'];
           if (this.checked) {
               fields.forEach(f => {
                   document.querySelector(`[name="per_${f}"]`).value =
                       document.querySelector(`[name="pre_${f}"]`).value;
               });
           } else {
               fields.forEach(f => {
                   document.querySelector(`[name="per_${f}"]`).value = '';
               });
           }
       });
   </script>


   {{--Attachment--}}
   <script>
       (function () {
           // cache DOM elements
           const $form   = $('#quickUploadForm');      // upload form
           const $modal  = $('#quickUploadModal');     // modal
           const $tbody  = $('#quickUploadTbody');     // tbody of attachments table
           const $btn    = $('#quickUploadSubmit');    // submit button
           const $spin   = $('#quickUploadSpinner');   // spinner in button

           // NEW: rule UI elements
           const $typeSelect  = $form.find('select[name="attachment_type_id"]');
           const $ruleBox     = $('#typeRuleBox');
           const $ruleContent = $('#typeRuleContent');

           // NEW: render rule text safely (convert \n to <br>, escape HTML)
           function renderRule(ruleText) {
               if (!ruleText) return '';
               return String(ruleText)
                   .replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;')
                   .replace(/\n/g, '<br>');
           }

           // NEW: update the rules panel based on current selection
           function updateRuleBox() {
               const $opt = $typeSelect.find('option:selected');
               const raw  = $opt.data('rule') || '';           // raw rule text from DB (already escaped in HTML)
               const rule = String(raw).trim();

               if (rule.length) {
                   // Convert newlines to <br> for display
                   $ruleContent.html(rule.replace(/\n/g, '<br>'));
                   $ruleBox.show();
               } else {
                   $ruleContent.empty();
                   $ruleBox.hide();
               }
           }

           // NEW: initialize rules when modal opens & when type changes
           $modal.on('shown.bs.modal', updateRuleBox);
           $typeSelect.on('change', updateRuleBox);

           // Configure Toastr (notification settings)
           toastr.options = {
               closeButton: true,
               progressBar: true,
               timeOut: 2500,
               positionClass: 'toast-bottom-right'
           };

           // ========== AJAX Upload ==========
           $form.on('submit', function (e) {
               e.preventDefault(); // prevent normal submit

               const formData = new FormData(this); // build FormData from form

               // disable button + show spinner while uploading
               $btn.prop('disabled', true);
               $spin.removeClass('d-none');

               $.ajax({
                   url: @json(route('attachments.ajaxUpload')), // endpoint
                   method: 'POST',
                   data: formData,
                   contentType: false,
                   processData: false,
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || @json(csrf_token())
                   }
               }).done(function (res) {
                   // Expecting JSON { id, type_id, type_title, title, url, is_image }
                   if (!res || !res.id) {
                       toastr.error('Unexpected server response.');
                       return;
                   }

                   // Extract response data
                   const typeTitle = res.type_title || 'N/A';
                   const title     = res.title || '';
                   const url       = res.url || '#';

                   // Decide how to render cell (image preview or View button)
                   const imgCell = res.is_image
                       ? `<img src="${url}" alt="image" style="max-width:120px; max-height:80px; border:1px solid #ddd; border-radius:6px;">`
                       : `<a href="${url}" target="_blank" class="btn btn-outline-info btn-sm">View</a>`;

                   // Build delete URL dynamically
                   const deleteUrl = @json(route('attachments.ajaxDelete', 0));
                   const finalDeleteUrl = deleteUrl.replace(/0$/, String(res.id));

                   // Build row HTML
                   const row = `
                <tr data-id="${res.id}">
                    <td>${escapeHtml(typeTitle)}</td>
                    <td>${escapeHtml(title)}</td>
                    <td class="text-center">${imgCell}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm q-del" data-delete-url="${finalDeleteUrl}">Delete</button>
                    </td>
                </tr>
            `;

                   // prepend new row to table body
                   $tbody.prepend(row);
                   toastr.success('File uploaded successfully.');

                   // reset only title + file inputs (keep type selected and keep rules visible)
                   $form.find('input[name="title"]').val('');
                   $form.find('input[name="file"]').val('');

                   // (Optional) re-evaluate rules in case you want to adapt after upload
                   updateRuleBox();
               }).fail(function (xhr) {
                   // handle upload error
                   let msg = 'Upload failed.';
                   if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                       msg = xhr.responseJSON.message;
                   }
                   toastr.error(msg);
               }).always(function () {
                   // re-enable button + hide spinner
                   $btn.prop('disabled', false);
                   $spin.addClass('d-none');
               });
           });

           // ========== AJAX Delete ==========
           $(document).on('click', '.q-del', function (e) {
               e.preventDefault();
               const $btn = $(this);
               const url  = $btn.data('delete-url');
               const $row = $btn.closest('tr');

               Swal.fire({
                   title: "Delete this file?",
                   icon: "warning",
                   showCancelButton: true,
                   confirmButtonText: "Delete",
                   confirmButtonColor: "#d33"
               }).then((res) => {
                   if (!res.isConfirmed) return;

                   // prevent duplicate clicks
                   if ($btn.data('busy')) return;
                   $btn.data('busy', true).prop('disabled', true);

                   $.ajax({
                       url: url,
                       method: 'DELETE',
                       headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || @json(csrf_token())
                       }
                   }).done(function () {
                       $row.remove();
                       toastr.success('File deleted.');
                   }).fail(function (xhr) {
                       // If Laravel route-model binding can’t find it, it returns 404 -> treat as already deleted
                       if (xhr && xhr.status === 404) {
                           $row.remove();
                           toastr.info('Item was already deleted.');
                           return;
                       }
                       let msg = 'Delete failed.';
                       if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                           msg = xhr.responseJSON.message;
                       }
                       toastr.error(msg);
                   }).always(function () {
                       $btn.removeData('busy').prop('disabled', false);
                   });
               });
           });

           // helper: escape text to prevent HTML injection
           function escapeHtml(s) {
               return (s || '').toString()
                   .replaceAll('&', '&amp;')
                   .replaceAll('<', '&lt;')
                   .replaceAll('>', '&gt;')
                   .replaceAll('"', '&quot;')
                   .replaceAll("'", '&#039;');
           }
       })();
   </script>

    {{--present and permanent address--}}
    <script>
        (function () {
            function buildAddress(prefix) {
                const get = (n) => (document.querySelector(`[name="${prefix}_${n}"]`)?.value || '').trim();
                const parts = [];

                const mapping = [
                    ['holding_no',    'Holding No'],
                    ['village_road',  'Village/Road'],
                    ['post_office',   'Post Office'],
                    ['upazila_thana', 'Upazila/Thana'],
                    ['district',      'District'],
                ];

                mapping.forEach(([key, label]) => {
                    const v = get(key);
                    if (v) parts.push(`${label}: ${v}`);
                });

                const hidden = document.querySelector(`[name="${prefix}_address"]`);
                if (hidden) hidden.value = parts.join('\n');
            }

            // Compose on submit of the Basic Info form
            const basicForm = document.getElementById('basicInfoForm');
            if (basicForm) {
                basicForm.addEventListener('submit', function () {
                    buildAddress('pre'); // fills hidden pre_address
                    buildAddress('per'); // fills hidden per_address
                });
            }
        })();
    </script>



    {{--for showing which data should upload--}}
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover();
        });
    </script>

@endsection
