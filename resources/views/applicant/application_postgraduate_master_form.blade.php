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
                    <h4 class="mb-0">Post-Graduate Application</h4>
                    <small class="text-muted">
                        Applicant: <b>{{ $applicant->user->name }}</b> &nbsp;|&nbsp; Roll: <b>{{ $applicant->roll }}</b>
                    </small>
                    <input type="hidden" id="applicant_id" value="{{ $applicant->id }}">
                </div>

                {{-- CARD 1: Basic Info --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Basic Information</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#basicInfoModal">Add / Update</button>
                    </div>
                    <div class="card-body">
                        @if($basicInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                <tr><th width="25%">Applicant Name (Block Letter)</th><td>{{ $basicInfo->full_name_block_letter }}</td></tr>
                                <tr><th>Father's Name</th><td>{{ $basicInfo->f_name }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $basicInfo->m_name }}</td></tr>
                                <tr><th>National ID</th><td>{{ $basicInfo->nid }}</td></tr>
                                <tr><th>Nationality</th><td>{{ $basicInfo->nationality }}</td></tr>
                                <tr><th>DOB</th><td>{{ optional($basicInfo->dob)->format('Y-m-d') }}</td></tr>
                                <tr><th>Religion</th><td>{{ $basicInfo->religion }}</td></tr>
                                <tr><th>Gender</th><td>{{ $basicInfo->gender }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ $basicInfo->marital_status }}</td></tr>
                                <tr><th>Passport No</th><td>{{ $basicInfo->passport_no }}</td></tr>
                                <tr><th>Present Address</th><td><pre class="mb-0" style="white-space:pre-wrap">{{ $basicInfo->pre_address }}</pre></td></tr>
                                <tr><th>Permanent Address</th><td><pre class="mb-0" style="white-space:pre-wrap">{{ $basicInfo->per_address }}</pre></td></tr>
                                </tbody>
                            </table>
                        @else
                            <em>No basic info yet.</em>
                        @endif
                    </div>
                </div>

                {{-- CARD 2: Eligibility Degree (single row) --}}
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
                                        <td>{{ $eligibilityDegree->duration }}</td>
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

                {{-- CARD 3: Education Info (multiple) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Education Info</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#educationModal">Add</button>
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

                {{-- Normalize $theses to a collection (supports hasOne or hasMany) --}}
                @php
                    $thesesCol = isset($theses) ? $theses : (isset($thesis) && $thesis ? collect([$thesis]) : collect());
                @endphp

                {{-- CARD 4: Thesis (multiple like Education) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Thesis</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#thesisModal">Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Institute</th>
                                    <th>Year</th>
                                    <th>Area</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($thesesCol as $t)
                                    <tr>
                                        <td>{{ $t->title }}</td>
                                        <td>{{ $t->institute }}</td>
                                        <td>{{ $t->year }}</td>
                                        <td>{{ $t->area }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm th-edit"
                                               data-update-url="{{ route('thesis.update', $t->id) }}"
                                               data-title="{{ $t->title }}"
                                               data-institute="{{ $t->institute }}"
                                               data-year="{{ $t->year }}"
                                               data-area="{{ $t->area }}"
                                               data-url="{{ $t->url }}"
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
                        <span><b>Publications</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#publicationModal">Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Journal/Conference</th>
                                    <th>Year</th>
                                    <th>Volume/Issue</th>
                                    <th>Pages</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($publications ?? collect() as $p)
                                    <tr>
                                        <td>{{ $p->title }}</td>
                                        <td>{{ $p->venue }}</td>
                                        <td>{{ $p->year }}</td>
                                        <td>{{ $p->volume }}</td>
                                        <td>{{ $p->pages }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm pub-edit"
                                               data-update-url="{{ route('publication.update', $p->id) }}"
                                               data-title="{{ $p->title }}"
                                               data-venue="{{ $p->venue }}"
                                               data-year="{{ $p->year }}"
                                               data-volume="{{ $p->volume }}"
                                               data-pages="{{ $p->pages }}"
                                               data-url="{{ $p->url }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('publication.delete', $p->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><em>No publications added.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- CARD 6: Job Experience --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Job Experience</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#jobModal">Add</button>
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
                                        <td>{{ optional($j->start_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($j->end_date)->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm job-edit"
                                               data-update-url="{{ route('job_experience.update', $j->id) }}"
                                               data-organization="{{ $j->organization }}"
                                               data-designation="{{ $j->designation }}"
                                               data-start_date="{{ optional($j->start_date)->format('Y-m-d') }}"
                                               data-end_date="{{ optional($j->end_date)->format('Y-m-d') }}"
                                               data-responsibilities="{{ $j->responsibilities }}"
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
                        <span><b>References</b></span>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#referenceModal">Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Organization</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($references ?? collect() as $r)
                                    <tr>
                                        <td>{{ $r->name }}</td>
                                        <td>{{ $r->designation }}</td>
                                        <td>{{ $r->organization }}</td>
                                        <td>{{ $r->email }}</td>
                                        <td>{{ $r->phone }}</td>
                                        <td>
                                            <a href="#"
                                               class="btn btn-outline-primary btn-sm ref-edit"
                                               data-update-url="{{ route('reference.update', $r->id) }}"
                                               data-name="{{ $r->name }}"
                                               data-designation="{{ $r->designation }}"
                                               data-organization="{{ $r->organization }}"
                                               data-email="{{ $r->email }}"
                                               data-phone="{{ $r->phone }}"
                                               title="Edit"><i class="fas fa-edit"></i></a>

                                            <a href="{{ route('reference.delete', $r->id) }}"
                                               class="btn btn-outline-danger btn-sm delete-link"
                                               title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><em>No references added.</em></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- CARD 8: Attachments (by type, with previews) --}}
                <div class="card">
                    <div class="card-header">Required Documents (which must be attached here with):</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($attachmentTypes as $type)
                                @php $uploaded = $attachments->where('attachment_type_id', $type->id); @endphp
                                <div class="col-md-12">
                                    <div class="card mb-3" id="doc-{{ $type->id }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span>{{ $type->title }}</span>
                                            @if($type->required)<span class="badge badge-danger">required</span>@endif
                                        </div>
                                        <div class="card-body">
                                            <form action="{{ route('attachments.upload') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="attachment_type_id" value="{{ $type->id }}">
                                                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">
                                                <div class="d-flex align-items-center">
                                                    <input type="file" name="files[]" class="form-control-file" accept="image/*,.pdf" multiple required>
                                                    <button type="submit" class="btn btn-primary btn-sm ml-2">Upload</button>
                                                </div>
                                            </form>

                                            @if($uploaded->count())
                                                <div class="mt-3">
                                                    @foreach($uploaded as $file)
                                                        <div class="d-inline-block text-center mr-3 mb-2">
                                                            @if(Str::endsWith(strtolower($file->file), ['.jpg','.jpeg','.png','.webp']))
                                                                <img src="{{ asset($file->file) }}" width="100"
                                                                     style="border:1px solid #ccc;border-radius:5px;margin-bottom:10px;display:block;">
                                                            @else
                                                                @php
                                                                    $filename  = basename($file->file);
                                                                    $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                                                                    $parts     = explode('_', $nameNoExt);
                                                                    $origSlug  = count($parts) >= 6 ? implode('_', array_slice($parts, 5)) : $nameNoExt;
                                                                    $displayName = str_replace('_', ' ', $origSlug);
                                                                @endphp
                                                                <a href="{{ asset($file->file) }}" target="_blank" class="btn btn-outline-info btn-sm"
                                                                   style="margin-bottom:10px;display:inline-block;">View {{ $displayName }}</a>
                                                            @endif

                                                            <div>
                                                                <a href="{{ asset($file->file) }}" download class="btn btn-success btn-sm mr-1">Download</a>
                                                                <a href="{{ route('attachments.delete', $file->id) }}"
                                                                   class="btn btn-danger btn-sm delete-link">Delete</a>
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

                {{-- spacer --}}
                <div class="my-5"></div>

            </div>
        </div>
    </div>

    {{-- =================== MODALS =================== --}}

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
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Applicant's Name (In Block Letter)</label>
                            <input type="text" name="full_name_block_letter" class="form-control text-uppercase"
                                   style="text-transform:uppercase"
                                   oninput="this.value=this.value.toUpperCase();"
                                   maxlength="255"
                                   value="{{ old('full_name_block_letter', $basicInfo->full_name_block_letter ?? '') }}"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Father's Name</label>
                            <input type="text" name="f_name" class="form-control" value="{{ old('f_name', $basicInfo->f_name ?? '') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mother's Name</label>
                            <input type="text" name="m_name" class="form-control" value="{{ old('m_name', $basicInfo->m_name ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>National ID</label>
                            <input type="text" name="nid" class="form-control" value="{{ old('nid', $basicInfo->nid ?? '') }}">
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
                                <option value="islam"   {{ $religionOld==='islam'   ? 'selected':'' }}>Islam</option>
                                <option value="hindu"   {{ $religionOld==='hindu'   ? 'selected':'' }}>Hindu</option>
                                <option value="cristan" {{ $religionOld==='cristan' ? 'selected':'' }}>Cristan</option>
                                <option value="baudda"  {{ $religionOld==='baudda'  ? 'selected':'' }}>Baudda</option>
                                <option value="others"  {{ $religionOld==='others'  ? 'selected':'' }}>Others</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Gender</label>
                            @php $genderOld = old('gender', $basicInfo->gender ?? ''); @endphp
                            <select name="gender" class="form-control" required>
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
                            <select name="marital_status" class="form-control" required>
                                <option value="">--select--</option>
                                <option value="Single"   {{ $msOld==='Single'?'selected':'' }}>Single</option>
                                <option value="Married"  {{ $msOld==='Married'?'selected':'' }}>Married</option>
                                <option value="Divorced" {{ $msOld==='Divorced'?'selected':'' }}>Divorced</option>
                                <option value="Widowed"  {{ $msOld==='Widowed'?'selected':'' }}>Widowed</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Passport No</label>
                            <input type="number" name="passport_no" class="form-control" value="{{ old('passport_no', $basicInfo->passport_no ?? '') }}">
                        </div>
                    </div>

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
                        <div class="col-md-6">
                            <label class="mb-2"><b>Present Address</b></label>
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
                                       value="{{ old('pre_post_office', addr_pick($preText, 'Post Office')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="pre_upazila_thana" class="form-control"
                                       value="{{ old('pre_upazila_thana', addr_pick($preText, 'Upazila/Thana')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="pre_district" class="form-control"
                                       value="{{ old('pre_district', addr_pick($preText, 'District')) }}">
                            </div>
                            <input type="hidden" name="pre_address" required>
                        </div>

                        <div class="col-md-6">
                            <label class="mb-2"><b>Permanent Address</b></label>
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
                                       value="{{ old('per_post_office', addr_pick($perText, 'Post Office')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>Upazila / Thana</small>
                                <input type="text" name="per_upazila_thana" class="form-control"
                                       value="{{ old('per_upazila_thana', addr_pick($perText, 'Upazila/Thana')) }}">
                            </div>
                            <div class="form-group mb-2">
                                <small>District</small>
                                <input type="text" name="per_district" class="form-control"
                                       value="{{ old('per_district', addr_pick($perText, 'District')) }}">
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
            <form class="modal-content" method="POST"
                  action="{{ $eligibilityDegree ? route('eligibility_degree.update', $eligibilityDegree->id) : route('eligibility_degree.store') }}">
                @csrf
                @if($eligibilityDegree) @method('PUT') @endif
                <input type="hidden" name="applicant_id" value="{{ $applicant->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="eligibilityLabel">{{ $eligibilityDegree ? 'Update Eligibility Degree' : 'Add Eligibility Degree' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group"><label>Degree</label>
                        <input type="text" name="degree" class="form-control" required
                               value="{{ old('degree', $eligibilityDegree->degree ?? '') }}"></div>
                    <div class="form-group"><label>Institute/University</label>
                        <input type="text" name="institute" class="form-control" required
                               value="{{ old('institute', $eligibilityDegree->institute ?? '') }}"></div>

                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Country</label>
                            <input type="text" name="country" class="form-control" required
                                   value="{{ old('country', $eligibilityDegree->country ?? '') }}"></div>
                        <div class="form-group col-md-4"><label>CGPA/GPA/Class</label>
                            <input type="number" step="0.01" name="cgpa" class="form-control" required
                                   value="{{ old('cgpa', $eligibilityDegree->cgpa ?? '') }}"></div>
                        <div class="form-group col-md-4"><label>Date of Graduation</label>
                            <input type="date" name="date_graduation" class="form-control" required
                                   value="{{ old('date_graduation', optional($eligibilityDegree->date_graduation ?? null)->format('Y-m-d')) }}"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Duration in Year</label>
                            <input type="number" name="duration" class="form-control" placeholder="e.g., 4" required
                                   value="{{ old('duration', $eligibilityDegree->duration ?? '') }}"></div>
                        <div class="form-group col-md-4"><label>Total Credit</label>
                            <input type="number" step="0.01" name="total_credit" class="form-control" required
                                   value="{{ old('total_credit', $eligibilityDegree->total_credit ?? '') }}"></div>
                        <div class="form-group col-md-4"><label>Mode</label>
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
                        <div class="form-group col-md-4"><label>Period</label>
                            <input type="text" name="period" class="form-control" placeholder="2018-2022" required
                                   value="{{ old('period', $eligibilityDegree->period ?? '') }}"></div>
                        <div class="form-group col-md-4"><label>University Status</label>
                            @php $uniOld = old('uni_status', $eligibilityDegree->uni_status ?? ''); @endphp
                            <select name="uni_status" class="form-control" required>
                                <option value="">--select--</option>
                                <option {{ $uniOld==='Public' ? 'selected':'' }}>Public</option>
                                <option {{ $uniOld==='Private' ? 'selected':'' }}>Private</option>
                                <option {{ $uniOld==='International' ? 'selected':'' }}>International</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4"><label>University Web Link</label>
                            <input type="url" name="url" class="form-control" placeholder="https://..." required
                                   value="{{ old('url', $eligibilityDegree->url ?? '') }}"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">{{ $eligibilityDegree ? 'Update' : 'Save' }}</button>
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
                    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group"><label>Institute</label><input type="text" name="institute" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Year</label><input type="number" name="year" class="form-control" min="1900" max="2100"></div>
                        <div class="form-group col-md-6"><label>Area/Field</label><input type="text" name="area" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>URL</label><input type="url" name="url" class="form-control" placeholder="https://..."></div>
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
                    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-group"><label>Journal/Conference</label><input type="text" name="venue" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Year</label><input type="number" name="year" class="form-control" min="1900" max="2100"></div>
                        <div class="form-group col-md-4"><label>Volume/Issue</label><input type="text" name="volume" class="form-control"></div>
                        <div class="form-group col-md-4"><label>Pages</label><input type="text" name="pages" class="form-control" placeholder="e.g., 12–25"></div>
                    </div>
                    <div class="form-group"><label>URL</label><input type="url" name="url" class="form-control" placeholder="https://..."></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="pub_submit_btn" type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

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
                    <div class="form-group"><label>Organization</label><input type="text" name="organization" class="form-control" required></div>
                    <div class="form-group"><label>Designation</label><input type="text" name="designation" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Start Date</label><input type="date" name="start_date" class="form-control"></div>
                        <div class="form-group col-md-6"><label>End Date</label><input type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>Responsibilities</label><textarea name="responsibilities" class="form-control" rows="3"></textarea></div>
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
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Designation</label><input type="text" name="designation" class="form-control"></div>
                    <div class="form-group"><label>Organization</label><input type="text" name="organization" class="form-control"></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="form-group col-md-6"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="ref_submit_btn" type="submit" class="btn btn-success">Save</button>
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

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>

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

            f.querySelector('[name=title]').value     = link.dataset.title || '';
            f.querySelector('[name=institute]').value = link.dataset.institute || '';
            f.querySelector('[name=year]').value      = link.dataset.year || '';
            f.querySelector('[name=area]').value      = link.dataset.area || '';
            f.querySelector('[name=url]').value       = link.dataset.url || '';

            $('#thesisModal').modal('show');
        });

        $('#thesisModal').on('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
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

            f.querySelector('[name=title]').value  = link.dataset.title || '';
            f.querySelector('[name=venue]').value  = link.dataset.venue || '';
            f.querySelector('[name=year]').value   = link.dataset.year || '';
            f.querySelector('[name=volume]').value = link.dataset.volume || '';
            f.querySelector('[name=pages]').value  = link.dataset.pages || '';
            f.querySelector('[name=url]').value    = link.dataset.url || '';

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

            f.querySelector('[name=organization]').value     = link.dataset.organization || '';
            f.querySelector('[name=designation]').value      = link.dataset.designation || '';
            f.querySelector('[name=start_date]').value       = link.dataset.start_date || '';
            f.querySelector('[name=end_date]').value         = link.dataset.end_date || '';
            f.querySelector('[name=responsibilities]').value = link.dataset.responsibilities || '';

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
            f.querySelector('[name=organization]').value = link.dataset.organization || '';
            f.querySelector('[name=email]').value        = link.dataset.email || '';
            f.querySelector('[name=phone]').value        = link.dataset.phone || '';

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
            }
        });
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
@endsection
