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
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#basicInfoModal">
                            Add / Update
                        </button>
                    </div>
                    <div class="card-body">
                        @if($basicInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                <tr><th width="25%">Applicant Name(Block Letter)</th><td>{{ $basicInfo->full_name_block_letter}}</td></tr>
                                <tr><th>Father's Name</th><td>{{ $basicInfo->f_name }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $basicInfo->m_name }}</td></tr>
                                <tr><th>National ID</th><td>{{ $basicInfo->nid }}</td></tr>
                                <tr><th>Nationality</th><td>{{ $basicInfo->nationality }}</td></tr>
                                <tr><th>DOB</th><td>{{ optional($basicInfo->dob)->format('Y-m-d') }}</td></tr>
                                <tr><th>Religion</th><td>{{ $basicInfo->religion }}</td></tr>
                                <tr><th>Gender</th><td>{{ $basicInfo->gender }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ $basicInfo->marital_status }}</td></tr>
                                <tr><th>Passport No</th><td>{{ $basicInfo->passport_no }}</td></tr>
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
                            <span><b>Eligibility Degree</b></span>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#eligibilityModal">
                                {{ $eligibilityDegree ? 'Update' : 'Add' }}
                            </button>
                        </div>
                        <div class="card-body">
                            @if($eligibilityDegree)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Degree</th><th>Institute</th><th>Country</th><th>CGPA</th><th>Grad. Date</th>
                                            <th>Duration</th><th>Total Credit</th><th>Mode</th><th>Period</th><th>Uni Status</th><th>Total Credit</th>
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
                                            <td>{{ $eligibilityDegree->total_credit }}</td>
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
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#educationModal">
                            Add
                        </button>
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

                {{-- CARD 4: Attachments --}}
                    <div class="card">
                        <div class="card-header">Required Documents (which must be attached here with) :</div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($attachmentTypes->sortBy('id') as $type)
                                    @continue(in_array($type->id, [4]))
                                    @php
                                        $uploaded = $attachments->where('attachment_type_id', $type->id);
                                    @endphp
                                    <div class="col-md-12">
                                        <div class="card mb-3" id="doc-{{ $type->id }}">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <span>{{ $type->title }}</span>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-sm btn-info mr-2"
                                                            data-toggle="popover"
                                                            data-html="true"
                                                            title="Instructions"
                                                            data-content="
                                                                @if($type->id == 1)
                                                                    1. Attested SSC Certificate <br>
                                                                    2. Attested Diploma Certificate <br>
                                                                    3. Attested BSc Certificate
                                                                @elseif($type->id == 2)
                                                                     1. Attested SSC Transcript/Grade-sheet <br>
                                                                    2. Attested Diploma Transcript/Grade-sheet <br>
                                                                    3. Attested BSc Transcript/Grade-sheet

                                                                @elseif($type->id == 3)
                                                                    1. Attested SSC Mark-sheet <br>
                                                                    2. Attested Diploma Mark-sheet <br>
                                                                    3. Attested BSc Mark-sheet
                                                                @elseif($type->id == 4)
                                                                   1.Attested Testimonial
                                                                @elseif($type->id == 5)
                                                                    1.Detailed Syllabus mentioning all the contents
                                                                @elseif($type->id == 6)
                                                                    Recent photo  (max 500KB)
                                                                @elseif($type->id == 7)
                                                                    1.Attested copy of National ID Card/Birth Certificate
                                                                @elseif($type->id == 8)
                                                                    1.Attested copy of Passport(Proof of valid Visa for staying abroad to achieve foreign degree)
                                                                @elseif($type->id == 9)
                                                                    1.Document in support of credit and courses waived/transferred(if any)
                                                                @elseif($type->id == 10)
                                                                    Signature  (max 500KB)
                                                                @else
                                                                    Upload relevant document
                                                                @endif
                                                            ">
                                                        ?
                                                    </button>

                                                    @if($type->required)
                                                        <span class="badge badge-danger">required</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body">

                                                {{-- Upload input (multiple) --}}
                                                <form action="{{ route('attachments.upload') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="attachment_type_id" value="{{ $type->id }}">
                                                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                                                    <div class="d-flex align-items-center">
                                                        <input type="file" name="files[]" class="form-control-file" accept="image/*,.pdf" multiple required>
                                                        <button type="submit" class="btn btn-primary btn-sm ml-2">Upload</button>
                                                    </div>
                                                </form>

                                                {{-- Preview all uploaded files --}}
                                                @if($uploaded->count())
                                                    <div class="mt-3">
                                                        @foreach($uploaded as $file)
                                                            <div class="d-inline-block text-center mr-3 mb-2">
                                                                @if(Str::endsWith($file->file, ['.jpg','.jpeg','.png']))
                                                                    <img src="{{ asset($file->file) }}" width="100"
                                                                         style="border:1px solid #ccc; border-radius:5px; margin-bottom:10px; display:block;" class="border rounded d-block mb-1">
                                                                @else
                                                                    @php
                                                                        // Build a display name from the stored file path
                                                                        $url        = asset($file->file);
                                                                        $filename   = basename($file->file);                           // e.g. 12_4_20250904_153012_123456_my_degree_certificate.pdf
                                                                        $nameNoExt  = pathinfo($filename, PATHINFO_FILENAME);          // 12_4_20250904_153012_123456_my_degree_certificate
                                                                        $parts      = explode('_', $nameNoExt);

                                                                        // Our pattern: applicantId _ typeId _ YYYYMMDD _ HHMMSS _ micro _ original_slug
                                                                        // So original starts from index 5; if not present, just use the whole base.
                                                                        $origSlug   = count($parts) >= 6 ? implode('_', array_slice($parts, 5)) : $nameNoExt;

                                                                        // Make it pretty for display
                                                                        $displayName = str_replace('_', ' ', $origSlug);
                                                                    @endphp

                                                                    <a href="{{ asset($file->file) }}" target="_blank" class="btn btn-outline-info btn-sm"
                                                                       style="margin-bottom:10px; display:inline-block;">View {{ $displayName }}</a>
                                                                @endif

                                                                <div>
                                                                    <a href="{{ asset($file->file) }}" download  style="margin-right:5px;" class="btn btn-success btn-sm">Download</a>
                                                                    <form action="{{ route('attachments.delete', $file->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>



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
                            <label>Applicant's Name(In Block Letter)</label>
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
                          <label>Bangla Name</label>
                          <input type="text" name="bn_name" class="form-control"
                                 value="{{ old('bn_name', $basicInfo->bn_name ?? '') }}">
                        </div> --}}
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Father's Name</label>
                            <input type="text" name="f_name" class="form-control"
                                   value="{{ old('f_name', $basicInfo->f_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mother's Name</label>
                            <input type="text" name="m_name" class="form-control"
                                   value="{{ old('m_name', $basicInfo->m_name ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>National ID</label>
                            <input type="text" name="nid" class="form-control"
                                   value="{{ old('nid', $basicInfo->nid ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control"
                                   value="{{ old('nationality', $basicInfo->nationality ?? '') }}"
                                   placeholder="Bangladesh"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>DOB</label>
                            <input type="date" name="dob" class="form-control"
                                   value="{{ old('dob', optional($basicInfo->dob ?? null)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Religion</label>
                            @php $religionOld = strtolower(old('religion', $basicInfo->religion ?? '')); @endphp
                            <select name="religion" class="form-control" required>
                                <option value="">--select--</option>
                                <option value="islam"   {{ $religionOld==='islam'   ? 'selected' : '' }}>Islam</option>
                                <option value="hindu"   {{ $religionOld==='hindu'   ? 'selected' : '' }}>Hindu</option>
                                <option value="cristan" {{ $religionOld==='cristan' ? 'selected' : '' }}>Cristan</option>
                                <option value="baudda"  {{ $religionOld==='baudda'  ? 'selected' : '' }}>Baudda</option>
                                <option value="others"  {{ $religionOld==='others'  ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Gender</label>
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
                            <label>Marital Status</label>
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
                            <label>Passport No</label>
                            <input type="number" name="passport_no" class="form-control"
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
                            <label class="mb-2"><b>Present Address</b></label>

                            <div class="form-group mb-2">
                                <small>Holding No</small>
                                <input type="text" name="pre_holding_no"    class="form-control"
                                       value="{{ old('pre_holding_no',    addr_pick($preText, 'Holding No')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Village / Road No</small>
                                <input type="text" name="pre_village_road"  class="form-control"
                                       value="{{ old('pre_village_road',  addr_pick($preText, 'Village/Road')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Post Office</small>

                                <input type="text" name="pre_post_office"   class="form-control"
                                       value="{{ old('pre_post_office',   addr_pick($preText, 'Post Office')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="pre_upazila_thana" class="form-control"
                                       value="{{ old('pre_upazila_thana', addr_pick($preText, 'Upazila/Thana')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="pre_district"      class="form-control"
                                       value="{{ old('pre_district',      addr_pick($preText, 'District')) }}">
                            </div>

                            {{-- Hidden field that actually gets submitted (required as before) --}}
                            <input type="hidden" name="pre_address" required>

                        </div>

                        {{-- Permanent Address --}}
                        <div class="col-md-6">
                            <label class="mb-2"><b>Permanent Address</b></label>

                            <div class="form-group mb-2">
                                <small>Holding No</small>
                                <input type="text" name="per_holding_no"    class="form-control"
                                       value="{{ old('per_holding_no',    addr_pick($perText, 'Holding No')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Village / Road No</small>
                                <input type="text" name="per_village_road"  class="form-control"
                                       value="{{ old('per_village_road',  addr_pick($perText, 'Village/Road')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Post Office</small>
                                <input type="text" name="per_post_office"   class="form-control"
                                       value="{{ old('per_post_office',   addr_pick($perText, 'Post Office')) }}">

                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="per_upazila_thana" class="form-control"
                                       value="{{ old('per_upazila_thana', addr_pick($perText, 'Upazila/Thana')) }}">

                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="per_district"      class="form-control"
                                       value="{{ old('per_district',      addr_pick($perText, 'District')) }}">
                            </div>

                            {{-- Hidden field that actually gets submitted (required as before) --}}
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
                            <label>Degree</label>
                            <input type="text" name="degree" class="form-control" required
                                   value="{{ old('degree', $eligibilityDegree->degree ?? '') }}"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Institute/University</label>
                            <input type="text" name="institute" class="form-control" required
                                   value="{{ old('institute', $eligibilityDegree->institute ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Country</label>
                            <input type="text" name="country" class="form-control" required
                                   value="{{ old('country', $eligibilityDegree->country ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>CGPA/GPA/Class</label>
                            <input type="number" step="0.01" name="cgpa" class="form-control" required
                                   value="{{ old('cgpa', $eligibilityDegree->cgpa ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Date of Graduation</label>
                            <input type="date" name="date_graduation" class="form-control" required
                                   value="{{ old('date_graduation', optional($eligibilityDegree->date_graduation ?? null)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Duration in Year</label>
                            <input type="number" name="duration" class="form-control" placeholder="e.g., 4" required
                                   value="{{ old('duration', $eligibilityDegree->duration ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Total Credit</label>
                            <input type="number" step="0.01" name="total_credit" class="form-control" required
                                   value="{{ old('total_credit', $eligibilityDegree->total_credit ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mode</label>
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
                        <div class="form-group col-md-4">
                            <label>Period</label>
                            <input type="text" name="period" class="form-control" placeholder="2018-2022" required
                                   value="{{ old('period', $eligibilityDegree->period ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>University Status</label>
                            @php $uniOld = old('uni_status', $eligibilityDegree->uni_status ?? ''); @endphp
                            <select name="uni_status" class="form-control" required>
                                <option value="">--select--</option>
                                <option {{ $uniOld==='Public' ? 'selected':'' }}>Public</option>
                                <option {{ $uniOld==='Private' ? 'selected':'' }}>Private</option>
                                <option {{ $uniOld==='International' ? 'selected':'' }}>International</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>University Web Link</label>
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
                    <div class="form-group"><label>Degree</label><input type="text" name="degree" class="form-control" required></div>
                    <div class="form-group"><label>Institute</label><input type="text" name="institute" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Year of Passing</label><input type="number" name="year_of_passing" class="form-control" min="1900" max="2100" required></div>
                        <div class="form-group col-md-6"><label>Field</label><input type="text" name="field" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>CGPA</label><input type="number" step="0.01" name="cgpa" class="form-control"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="ei_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>




    {{-- Attachment Modal --}}



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
