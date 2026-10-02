@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1 class="h3 mb-0"><i class="fas fa-clipboard-check text-success mr-2"></i>Required Attachments</h1>
        <form method="GET" action="{{ route('admin.attachment_requirements.index') }}" class="form-inline">
            <label class="mr-2 small text-muted" for="applicationtype_id">Application type</label>
            <select id="applicationtype_id" name="applicationtype_id" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($applicationtypes as $at)
                    <option value="{{ $at->id }}" @selected($current?->id === $at->id)>{{ $at->type }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @include('admin.settings._flash')

    <div class="alert alert-info small">
        <b>Not offered:</b> hidden from this application type's upload dropdown.
        <b>Optional:</b> shown, but Final Submit does not check it.
        <b>Required – all degrees:</b> every applicant of this type must upload it.
        <b>Selected degrees:</b> only applicants of the ticked degrees must upload it (optional for the rest).
    </div>

    @if($current)
    <form method="POST" action="{{ route('admin.attachment_requirements.update', $current->id) }}">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40%;">Attachment Type</th>
                            <th>Requirement for <span class="text-warning">{{ $current->type }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attachmentTypes as $t)
                            @php
                                $saved   = $rules[$t->id] ?? [];
                                $mode    = in_array(null, $saved, true) ? 'all'
                                         : (count($saved) ? 'selected' : (in_array($t->id, $offered) ? 'optional' : 'none'));
                                $mode    = old("req.{$t->id}.mode", $mode);
                                $checked = array_map('intval', old("req.{$t->id}.degrees", array_filter($saved)));
                            @endphp
                            <tr class="req-row {{ $mode === 'none' ? 'text-muted' : '' }}">
                                <td>
                                    {{ trim($t->title) }}
                                    @unless($t->status)
                                        <span class="badge badge-secondary ml-1">Inactive</span>
                                    @endunless
                                </td>
                                <td>
                                    @foreach(['none' => 'Not offered', 'optional' => 'Optional', 'all' => 'Required – all degrees', 'selected' => 'Selected degrees'] as $value => $label)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input req-mode"
                                                   id="req_{{ $t->id }}_{{ $value }}" name="req[{{ $t->id }}][mode]"
                                                   value="{{ $value }}" @checked($mode === $value)>
                                            <label class="custom-control-label" for="req_{{ $t->id }}_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach

                                    <div class="req-degrees mt-2 p-2 border rounded bg-light" style="{{ $mode === 'selected' ? '' : 'display:none;' }}">
                                        @foreach($degrees as $d)
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input type="checkbox" class="custom-control-input"
                                                       id="req_{{ $t->id }}_deg_{{ $d->id }}" name="req[{{ $t->id }}][degrees][]"
                                                       value="{{ $d->id }}" @checked(in_array($d->id, $checked))>
                                                <label class="custom-control-label small" for="req_{{ $t->id }}_deg_{{ $d->id }}">{{ $d->degree_name }}</label>
                                            </div>
                                        @endforeach
                                        @error("req.{$t->id}.degrees")<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Save</button>
            </div>
        </div>
    </form>
    @endif
</div>

<script>
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('req-mode')) return;
    const row = e.target.closest('.req-row');
    row.querySelector('.req-degrees').style.display = e.target.value === 'selected' ? '' : 'none';
    row.classList.toggle('text-muted', e.target.value === 'none');
});
</script>
@endsection
