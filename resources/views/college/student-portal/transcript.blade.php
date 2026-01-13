@extends('layouts.main')

@section('title', 'Academic Transcript')

@section('content')
<style>
    .transcript-page {
        margin-left: 250px;
        padding: 20px 30px;
        background: #f8fafc;
        min-height: 100vh;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        margin-top: 70px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .breadcrumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb-btn:hover {
        background: #3b82f6;
        border-color: transparent;
        color: white;
    }

    .breadcrumb-btn.active {
        background: #8b5cf6;
        border-color: transparent;
        color: white;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #f59e0b;
        font-size: 20px;
        font-weight: bold;
    }

    .transcript-container {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-width: 900px;
        margin: 0 auto;
    }

    .transcript-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        color: white;
        padding: 40px;
        text-align: center;
        position: relative;
    }

    .transcript-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.5;
    }

    .transcript-header-content {
        position: relative;
        z-index: 1;
    }

    .university-logo {
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 50%;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .university-logo i {
        font-size: 40px;
        color: #1e3a5f;
    }

    .university-name {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .document-title {
        font-size: 18px;
        font-weight: 500;
        opacity: 0.9;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .student-info-section {
        padding: 30px 40px;
        border-bottom: 2px solid #e5e7eb;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-item label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-item .value {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }

    .semester-block {
        border-bottom: 1px solid #e5e7eb;
    }

    .semester-title-bar {
        background: #f8fafc;
        padding: 15px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .semester-name {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .semester-gpa-info {
        display: flex;
        gap: 20px;
    }

    .gpa-item {
        text-align: right;
    }

    .gpa-item label {
        font-size: 10px;
        color: #64748b;
        text-transform: uppercase;
        display: block;
    }

    .gpa-item .value {
        font-size: 16px;
        font-weight: 700;
        color: #3b82f6;
    }

    .courses-table {
        width: 100%;
        border-collapse: collapse;
    }

    .courses-table th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        background: #fafafa;
        border-bottom: 1px solid #e5e7eb;
    }

    .courses-table td {
        padding: 12px 20px;
        font-size: 13px;
        color: #374151;
        border-bottom: 1px solid #f1f5f9;
    }

    .courses-table .course-code {
        font-weight: 600;
        color: #1e293b;
    }

    .courses-table .grade {
        font-weight: 700;
        text-align: center;
    }

    .courses-table .grade.a { color: #10b981; }
    .courses-table .grade.b { color: #3b82f6; }
    .courses-table .grade.c { color: #f59e0b; }
    .courses-table .grade.d { color: #f97316; }
    .courses-table .grade.f { color: #ef4444; }

    .summary-section {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        color: white;
        padding: 30px 40px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .summary-item {
        text-align: center;
    }

    .summary-item label {
        font-size: 11px;
        opacity: 0.8;
        text-transform: uppercase;
        display: block;
        margin-bottom: 5px;
    }

    .summary-item .value {
        font-size: 32px;
        font-weight: 800;
    }

    .summary-item .value.cgpa {
        color: #fbbf24;
    }

    .print-actions {
        padding: 20px 40px;
        text-align: center;
        background: #f8fafc;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    @media print {
        .transcript-page {
            margin-left: 0;
            padding: 0;
            background: white;
        }

        .breadcrumb-nav,
        .print-actions {
            display: none !important;
        }

        .transcript-container {
            box-shadow: none;
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .transcript-page {
            margin-left: 0;
            padding: 15px;
        }

        .student-info-section {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .semester-title-bar {
            flex-direction: column;
            gap: 10px;
            padding: 15px 20px;
        }

        .summary-section {
            padding: 20px;
        }

        .courses-table th,
        .courses-table td {
            padding: 10px;
            font-size: 12px;
        }
    }
</style>

<div class="transcript-page">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i> Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="#" class="breadcrumb-btn">
            <i class='bx bx-user'></i> Student Portal
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-file'></i> Transcript
        </span>
    </nav>

    <div class="transcript-container">
        <!-- Header -->
        <div class="transcript-header">
            <div class="transcript-header-content">
                <div class="university-logo">
                    <i class='bx bxs-graduation'></i>
                </div>
                <div class="university-name">{{ config('app.name', 'University Name') }}</div>
                <div class="document-title">Academic Transcript</div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="student-info-section">
            <div class="info-item">
                <label>Student Name</label>
                <div class="value">{{ $student->full_name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <label>Student Number</label>
                <div class="value">{{ $student->student_number ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <label>Program</label>
                <div class="value">{{ $student->program->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <label>Date Issued</label>
                <div class="value">{{ now()->format('F d, Y') }}</div>
            </div>
        </div>

        <!-- Semester Results -->
        @if(count($semesterData) > 0)
            @foreach($semesterData as $key => $data)
                <div class="semester-block">
                    <div class="semester-title-bar">
                        <div class="semester-name">{{ $data['academic_year'] }} - {{ $data['semester'] }}</div>
                        <div class="semester-gpa-info">
                            <div class="gpa-item">
                                <label>Credits</label>
                                <span class="value">{{ $data['credit_hours'] }}</span>
                            </div>
                            <div class="gpa-item">
                                <label>GPA</label>
                                <span class="value">{{ number_format($data['gpa'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th style="text-align: center;">Credits</th>
                                <th style="text-align: center;">Grade</th>
                                <th style="text-align: center;">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['results'] as $result)
                                <tr>
                                    <td class="course-code">{{ $result->course->code ?? '' }}</td>
                                    <td>{{ $result->course->name ?? '' }}</td>
                                    <td style="text-align: center;">{{ $result->credit_hours }}</td>
                                    <td class="grade {{ strtolower(substr($result->grade ?? 'F', 0, 1)) }}">
                                        {{ $result->grade }}
                                    </td>
                                    <td style="text-align: center;">{{ number_format($result->gpa_points, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <div style="padding: 60px; text-align: center;">
                <i class='bx bx-folder-open' style="font-size: 48px; color: #d1d5db;"></i>
                <p style="color: #6b7280; margin-top: 15px;">No academic records found.</p>
            </div>
        @endif

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-item">
                <label>Total Credit Hours</label>
                <div class="value">{{ $totalCreditHours }}</div>
            </div>
            <div class="summary-item">
                <label>Cumulative GPA</label>
                <div class="value cgpa">{{ number_format($cgpa, 2) }}</div>
            </div>
            <div class="summary-item">
                <label>Total Semesters</label>
                <div class="value">{{ count($semesterData) }}</div>
            </div>
        </div>

        <!-- Print Actions -->
        <div class="print-actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class='bx bx-printer'></i> Print Transcript
            </button>
            <a href="{{ route('college.student-portal.final-results') }}" class="btn btn-secondary" style="margin-left: 10px;">
                <i class='bx bx-arrow-back'></i> Back to Results
            </a>
        </div>
    </div>
</div>
@endsection
