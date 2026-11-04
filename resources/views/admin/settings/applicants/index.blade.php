@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1 class="h3 mb-0"><i class="fas fa-user-edit text-primary mr-2"></i>Edit Applicant Information</h1>
        <form method="GET" action="{{ route('admin.applicants.index') }}" class="form-inline">
            <select name="type" class="form-control form-control-sm mr-1">
                <option value="">All application types</option>
                <option value="2" @selected($type === '2')>Eligibility</option>
                <option value="1" @selected($type === '1')>Admission</option>
            </select>
            <select name="payment" class="form-control form-control-sm mr-1">
                <option value="">All payments</option>
                <option value="1" @selected($payment === '1')>Paid</option>
                <option value="0" @selected($payment === '0')>Unpaid</option>
            </select>
            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm mr-1"
                   placeholder="ID, roll, name, email or phone">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            @if($search !== '' || $type || ($payment !== null && $payment !== ''))
                <a href="{{ route('admin.applicants.index') }}" class="btn btn-secondary btn-sm ml-1">Clear</a>
            @endif
        </form>
    </div>

    @include('admin.settings._flash')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Roll</th>
                            <th>Applicant</th>
                            <th>Dept / Degree</th>
                            <th>Student Type</th>
                            <th>Application Type</th>
                            <th>Payment</th>
                            <th>Final Submit</th>
                            <th>Eligibility</th>
                            <th>Admission</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td><strong>{{ $item->roll }}</strong></td>
                                <td>
                                    {{ $item->user->name ?? '—' }}
                                    <small class="text-muted d-block">{{ $item->user->phone ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $item->department->short_name ?? '—' }}
                                    <small class="text-muted d-block">{{ $item->degree->degree_name ?? '' }}</small>
                                </td>
                                <td>{{ $item->studenttype->type ?? '—' }}</td>
                                <td>{{ $item->applicationtype->type ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $item->payment_status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $item->payment_status ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ (int) $item->final_submit === 1 ? 'badge-success' : 'badge-secondary' }}">
                                        {{ (int) $item->final_submit === 1 ? 'Submitted' : 'Not Submitted' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ (int) $item->eligibility_approve === 1 ? 'badge-success' : 'badge-secondary' }}">
                                        {{ (int) $item->eligibility_approve === 1 ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ (int) $item->admission_approve === 1 ? 'badge-success' : 'badge-secondary' }}">
                                        {{ (int) $item->admission_approve === 1 ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm btn-edit-applicant"
                                            data-url="{{ route('admin.applicants.update', $item->id) }}"
                                            data-account="{{ ($item->user->name ?? '—').' ('.($item->user->email ?? '—').')' }}"
                                            data-applicant="{{ json_encode([
                                                'id'                  => $item->id,
                                                'roll'                => $item->roll,
                                                'applicationtype_id'  => $item->applicationtype_id,
                                                'department_id'       => $item->department_id,
                                                'degree_id'           => $item->degree_id,
                                                'studenttype_id'      => $item->studenttype_id,
                                                'university_type'     => $item->university_type,
                                                'payment_status'      => (int) $item->payment_status,
                                                'final_submit'        => (int) $item->final_submit,
                                                'eligibility_approve' => (int) $item->eligibility_approve,
                                                'admission_approve'   => (int) $item->admission_approve,
                                                'edit_per'            => (int) $item->edit_per,
                                            ]) }}">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted py-4">No applicants found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                <span class="small text-muted">
                    Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} applicants
                </span>
                <div>{{ $items->links() }}</div>
            </div>
        @endif
    </div>
</div>

@php
    // name => [label, label for 0, label for 1]
    $flags = [
        'payment_status'      => ['Payment Status', 'Unpaid', 'Paid'],
        'final_submit'        => ['Final Submit', 'Not Submitted', 'Submitted'],
        'eligibility_approve' => ['Eligibility Approval', 'Pending', 'Approved'],
        'admission_approve'   => ['Admission Approval', 'Pending', 'Approved'],
        'edit_per'            => ['Edit Permission', 'No', 'Yes'],
    ];
@endphp

