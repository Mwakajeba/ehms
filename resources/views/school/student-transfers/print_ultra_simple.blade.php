@extends('layouts.print')

@section('title', 'Transfer Record - ' . $transfer->transfer_number)

@section('content')
<div style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.4; color: #000000; max-width: 210mm; margin: 0 auto; background: white; padding: 20px;">

    <!-- Header -->
    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 20px; margin-bottom: 20px;">
        <!-- Logo and School Info -->
        <div style="margin-bottom: 15px;">
            @if($company && $company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Company Logo" style="width: 120px; height: 120px; border: 2px solid #000000; object-fit: cover;">
            @else
                <div style="display: inline-block; width: 120px; height: 120px; border: 2px solid #000000; background: #f0f0f0; text-align: center; line-height: 120px;">
                    <span style="font-size: 48pt; font-weight: bold; color: #333333;">{{ substr($company->name ?? 'SMS', 0, 1) }}</span>
                </div>
            @endif
        </div>

        <h1 style="font-size: 24pt; font-weight: bold; margin: 0 0 10px 0; color: #000000;">{{ $company->name ?? 'School Management System' }}</h1>
        <div style="margin-bottom: 8px;">
            <div style="display: block; font-size: 10pt; color: #666666; margin-bottom: 4px;">
                <span>{{ $branch->address ?? $company->address ?? 'School Address' }}</span>
            </div>
            <div style="display: block; font-size: 10pt; color: #666666; margin-bottom: 4px;">
                <span>{{ $branch->phone ?? $company->phone ?? '+123-456-7890' }}</span> |
                <span>{{ $branch->email ?? $company->email ?? 'info@school.edu' }}</span>
            </div>
        </div>
        <div style="font-size: 11pt; color: #333333; font-style: italic; font-weight: bold; border-top: 1px solid #cccccc; padding-top: 6px; margin-bottom: 15px;">{{ $branch->location ?? $company->motto ?? 'Excellence in Education' }}</div>

        <!-- Document Title Section -->
        <h2 style="font-size: 18pt; font-weight: bold; margin: 0 0 6px 0; color: #000000; text-decoration: underline;">STUDENT TRANSFER CERTIFICATE</h2>
        <div style="font-size: 12pt; color: #666666; font-weight: bold;">Academic Year {{ $transfer->academicYear ? $transfer->academicYear->name : date('Y') }}</div>

        <!-- Certificate Reference -->
        <div style="margin-top: 15px; padding: 10px; border: 1px solid #cccccc; background: #f9f9f9;">
            <div style="margin-bottom: 6px;">
                <span style="font-size: 10pt; color: #666666; font-weight: bold;">Certificate No:</span>
                <span style="font-size: 11pt; color: #000000; font-weight: bold; margin-left: 10px;">{{ $transfer->transfer_number }}</span>
            </div>
            <div style="margin-bottom: 6px;">
                <span style="font-size: 10pt; color: #666666; font-weight: bold;">Issue Date:</span>
                <span style="font-size: 11pt; color: #000000; font-weight: bold; margin-left: 10px;">{{ now()->format('M d, Y') }}</span>
            </div>
            <div>
                <span style="font-size: 10pt; color: #666666; font-weight: bold;">Valid Until:</span>
                <span style="font-size: 11pt; color: #000000; font-weight: bold; margin-left: 10px;">{{ now()->addYear()->format('M d, Y') }}</span>
            </div>
        </div>

        <!-- Status Badge -->
        <div style="text-align: center; margin-top: 15px;">
            <div style="display: inline-block; padding: 8px 16px; background: #000000; color: white; font-size: 11pt; font-weight: bold; text-transform: uppercase; border: 1px solid #000000;">
                <span style="margin-right: 6px;">✓</span>
                <span>{{ ucfirst($transfer->status) }} Transfer</span>
            </div>
        </div>
    </div>

    <!-- Certificate Details -->
    <div style="margin-bottom: 20px;">
        <p><strong>Certificate No:</strong> {{ $transfer->transfer_number }}</p>
        <p><strong>Issue Date:</strong> {{ now()->format('M d, Y') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($transfer->status) }} Transfer</p>
    </div>

    <!-- Transfer Information -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer Information</h3>
        <p><strong>Transfer Type:</strong>
            @switch($transfer->transfer_type)
                @case('transfer_out') Transfer Out @break
                @case('transfer_in') Transfer In @break
                @case('re_admission') Re-admission @break
            @endswitch
        </p>
        <p><strong>Transfer Date:</strong> {{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</p>
        <p><strong>Certificate Number:</strong> {{ $transfer->transfer_certificate_number ?: 'N/A' }}</p>
        <p><strong>Reason:</strong> {{ $transfer->reason ? ucfirst(str_replace('_', ' ', $transfer->reason)) : 'N/A' }}</p>
        <p><strong>Processed By:</strong> {{ $transfer->processedBy ? $transfer->processedBy->name : 'System' }}</p>
        <p><strong>Record Date:</strong> {{ $transfer->created_at ? $transfer->created_at->format('F d, Y H:i') : 'N/A' }}</p>
    </div>

    <!-- Student Information -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Student Information</h3>
        <p style="font-size: 16pt; font-weight: bold;">{{ $transfer->student ? $transfer->student->first_name . ' ' . $transfer->student->last_name : $transfer->student_name }}</p>
        <p><strong>Admission Number:</strong> {{ $transfer->student ? $transfer->student->admission_number : 'N/A' }}</p>
        <p><strong>Class:</strong> {{ $transfer->student && $transfer->student->class ? $transfer->student->class->name : 'N/A' }}
            {{ $transfer->student && $transfer->student->stream ? ' - ' . $transfer->student->stream->name : '' }}</p>
        @if($transfer->student)
        <p><strong>Date of Birth:</strong> {{ $transfer->student->date_of_birth ? $transfer->student->date_of_birth->format('M d, Y') : 'N/A' }}</p>
        <p><strong>Gender:</strong> {{ ucfirst($transfer->student->gender ?? 'N/A') }}</p>
        @endif
    </div>

    <!-- Transfer Details -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer Details</h3>
        <div style="margin-bottom: 10px;">
            <strong>From School:</strong> {{ $transfer->previous_school ?: 'N/A' }}
        </div>
        <div style="text-align: center; font-size: 18pt; margin: 10px 0;">
            →
        </div>
        <div>
            <strong>To School:</strong> {{ $transfer->new_school ?: 'N/A' }}
        </div>
    </div>

    <!-- Academic Records -->
    @if($transfer->academic_records)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Academic Records</h3>
        <div style="border: 1px solid #000000; padding: 10px;">
            {!! nl2br(e($transfer->academic_records)) !!}
        </div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($transfer->notes)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Additional Notes</h3>
        <div style="border: 1px solid #000000; padding: 10px;">
            {!! nl2br(e($transfer->notes)) !!}
        </div>
    </div>
    @endif

    <!-- Transfer History -->
    @if($transfer->student && $transfer->student->transfers->count() > 1)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer History</h3>
        @foreach($transfer->student->transfers->sortByDesc('transfer_date') as $historyTransfer)
        <div style="margin-bottom: 10px; padding: 8px; border: 1px solid #cccccc; {{ $historyTransfer->id === $transfer->id ? 'background: #f0f0f0; font-weight: bold;' : '' }}">
            <p><strong>Date:</strong> {{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}</p>
            <p><strong>Type:</strong>
                @switch($historyTransfer->transfer_type)
                    @case('transfer_out') Transfer Out @break
                    @case('transfer_in') Transfer In @break
                    @case('re_admission') Re-admission @break
                @endswitch
            </p>
            <p><strong>From:</strong> {{ $historyTransfer->previous_school ?: 'N/A' }}</p>
            <p><strong>To:</strong> {{ $historyTransfer->new_school ?: 'N/A' }}</p>
            <p><strong>Status:</strong> {{ ucfirst($historyTransfer->status) }}</p>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Certification and Signatures -->
    <div style="margin-top: 40px;">
        <!-- Certification Statement -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 11pt; line-height: 1.4; color: #000000; margin-bottom: 8px;">
                <strong>Certification:</strong> This transfer certificate is issued in accordance with school transfer regulations and policies. All information contained herein is accurate and verified by authorized school personnel.
            </div>
            <div style="font-size: 10pt; color: #666666; font-style: italic;">
                <em>This document serves as official proof of student transfer and should be presented to the receiving institution.</em>
            </div>
        </div>

        <!-- Signatures -->
        <div style="margin-bottom: 20px;">
            <div style="width: 32%; float: left; text-align: center; padding: 10px;">
                <div style="border-bottom: 1px solid #000000; margin-bottom: 6px; font-size: 10pt;"></div>
                <div style="font-size: 10pt; color: #000000; font-weight: bold; margin-bottom: 4px;">Authorized School Official</div>
                <div style="font-size: 9pt; color: #666666; margin-bottom: 2px;">Transfer Coordinator</div>
                <div style="font-size: 9pt; color: #666666;">{{ now()->format('M d, Y') }}</div>
            </div>
            <div style="width: 32%; float: left; margin-left: 2%; text-align: center; padding: 10px;">
                <div style="border-bottom: 1px solid #000000; margin-bottom: 6px; font-size: 10pt;"></div>
                <div style="font-size: 10pt; color: #000000; font-weight: bold; margin-bottom: 4px;">School Principal/Director</div>
                <div style="font-size: 9pt; color: #666666; margin-bottom: 2px;">Head of Institution</div>
                <div style="font-size: 9pt; color: #666666;">{{ now()->format('M d, Y') }}</div>
            </div>
            <div style="width: 32%; float: left; margin-left: 2%; text-align: center; padding: 10px;">
                <div style="border-bottom: 1px solid #000000; margin-bottom: 6px; font-size: 10pt;"></div>
                <div style="font-size: 10pt; color: #000000; font-weight: bold; margin-bottom: 4px;">Student/Parent Acknowledgment</div>
                <div style="font-size: 9pt; color: #666666; margin-bottom: 2px;">Received By</div>
                <div style="font-size: 9pt; color: #666666;">________________</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- School Information -->
        <div style="margin-bottom: 15px;">
            <div style="margin-bottom: 8px;">
                <strong style="font-size: 10pt;">School Name:</strong> <span style="font-size: 10pt;">{{ $company->name ?? 'School Management System' }}</span>
                <span style="margin-left: 20px;"><strong style="font-size: 10pt;">Registration No:</strong> <span style="font-size: 10pt;">{{ $company->license_number ?? 'N/A' }}</span></span>
            </div>
            <div style="margin-bottom: 8px;">
                <strong style="font-size: 10pt;">Address:</strong> <span style="font-size: 10pt;">{{ $branch->address ?? $company->address ?? 'School Address' }}</span>
                <span style="margin-left: 20px;"><strong style="font-size: 10pt;">Phone:</strong> <span style="font-size: 10pt;">{{ $branch->phone ?? $company->phone ?? '+123-456-7890' }}</span></span>
            </div>
            <div>
                <strong style="font-size: 10pt;">Email:</strong> <span style="font-size: 10pt;">{{ $branch->email ?? $company->email ?? 'info@school.edu' }}</span>
                <span style="margin-left: 20px;"><strong style="font-size: 10pt;">Branch:</strong> <span style="font-size: 10pt;">{{ $branch->name ?? $branch->branch_name ?? 'Main Branch' }}</span></span>
            </div>
        </div>

        <!-- Footer Border -->
        <div style="border-top: 1px solid #000000; padding-top: 10px; text-align: center;">
            <div style="font-size: 9pt; color: #666666;">
                Generated by {{ $company->name ?? 'School Management System' }} • Transfer ID: {{ $transfer->id }} • {{ now()->format('F d, Y \a\t H:i') }} • Page 1 of 1
            </div>
        </div>
    </div>
</div>
@endsection