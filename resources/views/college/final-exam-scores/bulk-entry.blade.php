@extends('layouts.main')

@section('title', 'Bulk Final Exam Score Entry')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .page-header p {
        margin: 0.25rem 0 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }

    .breadcrumb-nav a {
        color: #6b7280;
        text-decoration: none;
    }

    .breadcrumb-nav a:hover {
        color: #3b82f6;
    }

    .breadcrumb-nav span {
        color: #9ca3af;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .filter-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-card h3 i {
        color: #3b82f6;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
    }

    @media (max-width: 1200px) {
        .filter-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .filter-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
    }

    .filter-group select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
    }

    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .load-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1.25rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        margin-top: 1.625rem;
    }

    .load-btn:hover {
        background: #2563eb;
    }

    .scores-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .scores-card-header {
        background: #f9fafb;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .scores-card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .scores-card-header h3 i {
        color: #3b82f6;
    }

    .exam-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
    }

    .exam-info .max-score {
        background: #3b82f6;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .exam-info .exam-date {
        background: #fef3c7;
        color: #92400e;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .scores-table {
        width: 100%;
        border-collapse: collapse;
    }

    .scores-table th {
        background: #f9fafb;
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
    }

    .scores-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.875rem;
    }

    .scores-table tbody tr:hover {
        background: #f9fafb;
    }

    .scores-table .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .scores-table .student-avatar {
        width: 36px;
        height: 36px;
        background: #dbeafe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #3b82f6;
        font-size: 0.75rem;
    }

    .scores-table .student-info h4 {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
    }

    .scores-table .student-info span {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .ca-badge {
        background: #d1fae5;
        color: #065f46;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .score-input {
        width: 80px;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        text-align: center;
    }

    .score-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .score-input.invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .score-input.valid {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .remarks-input {
        width: 150px;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    .existing-score {
        font-size: 0.75rem;
        color: #f59e0b;
        display: block;
        margin-top: 0.25rem;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    .form-actions-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .form-actions-left label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
    }

    .form-actions-right {
        display: flex;
        gap: 0.75rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .alert i {
        font-size: 1.25rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        font-size: 1rem;
        color: #374151;
        margin: 0 0 0.5rem;
    }

    .empty-state p {
        font-size: 0.875rem;
        margin: 0;
    }

    .stats-bar {
        display: flex;
        gap: 2rem;
        padding: 1rem 1.5rem;
        background: #eff6ff;
        border-bottom: 1px solid #bfdbfe;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-item label {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .stat-item span {
        font-weight: 600;
        color: #1e40af;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-success {
        background: #d1fae5;
        color: #065f46;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <div class="breadcrumb-nav">
        <a href="{{ route('college.exams-management.dashboard') }}">
            <i class='bx bx-home'></i> Dashboard
        </a>
        <span>/</span>
        <a href="{{ route('college.final-exam-scores.index') }}">Final Exam Scores</a>
        <span>/</span>
        <span>Bulk Entry</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class='bx bx-list-plus'></i> Bulk Final Exam Score Entry</h1>
        <p>Enter exam scores for all students in a class at once</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class='bx bx-error-circle'></i>
        <div>
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="filter-card">
        <h3><i class='bx bx-filter-alt'></i> Select Final Exam</h3>
        <form id="filterForm">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" required>
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ request('academic_year_id', $currentAcademicYear?->id) == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Semester</label>
                    <select name="semester_id" id="semester_id" required>
                        <option value="">Select Semester</option>
                        @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ request('semester_id', $currentSemester?->id) == $semester->id ? 'selected' : '' }}>
                            {{ $semester->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Program</label>
                    <select name="program_id" id="program_id" required>
                        <option value="">Select Program</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Course</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select Course</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Final Exam</label>
                    <select name="final_exam_id" id="final_exam_id" required>
                        <option value="">Select Exam</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="load-btn" id="loadStudentsBtn">
                <i class='bx bx-search'></i> Load Students
            </button>
        </form>
    </div>

    <!-- Scores Entry Card -->
    <div class="scores-card" id="scoresCard" style="display: none;">
        <form id="bulkScoresForm" action="{{ route('college.final-exam-scores.store-bulk') }}" method="POST">
            @csrf
            <input type="hidden" name="final_exam_id" id="form_final_exam_id">

            <div class="scores-card-header">
                <h3><i class='bx bx-user-check'></i> Student Exam Score Entry</h3>
                <div class="exam-info">
                    <span id="examName">-</span>
                    <span class="exam-date"><i class='bx bx-calendar'></i> <span id="examDate">-</span></span>
                    <span class="max-score">Max: <span id="maxScore">0</span></span>
                </div>
            </div>

            <div class="stats-bar" id="statsBar">
                <div class="stat-item">
                    <label>Total Students:</label>
                    <span id="totalStudents">0</span>
                </div>
                <div class="stat-item">
                    <label>Scores Entered:</label>
                    <span id="scoresEntered">0</span>
                </div>
                <div class="stat-item">
                    <label>Pending:</label>
                    <span id="scoresPending">0</span>
                </div>
                <div class="stat-item">
                    <label>Average CA:</label>
                    <span id="averageCA">-</span>
                </div>
            </div>

            <div id="studentsContainer">
                <table class="scores-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Student</th>
                            <th style="width: 100px;">CA Total</th>
                            <th style="width: 120px;">Exam Score</th>
                            <th style="width: 180px;">Remarks</th>
                            <th style="width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="empty-state" id="emptyState" style="display: none;">
                <i class='bx bx-user-x'></i>
                <h4>No Students Found</h4>
                <p>No students are registered for this course/exam.</p>
            </div>

            <div class="form-actions">
                <div class="form-actions-left">
                    <label>
                        <input type="checkbox" name="publish_all" id="publish_all" value="1">
                        Publish all scores to students
                    </label>
                </div>
                <div class="form-actions-right">
                    <a href="{{ route('college.final-exam-scores.index') }}" class="btn btn-secondary">
                        <i class='bx bx-x'></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                        <i class='bx bx-save'></i> Save All Scores
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Initial State -->
    <div class="alert alert-info" id="initialMessage">
        <i class='bx bx-info-circle'></i>
        <span>Select a program, course, and final exam above to load students for score entry.</span>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const programSelect = document.getElementById('program_id');
    const courseSelect = document.getElementById('course_id');
    const examSelect = document.getElementById('final_exam_id');
    const filterForm = document.getElementById('filterForm');
    const scoresCard = document.getElementById('scoresCard');
    const initialMessage = document.getElementById('initialMessage');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const emptyState = document.getElementById('emptyState');
    const saveBtn = document.getElementById('saveBtn');

    let maxScore = 0;

    // Load courses when program changes
    programSelect.addEventListener('change', function() {
        const programId = this.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        courseSelect.innerHTML = '<option value="">Loading...</option>';
        examSelect.innerHTML = '<option value="">Select Exam</option>';

        if (programId && academicYearId && semesterId) {
            fetch(`/college/api/courses-by-program?program_id=${programId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`)
                .then(response => response.json())
                .then(data => {
                    courseSelect.innerHTML = '<option value="">Select Course</option>';
                    data.forEach(course => {
                        courseSelect.innerHTML += `<option value="${course.id}">${course.code} - ${course.name}</option>`;
                    });
                })
                .catch(() => {
                    courseSelect.innerHTML = '<option value="">Failed to load</option>';
                });
        } else {
            courseSelect.innerHTML = '<option value="">Select Course</option>';
        }
    });

    // Load exams when course changes
    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        examSelect.innerHTML = '<option value="">Loading...</option>';

        if (courseId) {
            fetch(`/college/api/final-exams?course_id=${courseId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`)
                .then(response => response.json())
                .then(data => {
                    examSelect.innerHTML = '<option value="">Select Exam</option>';
                    data.forEach(exam => {
                        examSelect.innerHTML += `<option value="${exam.id}" data-max-score="${exam.max_score}" data-name="${exam.title}" data-date="${exam.exam_date}">${exam.title} (${exam.exam_type}) - ${exam.exam_date}</option>`;
                    });
                })
                .catch(() => {
                    examSelect.innerHTML = '<option value="">Failed to load</option>';
                });
        } else {
            examSelect.innerHTML = '<option value="">Select Exam</option>';
        }
    });

    // Load students when form is submitted
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const examId = examSelect.value;
        if (!examId) {
            alert('Please select a final exam');
            return;
        }

        const selectedOption = examSelect.options[examSelect.selectedIndex];
        maxScore = parseFloat(selectedOption.dataset.maxScore) || 100;
        const examName = selectedOption.dataset.name || 'Final Exam';
        const examDate = selectedOption.dataset.date || '-';

        document.getElementById('examName').textContent = examName;
        document.getElementById('examDate').textContent = examDate;
        document.getElementById('maxScore').textContent = maxScore;
        document.getElementById('form_final_exam_id').value = examId;

        // Load students
        studentsTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Loading students...</td></tr>';
        scoresCard.style.display = 'block';
        initialMessage.style.display = 'none';

        fetch(`/college/api/exam-students?final_exam_id=${examId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    studentsTableBody.innerHTML = '';
                    emptyState.style.display = 'block';
                    saveBtn.disabled = true;
                    return;
                }

                emptyState.style.display = 'none';
                saveBtn.disabled = false;

                let html = '';
                let enteredCount = 0;
                let totalCA = 0;
                let caCount = 0;
                
                data.forEach((student, index) => {
                    const hasScore = student.existing_score !== null;
                    if (hasScore) enteredCount++;
                    
                    if (student.ca_total) {
                        totalCA += parseFloat(student.ca_total);
                        caCount++;
                    }
                    
                    const initials = student.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                    
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">${initials}</div>
                                    <div class="student-info">
                                        <h4>${student.name}</h4>
                                        <span>${student.student_id}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="ca-badge">${student.ca_total !== null ? student.ca_total : 'N/A'}</span>
                            </td>
                            <td>
                                <input type="hidden" name="scores[${index}][course_registration_id]" value="${student.registration_id}">
                                <input type="number" 
                                       name="scores[${index}][score]" 
                                       class="score-input" 
                                       min="0" 
                                       max="${maxScore}" 
                                       step="0.01"
                                       value="${hasScore ? student.existing_score : ''}"
                                       data-max="${maxScore}"
                                       placeholder="0">
                                ${hasScore ? '<span class="existing-score">Previously: ' + student.existing_score + '</span>' : ''}
                            </td>
                            <td>
                                <input type="text" 
                                       name="scores[${index}][remarks]" 
                                       class="remarks-input" 
                                       value="${student.remarks || ''}"
                                       placeholder="Optional">
                            </td>
                            <td>
                                <span class="status-badge ${hasScore ? 'status-success' : 'status-pending'}">
                                    ${hasScore ? 'Entered' : 'Pending'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                studentsTableBody.innerHTML = html;

                // Update stats
                document.getElementById('totalStudents').textContent = data.length;
                document.getElementById('scoresEntered').textContent = enteredCount;
                document.getElementById('scoresPending').textContent = data.length - enteredCount;
                document.getElementById('averageCA').textContent = caCount > 0 ? (totalCA / caCount).toFixed(1) : 'N/A';

                // Add validation to score inputs
                document.querySelectorAll('.score-input').forEach(input => {
                    input.addEventListener('input', function() {
                        const value = parseFloat(this.value);
                        const max = parseFloat(this.dataset.max);
                        
                        this.classList.remove('valid', 'invalid');
                        if (this.value !== '') {
                            if (value > max || value < 0) {
                                this.classList.add('invalid');
                            } else {
                                this.classList.add('valid');
                            }
                        }
                        updateStats();
                    });
                });
            })
            .catch(error => {
                studentsTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: #ef4444;">Failed to load students. Please try again.</td></tr>';
                console.error('Error:', error);
            });
    });

    function updateStats() {
        const inputs = document.querySelectorAll('.score-input');
        let entered = 0;
        inputs.forEach(input => {
            if (input.value !== '') entered++;
        });
        document.getElementById('scoresEntered').textContent = entered;
        document.getElementById('scoresPending').textContent = inputs.length - entered;
    }
});
</script>
@endsection
