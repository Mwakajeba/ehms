@extends('layouts.main')

@section('title', 'Detailed Fee Collection Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Detailed Fee Collection', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">DETAILED FEE COLLECTION REPORT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div><i class="bx bx-money me-1 font-22 text-info"></i></div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-modern" onclick="exportToExcel()">
                                    <i class="bx bx-file me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-modern" onclick="exportToPDF()">
                                    <i class="bx bx-file-pdf me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <form method="GET" action="{{ route('school.reports.detailed-fee-collection') }}" id="filterForm">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="academic_year_id" class="form-label">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-select">
                                        <option value="">All Academic Years</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ Hashids::encode($year->id) }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select name="class_id" id="class_id" class="form-select">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ Hashids::encode($class->id) }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} ({{ $class->students_count }} students)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="period" class="form-label">Period</label>
                                    <select name="period" id="period" class="form-select">
                                        <option value="">All Periods</option>
                                        <option value="Q1" {{ (isset($period) && $period == 'Q1') ? 'selected' : '' }}>Q1</option>
                                        <option value="Q2" {{ (isset($period) && $period == 'Q2') ? 'selected' : '' }}>Q2</option>
                                        <option value="Q3" {{ (isset($period) && $period == 'Q3') ? 'selected' : '' }}>Q3</option>
                                        <option value="Q4" {{ (isset($period) && $period == 'Q4') ? 'selected' : '' }}>Q4</option>
                                        <option value="Term 1" {{ (isset($period) && $period == 'Term 1') ? 'selected' : '' }}>Term 1</option>
                                        <option value="Term 2" {{ (isset($period) && $period == 'Term 2') ? 'selected' : '' }}>Term 2</option>
                                        <option value="Annual" {{ (isset($period) && $period == 'Annual') ? 'selected' : '' }}>Annual</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('school.reports.detailed-fee-collection') }}" class="btn btn-secondary">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Data -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="feeCollectionTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Class Level</th>
                                        <th>Stream</th>
                                        <th class="text-center">Total Students</th>
                                        <th class="text-center">Paid Full Fees</th>
                                        <th class="text-center">Outstanding Fees</th>
                                        <th class="text-center">Collection Rate (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeCollectionData as $className => $streams)
                                        @foreach($streams as $streamName => $data)
                                            @if($streamName !== 'class_totals')
                                                <td>{{ $className }}</td>
                                                <td>{{ $streamName }}</td>
                                                <td class="text-center">{{ $data['total_students'] }}</td>
                                                <td class="text-center">
                                                    @if($data['paid_full_fees'] > 0)
                                                        <a href="#" class="text-success fw-bold" onclick="showStudentDetails({{ $data['paid_full_students']->toJson() }}, 'Paid Full Fees')">
                                                            {{ $data['paid_full_fees'] }}
                                                        </a>
                                                    @else
                                                        {{ $data['paid_full_fees'] }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($data['outstanding_fees'] > 0)
                                                        <a href="#" class="text-danger fw-bold" onclick="showStudentDetails({{ $data['outstanding_students']->toJson() }}, 'Outstanding Fees')">
                                                            {{ $data['outstanding_fees'] }}
                                                        </a>
                                                    @else
                                                        {{ $data['outstanding_fees'] }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($data['total_students'] > 0)
                                                        {{ number_format(($data['paid_full_fees'] / $data['total_students']) * 100, 1) }}%
                                                    @else
                                                        0.0%
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                        @if(count($streams) > 1)
                                            @php $classTotals = $streams['class_totals']; @endphp
                                            <tr class="table-secondary fw-bold">
                                                <td colspan="2" class="text-end">Class Total:</td>
                                                <td class="text-center">{{ $classTotals['total_students'] }}</td>
                                                <td class="text-center">
                                                    @if($classTotals['paid_full_fees'] > 0)
                                                        <a href="#" class="text-success fw-bold" onclick="showStudentDetails({{ $classTotals['paid_full_students']->toJson() }}, 'Paid Full Fees - {{ $className }}')">
                                                            {{ $classTotals['paid_full_fees'] }}
                                                        </a>
                                                    @else
                                                        {{ $classTotals['paid_full_fees'] }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($classTotals['outstanding_fees'] > 0)
                                                        <a href="#" class="text-danger fw-bold" onclick="showStudentDetails({{ $classTotals['outstanding_students']->toJson() }}, 'Outstanding Fees - {{ $className }}')">
                                                            {{ $classTotals['outstanding_fees'] }}
                                                        </a>
                                                    @else
                                                        {{ $classTotals['outstanding_fees'] }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($classTotals['total_students'] > 0)
                                                        {{ number_format(($classTotals['paid_full_fees'] / $classTotals['total_students']) * 100, 1) }}%
                                                    @else
                                                        0.0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No data available for the selected filters.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentDetailsModalLabel">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="modalQuarterFilter" class="form-label">Filter by Quarter</label>
                        <select id="modalQuarterFilter" class="form-select" onchange="filterStudentDetailsByQuarter()">
                            <option value="">All Quarters</option>
                            <option value="1">Quarter 1</option>
                            <option value="2">Quarter 2</option>
                            <option value="3">Quarter 3</option>
                            <option value="4">Quarter 4</option>
                        </select>
                    </div>
                </div>
                <div id="studentDetailsContent">
                    <!-- Student details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printStudentDetails()">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
                <button type="button" class="btn btn-danger" onclick="downloadStudentDetailsPDF()">
                    <i class="bx bx-file-pdf me-1"></i> Download PDF
                </button>
                <button type="button" class="btn btn-success" onclick="downloadStudentDetails()">
                    <i class="bx bx-download me-1"></i> Download CSV
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'excel');
    // Preserve all form parameters including period
    const form = document.getElementById('filterForm');
    if (form) {
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (value) {
                url.searchParams.set(key, value);
            }
        });
    }
    window.open(url.toString(), '_blank');
}

