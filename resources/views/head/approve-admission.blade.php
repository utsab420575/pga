@extends('layouts.app')

@section('css')
    <style>
        @media print {
            .pagebreak {
                page-break-before: always;
            }
        }

        .table thead th {
            white-space: nowrap;
        }


        /* Fancy download button */
        .btn-download{
            --btn-bg: #f8f9fa;
            --btn-txt: #212529;
            --btn-ring: rgba(13,110,253,.35);
            --btn-gradient: linear-gradient(135deg, #f8f9fa 0%, #eef2ff 100%);
            background: var(--btn-gradient);
            color: var(--btn-txt);
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: .4rem .9rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: transform .08s ease, box-shadow .2s ease, border-color .2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .btn-download .icon{ display:inline-block; }
        .btn-download:hover{
            transform: translateY(-1px);
            border-color:#cbd5e1;
            box-shadow: 0 8px 20px -12px var(--btn-ring);
        }
        .btn-download:active{ transform: translateY(0); }

        /* Loading/disabled state */
        .btn-download[disabled],
        .btn-download.is-loading{
            cursor: not-allowed;
            opacity: .85;
            box-shadow: 0 0 0 .25rem var(--btn-ring) inset;
        }
        .btn-download.is-loading .label{ opacity:.8; }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h3>Eligibility to Appear for the Admission Test</h3>
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
                    Listing applicants with <strong>Payment =
                        Done</strong>{{--, and <strong>Admission = Pending</strong>. --}}
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
                {{--<div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Department/Institute: {{ $deptName }}</strong>
                    <span class="badge bg-secondary">{{ $rows->count() }} applicant(s)</span>
                </div>--}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Department/Institute: {{ $deptName }}</strong>

                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary mr-2">{{ $rows->count() }} applicant(s)</span>

                        @php
                            $deptIdForGroup = optional($rows->first())->department_id;
                            $dlUrl  = route('approve-admission.download', ['dept' => $deptIdForGroup]);
                            $xlsUrl = route('approve-admission.export',   ['dept' => $deptIdForGroup]);
                        @endphp

                        <button
                            type="button"
                            class="btn btn-download mr-2"
                            data-url="{{ $dlUrl }}"
                            title="Download All Documents ({{ $deptName }})">
                            <svg class="icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                                <path d="M12 3v10m0 0l-4-4m4 4l4-4M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="label">Download Documents</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                        </button>

                        <button
                            type="button"
                            class="btn btn-download"
                            data-url="{{ $xlsUrl }}"
                            title="Download Applicant Information ({{ $deptName }})">
                            {{-- receipt icon --}}
                            <svg class="icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                                <path d="M7 3h10l2 2v14l-2 2H7l-2-2V5l2-2zm0 0v4h10V3M8 10h8M8 14h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="label">Export Applicant Information</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th>SL</th>
                                {{-- <th>Department</th> --}}
                                <th>Applicant Roll</th>
                                <th>Name</th>
                                {{--<th>Transaction ID</th>--}}
                                <th>Payment Date</th>
                                <th>Eligible for Application</th>
                                <th>Eligible for Admission Test</th>
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
                                    {{--<td>{{ optional($row->department)->short_name ?? '-' }}</td>--}}
                                    <td>{{ $row->roll }}<br>
                                        <span class="badge bg-primary">{{ $row->degree?->degree_name ?? 'N/A' }}</span><br>
                                        <span class="badge bg-warning">{{ $row->studenttype?->type ?? 'N/A' }}</span>
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
                                            {{ $isApproved ? 'Undo' : 'Eligible for Admission Test' }}
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
                                            class="btn btn-sm btn-primary ms-1 mt-1"
                                            title="Show Applicant"
                                        >
                                            Show Applicant
                                        </a>



                                        @php
                                            // Collection of attachments of type=3 (previous_eligibility)
                                            $prev = optional($row->user->userAttachments)->where('type', 3) ?? collect();
                                        @endphp

                                        @if($prev->isNotEmpty())
                                            @foreach($prev as $file)
                                                @php
                                                    // Normalize public path: DB may store "public/attachments/..."
                                                    $rel = ltrim($file->file ?? '', '/\\');
                                                    if (\Illuminate\Support\Str::startsWith($rel, 'public/')) {
                                                        $rel = substr($rel, 7);
                                                    }
                                                    $href  = asset($rel); // points to /public/...
                                                    $label = $file->title ?: basename($rel) ?: 'Document';
                                                @endphp

                                                <a href="{{ $href }}" target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-info mb-1 me-1 prev-elig-btn mt-1"
                                                   title="{{ $label }}">
                                                    {{-- simple file icon (Bootstrap icon if you have it; fallback emoji otherwise) --}}
                                                    <span class="me-1">📄</span> Eligibility  Document
                                                </a>
                                            @endforeach
                                        @endif
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

            const roll = btn.dataset.roll;
            const url = btn.dataset.url;
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
                    body: JSON.stringify({toggle: true})
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.msg || 'Failed to update.');

                // 1) Update button state/label
                btn.textContent = data.label; // 'Undo' or 'Approve Admission'
                btn.title = data.label === 'Undo' ? 'Undo approval' : 'Approve Admission';
                btn.dataset.approved = data.approved ? '1' : '0';
                btn.classList.remove('btn-success', 'btn-danger', 'btn-warning');
                btn.classList.add(data.class); // 'btn-danger' or 'btn-success'

                // 2) Update Status badge live
                const statusSel = btn.dataset.statusTarget;              // "#status-123"
                const statusEl = statusSel ? document.querySelector(statusSel) : null;
                if (statusEl) {
                    statusEl.textContent = data.approved ? 'Approved' : 'Pending';
                    statusEl.classList.remove('bg-success', 'bg-secondary', 'bg-warning');
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
                Swal.fire({icon: 'error', title: 'Error', text: err.message || 'Something went wrong.'});
            } finally {
                btn.disabled = false;
            }
        });
    </script>


    <script>
        // Download with lock + spinner
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-download');
            if (!btn) return;

            const url = btn.dataset.url;
            if (!url) return;

            // Lock UI
            btn.classList.add('is-loading');
            btn.setAttribute('disabled', 'true');
            const spinner = btn.querySelector('.spinner-border');
            if (spinner) spinner.classList.remove('d-none');

            // Start the download without losing the page (best-effort)
            // Option A: navigate (simplest & most reliable for auth-protected routes)
            setTimeout(() => { window.location.href = url; }, 100);

            // Fallback: if nothing happened, unlock after 25s
            setTimeout(() => {
                if (!document.hidden) { // user didn't leave/navigate
                    btn.classList.remove('is-loading');
                    btn.removeAttribute('disabled');
                    if (spinner) spinner.classList.add('d-none');
                }
            }, 25000);
        });

        // If the page unloads (navigation for file download), remove the lock visual quickly
        window.addEventListener('beforeunload', () => {
            document.querySelectorAll('.btn-download.is-loading').forEach(btn => {
                btn.classList.remove('is-loading');
                btn.removeAttribute('disabled');
                const spinner = btn.querySelector('.spinner-border');
                if (spinner) spinner.classList.add('d-none');
            });
        });
    </script>

@endsection
