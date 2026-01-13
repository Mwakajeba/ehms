@extends('layouts.main')

@section('title', 'Reception Reports')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'Reports', 'url' => '#', 'icon' => 'bx bx-bar-chart']
            ]" />
            <h6 class="mb-0 text-uppercase">RECEPTION REPORTS</h6>
            <hr />

            <!-- Date Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('hospital.reception.reports.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Visits</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_visits']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Completed Visits</h6>
                            <h3 class="mb-0">{{ number_format($summary['completed_visits']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Avg. Total Time</h6>
                            <h3 class="mb-0">
                                @php
                                    $avgSeconds = $summary['avg_total_time'];
                                    $avgHours = floor($avgSeconds / 3600);
                                    $avgMinutes = floor(($avgSeconds % 3600) / 60);
                                @endphp
                                {{ sprintf('%02d:%02d', $avgHours, $avgMinutes) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients by Category -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-pie-chart me-2"></i>Patients by Category
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Visit Type</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total = $patientsByCategory->sum();
                                        @endphp
                                        @foreach($patientsByCategory as $type => $count)
                                            <tr>
                                                <td><strong>{{ ucfirst(str_replace('_', ' ', $type)) }}</strong></td>
                                                <td>{{ number_format($count) }}</td>
                                                <td>
                                                    @if($total > 0)
                                                        {{ number_format(($count / $total) * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if($patientsByCategory->isEmpty())
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No data available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time per Department -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-time me-2"></i>Time Spent per Department
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th>Type</th>
                                            <th>Visit Count</th>
                                            <th>Avg. Waiting Time</th>
                                            <th>Avg. Service Time</th>
                                            <th>Total Waiting Time</th>
                                            <th>Total Service Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timePerDepartment as $dept)
                                            @php
                                                $avgWaitingHours = floor($dept->avg_waiting_seconds / 3600);
                                                $avgWaitingMinutes = floor(($dept->avg_waiting_seconds % 3600) / 60);
                                                $avgServiceHours = floor($dept->avg_service_seconds / 3600);
                                                $avgServiceMinutes = floor(($dept->avg_service_seconds % 3600) / 60);
                                                $totalWaitingHours = floor($dept->total_waiting_seconds / 3600);
                                                $totalWaitingMinutes = floor(($dept->total_waiting_seconds % 3600) / 60);
                                                $totalServiceHours = floor($dept->total_service_seconds / 3600);
                                                $totalServiceMinutes = floor(($dept->total_service_seconds % 3600) / 60);
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $dept->name }}</strong></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ ucfirst(str_replace('_', ' ', $dept->type)) }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($dept->visit_count) }}</td>
                                                <td>{{ sprintf('%02d:%02d', $avgWaitingHours, $avgWaitingMinutes) }}</td>
                                                <td>{{ sprintf('%02d:%02d', $avgServiceHours, $avgServiceMinutes) }}</td>
                                                <td>{{ sprintf('%02d:%02d', $totalWaitingHours, $totalWaitingMinutes) }}</td>
                                                <td>{{ sprintf('%02d:%02d', $totalServiceHours, $totalServiceMinutes) }}</td>
                                            </tr>
                                        @endforeach
                                        @if($timePerDepartment->isEmpty())
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No data available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Time per Visit -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>Total Time per Visit (Top 50)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>Visit Type</th>
                                            <th>Departments</th>
                                            <th>Total Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($totalTimePerVisit as $visit)
                                            <tr>
                                                <td><strong>{{ $visit['visit_number'] }}</strong></td>
                                                <td>{{ $visit['patient_name'] }}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ ucfirst(str_replace('_', ' ', $visit['visit_type'])) }}
                                                    </span>
                                                </td>
                                                <td>{{ $visit['departments_count'] }}</td>
                                                <td>
                                                    <span class="text-primary">
                                                        <i class="bx bx-time"></i> {{ $visit['total_time'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if($totalTimePerVisit->isEmpty())
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No data available</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