function exportToPDF() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'pdf');
    // Preserve all form parameters including period
    const form = document.getElementById('filterForm');
    if (form) {
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (value) {
                url.searchParams.set(key, value);
            }
        });
    }
    window.open(url.toString(), '_blank');
}

function showStudentDetails(students, title) {
    // Update modal title
    document.getElementById('studentDetailsModalLabel').textContent = title;

    // Store original students data
    window.originalStudents = students;

    // Reset quarter filter
    document.getElementById('modalQuarterFilter').value = '';

    // Display all students initially
    displayStudentDetails(students);

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    modal.show();
}

function filterStudentDetailsByQuarter() {
    const selectedQuarter = document.getElementById('modalQuarterFilter').value;
    const students = window.originalStudents || [];

    if (!selectedQuarter) {
        // Show all students
        displayStudentDetails(students);
    } else {
        // Filter students by quarter
        const filteredStudents = students.map(student => {
            const filteredInvoices = student.fee_invoices.filter(invoice => invoice.period == selectedQuarter);
            return {
                ...student,
                fee_invoices: filteredInvoices
            };
        }).filter(student => student.fee_invoices.length > 0);

        displayStudentDetails(filteredStudents);
    }
}

function displayStudentDetails(students) {
    let content = '<div class="table-responsive">';
    content += '<table class="table table-striped table-sm">';
    content += '<thead class="table-dark">';
    content += '<tr>';
    content += '<th>Admission No</th>';
    content += '<th>Student Name</th>';
    content += '<th>Quarter</th>';
    content += '<th>Total Amount</th>';
    content += '<th>Paid Amount</th>';
    content += '<th>Outstanding</th>';
    content += '<th>Status</th>';
    content += '</tr>';
    content += '</thead>';
    content += '<tbody>';

    students.forEach(function(student) {
        if (student.fee_invoices.length === 0) {
            // If no invoices after filtering, skip this student
            return;
        }

        let totalAmount = 0;
        let paidAmount = 0;
        let quarters = [];

        student.fee_invoices.forEach(function(invoice) {
            totalAmount += parseFloat(invoice.total_amount);
            paidAmount += parseFloat(invoice.paid_amount);
            if (invoice.period && !quarters.includes(invoice.period)) {
                quarters.push(invoice.period);
            }
        });

        let outstanding = totalAmount - paidAmount;
        let status = outstanding > 0 ? '<span class="badge bg-danger">Outstanding</span>' : '<span class="badge bg-success">Paid</span>';
        let quarterDisplay = quarters.length > 0 ? quarters.map(q => 'Q' + q).join(', ') : 'N/A';

        content += '<tr>';
        content += '<td>' + (student.admission_number || 'N/A') + '</td>';
        content += '<td>' + student.first_name + ' ' + student.last_name + '</td>';
        content += '<td>' + quarterDisplay + '</td>';
        content += '<td class="text-end">' + totalAmount.toFixed(2) + '</td>';
        content += '<td class="text-end">' + paidAmount.toFixed(2) + '</td>';
        content += '<td class="text-end">' + outstanding.toFixed(2) + '</td>';
        content += '<td>' + status + '</td>';
        content += '</tr>';
    });

    content += '</tbody>';
    content += '</table>';
    content += '</div>';

    document.getElementById('studentDetailsContent').innerHTML = content;
    document.getElementById('studentDetailsContent').dataset.students = JSON.stringify(students);
}

