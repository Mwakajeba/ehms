@extends('layouts.main')

@section('title', 'Student Profile - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
//            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => route('school.students.index'), 'icon' => 'bx bx-id-card'],
            ['label' => $student->first_name . ' ' . $student->last_name, 'url' => '#', 'icon' => 'bx bx-user']
        ]" />

        <!-- Student Profile Header -->
        <div class="student-profile-header mb-4">
            <div class="profile-cover">
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        @if($student->passport_photo)
                            <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Student Photo" class="avatar-image">
                        @else
                            <div class="avatar-placeholder">
                                <i class="bx bx-user avatar-icon"></i>
                            </div>
                        @endif
                    </div>
                    <div class="profile-status">
                        <span class="status-badge active">
                            <i class="bx bx-check-circle"></i> Active Student
                        </span>
                    </div>
                </div>
                <div class="profile-info-section">
                    <div class="profile-name-section">
                        <h1 class="student-name">{{ $student->first_name }} {{ $student->last_name }}</h1>
                        <p class="student-subtitle">
                            <i class="bx bx-id-card-alt"></i> Admission No: {{ $student->admission_number }}
                        </p>
                    </div>
                    <div class="profile-details-grid">
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="bx bx-graduation"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Class & Stream</span>
                                <span class="detail-value">{{ $student->class->name ?? 'N/A' }} {{ $student->stream ? '| ' . $student->stream->name : '' }}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="bx bx-calendar-star"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Academic Year</span>
                                <span class="detail-value">{{ $student->academicYear->year_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="bx bx-cake"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Age</span>
                                <span class="detail-value">{{ $student->date_of_birth ? $student->date_of_birth->age : 'N/A' }} years old</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="bx bx-{{ $student->gender === 'male' ? 'male' : 'female' }}"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Gender</span>
                                <span class="detail-value">{{ ucfirst($student->gender ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="{{ route('school.students.edit', $student) }}" class="btn btn-primary btn-lg">
                        <i class="bx bx-edit"></i> Edit Profile
                    </a>
                    <a href="{{ route('school.students.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="bx bx-arrow-back"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="stats-overview mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $student->class && $student->class->students ? $student->class->students->count() : 0 }}</h3>
                            <p class="stat-label">Classmates</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="bx bx-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $student->guardians ? $student->guardians->count() : 0 }}</h3>
                            <p class="stat-label">Guardians</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="bx bx-money"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">TSh {{ number_format($student->feeInvoices->sum('total_amount') - $student->feeInvoices->sum('paid_amount')) }}</h3>
                            <p class="stat-label">Outstanding Fees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="bx bx-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $student->enrollments ? $student->enrollments->count() : 0 }}</h3>
                            <p class="stat-label">Enrollments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Profile Content -->
        <div class="profile-content">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Personal Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-user"></i>
                            </div>
                            <div class="section-title">
                                <h3>Personal Information</h3>
                                <p>Basic details and contact information</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="info-grid">
                                <div class="info-row">
                                    <div class="info-col">
                                        <div class="info-item">
                                            <label class="info-label">Full Name</label>
                                            <div class="info-value">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        </div>
                                    </div>
                                    <div class="info-col">
                                        <div class="info-item">
                                            <label class="info-label">Date of Birth</label>
                                            <div class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-col">
                                        <div class="info-item">
                                            <label class="info-label">Gender</label>
                                            <div class="info-value">
                                                <span class="gender-badge {{ $student->gender }}">
                                                    <i class="bx bx-{{ $student->gender === 'male' ? 'male' : 'female' }}"></i>
                                                    {{ ucfirst($student->gender ?? 'N/A') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-col">
                                        <div class="info-item">
                                            <label class="info-label">Student ID</label>
                                            <div class="info-value">#{{ $student->id }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="info-row full-width">
                                    <div class="info-item">
                                        <label class="info-label">Residential Address</label>
                                        <div class="info-value address">{{ $student->address }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="profile-section">
                        <div class="section-header academic-header">
                            <div class="section-icon academic-icon">
                                <i class="bx bx-graduation"></i>
                            </div>
                            <div class="section-title">
                                <h3>Academic Information</h3>
                                <p>Education details and academic progress</p>
                            </div>
                            <div class="academic-progress-indicator">
                                <div class="progress-ring">
                                    <svg width="60" height="60">
                                        <circle cx="30" cy="30" r="25" stroke="#e9ecef" stroke-width="4" fill="none"/>
                                        <circle cx="30" cy="30" r="25" stroke="#28a745" stroke-width="4" fill="none"
                                                stroke-dasharray="157" stroke-dashoffset="31.4"
                                                transform="rotate(-90 30 30)"/>
                                    </svg>
                                    <div class="progress-text">
                                        <span class="progress-number">80%</span>
                                        <span class="progress-label">Complete</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="academic-overview">
                                <div class="academic-stats">
                                    <div class="stat-item">
                                        <div class="stat-icon-wrapper">
                                            <i class="bx bx-calendar-star"></i>
                                        </div>
                                        <div class="stat-details">
                                            <span class="stat-value">{{ $student->academicYear->year_name ?? 'N/A' }}</span>
                                            <span class="stat-label">Academic Year</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon-wrapper">
                                            <i class="bx bx-group"></i>
                                        </div>
                                        <div class="stat-details">
                                            <span class="stat-value">{{ $student->class && $student->class->students ? $student->class->students->count() : 0 }}</span>
                                            <span class="stat-label">Classmates</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon-wrapper">
                                            <i class="bx bx-time"></i>
                                        </div>
                                        <div class="stat-details">
                                            <span class="stat-value">{{ $student->enrollments ? $student->enrollments->count() : 0 }}</span>
                                            <span class="stat-label">Enrollments</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="academic-details-grid">
                                <!-- Admission Details Card -->
                                <div class="academic-detail-card admission-card">
                                    <div class="card-header">
                                        <div class="card-icon admission-icon">
                                            <i class="bx bx-id-card"></i>
                                        </div>
                                        <div class="card-title-section">
                                            <h4>Admission Details</h4>
                                            <span class="card-subtitle">Student enrollment information</span>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-hash"></i>
                                                Admission Number
                                            </div>
                                            <div class="detail-value admission-number">{{ $student->admission_number }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-calendar-check"></i>
                                                Admission Date
                                            </div>
                                            <div class="detail-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-user-check"></i>
                                                Student Status
                                            </div>
                                            <div class="detail-value">
                                                <span class="status-pill active">
                                                    <i class="bx bx-check-circle"></i>
                                                    Active Student
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Class Card -->
                                <div class="academic-detail-card class-card">
                                    <div class="card-header">
                                        <div class="card-icon class-icon">
                                            <i class="bx bx-graduation"></i>
                                        </div>
                                        <div class="card-title-section">
                                            <h4>Current Class</h4>
                                            <span class="card-subtitle">Academic placement details</span>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-school"></i>
                                                Class
                                            </div>
                                            <div class="detail-value class-name">{{ $student->class->name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-branch"></i>
                                                Stream
                                            </div>
                                            <div class="detail-value stream-name">{{ $student->stream->name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-calendar-star"></i>
                                                Academic Year
                                            </div>
                                            <div class="detail-value academic-year">{{ $student->academicYear->year_name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">
                                                <i class="bx bx-home"></i>
                                                Boarding Type
                                            </div>
                                            <div class="detail-value">
                                                <span class="boarding-badge {{ $student->boarding_type ?? 'day' }}">
                                                    <i class="bx bx-{{ $student->boarding_type === 'boarding' ? 'home' : 'bus' }}"></i>
                                                    {{ ucfirst($student->boarding_type ?? 'day') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Statistics Card -->
                                <div class="academic-detail-card stats-card">
                                    <div class="card-header">
                                        <div class="card-icon stats-icon">
                                            <i class="bx bx-bar-chart-alt-2"></i>
                                        </div>
                                        <div class="card-title-section">
                                            <h4>Class Statistics</h4>
                                            <span class="card-subtitle">Class performance metrics</span>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="stats-metrics">
                                            <div class="metric-item">
                                                <div class="metric-circle">
                                                    <span class="metric-number">{{ $student->class && $student->class->students ? $student->class->students->count() : 0 }}</span>
                                                </div>
                                                <div class="metric-info">
                                                    <span class="metric-label">Total Students</span>
                                                    <span class="metric-desc">In {{ $student->class->name ?? 'class' }}</span>
                                                </div>
                                            </div>
                                            <div class="metric-item">
                                                <div class="metric-circle">
                                                    <span class="metric-number">{{ $student->stream && $student->stream->students ? $student->stream->students->count() : 0 }}</span>
                                                </div>
                                                <div class="metric-info">
                                                    <span class="metric-label">Stream Students</span>
                                                    <span class="metric-desc">In {{ $student->stream->name ?? 'stream' }}</span>
                                                </div>
                                            </div>
                                            <div class="metric-item">
                                                <div class="metric-circle">
                                                    <span class="metric-number">{{ $student->enrollments ? $student->enrollments->count() : 0 }}</span>
                                                </div>
                                                <div class="metric-info">
                                                    <span class="metric-label">Enrollments</span>
                                                    <span class="metric-desc">Total records</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Timeline -->
                            <div class="academic-timeline">
                                <h5 class="timeline-title">
                                    <i class="bx bx-time"></i>
                                    Academic Journey
                                </h5>
                                <div class="timeline-container">
                                    <div class="timeline-item">
                                        <div class="timeline-marker admission">
                                            <i class="bx bx-plus-circle"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6>Admission</h6>
                                            <p>Joined the school on {{ $student->admission_date ? $student->admission_date->format('F d, Y') : 'N/A' }}</p>
                                            <span class="timeline-date">{{ $student->admission_date ? $student->admission_date->format('M Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker current">
                                            <i class="bx bx-graduation"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6>Current Class</h6>
                                            <p>Currently enrolled in {{ $student->class->name ?? 'N/A' }} {{ $student->stream ? '- ' . $student->stream->name : '' }}</p>
                                            <span class="timeline-date">{{ $student->academicYear->year_name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                            <!-- Transport Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-bus"></i>
                            </div>
                            <div class="section-title">
                                <h3>Transport Information</h3>
                                <p>Transportation and route details</p>
                            </div>
                        </div>
                        <div class="section-content">
                            @if($student->has_transport === 'yes')
                            <div class="transport-overview">
                                <div class="transport-status">
                                    <div class="status-indicator active">
                                        <i class="bx bx-check-circle"></i>
                                        <span>Transport Active</span>
                                    </div>
                                </div>
                                <div class="transport-details">
                                    @if($student->busStop)
                                    <div class="transport-card">
                                        <div class="transport-icon">
                                            <i class="bx bx-map-pin"></i>
                                        </div>
                                        <div class="transport-info">
                                            <h5>Bus Stop Details</h5>
                                            <p class="transport-name">{{ $student->busStop->stop_name ?? 'N/A' }}</p>
                                            <p class="transport-code">Stop Code: {{ $student->busStop->stop_code ?? 'N/A' }}</p>
                                            @if($student->busStop->description)
                                                <p class="transport-desc">{{ $student->busStop->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @if($student->route)
                                    <div class="transport-card">
                                        <div class="transport-icon">
                                            <i class="bx bx-route"></i>
                                        </div>
                                        <div class="transport-info">
                                            <h5>Route Information</h5>
                                            <p class="transport-name">{{ $student->route->route_name ?? 'N/A' }}</p>
                                            <p class="transport-code">Route Code: {{ $student->route->route_code ?? 'N/A' }}</p>
                                            @if($student->route->description)
                                                <p class="transport-desc">{{ $student->route->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @if($student->busStop && $student->busStop->routes->count() > 0)
                                    <div class="transport-card">
                                        <div class="transport-icon">
                                            <i class="bx bx-directions"></i>
                                        </div>
                                        <div class="transport-info">
                                            <h5>Available Routes</h5>
                                            @foreach($student->busStop->routes as $route)
                                                <div class="route-item">
                                                    <p class="transport-name">{{ $route->route_name }}</p>
                                                    <p class="transport-code">Code: {{ $route->route_code }}</p>
                                                    @if($route->buses->count() > 0)
                                                        <p class="transport-desc">Buses: {{ $route->buses->pluck('bus_number')->join(', ') }}</p>
                                                    @endif
                                                </div>
                                                @if(!$loop->last)
                                                    <hr style="margin: 10px 0; border-color: #e9ecef;">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    @if($student->busStop && $student->busStop->bus)
                                    <div class="transport-card">
                                        <div class="transport-icon">
                                            <i class="bx bx-bus-school"></i>
                                        </div>
                                        <div class="transport-info">
                                            <h5>Assigned Bus Details</h5>
                                            <p class="transport-name">Bus #{{ $student->busStop->bus->bus_number ?? 'N/A' }}</p>
                                            <p class="transport-code">Model: {{ $student->busStop->bus->model ?? 'N/A' }}</p>
                                            <p class="transport-code">Capacity: {{ $student->busStop->bus->capacity ?? 'N/A' }} students</p>
                                        </div>
                                    </div>
                                    @endif
                                    @if($student->busStop && $student->busStop->routes->pluck('buses')->flatten()->unique('id')->count() > 0)
                                    <div class="transport-card">
                                        <div class="transport-icon">
                                            <i class="bx bx-bus"></i>
                                        </div>
                                        <div class="transport-info">
                                            <h5>Available Buses</h5>
                                            @foreach($student->busStop->routes->pluck('buses')->flatten()->unique('id') as $bus)
                                                <div class="bus-item">
                                                    <p class="transport-name">Bus #{{ $bus->bus_number }}</p>
                                                    <p class="transport-code">Driver: {{ $bus->driver_name ?? 'N/A' }}</p>
                                                    <p class="transport-code">Phone: {{ $bus->driver_phone ?? 'N/A' }}</p>
                                                    <p class="transport-code">Capacity: {{ $bus->capacity ?? 'N/A' }} students</p>
                                                </div>
                                                @if(!$loop->last)
                                                    <hr style="margin: 10px 0; border-color: #e9ecef;">
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="no-transport">
                                <div class="no-transport-icon">
                                    <i class="bx bx-bus-school"></i>
                                </div>
                                <h4>No Transport Required</h4>
                                <p>This student does not require transportation services.</p>
                            </div>
                            @endif
                        </div>
                    </div>                    <!-- Fee Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-money"></i>
                            </div>
                            <div class="section-title">
                                <h3>Fee Information</h3>
                                <p>Financial details and payment status</p>
                            </div>
                        </div>
                        <div class="section-content">
                        @if($student->feeInvoices && $student->feeInvoices->count() > 0)
                            <div class="fee-overview">
                                <div class="fee-summary-cards">
                                    <div class="fee-card total">
                                        <div class="fee-icon">
                                            <i class="bx bx-calculator"></i>
                                        </div>
                                        <div class="fee-content">
                                            <h4>TSH {{ number_format($student->feeInvoices->sum('total_amount')) }}</h4>
                                            <p>Total Invoiced</p>
                                        </div>
                                    </div>
                                    <div class="fee-card paid">
                                        <div class="fee-icon">
                                            <i class="bx bx-check-circle"></i>
                                        </div>
                                        <div class="fee-content">
                                            <h4>TSH {{ number_format($student->feeInvoices->sum('paid_amount')) }}</h4>
                                            <p>Total Paid</p>
                                        </div>
                                    </div>
                                    <div class="fee-card balance">
                                        <div class="fee-icon">
                                            <i class="bx bx-time"></i>
                                        </div>
                                        <div class="fee-content">
                                            <h4>TSH {{ number_format($student->feeInvoices->sum('total_amount') - $student->feeInvoices->sum('paid_amount')) }}</h4>
                                            <p>Outstanding</p>
                                        </div>
                                    </div>
                                    <div class="fee-card rate">
                                        <div class="fee-icon">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                        <div class="fee-content">
                                            <h4>{{ $student->feeInvoices->sum('total_amount') > 0 ? round(($student->feeInvoices->sum('paid_amount') / $student->feeInvoices->sum('total_amount')) * 100) : 0 }}%</h4>
                                            <p>Payment Rate</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="fee-breakdown">
                                    <h5>Fee Invoice Breakdown</h5>
                                    <div class="fee-table-container">
                                        <table class="fee-table">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Academic Year</th>
                                                    <th>Class</th>
                                                    <th>Fee Group</th>
                                                    <th>Period</th>
                                                    <th>Amount</th>
                                                    <th>Paid</th>
                                                    <th>Balance</th>
                                                    <th>Status</th>
                                                    <th>Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($student->feeInvoices as $invoice)
                                                <tr>
                                                    <td>
                                                        <div class="invoice-number">
                                                            <a href="{{ route('school.fee-invoices.student', $student) }}" class="text-decoration-none">
                                                                {{ $invoice->invoice_number }}
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>{{ $invoice->academicYear->year_name ?? 'N/A' }}</td>
                                                    <td>{{ $invoice->classe->name ?? 'N/A' }}</td>
                                                    <td>{{ $invoice->feeGroup->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @switch($invoice->period)
                                                            @case(1)
                                                                Quarter 1
                                                                @break
                                                            @case(2)
                                                                Quarter 2
                                                                @break
                                                            @case(3)
                                                                Quarter 3
                                                                @break
                                                            @case(4)
                                                                Quarter 4
                                                                @break
                                                            @case(5)
                                                                Full Year
                                                                @break
                                                            @default
                                                                Period {{ $invoice->period }}
                                                        @endswitch
                                                    </td>
                                                    <td class="amount">TSH {{ number_format($invoice->total_amount) }}</td>
                                                    <td class="paid">TSH {{ number_format($invoice->paid_amount ?? 0) }}</td>
                                                    <td class="balance">TSH {{ number_format($invoice->total_amount - ($invoice->paid_amount ?? 0)) }}</td>
                                                    <td>
                                                        @php
                                                            $balance = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                                                            $isOverdue = $balance > 0 && $invoice->due_date && $invoice->due_date->isPast();
                                                        @endphp
                                                        @if($balance == 0)
                                                            <span class="status-badge paid">Paid</span>
                                                        @elseif($balance == $invoice->total_amount)
                                                            <span class="status-badge {{ $isOverdue ? 'overdue' : 'pending' }}">{{ $isOverdue ? 'Overdue' : 'Unpaid' }}</span>
                                                        @else
                                                            <span class="status-badge partial">Partial</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($invoice->due_date)
                                                            <span class="{{ $invoice->due_date->isPast() && $balance > 0 ? 'text-danger' : 'text-muted' }}">
                                                                {{ $invoice->due_date->format('M d, Y') }}
                                                            </span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Payment History Section -->
                                <div class="payment-history mt-4">
                                    <h5>Recent Payments</h5>
                                    @php
                                        $payments = collect();
                                        foreach($student->feeInvoices as $invoice) {
                                            $invoicePayments = $invoice->payments()->with('bankAccount')->orderBy('date', 'desc')->get();
                                            $payments = $payments->merge($invoicePayments);
                                        }
                                        $recentPayments = $payments->sortByDesc('date')->take(5);
                                    @endphp

                                    @if($recentPayments->count() > 0)
                                        <div class="payment-history-table">
                                            <table class="fee-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Invoice</th>
                                                        <th>Amount</th>
                                                        <th>Method</th>
                                                        <th>Reference</th>
                                                        <th>Bank Account</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentPayments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->date ? $payment->date->format('M d, Y') : 'N/A' }}</td>
                                                        <td>{{ $payment->reference }}</td>
                                                        <td class="paid">TSH {{ number_format($payment->amount) }}</td>
                                                        <td>
                                                            @switch($payment->reference_type)
                                                                @case('fee_invoice')
                                                                    Fee Payment
                                                                    @break
                                                                @default
                                                                    {{ ucfirst(str_replace('_', ' ', $payment->reference_type)) }}
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                                        <td>{{ $payment->bankAccount->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($payments->count() > 5)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('school.fee-invoices.student', $student) }}" class="btn btn-outline-primary btn-sm">
                                                    View All Payments
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="no-payments text-center py-4">
                                            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No payment records found</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="no-fees">
                                <div class="no-fees-icon">
                                    <i class="bx bx-money"></i>
                                </div>
                                <h4>No Fee Records</h4>
                                <p>No fee invoices have been generated for this student yet.</p>
                                <a href="{{ route('school.fee-invoices.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus"></i> Generate Invoice
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Guardian Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-user-check"></i>
                            </div>
                            <div class="section-title">
                                <h3>Guardian Information</h3>
                                <p>Parent and guardian details</p>
                            </div>
                        </div>
                        <div class="section-content">
                            @if($student->guardians && $student->guardians->isNotEmpty())
                            <div class="guardians-list">
                                @foreach($student->guardians as $guardian)
                                <div class="guardian-card">
                                    <div class="guardian-header">
                                        <div class="guardian-avatar">
                                            <i class="bx bx-user"></i>
                                        </div>
                                        <div class="guardian-basic">
                                            <h5>{{ $guardian->name }}</h5>
                                            <p class="relationship">{{ ucfirst($guardian->pivot->relationship) }}</p>
                                        </div>
                                    </div>
                                    <div class="guardian-details">
                                        <div class="guardian-contact">
                                            <div class="contact-item">
                                                <i class="bx bx-phone"></i>
                                                <span>{{ $guardian->phone }}</span>
                                            </div>
                                            @if($guardian->alt_phone)
                                            <div class="contact-item">
                                                <i class="bx bx-phone-call"></i>
                                                <span>{{ $guardian->alt_phone }}</span>
                                            </div>
                                            @endif
                                            @if($guardian->email)
                                            <div class="contact-item">
                                                <i class="bx bx-envelope"></i>
                                                <span>{{ $guardian->email }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="guardian-address">
                                            <i class="bx bx-map"></i>
                                            <span>{{ $guardian->address }}</span>
                                        </div>
                                        @if($guardian->occupation)
                                        <div class="guardian-occupation">
                                            <i class="bx bx-briefcase"></i>
                                            <span>{{ $guardian->occupation }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="no-guardians">
                                <div class="no-guardians-icon">
                                    <i class="bx bx-user-x"></i>
                                </div>
                                <h5>No Guardian Information</h5>
                                <p>No guardian details have been added yet.</p>
                                <a href="{{ route('school.students.assign-parents', $student) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus"></i> Add Guardian
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-cog"></i>
                            </div>
                            <div class="section-title">
                                <h3>Quick Actions</h3>
                                <p>Common tasks and operations</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="quick-actions">
                                <a href="{{ route('school.students.edit', $student) }}" class="action-btn primary">
                                    <i class="bx bx-edit"></i>
                                    <span>Edit Profile</span>
                                </a>
                                <a href="{{ route('school.students.assign-parents', $student) }}" class="action-btn success">
                                    <i class="bx bx-user-plus"></i>
                                    <span>Manage Guardians</span>
                                </a>
                                <a href="#" class="action-btn warning" onclick="printProfile()">
                                    <i class="bx bx-printer"></i>
                                    <span>Print Profile</span>
                                </a>
                                <a href="mailto:{{ $student->email ?? '' }}" class="action-btn info">
                                    <i class="bx bx-envelope"></i>
                                    <span>Send Email</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="profile-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="bx bx-info-circle"></i>
                            </div>
                            <div class="section-title">
                                <h3>Additional Info</h3>
                                <p>Extra details and notes</p>
                            </div>
                        </div>
                        <div class="section-content">
                            <div class="additional-info">
                                <div class="info-item">
                                    <label>Registration Date</label>
                                    <div class="value">{{ $student->created_at->format('F d, Y') }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Last Updated</label>
                                    <div class="value">{{ $student->updated_at->format('F d, Y') }}</div>
                                </div>
                                <div class="info-item">
                                    <label>Profile Status</label>
                                    <div class="value">
                                        <span class="status-indicator active">
                                            <i class="bx bx-check"></i> Complete
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printProfile() {
    window.print();
}
</script>

<style>
/* Student Profile Header */
.student-profile-header {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 50%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
    position: relative;
    overflow: hidden;
}

.student-profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50px, -50px);
}

.profile-cover {
    display: flex;
    align-items: center;
    gap: 30px;
    flex-wrap: wrap;
}

.profile-avatar-section {
    text-align: center;
}

.profile-avatar {
    position: relative;
    display: inline-block;
}

.avatar-image {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.avatar-icon {
    font-size: 3rem;
    color: rgba(255,255,255,0.8);
}

.profile-status {
    margin-top: 10px;
}

.status-badge {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-badge.active {
    background: rgba(40, 167, 69, 0.9);
}

.profile-info-section {
    flex: 1;
    min-width: 300px;
}

.profile-name-section h1 {
    margin: 0 0 5px 0;
    font-size: 2.2rem;
    font-weight: 700;
}

.student-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.profile-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.detail-icon {
    background: rgba(255,255,255,0.2);
    padding: 8px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.detail-icon i {
    font-size: 1.2rem;
}

.detail-content {
    flex: 1;
}

.detail-label {
    display: block;
    font-size: 0.85rem;
    opacity: 0.8;
    margin-bottom: 2px;
}

.detail-value {
    display: block;
    font-weight: 600;
    font-size: 1rem;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 150px;
}

.profile-actions .btn {
    border: 2px solid rgba(13, 110, 253, 0.3);
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    background: rgba(13, 110, 253, 0.2);
}

.profile-actions .btn:hover {
    background: #0d6efd;
    color: white;
    transform: translateY(-2px);
    border-color: #0d6efd;
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
}

/* Stats Overview */
.stats-overview {
    margin: 30px 0;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border-left: 4px solid;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-card.primary {
    border-left-color: #0d6efd;
}

.stat-card.success {
    border-left-color: #198754;
}

.stat-card.warning {
    border-left-color: #fd7e14;
}

.stat-card.info {
    border-left-color: #0dcaf0;
}

.stat-icon {
    background: rgba(13, 110, 253, 0.1);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: #0d6efd;
}

.stat-card.success .stat-icon {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.stat-card.warning .stat-icon {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}

.stat-card.info .stat-icon {
    background: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.stat-label {
    margin: 5px 0 0 0;
    color: #666;
    font-weight: 500;
}

/* Profile Sections */
.profile-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, rgba(13, 110, 253, 0.05) 100%);
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 3px solid #0d6efd;
    position: relative;
}

.section-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0d6efd 0%, #667eea 50%, #764ba2 100%);
}

.section-icon {
    background: #0d6efd;
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.section-title h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
}

.section-title p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9rem;
}

.section-content {
    padding: 25px;
}

/* Personal Information */
.info-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.info-row.full-width {
    grid-template-columns: 1fr;
}

.info-col {
    display: flex;
    flex-direction: column;
}

.info-item {
    margin-bottom: 15px;
}

.info-label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.info-value {
    color: #333;
    font-size: 1rem;
    line-height: 1.4;
}

.info-value.address {
    font-style: italic;
}

.gender-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.gender-badge.male {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.gender-badge.female {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

/* Academic Information */
.academic-header {
    background: linear-gradient(135deg, #0d6efd 0%, #4facfe 50%, #00f2fe 100%);
    position: relative;
    overflow: hidden;
    color: white;
}

.academic-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.academic-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 100%);
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
}

.academic-progress-indicator {
    margin-left: auto;
    display: flex;
    align-items: center;
}

.progress-ring {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-ring svg circle:nth-child(2) {
    transition: stroke-dashoffset 0.3s ease;
}

.progress-text {
    position: absolute;
    text-align: center;
    color: #28a745;
    font-weight: 700;
}

.progress-number {
    display: block;
    font-size: 1rem;
    line-height: 1;
}

.progress-label {
    display: block;
    font-size: 0.7rem;
    opacity: 0.8;
}

.academic-overview {
    margin-bottom: 30px;
}

.academic-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-item {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 100%);
    border-radius: 15px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    color: white;
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(13, 110, 253, 0.4);
}

.stat-item:nth-child(2) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.2);
}

.stat-item:nth-child(2):hover {
    box-shadow: 0 12px 35px rgba(79, 172, 254, 0.3);
}

.stat-item:nth-child(3) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    box-shadow: 0 8px 25px rgba(67, 233, 123, 0.2);
}

.stat-item:nth-child(3):hover {
    box-shadow: 0 12px 35px rgba(67, 233, 123, 0.3);
}

.stat-icon-wrapper {
    background: rgba(255,255,255,0.2);
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    backdrop-filter: blur(10px);
}

.stat-details {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 2px;
}

.stat-label {
    display: block;
    font-size: 0.9rem;
    opacity: 0.9;
}

.academic-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.academic-detail-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.academic-detail-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid #dee2e6;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.admission-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.class-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.card-title-section h4 {
    margin: 0 0 3px 0;
    color: #333;
    font-size: 1.2rem;
    font-weight: 600;
}

.card-subtitle {
    color: #666;
    font-size: 0.85rem;
    font-weight: 500;
}

.card-content {
    padding: 25px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f1f3f4;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #555;
    font-weight: 500;
    font-size: 0.9rem;
}

.detail-value {
    color: #333;
    font-weight: 600;
    font-size: 0.95rem;
}

.admission-number {
    color: #667eea;
    font-family: 'Courier New', monospace;
    background: rgba(102, 126, 234, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
}

.class-name {
    color: #f5576c;
    background: rgba(245, 87, 108, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
}

.stream-name {
    color: #00f2fe;
    background: rgba(0, 242, 254, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
}

.academic-year {
    color: #28a745;
    background: rgba(40, 167, 69, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill.active {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.boarding-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.boarding-badge.day {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.boarding-badge.boarding {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.stats-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.metric-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.metric-item:hover {
    background: #e9ecef;
    transform: scale(1.02);
}

.metric-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.metric-item:nth-child(2) .metric-circle {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
}

.metric-item:nth-child(3) .metric-circle {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.metric-info {
    flex: 1;
}

.metric-label {
    display: block;
    color: #333;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 2px;
}

.metric-desc {
    display: block;
    color: #666;
    font-size: 0.8rem;
}

.academic-timeline {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    padding: 25px;
    border: 1px solid #e9ecef;
}

.timeline-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
}

.timeline-container {
    position: relative;
    padding-left: 30px;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #0d6efd, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 40px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.timeline-marker.admission {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 100%);
}

.timeline-marker.current {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.timeline-content h6 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1rem;
    font-weight: 600;
}

.timeline-content p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}

.timeline-date {
    color: #999;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Transport Information */
.transport-overview {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.transport-status {
    display: flex;
    justify-content: center;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
}

.transport-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.transport-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: transform 0.3s ease;
}

.transport-card:hover {
    transform: translateY(-3px);
}

.transport-icon {
    background: #198754;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.transport-info h5 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1rem;
}

.transport-name {
    color: #198754;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.transport-code {
    color: #666;
    font-size: 0.85rem;
    margin: 2px 0;
}

.transport-desc {
    color: #777;
    font-size: 0.85rem;
    font-style: italic;
    margin: 4px 0 0 0;
}

.no-transport {
    text-align: center;
    padding: 40px 20px;
}

.no-transport-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 15px;
}

.no-transport h4 {
    color: #666;
    margin-bottom: 10px;
}

.no-transport p {
    color: #888;
}

/* Fee Information */
.fee-overview {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.fee-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.fee-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.fee-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.fee-card.total {
    border-color: #0d6efd;
}

.fee-card.paid {
    border-color: #198754;
}

.fee-card.balance {
    border-color: #fd7e14;
}

.fee-card.rate {
    border-color: #6f42c1;
}

.fee-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.3rem;
}

.fee-card.total .fee-icon {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.fee-card.paid .fee-icon {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.fee-card.balance .fee-icon {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}

.fee-card.rate .fee-icon {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}

.fee-content h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
}

.fee-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9rem;
}

.fee-breakdown h5 {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.1rem;
}

.fee-table-container {
    overflow-x: auto;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.fee-table th,
.fee-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.fee-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.fee-table .fee-type {
    display: flex;
    align-items: center;
    gap: 8px;
}

.fee-table .amount {
    font-weight: 600;
    color: #0d6efd;
}

.fee-table .paid {
    color: #198754;
}

.fee-table .balance {
    color: #fd7e14;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.paid {
    background: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.status-badge.pending {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}

.status-badge.overdue {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-badge.partial {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}

.no-fees {
    text-align: center;
    padding: 40px 20px;
}

.no-fees-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 15px;
}

.no-payments {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
}

.invoice-number {
    font-weight: 600;
    color: #0d6efd;
}

.invoice-number:hover {
    color: #0b5ed7;
}

.payment-history h5 {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.1rem;
}

.payment-history-table {
    margin-bottom: 15px;
}

/* Guardian Information */
.guardians-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.guardian-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid #e3f2fd;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.3s ease;
}

.guardian-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
}

.guardian-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.guardian-avatar {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 100%);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

.guardian-basic h5 {
    margin: 0 0 5px 0;
    color: #333;
}

.guardian-basic .relationship {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    font-style: italic;
}

.guardian-contact {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #555;
    font-size: 0.9rem;
}

.guardian-address,
.guardian-occupation {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    color: #555;
    font-size: 0.9rem;
    margin-top: 8px;
}

.no-guardians {
    text-align: center;
    padding: 30px 20px;
}

.no-guardians-icon {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 15px;
}

.no-guardians h5 {
    color: #666;
    margin-bottom: 10px;
}

.no-guardians p {
    color: #888;
    margin-bottom: 15px;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: left;
}

.action-btn.primary {
    background: linear-gradient(135deg, #0d6efd 0%, #667eea 100%);
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

.action-btn.primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #4c63d2 100%);
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.5);
}

.action-btn.success {
    background: #198754;
}

.action-btn.warning {
    background: #fd7e14;
}

.action-btn.info {
    background: #0dcaf0;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Additional Information */
.additional-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.additional-info .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.additional-info .info-item:last-child {
    border-bottom: none;
}

.additional-info label {
    font-weight: 600;
    color: #555;
    margin: 0;
}

.additional-info .value {
    color: #333;
    font-weight: 500;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .student-profile-header {
        padding: 20px;
    }

    .profile-cover {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }

    .profile-details-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .profile-actions {
        flex-direction: row;
        justify-content: center;
        min-width: auto;
    }

    .profile-actions .btn {
        flex: 1;
        min-width: 120px;
    }

    .stats-overview .row > div {
        margin-bottom: 15px;
    }

    .info-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .academic-stats,
    .transport-details,
    .fee-summary-cards {
        grid-template-columns: 1fr;
    }

    .academic-details-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .stat-item {
        padding: 15px;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .card-header {
        padding: 15px 20px;
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .card-content {
        padding: 20px;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .stats-metrics {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .metric-item {
        padding: 12px;
    }

    .timeline-container {
        padding-left: 20px;
    }

    .timeline-item {
        padding-left: 30px;
    }

    .timeline-marker {
        left: -15px;
        width: 30px;
        height: 30px;
        font-size: 0.9rem;
    }

    .fee-table {
        font-size: 0.85rem;
    }

    .fee-table th,
    .fee-table td {
        padding: 8px 10px;
    }

    .guardian-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .action-btn span {
        display: none;
    }

    .action-btn {
        justify-content: center;
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .student-name {
        font-size: 1.8rem !important;
    }

    .profile-actions {
        flex-direction: column;
    }

    .profile-actions .btn {
        width: 100%;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .action-btn span {
        display: inline;
    }
}
</style>
@endsection