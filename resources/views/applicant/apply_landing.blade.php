@extends('layouts.app')

@section('css')
<style>
    /* ── Page background ── */
    .apply-landing-wrap {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .apply-landing-wrap .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: .35rem;
        text-align: center;
    }
    .apply-landing-wrap .page-sub {
        color: #718096;
        font-size: .95rem;
        margin-bottom: 2.5rem;
        text-align: center;
    }

    /* ── Cards grid ── */
    .apply-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 1.75rem;
        justify-content: center;
        width: 100%;
        max-width: 820px;
    }

    .apply-card {
        flex: 1 1 340px;
        max-width: 380px;
        border: none;
        border-radius: 1.1rem;
        box-shadow: 0 8px 30px rgba(0,0,0,.09);
        overflow: hidden;
        transition: transform .22s, box-shadow .22s;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
    }
    .apply-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }

    /* Eligibility card — teal */
    .apply-card.elig .card-banner {
        background: linear-gradient(135deg, #1a9e8f, #0d7a6d);
    }
    /* Admission card — indigo */
    .apply-card.adm .card-banner {
        background: linear-gradient(135deg, #4361ee, #2d46c7);
    }

    .card-banner {
        padding: 2rem 1.5rem 1.4rem;
        color: #fff;
    }
    .card-banner .icon-wrap {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.4rem;
    }
    .card-banner h3 {
        font-size: 1.18rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .card-banner p {
        font-size: .88rem;
        opacity: .88;
        margin: 0;
        line-height: 1.5;
    }

    .card-body-inner {
        background: #fff;
        padding: 1.3rem 1.5rem 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .window-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .82rem;
        font-weight: 600;
        color: #4a5568;
        background: #f0faf8;
        border: 1px solid #c6f0ea;
        border-radius: 6px;
        padding: .3rem .7rem;
        margin-bottom: 1.2rem;
    }
    .apply-card.adm .window-badge {
        background: #eef2ff;
        border-color: #c7d2fe;
    }

    .who-list {
        font-size: .85rem;
        color: #555;
        margin-bottom: 1.4rem;
        padding-left: 0;
        list-style: none;
    }
    .who-list li { padding: .2rem 0; }
    .who-list li::before {
        content: "✓ ";
        color: #1a9e8f;
        font-weight: 700;
    }
    .apply-card.adm .who-list li::before { color: #4361ee; }

    .btn-apply-elig {
        background: linear-gradient(135deg, #1a9e8f, #0d7a6d);
        color: #fff !important;
        border: none;
        border-radius: .55rem;
        padding: .65rem 1.4rem;
        font-weight: 600;
        font-size: .95rem;
        text-align: center;
        display: block;
        transition: opacity .2s;
    }
    .btn-apply-adm {
        background: linear-gradient(135deg, #4361ee, #2d46c7);
        color: #fff !important;
        border: none;
        border-radius: .55rem;
        padding: .65rem 1.4rem;
        font-weight: 600;
        font-size: .95rem;
        text-align: center;
        display: block;
        transition: opacity .2s;
    }
    .btn-apply-elig:hover, .btn-apply-adm:hover { opacity: .88; }

    /* ── No-window message ── */
    .no-window-box {
        background: #fff8f0;
        border: 1px solid #fde8c8;
        border-radius: 1rem;
        padding: 2.5rem 2rem;
        text-align: center;
        max-width: 520px;
        color: #7c5a2a;
    }
    .no-window-box .icon { font-size: 2.4rem; margin-bottom: 1rem; color: #e9922a; }
    .no-window-box h4 { font-weight: 700; margin-bottom: .5rem; }
    .no-window-box p { font-size: .92rem; margin: 0; }
</style>
@endsection

@section('content')
<div class="apply-landing-wrap">

    {{-- Flash / error messages --}}
    @if(count($errors) > 0)
        @foreach($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show w-100" style="max-width:820px" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif
    @if(session('Status'))
        <div class="alert alert-info alert-dismissible fade show w-100" style="max-width:820px" role="alert">
            <i class="fas fa-info-circle mr-2"></i>{{ session('Status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <p class="page-title"><i class="fas fa-graduation-cap mr-2"></i>Postgraduate Application — DUET, Gazipur</p>
    <p class="page-sub">Select the application type that applies to you.</p>

    @if(!$showEligibilityCard && !$showAdmissionCard)
        {{-- Neither window is open --}}
        <div class="no-window-box">
            <div class="icon"><i class="fas fa-calendar-times"></i></div>
            <h4>No Applications Currently Open</h4>
            <p>There are no active application windows at this time.<br>
               Please check the <a href="{{ url('notice') }}">Notice Board</a> for upcoming dates.</p>
        </div>
    @else
        <div class="apply-cards">

            {{-- ── Eligibility Application Card ── --}}
            @if($showEligibilityCard)
            <a href="{{ route('apply-eligibility') }}" class="apply-card elig">
                <div class="card-banner">
                    <div class="icon-wrap"><i class="fas fa-clipboard-check"></i></div>
                    <h3>Eligibility Application</h3>
                    <p>For private university graduates who need eligibility clearance before admission.</p>
                </div>
                <div class="card-body-inner">
                    <div>
                        <div class="window-badge">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $eligStart->format('d M Y') }} &ndash; {{ $eligEnd->format('d M Y') }}
                        </div>
                        <ul class="who-list">
                            <li>Private university graduates</li>
                            <li>Seeking eligibility clearance</li>
                            <li>Must not have existing approval</li>
                        </ul>
                    </div>
                    <span class="btn-apply-elig">
                        Apply for Eligibility &rarr;
                    </span>
                </div>
            </a>
            @endif

            {{-- ── Admission Application Card ── --}}
            @if($showAdmissionCard)
            <a href="{{ route('apply-admission') }}" class="apply-card adm">
                <div class="card-banner">
                    <div class="icon-wrap"><i class="fas fa-university"></i></div>
                    <h3>Admission Application</h3>
                    <p>For public university graduates, eligibility-approved applicants, or previously approved students.</p>
                </div>
                <div class="card-body-inner">
                    <div>
                        <div class="window-badge">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $admissionStart->format('d M Y') }} &ndash; {{ $admissionEnd->format('d M Y') }}
                        </div>
                        <ul class="who-list">
                            <li>Public university graduates</li>
                            <li>Private graduates with eligibility approval</li>
                            <li>Previously approved eligibility holders</li>
                        </ul>
                    </div>
                    <span class="btn-apply-adm">
                        Apply for Admission &rarr;
                    </span>
                </div>
            </a>
            @endif

        </div>
    @endif

</div>
@endsection
