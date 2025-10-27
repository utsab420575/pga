@extends('layouts.app')

@section('css')
    <style>
        @media print { .pagebreak { page-break-before: always; } }
        .table thead th { white-space: nowrap; }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h3>Admission Application Verification</h3>
                <small class="text-muted">
                    @if(auth()->user()->user_type === 'admin')
                        Showing: All Departments
                    @else
                        Showing: {{ optional(auth()->user()->department)->full_name ?? 'Unknown Dept' }}
                    @endif
                </small>
            </div>
        </div>

        {{-- Optional: quick legend --}}
        <div class="row mb-3">
            <div class="col">
                <div class="alert alert-info py-2 mb-0">
                    Listing applicants with <strong>Payment = Done</strong>{{--, and <strong>Admission = Pending</strong>. --}}
                </div>
            </div>
        </div>

        {{-- group applicants by department short_name; fallback "Unknown" --}}
        @php
            // If paginator was passed, convert to a Collection first (safe no-op if not)
            if ($applicants instanceof \Illuminate\Pagination\LengthAwarePaginator ||
                $applicants instanceof \Illuminate\Pagination\Paginator) {
                $applicants = collect($applicants->items());
            }

            $grouped = $applicants->groupBy(function ($a) {
                return optional($a->department)->short_name ?? 'Unknown';
            });
        @endphp

        {{-- iterate each group: $deptName = department, $rows = applicants in that dept --}}
        @forelse($grouped as $deptName => $rows)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Department/Institute: {{ $deptName }}</strong>
                    <span class="badge bg-secondary">{{ $rows->count() }} applicant(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Department</th>
                                <th>Applicant Roll</th>
                                <th>Name</th>
                                {{--<th>Transaction ID</th>--}}
                                <th>Payment Date</th>
                                <th>Eligibility Status</th>
                                <th>Admission Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $i = 1;
                            @endphp

                            {{-- inner loop over the applicants in this department --}}
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ optional($row->department)->short_name ?? '-' }}</td>
                                    <td>{{ $row->roll }}</td>
                                    <td>
                                        {{ $row->user->name }} <br>
                                        {{ $row->user->phone }}
                                    </td>

                                    {{--<td>{{ optional($row->payment)->trxid ?? '-' }}</td>--}}
                                    <td>
                                        {{-- payment date (or dash) --}}
                                        {{ optional($row->payment)->paymentdate
                                            ? \Carbon\Carbon::parse($row->payment->paymentdate)->format('d M Y')
                                            : '-' }}

                                        {{-- final submit status (plain text on next line) --}}
                                        @if((int)($row->final_submit ?? 0) === 1)
                                            <div class="small text-success mt-1">Final Submitted</div>
                                        @else
                                            <div class="small text-danger mt-1">Not Final Submitted</div>
                                        @endif
                                    </td>

                                    {{-- Eligibility Status (using $eligMap: [user_id => 1|0], or missing) --}}
                                    @php
                                        // returns null if user_id not present in the map
                                        $flag = $eligMap instanceof \Illuminate\Support\Collection
                                            ? $eligMap->get($row->user_id)
                                            : ($eligMap[$row->user_id] ?? null);
                                    @endphp
                                    <td>
                                        @if($flag === null)
                                            <span class="badge bg-secondary">Not Applied</span>
                                        @elseif((int)$flag === 1)
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Not Approved</span>
                                        @endif
                                    </td>

                                    {{-- Admission Status --}}
                                    @php
                                        $isApproved = (int)$row->admission_approve === 1;
                                    @endphp
                                    <td class="cell-status">
                                        <span
                                            id="status-{{ $row->id }}"
                                            class="badge {{ $isApproved ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $isApproved ? 'Approved' : 'Pending' }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td>
                                        @php
                                            $isApproved = (int)$row->admission_approve === 1;
                                        @endphp
                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $isApproved ? 'btn-danger' : 'btn-success' }} btn-approve-admission"
                                            data-applicant="{{ $row->id }}"
                                            data-approved="{{ (int)$row->admission_approve }}"
                                            data-roll="{{ $row->roll }}"
                                            data-url="{{ route('approve-admission.toggle', $row->id) }}"
                                            data-status-target="#status-{{ $row->id }}"
                                            title="{{ $isApproved ? 'Undo' : 'Approve Admission' }}"
                                        >
                                            {{ $isApproved ? 'Undo' : 'Approve Admission' }}
                                        </button>

                                        {{-- Show Applicant (always open) --}}
                                        @php
                                            $viewUrl = (int)$row->applicationtype_id === 1
                                                ? url('applicant/application-postgraduate-form/'.$row->id)
                                                : url('applicant/eligibility-form/'.$row->id);
                                        @endphp
                                        <a
                                            href="{{ $viewUrl }}"
                                            target="_blank" rel="noopener"
                                            class="btn btn-sm btn-primary ms-1"
                                            title="Show Applicant"
                                        >
                                            Show Applicant
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-warning">
                No pending applicants found for Admission approval.
            </div>
        @endforelse
    </div>
@endsection

@section('script')
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Approve / Undo with confirm
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-approve-admission');
            if (!btn) return;

            const roll     = btn.dataset.roll;
            const url      = btn.dataset.url;
            const approved = btn.dataset.approved === '1';
            const actionTxt = approved ? 'undo approval' : 'approve';

            const confirm = await Swal.fire({
                title: `Confirm ${actionTxt}?`,
                text: `Are you sure you want to ${actionTxt} for Applicant Roll ${roll}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Yes, ${actionTxt}`,
                cancelButtonText: 'Cancel'
            });
            if (!confirm.isConfirmed) return;

            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ toggle: true })
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.msg || 'Failed to update.');

                // 1) Update button state/label
                btn.textContent = data.label; // 'Undo' or 'Approve Admission'
                btn.title = data.label === 'Undo' ? 'Undo approval' : 'Approve Admission';
                btn.dataset.approved = data.approved ? '1' : '0';
                btn.classList.remove('btn-success','btn-danger','btn-warning');
                btn.classList.add(data.class); // 'btn-danger' or 'btn-success'

                // 2) Update Status badge live
                const statusSel = btn.dataset.statusTarget;              // "#status-123"
                const statusEl  = statusSel ? document.querySelector(statusSel) : null;
                if (statusEl) {
                    statusEl.textContent = data.approved ? 'Approved' : 'Pending';
                    statusEl.classList.remove('bg-success','bg-secondary','bg-warning');
                    statusEl.classList.add(data.approved ? 'bg-success' : 'bg-secondary');
                }

                // Toast
                Swal.fire({
                    icon: 'success',
                    title: data.approved ? 'Approved' : 'Approval undone',
                    text: data.approved ? 'Admission approved.' : 'Admission approval has been undone.',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Something went wrong.' });
            } finally {
                btn.disabled = false;
            }
        });
    </script>
@endsection
