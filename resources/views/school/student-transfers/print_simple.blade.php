@extends('layouts.print')

@section('title', 'Transfer Record - ' . $transfer->transfer_number)

@section('content')
<div style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.4; color: #000000; max-width: 210mm; margin: 0 auto; background: white; padding: 20px;">

    <!-- Header -->
    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 20px; margin-bottom: 20px;">
        <h1 style="font-size: 24pt; font-weight: bold; margin: 0 0 10px 0;">{{ $company->name ?? 'School Management System' }}</h1>
        <p style="font-size: 14pt; margin: 5px 0;">{{ $branch->address ?? $company->address ?? 'School Address' }}</p>
        <p style="font-size: 12pt; margin: 5px 0;">Phone: {{ $branch->phone ?? $company->phone ?? '+123-456-7890' }} | Email: {{ $branch->email ?? $company->email ?? 'info@school.edu' }}</p>
        <h2 style="font-size: 18pt; font-weight: bold; margin: 20px 0; text-decoration: underline;">STUDENT TRANSFER CERTIFICATE</h2>
        <p style="font-size: 12pt; font-weight: bold;">Academic Year: {{ $transfer->academicYear ? $transfer->academicYear->name : date('Y') }}</p>
    </div>

    <!-- Certificate Details -->
    <div style="margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold; width: 30%;">Certificate No:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->transfer_number }}</td>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold; width: 30%;">Issue Date:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ now()->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Status:</td>
                <td colspan="3" style="padding: 8px; border: 1px solid #000000; font-weight: bold; text-transform: uppercase;">{{ ucfirst($transfer->status) }} Transfer</td>
            </tr>
        </table>
    </div>

    <!-- Transfer Information -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold; width: 25%;">Transfer Type:</td>
                <td style="padding: 8px; border: 1px solid #000000;">
                    @switch($transfer->transfer_type)
                        @case('transfer_out') Transfer Out @break
                        @case('transfer_in') Transfer In @break
                        @case('re_admission') Re-admission @break
                    @endswitch
                </td>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold; width: 25%;">Transfer Date:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Certificate Number:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->transfer_certificate_number ?: 'N/A' }}</td>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Reason:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->reason ? ucfirst(str_replace('_', ' ', $transfer->reason)) : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Processed By:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->processedBy ? $transfer->processedBy->name : 'System' }}</td>
                <td style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Record Date:</td>
                <td style="padding: 8px; border: 1px solid #000000;">{{ $transfer->created_at ? $transfer->created_at->format('F d, Y H:i') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Student Information -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Student Information</h3>
        <div style="border: 1px solid #000000; padding: 15px;">
            <div style="font-size: 16pt; font-weight: bold; margin-bottom: 10px;">{{ $transfer->student ? $transfer->student->first_name . ' ' . $transfer->student->last_name : $transfer->student_name }}</div>
            <div style="margin-bottom: 8px;">
                <strong>Admission Number:</strong> {{ $transfer->student ? $transfer->student->admission_number : 'N/A' }}
            </div>
            <div style="margin-bottom: 8px;">
                <strong>Class:</strong> {{ $transfer->student && $transfer->student->class ? $transfer->student->class->name : 'N/A' }}
                {{ $transfer->student && $transfer->student->stream ? ' - ' . $transfer->student->stream->name : '' }}
            </div>
            @if($transfer->student)
            <div style="margin-bottom: 8px;">
                <strong>Date of Birth:</strong> {{ $transfer->student->date_of_birth ? $transfer->student->date_of_birth->format('M d, Y') : 'N/A' }}
            </div>
            <div>
                <strong>Gender:</strong> {{ ucfirst($transfer->student->gender ?? 'N/A') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Transfer Details -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 20px; border: 1px solid #000000; text-align: center; width: 40%;">
                    <div style="font-weight: bold; margin-bottom: 10px;">From School:</div>
                    <div style="font-size: 14pt;">{{ $transfer->previous_school ?: 'N/A' }}</div>
                </td>
                <td style="padding: 20px; border: 1px solid #000000; text-align: center; width: 20%; font-size: 24pt; font-weight: bold;">
                    →
                </td>
                <td style="padding: 20px; border: 1px solid #000000; text-align: center; width: 40%;">
                    <div style="font-weight: bold; margin-bottom: 10px;">To School:</div>
                    <div style="font-size: 14pt;">{{ $transfer->new_school ?: 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Academic Records -->
    @if($transfer->academic_records)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Academic Records</h3>
        <div style="border: 1px solid #000000; padding: 15px; min-height: 60px;">
            {!! nl2br(e($transfer->academic_records)) !!}
        </div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($transfer->notes)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Additional Notes</h3>
        <div style="border: 1px solid #000000; padding: 15px; min-height: 60px;">
            {!! nl2br(e($transfer->notes)) !!}
        </div>
    </div>
    @endif

    <!-- Transfer History -->
    @if($transfer->student && $transfer->student->transfers->count() > 1)
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14pt; font-weight: bold; margin: 0 0 10px 0; border-bottom: 1px solid #000000; padding-bottom: 5px;">Transfer History</h3>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
            <thead>
                <tr style="background: #000000; color: white;">
                    <th style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Date</th>
                    <th style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Type</th>
                    <th style="padding: 8px; border: 1px solid #000000; font-weight: bold;">From School</th>
                    <th style="padding: 8px; border: 1px solid #000000; font-weight: bold;">To School</th>
                    <th style="padding: 8px; border: 1px solid #000000; font-weight: bold;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->student->transfers->sortByDesc('transfer_date') as $historyTransfer)
                <tr style="{{ $historyTransfer->id === $transfer->id ? 'background: #f0f0f0; font-weight: bold;' : '' }}">
                    <td style="padding: 6px; border: 1px solid #000000;">{{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #000000;">
                        @switch($historyTransfer->transfer_type)
                            @case('transfer_out') Transfer Out @break
                            @case('transfer_in') Transfer In @break
                            @case('re_admission') Re-admission @break
                        @endswitch
                    </td>
                    <td style="padding: 6px; border: 1px solid #000000;">{{ $historyTransfer->previous_school ?: 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #000000;">{{ $historyTransfer->new_school ?: 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #000000;">{{ ucfirst($historyTransfer->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Certification and Signatures -->
    <div style="margin-top: 40px;">
        <div style="text-align: center; margin-bottom: 30px; font-size: 11pt;">
            <p><strong>Certification:</strong> This transfer certificate is issued in accordance with school transfer regulations and policies. All information contained herein is accurate and verified by authorized school personnel.</p>
            <p style="font-style: italic;">This document serves as official proof of student transfer and should be presented to the receiving institution.</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 20px; text-align: center; width: 33%;">
                    <div style="border-bottom: 1px solid #000000; margin-bottom: 10px;"></div>
                    <div style="font-size: 11pt; font-weight: bold;">Authorized School Official</div>
                    <div style="font-size: 10pt;">Transfer Coordinator</div>
                    <div style="font-size: 10pt; margin-top: 5px;">{{ now()->format('M d, Y') }}</div>
                </td>
                <td style="padding: 20px; text-align: center; width: 33%;">
                    <div style="border-bottom: 1px solid #000000; margin-bottom: 10px;"></div>
                    <div style="font-size: 11pt; font-weight: bold;">School Principal/Director</div>
                    <div style="font-size: 10pt;">Head of Institution</div>
                    <div style="font-size: 10pt; margin-top: 5px;">{{ now()->format('M d, Y') }}</div>
                </td>
                <td style="padding: 20px; text-align: center; width: 33%;">
                    <div style="border-bottom: 1px solid #000000; margin-bottom: 10px;"></div>
                    <div style="font-size: 11pt; font-weight: bold;">Student/Parent Acknowledgment</div>
                    <div style="font-size: 10pt;">Received By</div>
                    <div style="font-size: 10pt; margin-top: 5px;">________________</div>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div style="border-top: 1px solid #000000; padding-top: 15px; text-align: center; font-size: 9pt; color: #666666;">
            Generated by {{ $company->name ?? 'School Management System' }} • Transfer ID: {{ $transfer->id }} • {{ now()->format('F d, Y \a\t H:i') }} • Page 1 of 1
        </div>
    </div>
</div>
@endsection