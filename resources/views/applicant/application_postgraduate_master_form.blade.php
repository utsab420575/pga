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
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#basicInfoModal">Add / Update</button>
                    </div>
                    <div class="card-body">
                        @if($basicInfo)
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                <tr><th width="25%">Full Name</th><td>{{ $basicInfo->full_name }}</td></tr>
                                <tr><th>Applicant Name (Block Letter)</th><td>{{ $basicInfo->full_name_block_letter }}</td></tr>
                                <tr><th>Name (Bangla)</th><td>{{ $basicInfo->bn_name }}</td></tr>

                                <tr><th>Father's Name</th><td>{{ $basicInfo->f_name }}</td></tr>
                                <tr><th>Mother's Name</th><td>{{ $basicInfo->m_name }}</td></tr>
                                <tr>
                                    <th>Guardian's Income</th>
                                    <td>
                                        @if(!is_null($basicInfo->g_income))
                                            {{ number_format((float)$basicInfo->g_income, 2) }}
                                        @endif
                                    </td>
                                </tr>

                                <tr><th>National ID</th><td>{{ $basicInfo->nid }}</td></tr>
                                <tr><th>Nationality</th><td>{{ $basicInfo->nationality }}</td></tr>
                                <tr><th>DOB</th><td>{{ optional($basicInfo->dob)->format('Y-m-d') }}</td></tr>
                                <tr><th>Religion</th><td>{{ $basicInfo->religion }}</td></tr>
                                <tr><th>Gender</th><td>{{ $basicInfo->gender }}</td></tr>
                                <tr><th>Marital Status</th><td>{{ $basicInfo->marital_status }}</td></tr>

                                <tr><th>Field of Interest</th><td>{{ $basicInfo->field_of_interest }}</td></tr>

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


                {{-- CARD 4: Thesis (multiple like Education) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><b>Thesis</b></span>
                        <button id="btnThAdd"
                                class="btn btn-primary btn-sm"
                                data-toggle="modal"
                                data-target="#thesisModal"
                                data-store-url="{{ route('thesis.store') }}">
                            Add
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Institute</th>
                                    <th>Period</th>
                                    <th class="w-110">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($theses as $t)
                                    <tr>
                                        <td>{{ $t->title }}</td>
                                        <td>{{ $t->institute }}</td>
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
                                    <tr><td colspan="4"><em>No thesis added.</em></td></tr>
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
                                    <th>Authors</th>
                                    <th>Year</th>
                                    <th>Details</th>
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
                                    <th>Institute</th>
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
                <div class="card">
                    <div class="card-header">Required Documents (which must be attached here with):</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($attachmentTypes as $type)
                                @continue(in_array($type->id, [5, 7, 8, 9]))

                                @php $uploaded = $attachments->where('attachment_type_id', $type->id); @endphp
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
                                                                    1. Attested SSC Mark-sheet <br>
                                                                    2. Attested Diploma Mark-sheet <br>
                                                                    3. Attested BSc Mark-sheet
                                                                @elseif($type->id == 3)
                                                                     1. Attested SSC Transcript/Grade-sheet <br>
                                                                    2. Attested Diploma Transcript/Grade-sheet <br>
                                                                    3. Attested BSc Transcript/Grade-sheet
                                                                @elseif($type->id == 4)
                                                                    1.Attested Testimonial
                                                                @elseif($type->id == 6)
                                                                    Recent photo  (max 500KB)
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
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   maxlength="255"
                                   value="{{ old('full_name', $basicInfo->full_name ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Applicant's Name (In Block Letter)</label>
                            <input type="text" name="full_name_block_letter" class="form-control text-uppercase"
                                   style="text-transform:uppercase"
                                   oninput="this.value=this.value.toUpperCase();"
                                   maxlength="255"
                                   value="{{ old('full_name_block_letter', $basicInfo->full_name_block_letter ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>Name (Bangla)</label>
                            <input type="text" name="bn_name" class="form-control"
                                   maxlength="255"
                                   value="{{ old('bn_name', $basicInfo->bn_name ?? '') }}">
                        </div>
                    </div>

                    {{-- Parents & Income --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Father's Name</label>
                            <input type="text" name="f_name" class="form-control"
                                   value="{{ old('f_name', $basicInfo->f_name ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mother's Name</label>
                            <input type="text" name="m_name" class="form-control"
                                   value="{{ old('m_name', $basicInfo->m_name ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Guardian's Income</label>
                            <input type="number" name="g_income" class="form-control"
                                   step="0.01" min="0"
                                   value="{{ old('g_income', $basicInfo->g_income ?? '') }}">
                        </div>
                    </div>

                    {{-- IDs, Nationality, DOB --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>National ID</label>
                            <input type="text" name="nid" class="form-control"
                                   value="{{ old('nid', $basicInfo->nid ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control"
                                   value="{{ old('nationality', $basicInfo->nationality ?? '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>DOB</label>
                            <input type="date" name="dob" class="form-control"
                                   value="{{ old('dob', optional($basicInfo->dob ?? null)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    {{-- Religion, Gender, Marital --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Religion</label>
                            @php $religionOld = strtolower(old('religion', $basicInfo->religion ?? '')); @endphp
                            <select name="religion" class="form-control">
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
                            <select name="gender" class="form-control">
                                <option value="">--select--</option>
                                <option value="Male"   {{ $genderOld==='Male'?'selected':'' }}>Male</option>
                                <option value="Female" {{ $genderOld==='Female'?'selected':'' }}>Female</option>
                                <option value="Other"  {{ $genderOld==='Other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Marital Status</label>
                            @php $msOld = old('marital_status', $basicInfo->marital_status ?? ''); @endphp
                            <select name="marital_status" class="form-control">
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
                            <label>Field of Interest</label>
                            <input type="text" name="field_of_interest" class="form-control"
                                   maxlength="255"
                                   value="{{ old('field_of_interest', $basicInfo->field_of_interest ?? '') }}">
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
                            <input type="hidden" name="pre_address">
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
                            <input type="hidden" name="per_address">
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
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Institute</label>
                        <input type="text" name="institute" class="form-control"
                               value="{{ old('institute') }}">
                    </div>

                    <div class="form-group">
                        <label>Period</label>
                        <input type="text" name="period" class="form-control"
                               placeholder="e.g. 2019–2021 or Jan 2020 - Dec 2021"
                               value="{{ old('period') }}">
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
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Authors</label>
                        <input type="text" name="authors" class="form-control" value="{{ old('authors') }}" placeholder="e.g., A. Rahman, B. Akter">
                    </div>

                    <div class="form-group">

                            <label>Year</label>
                            <input type="number" name="year_of_publication" class="form-control" min="1900" max="2100" value="{{ old('year_of_publication') }}">

                    </div>
                    <div class="form-group">

                        <label>Details</label>
                        <textarea name="details" class="form-control" rows="2" placeholder="Journal/Conference, DOI, volume/issue, pages...">{{ old('details') }}</textarea>

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
                        <label>Organization</label>
                        <input type="text" name="organization" class="form-control" value="{{ old('organization') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" name="designation" class="form-control" value="{{ old('designation') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>From</label>
                            <input type="date" name="from" class="form-control" value="{{ old('from') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>To</label>
                            <input type="date" name="to" class="form-control" value="{{ old('to') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="details" class="form-control" rows="3">{{ old('details') }}</textarea>
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
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Designation and Affiliation</label>
                        <textarea name="designation" class="form-control" rows="2">{{ old('designation') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Institute</label>
                        <input type="text" name="institute" class="form-control" value="{{ old('institute') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>


                    <div class="form-group">
                        <label>Order No</label>
                        <input type="number" name="order_no" class="form-control" min="1" max="2" value="{{ old('order_no', 1) }}">
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
