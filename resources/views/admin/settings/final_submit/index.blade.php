@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-undo-alt text-warning mr-2"></i>Undo / Make Final Submit
            </h1>
            <small class="text-muted">Manage applicant final submission state, unlock for editing, or submit on applicant's behalf.</small>
        </div>
    </div>

    @include('admin.settings._flash')

    {{-- Filter & Search Card --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.final_submit.index') }}" class="form-row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="sr-only" for="searchInput">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search by Applicant ID, Roll, Name, Email or Phone">
                    </div>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="status" class="form-control form-control-sm">
                        <option value="all" {{ ($status === 'all' || $status === null || $status === '') ? 'selected' : '' }}>All Final Submit Statuses</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Final Submitted (Locked)</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Not Submitted (Draft / Editable)</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="type" class="form-control form-control-sm">
                        <option value="all" {{ ($type === 'all' || $type === null || $type === '') ? 'selected' : '' }}>All Application Types</option>
                        <option value="1" {{ $type === '1' ? 'selected' : '' }}>Admission (Type 1)</option>
                        <option value="2" {{ $type === '2' ? 'selected' : '' }}>Eligibility Verification (Type 2)</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                    @if($search !== '' || ($status !== null && $status !== '' && $status !== 'all') || ($type !== null && $type !== '' && $type !== 'all'))
                        <a href="{{ route('admin.final_submit.index') }}" class="btn btn-secondary btn-sm" title="Clear Filters">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 80px;">App ID</th>
                            <th>Roll</th>
                            <th>Applicant</th>
                            <th>Program / Dept</th>
                            <th>Type</th>
                            <th>Data Fillup</th>
                            <th>Final Submit</th>
                            <th class="text-right" style="min-width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td>
                                    <span class="badge badge-dark">#{{ $item->id }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item->roll ?? '—' }}</strong>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $item->user->name ?? '—' }}</div>
                                    <small class="text-muted d-block"><i class="fas fa-envelope mr-1"></i>{{ $item->user->email ?? '—' }}</small>
                                    @if(!empty($item->user->phone))
                                        <small class="text-muted d-block"><i class="fas fa-phone mr-1"></i>{{ $item->user->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $item->department->short_name ?? $item->department->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $item->degree->degree_name ?? '' }}</small>
                                </td>
                                <td>
                                    @if((int)$item->applicationtype_id === 2)
                                        <span class="badge badge-info">Eligibility</span>
                                    @else
                                        <span class="badge badge-primary">Admission</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_fillup_complete)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle mr-1"></i>Complete
                                        </span>
                                    @else
                                        <span class="badge badge-warning text-dark" data-toggle="tooltip" data-html="true"
                                              title="{{ implode('<br>• ', array_merge(['Missing items:'], $item->fillup_errors)) }}">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Incomplete ({{ count($item->fillup_errors) }})
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if((int)$item->final_submit === 1)
                                        <span class="badge badge-success">
                                            <i class="fas fa-lock mr-1"></i>Submitted
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-lock-open mr-1"></i>Not Submitted
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{-- Preview Link --}}
                                    @php
                                        $previewUrl = (int)$item->applicationtype_id === 2
                                            ? route('applicant.preview.eligibility.form', $item->id)
                                            : route('applicant.preview.admission.form', $item->id);
                                    @endphp
                                    <a href="{{ $previewUrl }}" target="_blank" class="btn btn-outline-info btn-sm mr-1" title="Preview Application Form">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Action Buttons --}}
                                    @if((int)$item->final_submit === 1)
                                        {{-- Undo Final Submit --}}
                                        <form method="POST" action="{{ route('admin.final_submit.undo', $item->id) }}" class="d-inline form-action-undo"
                                              data-id="{{ $item->id }}"
                                              data-name="{{ $item->user->name ?? 'Applicant' }}"
                                              data-roll="{{ $item->roll ?? '—' }}">
                                            @csrf
                                            <button type="button" class="btn btn-warning btn-sm btn-undo-submit" title="Unlock and undo final submission">
                                                <i class="fas fa-undo mr-1"></i>Undo Submit
                                            </button>
                                        </form>
                                    @else
                                        {{-- Make Final Submit --}}
                                        <form method="POST" action="{{ route('admin.final_submit.submit', $item->id) }}" class="d-inline form-action-submit"
                                              data-id="{{ $item->id }}"
                                              data-name="{{ $item->user->name ?? 'Applicant' }}"
                                              data-roll="{{ $item->roll ?? '—' }}"
                                              data-complete="{{ $item->is_fillup_complete ? '1' : '0' }}"
                                              data-errors="{{ json_encode($item->fillup_errors) }}">
                                            @csrf
                                            <button type="button" class="btn btn-success btn-sm btn-make-submit" title="Mark as Final Submitted">
                                                <i class="fas fa-check-circle mr-1"></i>Make Final Submit
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                    No applications found matching the criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} applications
                </span>
                <div>{{ $items->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        // SweetAlert2 Confirmation for Undo Final Submit
        $(document).on('click', '.btn-undo-submit', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const id   = form.data('id');
            const name = form.data('name');
            const roll = form.data('roll');

            Swal.fire({
                title: 'Undo Final Submit?',
                html: `Are you sure you want to <strong>undo final submit</strong> for <strong>${name}</strong> (Roll: ${roll}, ID #${id})?<br><br><span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>This will UNLOCK the application and allow the applicant to edit and re-submit.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-undo mr-1"></i> Yes, Undo Submit',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // SweetAlert2 Confirmation for Make Final Submit
        $(document).on('click', '.btn-make-submit', function (e) {
            e.preventDefault();
            const form       = $(this).closest('form');
            const id         = form.data('id');
            const name       = form.data('name');
            const roll       = form.data('roll');
            const isComplete = form.data('complete') == 1;
            const errors     = form.data('errors') || [];

            let extraHtml = '';
            if (!isComplete && errors.length > 0) {
                let errorList = errors.map(err => `<li>${err}</li>`).join('');
                extraHtml = `<div class="alert alert-warning text-left mt-3 mb-0 small">
                    <strong><i class="fas fa-exclamation-triangle mr-1"></i>Warning: Incomplete Data Detected</strong>
                    <ul class="mb-0 pl-3 mt-1">${errorList}</ul>
                    <div class="mt-1 text-muted">Rules will be validated on submission.</div>
                </div>`;
            }

            Swal.fire({
                title: 'Make Final Submit?',
                html: `Mark application as <strong>Final Submitted</strong> for <strong>${name}</strong> (Roll: ${roll}, ID #${id})?${extraHtml}`,
                icon: isComplete ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Yes, Final Submit',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