function printStudentDetails() {
    const modalContent = document.getElementById('studentDetailsContent');
    const printWindow = window.open('', '_blank');
    const modalTitle = document.getElementById('studentDetailsModalLabel').textContent;

    printWindow.document.write(`
        <html>
        <head>
            <title>${modalTitle}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-end { text-align: right; }
                .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                .bg-success { background-color: #d4edda; color: #155724; }
                .bg-danger { background-color: #f8d7da; color: #721c24; }
                h2 { color: #17a2b8; border-bottom: 2px solid #17a2b8; padding-bottom: 10px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <h2>${modalTitle}</h2>
            ${modalContent.innerHTML}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

function downloadStudentDetails() {
    const modalTitle = document.getElementById('studentDetailsModalLabel').textContent;
    const students = JSON.parse(document.getElementById('studentDetailsContent').dataset.students || '[]');

    // Create CSV content
    let csvContent = 'Admission No,Student Name,Quarter,Total Amount,Paid Amount,Outstanding,Status\n';

    students.forEach(function(student) {
        if (student.fee_invoices.length === 0) return;

        let totalAmount = 0;
        let paidAmount = 0;
        let quarters = [];

        student.fee_invoices.forEach(function(invoice) {
            totalAmount += parseFloat(invoice.total_amount);
            paidAmount += parseFloat(invoice.paid_amount);
            if (invoice.period && !quarters.includes(invoice.period)) {
                quarters.push(invoice.period);
            }
        });

        let outstanding = totalAmount - paidAmount;
        let status = outstanding > 0 ? 'Outstanding' : 'Paid';
        let quarterDisplay = quarters.length > 0 ? quarters.map(q => 'Q' + q).join(', ') : 'N/A';

        csvContent += `"${student.admission_number || 'N/A'}","${student.first_name} ${student.last_name}","${quarterDisplay}","${totalAmount.toFixed(2)}","${paidAmount.toFixed(2)}","${outstanding.toFixed(2)}","${status}"\n`;
    });

    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${modalTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function downloadStudentDetailsPDF() {
    const modalTitle = document.getElementById('studentDetailsModalLabel').textContent;
    const students = JSON.parse(document.getElementById('studentDetailsContent').dataset.students || '[]');

    // Create form data to send to server
    const formData = new FormData();
    formData.append('title', modalTitle);
    formData.append('students', JSON.stringify(students));
    formData.append('export', 'pdf');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    // Send request to generate PDF
    fetch('{{ route("school.reports.detailed-fee-collection") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Failed to generate PDF');
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${modalTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        alert('Error generating PDF: ' + error.message);
    });
}
</script>
@endsection