{{-- Edit modal: filled from the clicked row, saved with AJAX --}}
<div class="modal fade" id="applicantModal" tabindex="-1" role="dialog" aria-labelledby="applicantModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="applicant-form" method="POST" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="applicantModalTitle">
                        <i class="fas fa-user-edit mr-2"></i>Edit Applicant #<span id="applicant-id"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Applicant account: <strong id="applicant-account"></strong></p>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="roll">Roll <span class="text-danger">*</span></label>
                            <input type="text" id="roll" name="roll" class="form-control track" data-label="Roll" required>
                            <div class="invalid-feedback" data-error-for="roll"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="applicationtype_id">Application Type <span class="text-danger">*</span></label>
                            <select id="applicationtype_id" name="applicationtype_id" class="form-control track" data-label="Application Type" required>
                                @foreach($applicationtypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->type }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="applicationtype_id"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="department_id">Department <span class="text-danger">*</span></label>
                            <select id="department_id" name="department_id" class="form-control track" data-label="Department" required>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->short_name }} - {{ $d->full_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="department_id"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="degree_id">Degree <span class="text-danger">*</span></label>
                            <select id="degree_id" name="degree_id" class="form-control track" data-label="Degree" required>
                                <option value="">-- Select Degree --</option>
                                @foreach($degrees as $d)
                                    <option value="{{ $d->id }}">{{ $d->degree_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="degree_id"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="studenttype_id">Student Type <span class="text-danger">*</span></label>
                            <select id="studenttype_id" name="studenttype_id" class="form-control track" data-label="Student Type" required>
                                @foreach($studenttypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->type }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="studenttype_id"></div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="university_type">University Type</label>
                            <select id="university_type" name="university_type" class="form-control track" data-label="University Type">
                                <option value="">-- None --</option>
                                @foreach($universityTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="university_type"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        @foreach($flags as $name => [$label, $off, $on])
                            <div class="form-group col-md-4">
                                <label for="{{ $name }}">{{ $label }} <span class="text-danger">*</span></label>
                                <select id="{{ $name }}" name="{{ $name }}" class="form-control track" data-label="{{ $label }}" required>
                                    <option value="0">{{ $off }}</option>
                                    <option value="1">{{ $on }}</option>
                                </select>
                                @if($name === 'payment_status')
                                    <small class="form-text text-muted">Does not create a payment record.</small>
                                @endif
                                <div class="invalid-feedback" data-error-for="{{ $name }}"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="applicant-save" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('applicant-form');
        const saveBtn = document.getElementById('applicant-save');
        const deptSelect = document.getElementById('department_id');
        const degreeSelect = document.getElementById('degree_id');
        const fields = Array.from(form.querySelectorAll('.track'));
        const departmentDegrees = @json($departmentDegrees);

        // Offer only the degrees mapped to the department (plus the record's current pair)
        function filterDegrees() {
            const allowed = (departmentDegrees[deptSelect.value] || []).map(String);
            if (deptSelect.value === deptSelect.dataset.original) {
                allowed.push(degreeSelect.dataset.original);
            }
            Array.from(degreeSelect.options).forEach(function (option) {
                const show = option.value === '' || allowed.includes(option.value);
                option.hidden = !show;
                option.disabled = !show;
            });
            if (degreeSelect.selectedOptions[0] && degreeSelect.selectedOptions[0].disabled) {
                degreeSelect.value = '';
            }
        }
        deptSelect.addEventListener('change', filterDegrees);

        function clearErrors() {
            fields.forEach(field => field.classList.remove('is-invalid'));
            form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
        }

        function showErrors(errors) {
            Object.keys(errors).forEach(function (name) {
                const field = form.elements[name];
                const box = form.querySelector(`[data-error-for="${name}"]`);
                if (field && box) {
                    field.classList.add('is-invalid');
                    box.textContent = errors[name][0];
                }
            });
        }

        // Open the modal with the clicked row's values
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit-applicant');
            if (!btn) return;

            const applicant = JSON.parse(btn.dataset.applicant);
            clearErrors();
            form.action = btn.dataset.url;
            document.getElementById('applicant-id').textContent = applicant.id;
            document.getElementById('applicant-account').textContent = btn.dataset.account;

            fields.forEach(function (field) {
                const value = applicant[field.name] === null ? '' : String(applicant[field.name]);
                field.dataset.original = value;
                field.value = value;
            });
            filterDegrees();

            $('#applicantModal').modal('show');
        });

        function esc(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function display(field, value) {
            if (field.tagName !== 'SELECT') {
                return value;
            }
            const option = Array.from(field.options).find(o => o.value === value);
            return option ? option.textContent.trim() : value;
        }

        async function save() {
            saveBtn.disabled = true;
            try {
                const res = await fetch(form.action, {
                    method: 'POST', // _method=PUT is in the form
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });
                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    showErrors(data.errors || {});
                    return;
                }
                if (!res.ok || !data.ok) {
                    throw new Error(data.msg || data.message || 'Could not save. Please reload the page and try again.');
                }

                $('#applicantModal').modal('hide');
                await Swal.fire({ icon: 'success', title: 'Updated', text: data.msg, timer: 1500, showConfirmButton: false });
                window.location.reload(); // keeps the current filters and page
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            } finally {
                saveBtn.disabled = false;
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            if (!form.reportValidity()) return;

            const changes = fields
                .filter(field => field.value !== field.dataset.original)
                .map(field => `<tr>
                    <td>${esc(field.dataset.label)}</td>
                    <td>${esc(display(field, field.dataset.original) || '—')}</td>
                    <td><strong>${esc(display(field, field.value) || '—')}</strong></td>
                </tr>`);

            if (changes.length === 0) {
                Swal.fire({ icon: 'info', title: 'No changes', text: 'You have not changed anything.' });
                return;
            }

            Swal.fire({
                title: 'Update this applicant?',
                html: `<table class="table table-sm text-left small mb-0">
                    <thead><tr><th>Field</th><th>Current</th><th>New</th></tr></thead>
                    <tbody>${changes.join('')}</tbody>
                </table>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    save();
                }
            });
        });
    });
</script>
@endsection
