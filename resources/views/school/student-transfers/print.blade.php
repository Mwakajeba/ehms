@extends('layouts.print')

@section('title', 'Transfer Record - ' . $transfer->transfer_number)

@section('content')
<div class="print-document">
    <!-- Official Header -->
    <div class="document-header">
        <!-- School Information Banner -->
        <table class="school-banner">
            <tr>
                <td class="school-logo-section">
                    @if($company && $company->logo)
                        <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="company-logo-main">
                    @else
                        <div class="company-logo-placeholder">
                            <span class="logo-text">{{ substr($company->name ?? 'SMS', 0, 1) }}</span>
                        </div>
                    @endif
                </td>
                <td class="school-info-section">
                    <h1 class="school-full-name">{{ $company->name ?? 'School Management System' }}</h1>
                    <div class="school-details">
                        <div class="school-address">
                            <i class="bx bx-map"></i>
                            <span>{{ $branch->address ?? $company->address ?? 'School Address' }}</span>
                        </div>
                        <div class="school-contact">
                            <i class="bx bx-phone"></i>
                            <span>{{ $branch->phone ?? $company->phone ?? '+123-456-7890' }}</span>
                            <i class="bx bx-envelope"></i>
                            <span>{{ $branch->email ?? $company->email ?? 'info@school.edu' }}</span>
                        </div>
                    </div>
                    <div class="school-motto">{{ $branch->location ?? $company->motto ?? 'Excellence in Education' }}</div>
                </td>
                <td class="document-seal">
                    <div class="seal-circle">
                        <span class="seal-icon">✓</span>
                    </div>
                    <div class="seal-text">OFFICIAL</div>
                </td>
            </tr>
        </table>

        <!-- Document Title Section -->
        <div class="document-title-section">
            <h2 class="document-main-title">STUDENT TRANSFER CERTIFICATE</h2>
            <div class="document-subtitle">Academic Year {{ $transfer->academicYear ? $transfer->academicYear->name : date('Y') }}</div>
            <div class="document-reference">
                <div class="ref-item">
                    <span class="ref-label">Certificate No:</span>
                    <span class="ref-value">{{ $transfer->transfer_number }}</span>
                </div>
                <div class="ref-item">
                    <span class="ref-label">Issue Date:</span>
                    <span class="ref-value">{{ now()->format('M d, Y') }}</span>
                </div>
                <div class="ref-item">
                    <span class="ref-label">Valid Until:</span>
                    <span class="ref-value">{{ now()->addYear()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="status-section">
            <div class="status-badge">
                <span class="status-icon">✓</span>
                <span>{{ ucfirst($transfer->status) }} Transfer</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="document-body">
        <!-- Transfer Information Section -->
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">↗</span> Transfer Information</h3>
            <table class="info-table">
                <tr>
                    <td class="info-label">Transfer Type:</td>
                    <td class="info-value">
                        <span class="transfer-type-badge {{ $transfer->transfer_type }}">
                            @switch($transfer->transfer_type)
                                @case('transfer_out') → Transfer Out @break
                                @case('transfer_in') ← Transfer In @break
                                @case('re_admission') ⟲ Re-admission @break
                            @endswitch
                        </span>
                    </td>
                    <td class="info-label">Transfer Date:</td>
                    <td class="info-value">{{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Certificate Number:</td>
                    <td class="info-value">{{ $transfer->transfer_certificate_number ?: 'N/A' }}</td>
                    <td class="info-label">Reason:</td>
                    <td class="info-value">{{ $transfer->reason ? ucfirst(str_replace('_', ' ', $transfer->reason)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Processed By:</td>
                    <td class="info-value">{{ $transfer->processedBy ? $transfer->processedBy->name : 'System' }}</td>
                    <td class="info-label">Record Date:</td>
                    <td class="info-value">{{ $transfer->created_at ? $transfer->created_at->format('F d, Y H:i') : 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Student Information Section -->
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">👤</span> Student Information</h3>
            <table class="student-info-table">
                <tr>
                    <td class="student-photo-cell">
                        @if($transfer->student && $transfer->student->passport_photo)
                            <img src="{{ asset('storage/' . $transfer->student->passport_photo) }}" alt="Student Photo" class="student-photo">
                        @else
                            <div class="student-photo-placeholder">
                                <span class="photo-icon">👤</span>
                            </div>
                        @endif
                    </td>
                    <td class="student-details-cell">
                        <div class="student-name">{{ $transfer->student ? $transfer->student->first_name . ' ' . $transfer->student->last_name : $transfer->student_name }}</div>
                        <div class="student-meta">
                            <span class="admission-no">Admission #: {{ $transfer->student ? $transfer->student->admission_number : 'N/A' }}</span>
                            <span class="class-info">
                                Class: {{ $transfer->student && $transfer->student->class ? $transfer->student->class->name : 'N/A' }}
                                {{ $transfer->student && $transfer->student->stream ? ' - ' . $transfer->student->stream->name : '' }}
                            </span>
                        </div>
                        @if($transfer->student)
                        <div class="student-additional">
                            <span class="detail-item">
                                <span class="label">Date of Birth:</span>
                                <span class="value">{{ $transfer->student->date_of_birth ? $transfer->student->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                            </span>
                            <span class="detail-item">
                                <span class="label">Gender:</span>
                                <span class="value">{{ ucfirst($transfer->student->gender ?? 'N/A') }}</span>
                            </span>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- School Transfer Details -->
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">🏫</span> Transfer Details</h3>
            <table class="transfer-details-table">
                <tr>
                    <td class="school-cell from-school">
                        <div class="school-label">From School:</div>
                        <div class="school-name">{{ $transfer->previous_school ?: 'N/A' }}</div>
                    </td>
                    <td class="arrow-cell">
                        <span class="transfer-arrow">→</span>
                    </td>
                    <td class="school-cell to-school">
                        <div class="school-label">To School:</div>
                        <div class="school-name">{{ $transfer->new_school ?: 'N/A' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Academic Records -->
        @if($transfer->academic_records)
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">📚</span> Academic Records</h3>
            <div class="content-box">
                <div class="content-text">
                    {!! nl2br(e($transfer->academic_records)) !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Notes -->
        @if($transfer->notes)
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">📝</span> Additional Notes</h3>
            <div class="content-box">
                <div class="content-text">
                    {!! nl2br(e($transfer->notes)) !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Transfer History -->
        @if($transfer->student && $transfer->student->transfers->count() > 1)
        <div class="info-section">
            <h3 class="section-title"><span class="section-icon">📋</span> Transfer History</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>From School</th>
                        <th>To School</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->student->transfers->sortByDesc('transfer_date') as $historyTransfer)
                    <tr class="{{ $historyTransfer->id === $transfer->id ? 'current-transfer' : '' }}">
                        <td>{{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @switch($historyTransfer->transfer_type)
                                @case('transfer_out') Transfer Out @break
                                @case('transfer_in') Transfer In @break
                                @case('re_admission') Re-admission @break
                            @endswitch
                        </td>
                        <td>{{ $historyTransfer->previous_school ?: 'N/A' }}</td>
                        <td>{{ $historyTransfer->new_school ?: 'N/A' }}</td>
                        <td>
                            <span class="status-indicator {{ $historyTransfer->status }}">
                                {{ ucfirst($historyTransfer->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Document Footer -->
    <div class="document-footer">
        <!-- Certification Statement -->
        <div class="certification-section">
            <div class="certification-text">
                <strong>Certification:</strong> This transfer certificate is issued in accordance with school transfer regulations and policies. All information contained herein is accurate and verified by authorized school personnel.
            </div>
            <div class="certification-note">
                <em>This document serves as official proof of student transfer and should be presented to the receiving institution.</em>
            </div>
        </div>

        <!-- Signatures -->
        <table class="signatures-table">
            <tr>
                <td class="signature-cell">
                    <div class="signature-line">_______________________________</div>
                    <div class="signature-label">Authorized School Official</div>
                    <div class="signature-title">Transfer Coordinator</div>
                    <div class="signature-date">{{ now()->format('M d, Y') }}</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-line">_______________________________</div>
                    <div class="signature-label">School Principal/Director</div>
                    <div class="signature-title">Head of Institution</div>
                    <div class="signature-date">{{ now()->format('M d, Y') }}</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-line">_______________________________</div>
                    <div class="signature-label">Student/Parent Acknowledgment</div>
                    <div class="signature-title">Received By</div>
                    <div class="signature-date">________________</div>
                </td>
            </tr>
        </table>

        <!-- School Information -->
        <div class="school-info-footer">
            <table class="school-info-table">
                <tr>
                    <td class="school-info-cell">
                        <strong>School Name:</strong> {{ $company->name ?? 'School Management System' }}
                    </td>
                    <td class="school-info-cell">
                        <strong>Registration No:</strong> {{ $company->license_number ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td class="school-info-cell">
                        <strong>Address:</strong> {{ $branch->address ?? $company->address ?? 'School Address' }}
                    </td>
                    <td class="school-info-cell">
                        <strong>Phone:</strong> {{ $branch->phone ?? $company->phone ?? '+123-456-7890' }}
                    </td>
                </tr>
                <tr>
                    <td class="school-info-cell">
                        <strong>Email:</strong> {{ $branch->email ?? $company->email ?? 'info@school.edu' }}
                    </td>
                    <td class="school-info-cell">
                        <strong>Branch:</strong> {{ $branch->name ?? $branch->branch_name ?? 'Main Branch' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Border -->
        <div class="footer-border">
            <div class="footer-text">
                Generated by {{ $company->name ?? 'School Management System' }} • Transfer ID: {{ $transfer->id }} • {{ now()->format('F d, Y \a\t H:i') }} • Page 1 of 1
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .print-document {
        max-width: 210mm;
        margin: 0 auto;
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.4;
        color: #000000;
        background: white;
    }

    /* Header Styles */
    .document-header {
        border-bottom: 2px solid #000000;
        padding: 20px;
        margin-bottom: 20px;
    }

    /* School Banner Table */
    .school-banner {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border: 1px solid #cccccc;
    }

    .school-banner td {
        padding: 15px;
        vertical-align: top;
    }

    .school-logo-section {
        width: 150px;
        text-align: center;
    }

    .company-logo-main {
        width: 120px;
        height: 120px;
        border: 2px solid #000000;
        object-fit: cover;
    }

    .company-logo-placeholder {
        width: 120px;
        height: 120px;
        border: 2px solid #000000;
        background: #f0f0f0;
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }

    .logo-text {
        font-size: 48pt;
        font-weight: bold;
        color: #333333;
    }

    .school-info-section {
        text-align: center;
    }

    .school-full-name {
        font-size: 24pt;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #000000;
    }

    .school-details {
        margin-bottom: 8px;
    }

    .school-address,
    .school-contact {
        display: block;
        font-size: 10pt;
        color: #666666;
        margin-bottom: 4px;
    }

    .school-motto {
        font-size: 11pt;
        color: #333333;
        font-style: italic;
        font-weight: bold;
        border-top: 1px solid #cccccc;
        padding-top: 6px;
    }

    .document-seal {
        width: 80px;
        text-align: center;
    }

    .seal-circle {
        width: 50px;
        height: 50px;
        border: 2px solid #000000;
        border-radius: 50%;
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        margin: 0 auto 6px;
    }

    .seal-icon {
        font-size: 24pt;
        font-weight: bold;
        color: #000000;
    }

    .seal-text {
        font-size: 8pt;
        font-weight: bold;
        color: #000000;
        text-transform: uppercase;
    }

    /* Document Title Section */
    .document-title-section {
        text-align: center;
        margin-bottom: 15px;
    }

    .document-main-title {
        font-size: 18pt;
        font-weight: bold;
        margin: 0 0 6px 0;
        color: #000000;
        text-decoration: underline;
    }

    .document-subtitle {
        font-size: 12pt;
        color: #666666;
        font-weight: bold;
    }

    .document-reference {
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #cccccc;
        background: #f9f9f9;
    }

    .ref-item {
        display: block;
        margin-bottom: 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #cccccc;
    }

    .ref-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
    }

    .ref-label {
        font-size: 10pt;
        color: #666666;
        font-weight: bold;
    }

    .ref-value {
        font-size: 11pt;
        color: #000000;
        font-weight: bold;
        margin-left: 10px;
    }

    .status-section {
        text-align: center;
        margin-top: 15px;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        background: #000000;
        color: white;
        font-size: 11pt;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #000000;
    }

    .status-icon {
        margin-right: 6px;
    }

    /* Body Styles */
    .document-body {
        padding: 0 15px;
    }

    .info-section {
        margin-bottom: 20px;
        page-break-inside: avoid;
    }

    .section-title {
        font-size: 14pt;
        color: #000000;
        margin: 0 0 10px 0;
        padding-bottom: 6px;
        border-bottom: 1px solid #000000;
        font-weight: bold;
    }

    .section-icon {
        margin-right: 8px;
    }

    /* Info Table */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cccccc;
        margin-bottom: 15px;
    }

    .info-table td {
        padding: 8px;
        border: 1px solid #cccccc;
        vertical-align: top;
    }

    .info-label {
        font-size: 11pt;
        color: #666666;
        font-weight: bold;
        width: 25%;
    }

    .info-value {
        font-size: 11pt;
        color: #000000;
        font-weight: normal;
    }

    .transfer-type-badge {
        padding: 4px 8px;
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #000000;
    }

    .transfer-type-badge.transfer_out {
        background: #ffffff;
        color: #000000;
    }

    .transfer-type-badge.transfer_in {
        background: #ffffff;
        color: #000000;
    }

    .transfer-type-badge.re_admission {
        background: #ffffff;
        color: #000000;
    }

    /* Student Info Table */
    .student-info-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cccccc;
    }

    .student-info-table td {
        padding: 15px;
        border: 1px solid #cccccc;
        vertical-align: top;
    }

    .student-photo-cell {
        width: 120px;
        text-align: center;
    }

    .student-photo {
        width: 100px;
        height: 100px;
        border: 2px solid #000000;
        object-fit: cover;
    }

    .student-photo-placeholder {
        width: 100px;
        height: 100px;
        border: 2px solid #000000;
        background: #f0f0f0;
        display: table-cell;
        vertical-align: middle;
        text-align: center;
    }

    .photo-icon {
        font-size: 36pt;
    }

    .student-details-cell {
        padding-left: 20px;
    }

    .student-name {
        font-size: 16pt;
        font-weight: bold;
        color: #000000;
        margin-bottom: 8px;
    }

    .student-meta {
        margin-bottom: 10px;
    }

    .admission-no,
    .class-info {
        display: block;
        font-size: 10pt;
        color: #666666;
        background: #f0f0f0;
        padding: 4px 8px;
        border: 1px solid #cccccc;
        margin-bottom: 4px;
    }

    .student-additional {
        font-size: 10pt;
    }

    .detail-item {
        display: block;
        margin-bottom: 4px;
    }

    .detail-item .label {
        color: #666666;
        font-weight: bold;
        margin-right: 8px;
    }

    .detail-item .value {
        color: #000000;
    }

    /* Transfer Details Table */
    .transfer-details-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cccccc;
    }

    .transfer-details-table td {
        padding: 15px;
        border: 1px solid #cccccc;
        vertical-align: top;
        text-align: center;
    }

    .school-cell {
        width: 40%;
    }

    .arrow-cell {
        width: 20%;
        text-align: center;
    }

    .school-label {
        font-size: 10pt;
        color: #666666;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .school-name {
        font-size: 12pt;
        font-weight: bold;
        color: #000000;
        background: #f9f9f9;
        padding: 10px;
        border: 1px solid #cccccc;
        min-height: 40px;
        display: table-cell;
        vertical-align: middle;
    }

    .transfer-arrow {
        font-size: 18pt;
        font-weight: bold;
        color: #000000;
    }

    /* Content Box */
    .content-box {
        border: 1px solid #cccccc;
        padding: 15px;
        background: #f9f9f9;
    }

    .content-text {
        font-size: 11pt;
        line-height: 1.5;
        color: #000000;
        white-space: pre-wrap;
    }

    /* History Table */
    .history-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-size: 10pt;
    }

    .history-table th {
        background: #000000;
        color: white;
        padding: 8px 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #000000;
    }

    .history-table td {
        padding: 6px;
        border: 1px solid #cccccc;
        background: white;
    }

    .history-table tr:nth-child(even) td {
        background: #f9f9f9;
    }

    .current-transfer td {
        background: #e8f5e8 !important;
        font-weight: bold;
    }

    .status-indicator {
        padding: 3px 6px;
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #000000;
    }

    .status-indicator.completed {
        background: white;
        color: #000000;
    }

    .status-indicator.pending {
        background: white;
        color: #000000;
    }

    .status-indicator.approved {
        background: white;
        color: #000000;
    }

    /* Footer */
    .document-footer {
        margin-top: 30px;
        border-top: 1px solid #000000;
        padding-top: 15px;
    }

    .certification-section {
        margin-bottom: 20px;
        text-align: center;
    }

    .certification-text {
        font-size: 11pt;
        line-height: 1.4;
        color: #000000;
        margin-bottom: 8px;
    }

    .certification-note {
        font-size: 10pt;
        color: #666666;
        font-style: italic;
    }

    /* Signatures Table */
    .signatures-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .signatures-table td {
        padding: 10px;
        vertical-align: top;
        text-align: center;
        width: 33%;
    }

    .signature-line {
        border-bottom: 1px solid #000000;
        margin-bottom: 6px;
        font-size: 10pt;
    }

    .signature-label {
        font-size: 10pt;
        color: #000000;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .signature-title {
        font-size: 9pt;
        color: #666666;
        margin-bottom: 2px;
    }

    .signature-date {
        font-size: 9pt;
        color: #666666;
    }

    /* School Info Footer */
    .school-info-footer {
        margin-bottom: 15px;
    }

    .school-info-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cccccc;
    }

    .school-info-table td {
        padding: 6px 8px;
        border: 1px solid #cccccc;
        font-size: 10pt;
    }

    .school-info-cell {
        width: 50%;
    }

    .school-info-cell strong {
        color: #000000;
        margin-right: 6px;
    }

    .footer-border {
        border-top: 1px solid #000000;
        padding-top: 10px;
        text-align: center;
    }

    .footer-text {
        font-size: 9pt;
        color: #666666;
    }

    /* Print Media Queries */
    @media print {
        body {
            margin: 0;
            padding: 0;
            background: white;
        }

        .print-document {
            padding: 10mm;
        }

        .info-section {
            page-break-inside: avoid;
        }

        .student-info-table {
            page-break-inside: avoid;
        }

        .history-table {
            page-break-inside: avoid;
        }

        .document-footer {
            page-break-inside: avoid;
        }
    }
</style>
@endpush