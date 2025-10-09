@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <style>
        .card + .card { margin-top: 1rem; }
        .table-sm td, .table-sm th { padding: .35rem; }
        .w-110 { width: 110px; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-sm-12">

                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <p class="alert alert-danger">{{ $error }}</p>
                    @endforeach
                @endif
                @if(session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="mb-3">
                    <h4 class="mb-0">Application for admission to Postgraduate Program</h4>
                    <small class="text-muted">
                        Applicant: <b>{{ $applicant->user->name }}</b> &nbsp;|&nbsp; Roll: <b>{{ $applicant->roll }}</b>
                    </small>
                    <input type="hidden" id="applicant_id" value="{{ $applicant->id }}">
                </div>


                {{-- CARD 1: Basic Info --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Basic Information</b></span>
                        {{-- Floating Info Button --}}
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#basicInfoModal">Add / Update</button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($basicInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                <tr><th width="35%">Full Name (As per S.S.C Certificate)</th><td>{{ $basicInfo->full_name }}</td></tr>
                                <tr><th>Applicant Name (Block Letter)</th><td>{{ $basicInfo->full_name_block_letter }}</td></tr>
                                <tr><th>Name (Bangla)(As per S.S.C Certificate)</th><td>{{ $basicInfo->bn_name }}</td></tr>

                                <tr><th>Father's Name</th><td>{{ $basicInfo->f_name }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $basicInfo->m_name }}</td></tr>
                                <tr>
                                    <th>Guardian's Annual Income</th>
                                    <td>
                                        @if(!is_null($basicInfo->g_income))
                                            {{ number_format((float)$basicInfo->g_income, 2) }}
                                        @endif
                                    </td>
                                </tr>

                                <tr><th>National ID</th><td>{{ $basicInfo->nid }}</td></tr>
                                <tr><th>Nationality</th><td>{{ $basicInfo->nationality }}</td></tr>
                                <tr><th>Date of Birth</th><td>{{ optional($basicInfo->dob)->format('Y-m-d') }}</td></tr>
                                <tr><th>Religion</th><td>{{ $basicInfo->religion }}</td></tr>
                                <tr><th>Gender</th><td>{{ $basicInfo->gender }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ $basicInfo->marital_status }}</td></tr>

                                <tr><th>Field of Interest</th><td>{{ $basicInfo->field_of_interest }}</td></tr>
                                @if($applicant->department_id == 1)
                                    <tr>
                                        <th>Field (Civil Engineering Specific)</th>
                                        <td>{{ $basicInfo->field_name_ce ?? 'N/A' }}</td>
                                    </tr>
                                @endif

                                <tr><th>Present Address</th>
                                    <td><pre class="mb-0" style="white-space:pre-wrap">{{ $basicInfo->pre_address }}</pre></td>
                                </tr>
                                <tr><th>Permanent Address</th>
                                    <td><pre class="mb-0" style="white-space:pre-wrap">{{ $basicInfo->per_address }}</pre></td>
                                </tr>
                                </tbody>
                            </table>
                        @else
                            <em>No basic info yet.</em>
                        @endif
                    </div>
                </div>


                {{-- CARD 3: Education Info (multiple) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Degrees Obtained (Starting from the Most Recent)</strong><br>
                            <small class="text-muted">
                                N.B.: Please attach attested copies of the Certificate, Mark-sheet, Transcript/Grade Sheet,
                                and Testimonial for all academic qualifications.
                            </small>
                        </div>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#educationModal">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Degree/Certificate</th>
                                    <th>University/Institute/Board</th>
                                    <th>Year of Passing</th>
                                    <th>Discipline/Field</th>
                                    <th>CGPA</th>
                                    <th class="w-110">Action</th>
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
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm ei-edit"
                                               data-update-url="{{ route('education_info.update', $ei->id) }}"
                                               data-degree="{{ $ei->degree }}"
                                               data-institute="{{ $ei->institute }}"
                                               data-year_of_passing="{{ $ei->year_of_passing }}"
                                               data-field="{{ $ei->field }}"
                                               data-cgpa="{{ $ei->cgpa }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('education_info.delete', $ei->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
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


                {{-- CARD 4: Thesis (multiple like Education) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Thesis (if any):</b></span>
                        @if($applicant->final_submit != 1)
                            <button id="btnThAdd"
                                    class="btn btn-primary btn-sm"
                                    data-toggle="modal"
                                    data-target="#thesisModal"
                                    data-store-url="{{ route('thesis.store') }}">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Name of University/Institute</th>
                                    <th>Supervisor</th>
                                    <th>Period</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($theses as $t)
                                    <tr>
                                        <td>{{ $t->title }}</td>
                                        <td>{{ $t->institute }}</td>
                                        <td>{{ $t->supervisor }}</td>
                                        <td>{{ $t->period }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm th-edit"
                                               data-update-url="{{ route('thesis.update', $t->id) }}"
                                               data-title="{{ $t->title }}"
                                               data-institute="{{ $t->institute }}"
                                               data-period="{{ $t->period }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('thesis.delete', $t->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><em>No thesis added.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



                {{-- CARD 5: Publications --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Publication (if any):</b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#publicationModal"><i class="fas fa-plus"></i> Add</button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author's</th>
                                    <th>Year of Publication</th>
                                    <th>Publication Details </th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($publications ?? collect() as $p)
                                    <tr>
                                        <td>{{ $p->title }}</td>
                                        <td>{{ $p->authors }}</td>
                                        <td>{{ $p->year_of_publication }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($p->details, 120) }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm pub-edit"
                                               data-update-url="{{ route('publication.update', $p->id) }}"
                                               data-title="{{ $p->title }}"
                                               data-authors="{{ $p->authors }}"
                                               data-year="{{ $p->year_of_publication }}"
                                               data-details="{{ $p->details }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('publication.delete', $p->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><em>No publications added.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                {{-- CARD 6: Job Experience --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Practical Job Experience (if any)	: </b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#jobModal"><i class="fas fa-plus"></i> Add</button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Designation</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($jobExperiences ?? collect() as $j)
                                    <tr>
                                        <td>{{ $j->organization }}</td>
                                        <td>{{ $j->designation }}</td>
                                        <td>{{ optional($j->from)->format('Y-m-d') }}</td>
                                        <td>{{ optional($j->to)->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm job-edit"
                                               data-update-url="{{ route('job_experience.update', $j->id) }}"
                                               data-organization="{{ $j->organization }}"
                                               data-designation="{{ $j->designation }}"
                                               data-from="{{ optional($j->from)->format('Y-m-d') }}"
                                               data-to="{{ optional($j->to)->format('Y-m-d') }}"
                                               data-details="{{ $j->details }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('job_experience.delete', $j->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><em>No job experience added.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                {{-- CARD 7: References --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Name of Two Referees</strong><br>
                            <small class="text-muted">
                                At least one referee must be a teacher from the last institution you attended.
                            </small>
                        </div>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#referenceModal">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>University/Institute/Organization</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Order No</th>
                                    <th>Address</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($references ?? collect() as $r)
                                    <tr>
                                        <td>{{ $r->name }}</td>
                                        <td>{{ $r->designation }}</td>
                                        <td>{{ $r->institute }}</td>
                                        <td>{{ $r->email }}</td>
                                        <td>{{ $r->phone }}</td>
                                        <td>{{ $r->order_no }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($r->address, 80) }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm ref-edit"
                                               data-update-url="{{ route('reference.update', $r->id) }}"
                                               data-name="{{ $r->name }}"
                                               data-designation="{{ $r->designation }}"
                                               data-institute="{{ $r->institute }}"
                                               data-email="{{ $r->email }}"
                                               data-phone="{{ $r->phone }}"
                                               data-order_no="{{ $r->order_no }}"
                                               data-address="{{ $r->address }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('reference.delete', $r->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8"><em>No references added.</em></td></tr>
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
                    $selectableTypes = $attachmentTypes->reject(fn($t) => in_array($t->id, [13,16]));
                @endphp

                <div class="card">
                    {{-- Card header with title and "Add" button (opens modal) --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Upload all necessary documents</b></span>
                        @if($applicant->final_submit != 1)
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#quickUploadModal"><i class="fas fa-plus"></i> Add</button>
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
                        <form method="POST" action="{{ route('final.submit.application', $applicant->id) }}">
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

                {{-- spacer --}}
                <div class="my-5"></div>

            </div>
        </div>
    </div>

    {{-- =================== MODALS =================== --}}

    {{-- Basic Info Modal --}}
    {{-- Basic Info Modal --}}
    <div class="modal fade" id="basicInfoModal" tabindex="-1" role="dialog" aria-labelledby="basicInfoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content" id="basicInfoForm"
                  method="POST"
                  action="{{ $basicInfo ? route('basic_info.update', $basicInfo->id) : route('basic_info.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($basicInfo) @method('PUT') @endif

                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="basicInfoLabel">Basic Information</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    {{-- Names --}}
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>
                                Full Name in English
                                <span class="text-muted">(As per S.S.C Certificate)</span>
                            </label><span class="text-danger">*</span>
                            <input type="text" name="full_name" class="form-control"
                                   maxlength="255"
                                   value="{{ old('full_name', $basicInfo->full_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Applicant's Name (In Block Letter)</label><span class="text-danger">*</span>
                            <input type="text" name="full_name_block_letter" class="form-control text-uppercase"
                                   style="text-transform:uppercase"
                                   oninput="this.value=this.value.toUpperCase();"
                                   maxlength="255"
                                   value="{{ old('full_name_block_letter', $basicInfo->full_name_block_letter ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>
                                Full Name in Bengali
                                <span class="text-muted">(As per S.S.C Certificate)</span>
                            </label><span class="text-danger">*</span>
                            <input type="text" name="bn_name" class="form-control"
                                   maxlength="255"
                                   value="{{ old('bn_name', $basicInfo->bn_name ?? '') }}" required>
                        </div>
                    </div>

                    {{-- Parents & Income --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Father's Name</label><span class="text-danger">*</span>
                            <input type="text" name="f_name" class="form-control"
                                   value="{{ old('f_name', $basicInfo->f_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mother's Name</label><span class="text-danger">*</span>
                            <input type="text" name="m_name" class="form-control"
                                   value="{{ old('m_name', $basicInfo->m_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Guardian's Annual Income</label><span class="text-danger">*</span>
                            <input type="number" name="g_income" class="form-control"
                                   step="0.01" min="0"
                                   value="{{ old('g_income', $basicInfo->g_income ?? '') }}" required>
                        </div>
                    </div>

                    {{-- IDs, Nationality, DOB --}}
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>National ID</label><span class="text-danger">*</span>
                            <input type="text" name="nid" class="form-control"
                                   value="{{ old('nid', $basicInfo->nid ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nationality</label><span class="text-danger">*</span>
                            <input type="text" name="nationality" class="form-control"
                                   value="{{ old('nationality', $basicInfo->nationality ?? '') }}" required>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>
                                Date of Birth
                                <span class="text-muted">(As per S.S.C Certificate)</span>
                            </label><span class="text-danger">*</span>
                            <input type="date" name="dob" class="form-control"
                                   value="{{ old('dob', optional($basicInfo->dob ?? null)->format('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Religion</label><span class="text-danger">*</span>
                            @php $religionOld = old('religion', $basicInfo->religion ?? ''); @endphp
                            <select name="religion" class="form-control">
                                <option value="">--select--</option>
                                <option value="Islam"   {{ $religionOld==='Islam'   ? 'selected':'' }}>Islam</option>
                                <option value="Hindu"   {{ $religionOld==='Hindu'   ? 'selected':'' }}>Hindu</option>
                                <option value="Cristan" {{ $religionOld==='Cristan' ? 'selected':'' }}>Cristan</option>
                                <option value="Baudda"  {{ $religionOld==='Baudda'  ? 'selected':'' }}>Baudda</option>
                                <option value="others"  {{ $religionOld==='Others'  ? 'selected':'' }}>Others</option>
                            </select>
                        </div>
                    </div>


                    {{-- Religion, Gender, Marital --}}
                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>Gender</label><span class="text-danger">*</span>
                            @php $genderOld = old('gender', $basicInfo->gender ?? ''); @endphp
                            <select name="gender" class="form-control">
                                <option value="">--select--</option>
                                <option value="Male"   {{ $genderOld==='Male'?'selected':'' }}>Male</option>
                                <option value="Female" {{ $genderOld==='Female'?'selected':'' }}>Female</option>
                                <option value="Other"  {{ $genderOld==='Other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Marital Status</label><span class="text-danger">*</span>
                            @php $msOld = old('marital_status', $basicInfo->marital_status ?? ''); @endphp
                            <select name="marital_status" class="form-control" required>
                                <option value="">--select--</option>
                                <option value="Single"   {{ $msOld==='Single'?'selected':'' }}>Single</option>
                                <option value="Married"  {{ $msOld==='Married'?'selected':'' }}>Married</option>
                                <option value="Divorced" {{ $msOld==='Divorced'?'selected':'' }}>Divorced</option>
                                <option value="Widowed"  {{ $msOld==='Widowed'?'selected':'' }}>Widowed</option>
                            </select>
                        </div>
                    </div>

                    {{-- Field of Interest --}}
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Field of Interest</label><span class="text-danger">*</span>
                            <input type="text" name="field_of_interest" class="form-control"
                                   maxlength="255"
                                   value="{{ old('field_of_interest', $basicInfo->field_of_interest ?? '') }}" required>
                        </div>
                    </div>

                    {{-- Extra row only for CE (department_id = 1) --}}
                    @if($applicant->department_id == 1)
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Field Name (Civil Engineering Specific)</label><span class="text-danger">*</span>
                                <input type="text" name="field_name_ce" class="form-control"
                                       maxlength="255"
                                       value="{{ old('field_name_ce', $basicInfo->field_name_ce ?? '') }}" required>
                            </div>
                        </div>
                    @endif

                    {{-- Split Address (Present & Permanent) --}}
                    @php
                        $preText = $basicInfo->pre_address ?? '';
                        $perText = $basicInfo->per_address ?? '';
                        function addr_pick($text, $label) {
                          if (!$text) return '';
                          foreach (preg_split("/\r\n|\n|\r/", $text) as $line) {
                            [$k,$v] = array_pad(explode(':', $line, 2), 2, '');
                            if (strcasecmp(trim($k), $label) === 0) return trim($v);
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
                    <button type="submit" class="btn btn.success">Save</button>
                </div>
            </form>
        </div>
    </div>



    {{-- Education Modal --}}
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
                    <div class="form-group"><label>University/Institute/Board</label><span class="text-danger">*</span><input type="text" name="institute" class="form-control" required></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Year of Passing</label><span class="text-danger">*</span><input type="number" name="year_of_passing" class="form-control" min="1900" max="2100" required></div>
                        <div class="form-group col-md-6"><label>Discipline/Field</label><span class="text-danger">*</span><input type="text" name="field" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>CGPA</label><span class="text-danger">*</span><input type="number" step="0.01" name="cgpa" class="form-control" required></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="ei_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Thesis Modal --}}
    {{-- Thesis Modal --}}
    <div class="modal fade" id="thesisModal" tabindex="-1" role="dialog" aria-labelledby="thesisLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="thesisForm" class="modal-content" method="POST" action="{{ route('thesis.store') }}">
                @csrf
                <input type="hidden" id="th_method" name="_method" value="POST">
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="thesisLabel"><span id="th_modal_title">Add Thesis</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Title </label><span class="text-danger">*</span>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="supervisor">Supervisor Name with Designation <span class="text-danger">*</span></label>
                        <textarea name="supervisor" id="supervisor" class="form-control" rows="3" required>{{ old('supervisor') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Name of University/Institute</label><span class="text-danger">*</span>
                        <input type="text" name="institute" class="form-control"
                               value="{{ old('institute') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Period</label><span class="text-danger">*</span>
                        <input type="text" name="period" class="form-control"
                               placeholder="e.g. 2019–2021 or Jan 2020 - Dec 2021"
                               value="{{ old('period') }}" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="th_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Publication Modal --}}
    <div class="modal fade" id="publicationModal" tabindex="-1" role="dialog" aria-labelledby="publicationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="publicationForm" class="modal-content" method="POST" action="{{ route('publication.store') }}">
                @csrf
                <input type="hidden" id="pub_method" name="_method" value="POST">
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="publicationLabel"><span id="pub_modal_title">Add Publication</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Title </label><span class="text-danger">*</span>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Author's</label><span class="text-danger">*</span>
                        <input type="text" name="authors" class="form-control" value="{{ old('authors') }}" placeholder="e.g., A. Rahman, B. Akter" required>
                    </div>

                    <div class="form-group">

                            <label>Year of Publication</label><span class="text-danger">*</span>
                            <input type="number" name="year_of_publication" class="form-control" min="1900" max="2100" value="{{ old('year_of_publication') }}" required>

                    </div>
                    <div class="form-group">

                        <label>Publication Details
                        </label><span class="text-danger">*</span>
                        <textarea name="details" class="form-control" rows="2" placeholder="Journal/Conference/Patent/ Book Chapter/Book..." required>{{ old('details') }}</textarea>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="pub_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Job Modal --}}
    {{-- Job Modal --}}
    <div class="modal fade" id="jobModal" tabindex="-1" role="dialog" aria-labelledby="jobLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="jobForm" class="modal-content" method="POST" action="{{ route('job_experience.store') }}">
                @csrf
                <input type="hidden" id="job_method" name="_method" value="POST">
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="jobLabel"><span id="job_modal_title">Add Job Experience</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Organization</label><span class="text-danger">*</span>
                        <input type="text" name="organization" class="form-control" value="{{ old('organization') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Designation</label><span class="text-danger">*</span>
                        <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>From</label><span class="text-danger">*</span>
                            <input type="date" name="from" class="form-control" value="{{ old('from') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>To</label><span class="text-danger">*</span>
                            <input type="date" name="to" class="form-control" value="{{ old('to') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Job Description</label><span class="text-danger">*</span>
                        <textarea name="details" class="form-control" rows="3" required>{{ old('details') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="job_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reference Modal --}}
    <div class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="referenceForm" class="modal-content" method="POST" action="{{ route('reference.store') }}">
                @csrf
                <input type="hidden" id="ref_method" name="_method" value="POST">
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="referenceLabel"><span id="ref_modal_title">Add Reference</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name </label><span class="text-danger">*</span>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Designation and Affiliation</label><span class="text-danger">*</span>
                        <textarea name="designation" class="form-control" rows="2" required>{{ old('designation') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>University/Institute/Organization</label><span class="text-danger">*</span>
                        <input type="text" name="institute" class="form-control" value="{{ old('institute') }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email</label><span class="text-danger">*</span>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone</label><span class="text-danger">*</span>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Address</label><span class="text-danger">*</span>
                        <textarea name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                    </div>


                    <div class="form-group">
                        <label>Order No</label><span class="text-danger">*</span>
                        <input type="number" name="order_no" class="form-control" min="1" max="2" value="{{ old('order_no', 1) }}" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="ref_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{--Attachment modal--}}
    {{-- Attachment modal --}}
    {{-- Modal: Quick Upload --}}
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
                        <label>Choose File</label><span class="text-danger">*</span>
                        <input type="file" name="file" class="form-control-file" accept="image/*,.pdf" required>
                        {{--<small class="text-muted d-block mt-1">Single PDF is accepted.”</small>--}}
                    </div>


                    <div class="form-group">
                        <label class="mb-1 d-flex align-items-center">
                            <span class="mr-2 text-secondary">Rules for selected type</span>
                            <small class="text-muted"></small>
                        </label>

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

{{-- Hidden generic DELETE form --}}
<form id="genericDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>


{{--@php
    // Build an { id: rule } map from your $attachmentTypes collection
    $__typeRules = ($attachmentTypes ?? collect())->mapWithKeys(function($t){
        return [$t->id => $t->rule];
    });
@endphp--}}
@section('script')
    {{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>--}}

    {{-- SweetAlert handler for all deletes (assumes Swal loaded in layout) --}}
    <script>
        document.addEventListener('click', function (e) {
            const a = e.target.closest('.delete-link');
            if (!a) return;
            e.preventDefault();
            const url = a.getAttribute('href');

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
                    const f = document.getElementById('genericDeleteForm');
                    f.setAttribute('action', url);
                    f.submit();
                }
            });
        });
    </script>

    {{-- Education: edit (prefill) --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.ei-edit');
            if (!link) return;

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

        // Reset Education modal for Add (when header button clicked)
        $('#educationModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            if (trigger.matches('[data-target="#educationModal"]')) {
                const f = document.getElementById('educationForm');
                f.reset();
                f.setAttribute('action', @json(route('education_info.store')));
                document.getElementById('ei_method').value = 'POST';
                document.getElementById('ei_modal_title').textContent = 'Add Education Info';
                document.getElementById('ei_submit_btn').textContent  = 'Save';
            }
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


    {{-- Thesis: edit/add --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.th-edit');
            if (!link) return;

            const f = document.getElementById('thesisForm');
            f.setAttribute('action', link.dataset.updateUrl);
            document.getElementById('th_method').value = 'PUT';
            document.getElementById('th_modal_title').textContent = 'Update Thesis';
            document.getElementById('th_submit_btn').textContent  = 'Update';

            // Only these 3 inputs exist now:
            const setVal = (name, val) => {
                const inp = f.querySelector(`[name="${name}"]`);
                if (inp) inp.value = val || '';
            };

            setVal('title',     link.dataset.title);
            setVal('institute', link.dataset.institute);
            setVal('period',    link.dataset.period);

            $('#thesisModal').modal('show');
        });

        // When opening via the "Add" button, reset to POST/store
        $('#thesisModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;

            // Only reset when the trigger is the Add button (the one that has data-target="#thesisModal")
            if (trigger.matches('[data-target="#thesisModal"]')) {
                const f = document.getElementById('thesisForm');
                f.reset();
                f.setAttribute('action', @json(route('thesis.store')));
                document.getElementById('th_method').value = 'POST';
                document.getElementById('th_modal_title').textContent = 'Add Thesis';
                document.getElementById('th_submit_btn').textContent  = 'Save';
            }
        });
    </script>


    {{-- Publication: edit/add --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.pub-edit');
            if (!link) return;
            const f = document.getElementById('publicationForm');
            f.setAttribute('action', link.dataset.updateUrl);
            document.getElementById('pub_method').value = 'PUT';
            document.getElementById('pub_modal_title').textContent = 'Update Publication';
            document.getElementById('pub_submit_btn').textContent  = 'Update';

            f.querySelector('[name=title]').value                = link.dataset.title || '';
            f.querySelector('[name=authors]').value              = link.dataset.authors || '';
            f.querySelector('[name=year_of_publication]').value  = link.dataset.year || '';
            f.querySelector('[name=details]').value              = link.dataset.details || '';

            $('#publicationModal').modal('show');
        });

        $('#publicationModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            if (trigger.matches('[data-target="#publicationModal"]')) {
                const f = document.getElementById('publicationForm');
                f.reset();
                f.setAttribute('action', @json(route('publication.store')));
                document.getElementById('pub_method').value = 'POST';
                document.getElementById('pub_modal_title').textContent = 'Add Publication';
                document.getElementById('pub_submit_btn').textContent  = 'Save';
            }
        });
    </script>


    {{-- Job: edit/add --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.job-edit');
            if (!link) return;
            const f = document.getElementById('jobForm');
            f.setAttribute('action', link.dataset.updateUrl);
            document.getElementById('job_method').value = 'PUT';
            document.getElementById('job_modal_title').textContent = 'Update Job Experience';
            document.getElementById('job_submit_btn').textContent  = 'Update';

            f.querySelector('[name=organization]').value = link.dataset.organization || '';
            f.querySelector('[name=designation]').value  = link.dataset.designation || '';
            f.querySelector('[name=from]').value         = link.dataset.from || '';
            f.querySelector('[name=to]').value           = link.dataset.to || '';
            f.querySelector('[name=details]').value      = link.dataset.details || '';

            $('#jobModal').modal('show');
        });

        $('#jobModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            if (trigger.matches('[data-target="#jobModal"]')) {
                const f = document.getElementById('jobForm');
                f.reset();
                f.setAttribute('action', @json(route('job_experience.store')));
                document.getElementById('job_method').value = 'POST';
                document.getElementById('job_modal_title').textContent = 'Add Job Experience';
                document.getElementById('job_submit_btn').textContent  = 'Save';
            }
        });
    </script>


    {{-- Reference: edit/add --}}
    <script>
        document.addEventListener('click', function(e){
            const link = e.target.closest('.ref-edit');
            if (!link) return;
            const f = document.getElementById('referenceForm');
            f.setAttribute('action', link.dataset.updateUrl);
            document.getElementById('ref_method').value = 'PUT';
            document.getElementById('ref_modal_title').textContent = 'Update Reference';
            document.getElementById('ref_submit_btn').textContent  = 'Update';

            f.querySelector('[name=name]').value         = link.dataset.name || '';
            f.querySelector('[name=designation]').value  = link.dataset.designation || '';
            f.querySelector('[name=institute]').value    = link.dataset.institute || '';
            f.querySelector('[name=email]').value        = link.dataset.email || '';
            f.querySelector('[name=phone]').value        = link.dataset.phone || '';
            f.querySelector('[name=order_no]').value     = link.dataset.order_no || 1;
            f.querySelector('[name=address]').value      = link.dataset.address || '';

            $('#referenceModal').modal('show');
        });

        $('#referenceModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            if (trigger.matches('[data-target="#referenceModal"]')) {
                const f = document.getElementById('referenceForm');
                f.reset();
                f.setAttribute('action', @json(route('reference.store')));
                document.getElementById('ref_method').value = 'POST';
                document.getElementById('ref_modal_title').textContent = 'Add Reference';
                document.getElementById('ref_submit_btn').textContent  = 'Save';
                // Default order to 1 when adding new
                const orderInp = f.querySelector('[name=order_no]');
                if (orderInp && !orderInp.value) orderInp.value = 1;
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



    {{-- Compose Present/Perm addresses into hidden fields on submit --}}
    <script>
        (function(){
            function buildAddress(prefix){
                const get = (n) => (document.querySelector(`[name="${prefix}_${n}"]`)?.value || '').trim();
                const pairs = [
                    ['holding_no',    'Holding No'],
                    ['village_road',  'Village/Road'],
                    ['post_office',   'Post Office'],
                    ['upazila_thana', 'Upazila/Thana'],
                    ['district',      'District'],
                ];
                const parts = [];
                pairs.forEach(([k,label]) => {
                    const v = get(k);
                    if (v) parts.push(`${label}: ${v}`);
                });
                const hidden = document.querySelector(`[name="${prefix}_address"]`);
                if (hidden) hidden.value = parts.join('\n');
            }

            const basicForm = document.getElementById('basicInfoForm');
            if (basicForm) {
                basicForm.addEventListener('submit', function(){
                    buildAddress('pre');
                    buildAddress('per');
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