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
                <h3>Approve Eligibility</h3>
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
                    Listing applicants with <strong>Payment = Done</strong>{{--, and <strong>Eligibility = Pending</strong>--}}.
                </div>
            </div>
        </div>

        {{-- group applicants by department short_name; fallback "Unknown" --}}
        @php($grouped = $applicants->groupBy(fn($a) => optional($a->department)->short_name ?? 'Unknown'))

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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php($i=1)

                            {{-- Example inner loop over the applicants in this department --}}
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


                                    @php($isApproved = (int)$row->eligibility_approve === 1) {{-- ensure correct field --}}
                                    <td class="cell-status">
                                          <span
                                              id="status-{{ $row->id }}"
                                              class="badge {{ $isApproved ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $isApproved ? 'Approved' : 'Pending' }}
                                          </span>
                                    </td>
                                    <td>
                                        {{-- Approve/Undo toggle --}}
                                        @php($isApproved = (int)$row->eligibility_approve === 1)
                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $isApproved ? 'btn-danger' : 'btn-success' }} btn-approve-eligibility"
                                            data-applicant="{{ $row->id }}"
                                            data-approved="{{ (int)$row->eligibility_approve }}"
                                            data-roll="{{ $row->roll }}"
                                            data-url="{{ route('approve-eligibility.toggle', $row->id) }}"
                                            data-status-target="#status-{{ $row->id }}"
                                            title="{{ $isApproved ? 'Undo' : 'Approve Eligibility' }}"
                                        >
                                            {{ $isApproved ? 'Undo' : 'Approve Eligibility' }}
                                        </button>


                                        {{-- Show Applicant (always open) --}}
                                        @php(
                                          $viewUrl = (int)$row->applicationtype_id === 1
                                            ? url('applicant/application-postgraduate-form/'.$row->id)
                                            : url('applicant/eligibility-form/'.$row->id)
                                        )
                                        <a
                                            href="{{ $viewUrl }}"
                                            target="_blank" rel="noopener"
                                            class="btn btn-sm btn-primary ml-1"
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
                No pending applicants found for eligibility approval.
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
        // Approve / Undo with confirm (your existing listener)
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-approve-eligibility');
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
                btn.textContent = data.label; // 'Undo' or 'Approve Eligibility'
                btn.title = data.label === 'Undo' ? 'Undo approval' : 'Approve Eligibility';
                btn.dataset.approved = data.approved ? '1' : '0';
                btn.classList.remove('btn-success','btn-danger','btn-warning');
                btn.classList.add(data.class); // 'btn-warning' or 'btn-success'

                // 2) Update Status badge live
                const statusSel = btn.dataset.statusTarget;              // "#status-123"
                const statusEl  = statusSel ? document.querySelector(statusSel) : null;
                if (statusEl) {
                    statusEl.textContent = data.approved ? 'Approved' : 'Pending';
                    statusEl.classList.remove('badge-success','badge-secondary','badge-warning');
                    statusEl.classList.add(data.approved ? 'badge-success' : 'badge-secondary');
                }

                // Toast
                Swal.fire({
                    icon: 'success',
                    title: data.approved ? 'Approved' : 'Approval undone',
                    text: data.approved ? 'Eligibility approved.' : 'Eligibility approval has been undone.',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Something went wrong.' });
            } finally {
                btn.disabled = false;
            }
        });


        /*// Show Applicant (only if final_submit == 1)
        document.addEventListener('click', (e) => {
            const linkBtn = e.target.closest('.btn-show-applicant');
            if (!linkBtn) return;

            const isFinal = linkBtn.dataset.final === '1';
            const url = linkBtn.dataset.url;

            if (!isFinal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Not submitted yet',
                    text: 'This application has not been finally submitted.',
                });
                return;
            }

            window.open(url, '_blank', 'noopener');
        });*/
    </script>
@endsection