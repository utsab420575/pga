@extends('layouts.app')

@section('css')
    <style>
        .preview-container { background: white; margin: 0 auto; max-width: 900px; }
        .header-section { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .university-logo { max-height: 80px; margin-bottom: 10px; }
        .section-title { background: #f8f9fa; padding: 8px 12px; margin: 20px 0 10px 0; font-weight: bold; border-left: 4px solid #007bff; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .info-table th { background-color: #f8f9fa; width: 30%; font-weight: normal; }
        .info-table2 { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table2 th, .info-table2 td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .info-table2 th { background-color: #f8f9fa;  font-weight: normal; }
        .photo-section { float: right; margin: 0 0 15px 15px; border: 1px solid #ddd; padding: 5px; }
        .signature-section { margin-top: 20px; }
        .print-button { margin: 20px 0; text-align: center; }
        .clearfix::after { content: ""; display: table; clear: both; }
        @media print {
            .print-button, .no-print { display: none !important; }
            .preview-container { margin: 0; max-width: none; }
            body { font-size: 12px; }
        }
        .attachment-list { list-style: none; padding: 0; }
        .attachment-list li { padding: 5px; border-bottom: 1px solid #eee; }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="no-print print-button">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Application
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="preview-container">
        {{-- Header Section --}}
        <div class="header-section">
            <h3 style="margin: 0; font-size: 18px;">Dhaka University of Engineering & Technology, Gazipur</h3>
            <h4 style="margin: 0; font-size: 16px;">Gazipur-1707</h4>
            <h4 style="margin: 5px 0; font-size: 16px;">Application for Eligibility Verification of Obtained Degree</h4>
            <h4 style="margin: 5px 0; font-size: 16px;">(for Admission to Postgraduate Program; Session: {{ $setting->session }})</h4>
        </div>

        <div class="clearfix">
            <div class="row pb-3">
                <div class="col-md-6"><p style="text-align: left; margin: 5px 0; font-size: 16px;">To,<br>
            The Chairman<br>
            Eligibility Verification Committee<br>
            Dhaka University of Engineering & Technology, Gazipur<br>
            Gazipur-1707, Bangladesh.<br>
            </p></div>
                <div class="col-md-6">
                    <table class="info-table">
                <tr>
                    <th>Application ID:</th>
                    <td><strong>{{ $applicant->roll }}</strong></td>
                </tr>
                <tr>
                    <th>Transaction ID:</th>
                    <td><strong>{{ $applicant->payment->trxid }}</strong></td>
                </tr>
                
            </table>
                </div>
            </div>
        </div>

        {{-- Application Info with Photo --}}
        <div class="clearfix">
            @if($applicant->basicInfo && $applicant->basicInfo->photo)
                <div class="photo-section">
                    <img src="{{ asset($applicant->basicInfo->photo) }}" 
                         alt="Applicant Photo" 
                         style="width: 120px; height: 150px; object-fit: cover;">
                    <p style="text-align: center; margin: 5px 0; font-size: 12px;">Applicant Photo</p>
                </div>
            @endif
            <div class="section-title">1. Choose a Preferred Program and Department/Institute:</div>
            <table class="info-table2">
                <tr>
                    <td width="5%">(a)</td>
                    <th>Program applied for:</th>
                    <td>{{ $applicant->degree->degree_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td width="5%">(b</td>
                    <th width="30%">Department / Institute:</th>
                    <td>{{ $applicant->department->full_name ?? 'N/A' }}</td>
                </tr>
                
                <tr>
                    <td width="5%">(c)</td>
                    <th width="30%">Student Status:</th>
                    <td>{{ $applicant->studenttype->type ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>


        {{-- Particulars of the Applicant --}}
        @if($applicant->basicInfo)
        <div class="section-title">2. Particulars of the Applicant</div>
        <table class="info-table">
            <tr>
                <th>(a) Applicant's Name (In Block Letter) (As per S.S.C Certificate):</th>
                <td>{{ $applicant->basicInfo->full_name_block_letter }}</td>
            </tr>
            <tr>
                <th>(b) Father's Name:</th>
                <td>{{ $applicant->basicInfo->f_name }}</td>
            </tr>
            <tr>
                <th>(c) Mother's Name:</th>
                <td>{{ $applicant->basicInfo->m_name }}</td>
            </tr>
            <tr>
                <th>(d) Date of Birth:</th>
                <td>{{ optional($applicant->basicInfo->dob)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>(e) Present Address:</th>
                <td style="white-space: pre-wrap;">{{ $applicant->basicInfo->pre_address }}</td>
            </tr>
            <tr>
                <th>(f) Permanent Address:</th>
                <td style="white-space: pre-wrap;">{{ $applicant->basicInfo->per_address }}</td>
            </tr>
            <tr>
                <th>(g) Cell Number:</th>
                <td>{{ $applicant->user->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>(h) E-mail Id:</th>
                <td>{{ $applicant->user->email }}</td>
            </tr>
            <tr>
                <th>(i) Nationality:</th>
                <td>{{ $applicant->basicInfo->nationality }}</td>
            </tr>
            <tr>
                <th>(j) National ID Card/Birth Certificate No.:</th>
                <td>{{ $applicant->basicInfo->nid }}</td>
            </tr>
            <tr>
                <th>(k) Passport Number (if any):</th>
                <td>{{ $applicant->basicInfo->passport_no ?: 'N/A' }}</td>
            </tr>
            <tr>
                <th>(l) Religion:</th>
                <td>{{ ucfirst($applicant->basicInfo->religion) }}</td>
            </tr>
            <tr>
                <th>(m) Gender:</th>
                <td>{{ $applicant->basicInfo->gender }}</td>
            </tr>
            <tr>
                <th>(n) Marital Status:</th>
                <td>{{ $applicant->basicInfo->marital_status }}</td>
            </tr>
        </table>
        @endif

        {{-- Particulars of Obtained Degree for Eligibility --}}
        @if($applicant->eligibilityDegree)
        <div class="section-title">3. Particulars of Obtained Degree for which Eligibility is required</div>
        <table class="info-table">
            <tr>
                <th>(a) Name of the degree:</th>
                <td>{{ $applicant->eligibilityDegree->degree }}</td>
            </tr>
            <tr>
                <th>(b) Name of the University/Institution:</th>
                <td>{{ $applicant->eligibilityDegree->institute }}</td>
            </tr>
            <tr>
                <th>(c) Country from which the degree obtained:</th>
                <td>{{ $applicant->eligibilityDegree->country }}</td>
            </tr>
            <tr>
                <th>(d) CGPA/GPA/Class:</th>
                <td>{{ $applicant->eligibilityDegree->cgpa }}</td>
            </tr>
            <tr>
                <th>(e) Date of graduation:</th>
                <td>{{ optional($applicant->eligibilityDegree->date_graduation)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>(f) Duration of the program:</th>
                <td>{{ $applicant->eligibilityDegree->duration }}</td>
            </tr>
            <tr>
                <th>(g) Total credit earned:</th>
                <td>{{ $applicant->eligibilityDegree->total_credit }}</td>
            </tr>
            <tr>
                <th>(h) Mode of Study:</th>
                <td>
                    @if($applicant->eligibilityDegree->mode)
                        @php
                            $modes = ['full-time' => 'Full-time', 'part-time' => 'Part-Time', 'distance' => 'Distance learning'];
                            $selectedMode = strtolower($applicant->eligibilityDegree->mode);
                        @endphp
                        @foreach($modes as $key => $label)
                            {{ $selectedMode === $key ? '✓' : '☐' }} {{ $label }}
                            @if(!$loop->last) &nbsp;&nbsp; @endif
                        @endforeach
                    @else
                        ☐ Full-time &nbsp;&nbsp; ☐ Part-Time &nbsp;&nbsp; ☐ Distance learning
                    @endif
                </td>
            </tr>
            <tr>
                <th>(i) Period of Stay at Abroad:</th>
                <td>{{ $applicant->eligibilityDegree->period ?: 'Not Applicable' }}</td>
            </tr>
            <tr>
                <th>(j) University/Institution Status:</th>
                <td>
                    @if($applicant->eligibilityDegree->uni_status)
                        @php
                            $statuses = ['public' => 'Public', 'private' => 'Private', 'international' => 'International'];
                            $selectedStatus = strtolower($applicant->eligibilityDegree->uni_status);
                        @endphp
                        @foreach($statuses as $key => $label)
                            {{ $selectedStatus === $key ? '✓' : '☐' }} {{ $label }}
                            @if(!$loop->last) &nbsp;&nbsp; @endif
                        @endforeach
                    @else
                        ☐ Public &nbsp;&nbsp; ☐ Private &nbsp;&nbsp; ☐ International
                    @endif
                </td>
            </tr>
            <tr>
                <th>(k) Web-link of the University/Institution:</th>
                <td>
                    @if($applicant->eligibilityDegree->url)
                        <a href="{{ $applicant->eligibilityDegree->url }}" target="_blank" style="color: #007bff; text-decoration: underline;">
                            {{ $applicant->eligibilityDegree->url }}
                        </a>
                    @endif
                </td>
            </tr>
        </table>
        @endif


        {{-- Education Information --}}
        @if($applicant->educationInfos->count() > 0)
        <div class="section-title">4. Degrees Obtained : (Starting from Recent Degrees)</div>
        <table class="info-table">
            <thead>
                <tr style="background-color: #e9ecef;">
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
        <div class="section-title">5. Required Documents (which must be attached herewith):</div>
        {{-- Declaration and Signature --}}
        <p>
(a) Attested copies of all academic Certificates and Transcripts (in the case of language other than English
translated documents into English must be attested by the respective diplomatic office/notary public).
(b) Detailed syllabus mentioning all the contents.<br>
(c) Attested copies of two recent passport-sized color photographs.<br>
(d) Attested copy of National ID Card/Birth Certificate.<br>
(e) Attested copy of Passport (Proof of valid Visa for staying abroad to achieve foreign degree).<br>
(f) Document in support of credit and courses waived/transferred (if any).<br>
        </p>
        <div class="section-title">6. Eligibility Verification Fee Details (Non refundable): Eligibility Verification Fee of Tk. 3,000.00
(Three thousand only) Payment by online.</div>

        {{-- Declaration and Signature --}}
        <div class="section-title">7. Declaration</div>
        <div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd;">
            <p style="text-align: justify; margin-bottom: 15px;">
                I declare that the information provided in this form is correct, true and complete to the best of my
knowledge and belief. If any information is found false, incorrect, and incomplete or if any ineligibility
is detected before or after the examination, any legal action can be taken against me by the authority
including the cancellation of my application.
            </p>
            
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div>
                    <div style="border-bottom: 1px solid #000; width: 200px; margin-bottom: 5px;"></div>
                    <p style="margin: 0; text-align: center;">Date</p>
                </div>
                <div>
                    @if($applicant->basicInfo && $applicant->basicInfo->sign)
                        <img src="{{ asset($applicant->basicInfo->sign) }}" 
                             alt="Signature" 
                             style="max-width: 150px; max-height: 50px; border-bottom: 1px solid #000;">
                    @else
                        <div style="border-bottom: 1px solid #000; width: 200px; margin-bottom: 5px;"></div>
                    @endif
                    <p style="margin: 0; text-align: center;">Applicant's Signature</p>
                </div>
            </div>
        </div>



        {{-- Footer --}}
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;">
            <p>Generated on: {{ now()->format('d-m-Y H:i:s') }}</p>
            <p>This is a computer-generated document. No signature is required for validity.</p>
        </div>
    </div>
</div>
@endsection