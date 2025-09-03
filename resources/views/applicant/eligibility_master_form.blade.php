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
                            Add
                        </button>
                    </div>
                    <div class="card-body">
                        @if($eligibilityDegrees->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>Degree</th><th>Institute</th><th>Country</th><th>CGPA</th><th>Grad. Date</th><th>Mode</th><th>Uni Status</th><th>Total Credit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($eligibilityDegrees as $ed)
                                        <tr>
                                            <td>{{ $ed->degree }}</td>
                                            <td>{{ $ed->institute }}</td>
                                            <td>{{ $ed->country }}</td>
                                            <td>{{ $ed->cgpa }}</td>
                                            <td>{{ optional($ed->date_graduation)->format('Y-m-d') }}</td>
                                            <td>{{ $ed->mode }}</td>
                                            <td>{{ $ed->uni_status }}</td>
                                            <td>{{ $ed->total_credit }}</td>
                                        </tr>
                                    @endforeach
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
                        @if($educationInfos->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>Degree</th><th>Institute</th><th>Year</th><th>Field</th><th>CGPA</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($educationInfos as $ei)
                                        <tr>
                                            <td>{{ $ei->degree }}</td>
                                            <td>{{ $ei->institute }}</td>
                                            <td>{{ $ei->year_of_passing }}</td>
                                            <td>{{ $ei->field }}</td>
                                            <td>{{ $ei->cgpa }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <em>No education info yet.</em>
                        @endif
                    </div>
                </div>

                {{-- CARD 4: Attachments --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Attachments</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#attachmentModal">
                            Upload
                        </button>
                    </div>
                    <div class="card-body">
                        @if($attachments->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                    <tr>
                                        <th>Type</th><th>File</th><th>Uploaded</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($attachments as $at)
                                        <tr>
                                            <td>{{ optional($at->type)->title }}</td>
                                            <td>
                                                <a href="{{ Storage::url($at->file) }}" target="_blank">view</a>
                                            </td>
                                            <td>{{ $at->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <em>No attachments yet.</em>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Basic Info Modal --}}
    {{-- Basic Info Modal --}}
    <div class="modal fade" id="basicInfoModal" tabindex="-1" role="dialog" aria-labelledby="basicInfoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content"
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
                                   value="{{ old('nationality', $basicInfo->nationality ?? '') }}" required>
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

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Present Address</label>
                            <textarea name="pre_address" class="form-control" rows="2">{{ old('pre_address', $basicInfo->pre_address ?? '') }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Permanent Address</label>
                            <textarea name="per_address" class="form-control" rows="2">{{ old('per_address', $basicInfo->per_address ?? '') }}</textarea>
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
            <form class="modal-content" method="POST" action="{{ route('eligibility_degree.store') }}">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="eligibilityLabel">Eligibility Degree</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Degree</label><input type="text" name="degree" class="form-control" required></div>
                        <div class="form-group col-md-6"><label>Institute/University</label><input type="text" name="institute" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Country</label><input type="text" name="country" class="form-control"></div>
                        <div class="form-group col-md-4"><label>CGPA/GPA/Class</label><input type="number" step="0.01" name="cgpa" class="form-control"></div>
                        <div class="form-group col-md-4"><label>Date of Graduation</label><input type="date" name="date_graduation" class="form-control"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Duration</label><input type="text" name="duration" class="form-control" placeholder="e.g., 4 years"></div>
                        <div class="form-group col-md-4"><label>Total Credit</label><input type="number" step="0.01" name="total_credit" class="form-control"></div>
                        <div class="form-group col-md-4"><label>Mode</label>
                            <select name="mode" class="form-control">
                                <option value="">--select--</option>
                                <option>Full-time</option>
                                <option>Part-Time</option>
                                <option>Distance learning</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Period</label><input type="text" name="period" class="form-control" placeholder="2018-2022"></div>
                        <div class="form-group col-md-4"><label>University Status</label>
                            <select name="uni_status" class="form-control">
                                <option value="">--select--</option>
                                <option>Public</option><option>Private</option><option>International</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4"><label>University Web Link</label><input type="url" name="url" class="form-control" placeholder="https://..."></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Education Info Modal --}}
    <div class="modal fade" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="educationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" method="POST" action="{{ route('education_info.store') }}">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="educationLabel">Education Info</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Degree</label><input type="text" name="degree" class="form-control" required></div>
                    <div class="form-group"><label>Institute</label><input type="text" name="institute" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Year of Passing</label><input type="number" name="year_of_passing" class="form-control" min="1900" max="2100"></div>
                        <div class="form-group col-md-6"><label>Field</label><input type="text" name="field" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>CGPA</label><input type="number" step="0.01" name="cgpa" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Attachment Modal --}}
    <div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content" method="POST" action="{{ route('attachment.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentLabel">Upload Attachment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Attachment Type</label>
                        <select name="attachment_type_id" class="form-control" required>
                            <option value="">--select--</option>
                            @foreach($attachmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>File (jpg/png/webp/pdf)</label>
                        <input type="file" name="file" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        <small class="text-muted">Images or PDF allowed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
@endsection
