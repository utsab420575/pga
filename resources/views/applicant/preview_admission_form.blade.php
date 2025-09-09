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
            <h4 style="margin: 5px 0; font-size: 16px;">Application for admission to Postgraduate Program</h4>
            <h4 style="margin: 5px 0; font-size: 16px;">Session: {{ $setting->session }}</h4>
        </div>

        <div class="clearfix">
            <div class="row pb-3">
                <div class="col-md-6"><p style="text-align: left; margin: 5px 0; font-size: 16px;">To,<br>
            The Registrar<br>
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
            <p>1. Choose a Preferred Program and Department/Institute:</p>
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

        {{-- Basic Information --}}
        @if($applicant->basicInfo)
        <table class="info-table2">
            <tr>
                <td width="5%">2.</td>
                <th  width="30%">Full Name in English:</th>
                <td>{{ $applicant->basicInfo->full_name }}</td>
            </tr>
            <tr>
                <th colspan="2">Full Name in Bengali:</th>
                <td>{{ $applicant->basicInfo->bn_name }}</td>
            </tr>
            <tr>
                <td width="5%">3.</td>
                <th  width="30%">Father's Name:</th>
                <td>{{ $applicant->basicInfo->f_name }}</td>
            </tr>
            <tr>
                <td width="5%">4.</td>
                <th  width="30%">Mother's Name:</th>
                <td>{{ $applicant->basicInfo->m_name }}</td>
            </tr>
            <tr>
                <td width="5%">5.</td>
                <th  width="30%">Guardian’s Income (Per Annum):</th>
                <td>৳ {{ number_format($applicant->basicInfo->g_income ?? 0, 2) }}</td>
            </tr>

            </table>
            <p>6. Addresses:</p>
            {{-- Addresses --}}
            <div class="pb-3" style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <p style="margin: 15px 0 5px 0; ">Present Address:</p>
                    <div style="border: 1px solid #ddd; padding: 10px; min-height: 80px;">
                        <pre style="margin: 0; white-space: pre-wrap; font-family: inherit;">{{ $applicant->basicInfo->pre_address }}</pre>
                    </div>
                </div>
                <div style="flex: 1;">
                    <p style="margin: 15px 0 5px 0;">Permanent Address:</p>
                    <div style="border: 1px solid #ddd; padding: 10px; min-height: 80px;">
                        <pre style="margin: 0; white-space: pre-wrap; font-family: inherit;">{{ $applicant->basicInfo->per_address }}</pre>
                    </div>
                </div>
            </div>
            <table class="info-table">
            <tr>
                <td width="5%">7.</td>
                <th  width="30%">Date of Birth:</th>
                <td>{{ optional($applicant->basicInfo->dob)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td width="5%">8.</td>
                <th  width="30%">Nationality:</th>
                <td>{{ $applicant->basicInfo->nationality }}</td>
            </tr>
            <tr>
                <th colspan="2">National ID:</th>
                <td>{{ $applicant->basicInfo->nid }}</td>
            </tr>
            <tr>
                <td width="5%">9.</td>
                <th  width="30%">Religion:</th>
                <td>{{ ucfirst($applicant->basicInfo->religion) }}</td>
            </tr>
            <tr>
                <td width="5%">10.</td>
                <th  width="30%">Gender:</th>
                <td>{{ $applicant->basicInfo->gender }}</td>
            </tr>
            <tr>
                <td width="5%">11.</td>
                <th  width="30%">Marital Status:</th>
                <td>{{ $applicant->basicInfo->marital_status }}</td>
            </tr>
            <tr>
                <td width="5%">12.</td>
                <th  width="30%">Field of Interest:</th>
                <td>{{ $applicant->basicInfo->field_of_interest }}</td>
            </tr>
            
        </table>

        
        @endif

        {{-- Education Information --}}
        @if($applicant->educationInfos->count() > 0)
        <p>13. Degrees Obtained : (Starting from Recent Degrees):</p>
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

        {{-- Research Experience / Thesis --}}
        @if($applicant->theses->count() > 0)
        <p>14. Thesis (if any):</p>
        <table class="info-table2">
            <thead>
                <tr style="background-color: #e9ecef;">
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
                <tr style="background-color: #e9ecef;">
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
                <tr style="background-color: #e9ecef;">
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
                    <td>{{ optional($job->from)->format('d-m-Y') }} to {{ optional($job->to)->format('d-m-Y') }}</td>
                    <td>{{ $job->details }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- References --}}
        @if($applicant->references->count() > 0)
        <p>17. Name of two referees, at least one of whom has taught you in the Institution last attended:</p>
        <div class="row">
        @foreach($applicant->references->sortBy('order_no') as $index => $ref)
            <div class="col-md-6">
                <div style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;">
                    <strong>Reference {{ $index +1 }}:</strong><br>
                    <strong>Name:</strong> {{ $ref->name }}<br>
                    <strong>Designation:</strong> {{ $ref->designation }}<br>
                    <strong>Institute:</strong> {{ $ref->institute }}<br>
                    <strong>Email:</strong> {{ $ref->email }}<br>
                    <strong>Phone:</strong> {{ $ref->phone }}<br>
                    <strong>Address:</strong> {{ $ref->address }}
                </div>
            </div>
        @endforeach
        </div>
        @endif

        {{-- Declaration and Signature --}}
        <p>18. I hereby apply for admission in the program <b>{{ $applicant->degree->degree_name ?? 'N/A' }}</b> in the <b>{{ $applicant->department->full_name ?? 'N/A' }}</b> Department/Institute of DUET, Gazipur as full-time/part-time student. I agree to abide by all the rules and regulations of the University.</p>

        {{-- Declaration and Signature --}}
        <p>19. Declaration</p>
        <div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd;">
            <p style="text-align: justify; margin-bottom: 15px;">
                I declare that the information provided in this form is correct, true and complete to the best of my knowledge and belief. If any information is found false, incorrect, and incomplete or if any ineligibility is detected before or after the examination, any legal action can be taken against me by the authority including the cancellation of my candidature.
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