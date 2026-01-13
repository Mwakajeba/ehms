@extends('layouts.main')

@section('title', 'My Continuous Assessment Results')

@section('content')
<style>
    .student-portal {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f9ff 0%, #ecfdf5 100%);
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
        background: #10b981;
        border-color: transparent;
        color: white;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #f59e0b;
        font-size: 20px;
        font-weight: bold;
    }

    .page-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 25px;
        color: white;
    }

    .page-header h1 {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header p {
        opacity: 0.9;
        font-size: 15px;
        margin: 0;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
    }

    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: #10b981;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-primary { background: #10b981; color: white; }
    .btn-primary:hover { background: #059669; }

    .course-card {
        background: white;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .course-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .course-info h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .course-info span {
        font-size: 13px;
        color: #64748b;
    }

    .course-total {
        text-align: right;
    }

    .course-total label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }

    .course-total .total-value {
        font-size: 24px;
        font-weight: 700;
        color: #10b981;
    }

    .assessments-grid {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .assessment-item {
        background: #f8fafc;
        border-radius: 10px;
        padding: 15px;
        border: 1px solid #e5e7eb;
    }

    .assessment-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .assessment-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .assessment-type.test { background: #dbeafe; color: #1e40af; }
    .assessment-type.quiz { background: #fef3c7; color: #92400e; }
    .assessment-type.midterm { background: #f3e8ff; color: #7c3aed; }
    .assessment-type.assignment { background: #dcfce7; color: #166534; }
    .assessment-type.default { background: #f1f5f9; color: #475569; }

    .assessment-date {
        font-size: 11px;
        color: #64748b;
    }

    .assessment-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .score-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .score-value {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .score-value .score {
        font-size: 28px;
        font-weight: 700;
    }

    .score-value .max {
        font-size: 14px;
        color: #64748b;
    }

    .score-value.excellent .score { color: #10b981; }
    .score-value.good .score { color: #3b82f6; }
    .score-value.average .score { color: #f59e0b; }
    .score-value.poor .score { color: #ef4444; }

    .weighted-score {
        text-align: right;
    }

    .weighted-score label {
        font-size: 10px;
        color: #64748b;
        display: block;
        margin-bottom: 2px;
    }

    .weighted-score .value {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .progress-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        margin-top: 12px;
        overflow: hidden;
    }

    .progress-bar .fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .progress-bar .fill.excellent { background: #10b981; }
    .progress-bar .fill.good { background: #3b82f6; }
    .progress-bar .fill.average { background: #f59e0b; }
    .progress-bar .fill.poor { background: #ef4444; }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 18px;
        color: #374151;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 14px;
    }

    .info-banner {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-banner i {
        font-size: 24px;
        color: #3b82f6;
    }

    .info-banner p {
        margin: 0;
        font-size: 14px;
        color: #1e40af;
    }

    @media (max-width: 768px) {
        .student-portal {
            margin-left: 0;
            padding: 15px;
        }

        .assessments-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="student-portal">
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
            <i class='bx bx-edit'></i> CA Results
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class='bx bx-edit'></i> My Continuous Assessment Results</h1>
        <p>View your test scores, quiz results, midterm marks, assignments, and other continuous assessments</p>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <i class='bx bx-info-circle'></i>
        <p>Only published results are displayed here. Results are published by your instructors after marking is complete.</p>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form action="{{ route('college.student-portal.ca-results') }}" method="GET">
            <div class="filter-row">
                <div class="form-group">
                    <label>Academic Year</label>
                    <select name="academic_year_id" class="form-control">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester_id" class="form-control">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex: 0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-filter'></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results by Course -->
    @if($scores->count() > 0)
        @foreach($scores as $courseName => $courseScores)
            @php
                $totalWeighted = $courseScores->sum('weighted_score');
            @endphp
            <div class="course-card">
                <div class="course-header">
                    <div class="course-info">
                        <h3>{{ $courseName }}</h3>
                        <span>{{ $courseScores->count() }} assessment(s)</span>
                    </div>
                    <div class="course-total">
                        <label>CA Total</label>
                        <div class="total-value">{{ number_format($totalWeighted, 1) }}%</div>
                    </div>
                </div>
                <div class="assessments-grid">
                    @foreach($courseScores as $score)
                        @php
                            $percentage = $score->max_marks > 0 ? ($score->score / $score->max_marks) * 100 : 0;
                            $scoreClass = $percentage >= 80 ? 'excellent' : ($percentage >= 60 ? 'good' : ($percentage >= 40 ? 'average' : 'poor'));
                            $typeName = strtolower($score->courseAssessment->assessmentType->name ?? 'default');
                            $typeClass = in_array($typeName, ['test', 'quiz', 'midterm', 'assignment']) ? $typeName : 'default';
                        @endphp
                        <div class="assessment-item">
                            <div class="assessment-header">
                                <span class="assessment-type {{ $typeClass }}">
                                    <i class='bx bx-{{ $typeClass == 'test' ? 'file' : ($typeClass == 'quiz' ? 'help-circle' : ($typeClass == 'midterm' ? 'calendar' : 'task')) }}'></i>
                                    {{ $score->courseAssessment->assessmentType->name ?? 'Assessment' }}
                                </span>
                                <span class="assessment-date">
                                    {{ $score->courseAssessment->assessment_date ? \Carbon\Carbon::parse($score->courseAssessment->assessment_date)->format('M d, Y') : '' }}
                                </span>
                            </div>
                            <div class="assessment-title">{{ $score->courseAssessment->title }}</div>
                            <div class="score-display">
                                <div class="score-value {{ $scoreClass }}">
                                    <span class="score">{{ number_format($score->score, 1) }}</span>
                                    <span class="max">/ {{ $score->max_marks }}</span>
                                </div>
                                <div class="weighted-score">
                                    <label>Weighted</label>
                                    <span class="value">{{ number_format($score->weighted_score, 1) }}%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="fill {{ $scoreClass }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class='bx bx-folder-open'></i>
            <h3>No Assessment Results Found</h3>
            <p>You don't have any published continuous assessment results yet. Check back later or adjust your filters.</p>
        </div>
    @endif
</div>
@endsection
