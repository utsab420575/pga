@extends('layouts.app')

@section('css')
    <style>
        .preview-container {
            background: white;
            margin: 0 auto;
            max-width: 900px;
        }

        .print-button {
            margin: 20px 0;
            text-align: center;
        }

        /* HEADER */
        .header-section {
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
        }

        .header-row {
            display: grid;
            grid-template-columns: 120px 1fr 140px;
            align-items: center;
            gap: 12px;
        }

        .header-logo img {
            width: 100%;
            max-width: 100px;
            height: auto;
            display: block;
        }

        .header-titles {
            text-align: center;
        }

        .header-titles h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .header-titles h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
        }

        .boxed-title {
            margin-top: 15px;
            display: inline-block;
            padding: 10px 18px;
            outline: 3px solid #000;
            outline-offset: 6px;
            font-weight: 700;
            font-size: 18px;
        }

        .header-photo {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }

        .header-photo img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

        /* SECTIONS */
        .section-title {
            background: #f8f9fa;
            padding: 8px 12px;
            margin: 20px 0 10px;
            font-weight: bold;
            border-left: 4px solid #007bff;
        }

        /* TABLES */
        .info-table, .info-table2 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table th, .info-table td,
        .info-table2 th, .info-table2 td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .info-table th {
            background-color: #f8f9fa;
            width: 30%;
            font-weight: normal;
        }

        .info-table2 th {
            background-color: #f8f9fa;
            font-weight: normal;
        }

        /* REFERENCES: 2 columns on screen and print */
        .refs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .ref-card {
            border: 1px solid #ddd;
            padding: 10px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Keep “To + IDs” two columns in print as well */
        .col-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }


        /* Dept-only verification block */
        .dept-only-title {
            text-align: center;
            font-weight: 700;
            text-decoration: underline;
            margin: 28px 0 10px;
        }

        .dept-note {
            margin: 0 0 8px;
            font-weight: 600;
        }

        .dept-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dept-table th, .dept-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .dept-table thead th {
            background: #fff;
            color: #000;
            text-align: center;
        }

        .dept-table .sl {
            width: 60px;
            text-align: center;
        }

        .dept-table .yn {
            width: 60px;
            text-align: center;
        }

        /* Signature / meta tables */
        .sig-table, .meta-table, .sig3-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .sig-table th, .sig-table td,
        .meta-table th, .meta-table td,
        .sig3-table th, .sig3-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .meta-table th {
            font-weight: 700;
        }

        .sig-space {
            height: 90px;
        }

        /* space for signatures */
        .meta-space {
            height: 35px;
        }

        /* space to handwrite numbers/dates */


        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            .print-button, .no-print {
                display: none !important;
            }

            .preview-container {
                margin: 0;
                max-width: none;
            }

            body {
                font-size: 12px;
            }

            .header-section, .row.pb-3, .refs {
                page-break-inside: avoid;
            }

            .sig-space {
                height: 90px;
            }

            .sig-table, .meta-table, .sig3-table {
                page-break-inside: avoid;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- Print controls --}}
        <div class="no-print print-button">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print/Save Application
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="preview-container">

            {{-- HEADER: logo left, titles center, applicant photo right --}}
            <div class="header-section">
                <div class="header-row">
                    <div class="header-logo">
                        <img src="{{ asset('logo.png') }}" alt="University Logo">
                    </div>

                    <div class="header-titles">
                        <h3>Dhaka University of Engineering &amp; Technology, Gazipur</h3>
                        <h4>Gazipur-1707</h4>
                        <div class="boxed-title">
                            Application for admission to Postgraduate Program<br>
                            <span style="font-weight:600;">Session: {{ $setting->session ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="header-photo">
                        @if($applicant->basicInfo && $applicant->basicInfo->photo)
                            <img src="{{ asset($applicant->basicInfo->photo) }}" alt="Applicant Photo">
                        @else
                            <small>Photo<br></small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BELOW HEADER: recipient + table (side-by-side also in print) --}}
            <div class="clearfix">
                <div class="row pb-3 align-items-start">
                    <div class="col-6 col-md-6">
                        <p style="text-align: left; margin: 5px 0; font-size: 16px;">
                            To,<br>
                            The Registrar<br>
                            Dhaka University of Engineering &amp; Technology, Gazipur<br>
                            Gazipur-1707, Bangladesh.<br>
                        </p>
                    </div>

                    <div class="col-6 col-md-6">
                        <table class="info-table mb-0">
                            <tr>
                                <th width="25%">Application ID:</th>
                                <td width="75%"><strong>{{ $applicant->roll }}</strong></td>
                            </tr>
                            <tr>
                                <th width="25%">Transaction ID:</th>
                                <td width="75%"><strong>{{ $applicant->payment?->trxid ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <th width="25%">Eligibility Check:</th>
                                <td width="75%">{{ ($hasEligibility ?? 0) == 1 ? 'Approved' : 'None' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Application Info with Photo (removed photo here; already in header) --}}
            <div class="section-title">1. Choose a Preferred Program and Department/Institute:</div>
            <table class="info-table2">
                <tr>
                    <td width="5%">(a)</td>
                    <th>Program applied for:</th>
                    <td>{{ $applicant->degree->degree_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td width="5%">(b)</td>
                    <th width="30%">Department / Institute:</th>
                    <td>{{ $applicant->department->full_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td width="5%">(c)</td>
                    <th width="30%">Student Status:</th>
                    <td>{{ $applicant->studenttype->type ?? 'N/A' }}</td>
                </tr>
            </table>

            {{-- Basic Information --}}
            @if($applicant->basicInfo)
                <table class="info-table2">
                    <tr>
                        <td width="5%">2.</td>
                        <th width="30%">Full Name in English:</th>
                        <td>{{ $applicant->basicInfo->full_name }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">Full Name in Bengali:</th>
                        <td>{{ $applicant->basicInfo->bn_name }}</td>
                    </tr>
                    <tr>
                        <td width="5%">3.</td>
                        <th width="30%">Father's Name:</th>
                        <td>{{ $applicant->basicInfo->f_name }}</td>
                    </tr>
                    <tr>
                        <td width="5%">4.</td>
                        <th width="30%">Mother's Name:</th>
                        <td>{{ $applicant->basicInfo->m_name }}</td>
                    </tr>
                    <tr>
                        <td width="5%">5.</td>
                        <th width="30%">Guardian’s Income (Per Annum):</th>
                        <td>৳ {{ number_format($applicant->basicInfo->g_income ?? 0, 2) }}</td>
                    </tr>
                </table>

                <p>6. Addresses:</p>
                <div class="pb-3" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <p style="margin:15px 0 5px 0;">Present Address:</p>
                        <div style="border:1px solid #ddd; padding:10px; min-height:80px;">
                            {!! nl2br(e($applicant->basicInfo->pre_address)) !!}
                        </div>
                    </div>
                    <div style="flex:1;">
                        <p style="margin:15px 0 5px 0;">Permanent Address:</p>
                        <div style="border:1px solid #ddd; padding:10px; min-height:80px;">
                            {!! nl2br(e($applicant->basicInfo->per_address)) !!}
                        </div>
                    </div>
                </div>


                <table class="info-table">
                    <tr>
                        <td width="5%">7.</td>
                        <th width="30%">Date of Birth:</th>
                        <td>{{ optional($applicant->basicInfo->dob)->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td width="5%">8.</td>
                        <th width="30%">Nationality:</th>
                        <td>{{ $applicant->basicInfo->nationality }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">National ID:</th>
                        <td>{{ $applicant->basicInfo->nid }}</td>
                    </tr>
                    <tr>
                        <td width="5%">9.</td>
                        <th width="30%">Religion:</th>
                        <td>{{ ucfirst($applicant->basicInfo->religion) }}</td>
                    </tr>
                    <tr>
                        <td width="5%">10.</td>
                        <th width="30%">Gender:</th>
                        <td>{{ $applicant->basicInfo->gender }}</td>
                    </tr>
                    <tr>
                        <td width="5%">11.</td>
                        <th width="30%">Marital Status:</th>
                        <td>{{ $applicant->basicInfo->marital_status }}</td>
                    </tr>
                    <tr>
                        <td width="5%">12.</td>
                        <th width="30%">Field of Interest:</th>
                        <td>{{ $applicant->basicInfo->field_of_interest }}</td>
                    </tr>
                </table>
            @endif

            {{-- Education Information --}}
            @if($applicant->educationInfos->count() > 0)
                <p>13. Degrees Obtained : (Starting from Recent Degrees):</p>
                <table class="info-table">
                    <thead>
                    <tr style="background-color:#e9ecef;">
                        <th>Degree/Certificate</th>
                        <th>University/Institute/Board</th>
                        <th>Year of Passing</th>
                        <th>Discipline/Field</th>
                        <th>CGPA/Class/Division</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->educationInfos as $edu)
                        <tr>
                            <td>{{ $edu->degree }}</td>
                            <td>{{ $edu->institute }}</td>
                            <td>{{ $edu->year_of_passing }}</td>
                            <td>{{ $edu->field }}</td>
                            <td>{{ $edu->cgpa }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Research Experience / Thesis --}}
            @if($applicant->theses->count() > 0)
                <p>14. Thesis (if any):</p>
                <table class="info-table2">
                    <thead>
                    <tr style="background-color:#e9ecef;">
                        <th width="5%">S.No</th>
                        <th width="45%">Title</th>
                        <th width="30%">Institute</th>
                        <th width="20%">Period</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->theses as $index => $thesis)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $thesis->title }}</td>
                            <td>{{ $thesis->institute }}</td>
                            <td>{{ $thesis->period }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Publications --}}
            @if($applicant->publications->count() > 0)
                <p>15. Publication (if any):</p>
                <table class="info-table2">
                    <thead>
                    <tr style="background-color:#e9ecef;">
                        <th width="5%">S.No</th>
                        <th width="35%">Title</th>
                        <th width="25%">Authors</th>
                        <th width="10%">Year</th>
                        <th width="25%">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->publications as $index => $pub)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pub->title }}</td>
                            <td>{{ $pub->authors }}</td>
                            <td>{{ $pub->year_of_publication }}</td>
                            <td>{{ $pub->details }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Job Experience --}}
            @if($applicant->jobExperiences->count() > 0)
                <p>16. Practical Job Experience (if any):</p>
                <table class="info-table2">
                    <thead>
                    <tr style="background-color:#e9ecef;">
                        <th width="5%">S.No</th>
                        <th width="20%">Designation</th>
                        <th width="25%">Organization</th>
                        <th width="15%">Duration</th>
                        <th width="35%">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($applicant->jobExperiences as $index => $job)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $job->designation }}</td>
                            <td>{{ $job->organization }}</td>
                            <td>{{ optional($job->from)->format('d-m-Y') }}
                                to {{ optional($job->to)->format('d-m-Y') }}</td>
                            <td>{{ $job->details }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            {{-- References (2 columns in print too) --}}
            @if($applicant->references->count() > 0)
                <p>17. Name of two referees, at least one of whom has taught you in the Institution last attended:</p>
                <div class="refs">
                    @foreach($applicant->references->sortBy('order_no') as $index => $ref)
                        <div class="ref-card">
                            <strong>Reference {{ $index + 1 }}:</strong><br>
                            <strong>Name:</strong> {{ $ref->name }}<br>
                            <strong>Designation:</strong> {{ $ref->designation }}<br>
                            <strong>Institute:</strong> {{ $ref->institute }}<br>
                            <strong>Email:</strong> {{ $ref->email }}<br>
                            <strong>Phone:</strong> {{ $ref->phone }}<br>
                            <strong>Address:</strong> {{ $ref->address }}
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Declaration and Signature --}}
            <p>18. I hereby apply for admission in the program <b>{{ $applicant->degree->degree_name ?? 'N/A' }}</b> in
                the <b>{{ $applicant->department->full_name ?? 'N/A' }}</b> Department/Institute of DUET, Gazipur as
                full-time/part-time student. I agree to abide by all the rules and regulations of the University.</p>

            <p>19. Declaration</p>
            <div style="margin:20px 0; padding:15px; border:1px solid #ddd;">
                <p style="text-align: justify; margin-bottom: 15px;">
                    I declare that the information provided in this form is correct, true and complete to the best of my
                    knowledge and belief. If any information is found false, incorrect, and incomplete or if any
                    ineligibility is detected before or after the examination, any legal action can be taken against me
                    by the authority including the cancellation of my candidature.
                </p>

                <div style="display:flex; justify-content:space-between; margin-top:40px;">
                    {{-- Date (text printed inside the line) --}}
                    <div style="text-align:center;">
                        <div style="border-bottom:1px solid #000; width:200px; margin:0 auto 5px; padding-bottom:4px;">
                            {{ ($generatedAt ?? now())->timezone(config('app.timezone'))->format('d/m/Y') }}
                        </div>
                        <p style="margin:0;">Date</p>
                    </div>

                    {{-- Signature --}}
                    <div style="text-align:center;">
                        @if($applicant->basicInfo && $applicant->basicInfo->sign)
                            <img src="{{ asset($applicant->basicInfo->sign) }}"
                                 alt="Signature"
                                 style="max-width:150px; max-height:50px; border-bottom:1px solid #000;">
                        @else
                            <div style="border-bottom:1px solid #000; width:200px; margin:0 auto 5px;"></div>
                        @endif
                        <p style="margin:0;">Applicant's Signature</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="margin-top:30px; padding-top:15px; border-top:1px solid #000; text-align:center; font-size:12px; color:#000;">
            </div>

            {{-- =========================
     Use only for Department/Institute
     ========================= --}}
            <div class="dept-only-title">Use only for respective Department/Institute</div>

            <p class="dept-note">
                <strong>20.</strong> List of necessary documents have been verified and found correct as follows
                <em>(Use √ mark)</em>:
            </p>

            @php
                $docs = [
                    'Two recent passport size colored photographs',
                    'Attested copy of SSC/equivalent Certificate',
                    'Attested copy of SSC or equivalent Mark Sheet/Grade Sheet',
                    'Attested copy of HSC or equivalent/Diploma in Engineering Certificate',
                    'Attested copy of HSC or equivalent/Diploma in Engineering Mark Sheet/Grade Sheet/Transcript',
                    "Attested copy of B Sc. Engg /B Sc. Hon's/equivalent Certificate",
                    "Attested copy of B Sc. Engg /B Sc. Hon's/equivalent Mark Sheet/Transcript",
                    'Attested copy of M Engg /M Sc. Engg /M Sc./M Phil/equivalent Certificate',
                    'Attested copy of M Engg /M Sc. Engg /M Sc./M Phil/equivalent Mark Sheet/Transcript',
                    'Attested copy of Testimonial from the institute last attended',
                    'NOC from employer (if applicable)',
                ];
            @endphp

            <table class="dept-table">
                <thead>
                <tr>
                    <th class="sl" rowspan="2">SL<br>No.</th>
                    <th rowspan="2">Particulars</th>
                    <th class="yn" colspan="2">Submitted<br>Documents</th>
                </tr>
                <tr>
                    <th class="yn">Yes</th>
                    <th class="yn">No</th>
                </tr>
                </thead>
                <tbody>
                @foreach($docs as $i => $item)
                    <tr>
                        <td class="sl">({{ $i+1 }})</td>
                        <td>{{ $item }}</td>
                        <td class="yn"></td>
                        <td class="yn"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- 1) Applicant + Authorized Teacher (two columns) --}}
            <table class="sig-table">
                <thead>
                <tr>
                    <th>Signature of the applicant</th>
                    <th>Signature of the Authorized Teacher</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                </tr>
                </tbody>
            </table>

            {{-- 2) Reg / Student / Enrollment (three columns) --}}
            <table class="meta-table">
                <thead>
                <tr>
                    <th>Reg. No.</th>
                    <th>Student No.</th>
                    <th>Date of Enrollment</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="meta-space"></td>
                    <td class="meta-space"></td>
                    <td class="meta-space"></td>
                </tr>
                </tbody>
            </table>

            {{-- 3) Three signatories (three columns) --}}
            <table class="sig3-table">
                <thead>
                <tr>
                    <th>Signature of the<br>PG Coordinator</th>
                    <th>Signature of the<br>Head of the Department/<br>Director of the Institute</th>
                    <th>Signature of the<br>Registrar/Authorized Officer</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                </tr>
                </tbody>
            </table>

            {{-- Footer --}}
            <div style="margin-top:30px; padding-top:15px; text-align:center; font-size:12px; color:#666;">
                <p>Generated on: {{ now()->format('d-m-Y H:i:s') }}</p>
                <p>This is a computer-generated document. No signature is required for validity.</p>
            </div>
        </div>
    </div>
@endsection