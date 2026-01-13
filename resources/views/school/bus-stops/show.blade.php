@extends('layouts.main')

@section('title', 'Bus Stop Details - System View')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- System Header -->
        <div class="system-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="system-title">
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="bx bx-map-pin me-2 text-primary"></i>BUS STOP MANAGEMENT SYSTEM
                    </h4>
                    <p class="text-muted mb-0 small">Bus Stop Record Details</p>
                </div>
                <div class="system-actions">
                    <a href="{{ route('school.bus-stops.edit', $busStop) }}" class="btn btn-system-primary btn-sm me-2">
                        <i class="bx bx-edit me-1"></i>Edit Record
                    </a>
                    <a href="{{ route('school.bus-stops.index') }}" class="btn btn-system-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="record-info">
                    <span class="badge badge-system-status {{ $busStop->is_active ? 'bg-success' : 'bg-danger' }}">
                        <i class="bx {{ $busStop->is_active ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                        {{ $busStop->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                    <span class="ms-3 text-muted small">Record ID: {{ $busStop->id }}</span>
                </div>
                <div class="timestamp-info">
                    <small class="text-muted">
                        Last Modified: {{ $busStop->updated_at->format('M d, Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-12 col-xl-8">
                <!-- Primary Information Panel -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>PRIMARY INFORMATION
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="data-grid">
                            <!-- Row 1 -->
                            <div class="data-row">
                                <div class="data-field">
                                    <label class="field-label">Stop Code</label>
                                    <div class="field-value">
                                        <span class="system-badge">{{ $busStop->stop_code }}</span>
                                    </div>
                                </div>
                                <div class="data-field">
                                    <label class="field-label">Stop Name</label>
                                    <div class="field-value fw-semibold">{{ $busStop->stop_name }}</div>
                                </div>
                            </div>

                            <!-- Row 2 -->
                            <div class="data-row">
                                <div class="data-field">
                                    <label class="field-label">Fare Amount</label>
                                    <div class="field-value">
                                        @if($busStop->fare)
                                            <span class="currency-amount">{{ number_format($busStop->fare, 2) }}</span>
                                            <span class="currency-code">TZS</span>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="data-field">
                                    <label class="field-label">Sequence Order</label>
                                    <div class="field-value">
                                        <span class="sequence-badge">{{ $busStop->sequence_order }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Coordinates Row (if available) -->
                            @if($busStop->latitude || $busStop->longitude)
                            <div class="data-row">
                                <div class="data-field">
                                    <label class="field-label">Latitude</label>
                                    <div class="field-value">{{ $busStop->latitude ?? 'Not set' }}</div>
                                </div>
                                <div class="data-field">
                                    <label class="field-label">Longitude</label>
                                    <div class="field-value">{{ $busStop->longitude ?? 'Not set' }}</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Description Section -->
                        <div class="description-section mt-4">
                            <label class="field-label mb-2">Description</label>
                            <div class="description-content">
                                @if($busStop->description)
                                    <p class="mb-0">{{ $busStop->description }}</p>
                                @else
                                    <p class="text-muted mb-0 fst-italic">No description provided for this bus stop.</p>
                                @endif
                            </div>
                        </div>

                        <!-- System Timestamps -->
                        <div class="system-timestamps mt-4">
                            <div class="timestamp-grid">
                                <div class="timestamp-item">
                                    <label class="timestamp-label">Created</label>
                                    <div class="timestamp-value">{{ $busStop->created_at->format('M d, Y H:i:s') }}</div>
                                </div>
                                <div class="timestamp-item">
                                    <label class="timestamp-label">Last Updated</label>
                                    <div class="timestamp-value">{{ $busStop->updated_at->format('M d, Y H:i:s') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associated Routes Panel -->
                @if($busStop->routes->count() > 0)
                <div class="system-panel">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-route me-2"></i>ASSOCIATED ROUTES
                            <span class="record-count">({{ $busStop->routes->count() }} records)</span>
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="system-table-container">
                            <table class="system-table">
                                <thead class="table-header">
                                    <tr>
                                        <th>Route Code</th>
                                        <th>Route Name</th>
                                        <th>Fare Amount</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($busStop->routes as $route)
                                    <tr class="table-row">
                                        <td>
                                            <span class="system-badge">{{ $route->route_code }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $route->route_name }}</td>
                                        <td>
                                            <span class="currency-amount">{{ number_format($route->fare, 2) }}</span>
                                            <span class="currency-code">TZS</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('school.routes.show', $route) }}" class="btn btn-system-link btn-sm" title="View Route Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- System Sidebar -->
            <div class="col-12 col-xl-4">
                <!-- Quick Actions Panel -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-cog me-2"></i>QUICK ACTIONS
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="action-buttons">
                            <a href="{{ route('school.bus-stops.edit', $busStop) }}" class="btn btn-system-primary w-100 mb-2">
                                <i class="bx bx-edit me-2"></i>Edit This Record
                            </a>
                            <a href="{{ route('school.bus-stops.create') }}" class="btn btn-system-success w-100 mb-2">
                                <i class="bx bx-plus me-2"></i>Create New Record
                            </a>
                            <a href="{{ route('school.bus-stops.index') }}" class="btn btn-system-secondary w-100">
                                <i class="bx bx-list-ul me-2"></i>View All Records
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Panel -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>STATISTICS
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="stats-container">
                            <div class="stat-item">
                                <div class="stat-value text-primary">{{ $busStop->routes->count() }}</div>
                                <div class="stat-label">Routes Assigned</div>
                            </div>
                            <hr class="stat-divider">
                            <div class="stat-item">
                                <div class="stat-value text-info">{{ $busStop->sequence_order }}</div>
                                <div class="stat-label">Sequence Position</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Panel (if coordinates available) -->
                @if($busStop->latitude && $busStop->longitude)
                <div class="system-panel">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-map me-2"></i>LOCATION DATA
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="location-info">
                            <div class="location-icon">
                                <i class="bx bx-map-pin text-primary"></i>
                            </div>
                            <div class="coordinates-display">
                                <div class="coord-label">GPS Coordinates</div>
                                <div class="coord-values">
                                    {{ $busStop->latitude }}, {{ $busStop->longitude }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* System Interface Styles - Professional Business Application */

    /* System Header */
    .system-header {
        background: #ffffff;
        border-bottom: 2px solid #e9ecef;
        padding: 1.5rem 0;
    }

    .system-title h4 {
        color: #2c3e50;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 1.1rem;
    }

    .system-actions .btn {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    /* Status Bar */
    .status-bar {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
    }

    .record-info {
        font-weight: 600;
    }

    .badge-system-status {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.75rem;
    }

    /* System Panels */
    .system-panel {
        border: 1px solid #dee2e6;
        background: #ffffff;
        margin-bottom: 1.5rem;
    }

    .panel-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .panel-title {
        color: #495057;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85rem;
    }

    .record-count {
        color: #6c757d;
        font-weight: 500;
        font-size: 0.8rem;
    }

    .panel-body {
        padding: 1.25rem;
    }

    /* Data Grid Layout */
    .data-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .data-row {
        display: flex;
        gap: 2rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .data-row:last-child {
        border-bottom: none;
    }

    .data-field {
        flex: 1;
        min-width: 0;
    }

    .field-label {
        display: block;
        font-weight: 600;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .field-value {
        color: #212529;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    /* System Badges */
    .system-badge {
        background: #007bff;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sequence-badge {
        background: #6c757d;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* Currency Display */
    .currency-amount {
        font-weight: 700;
        color: #28a745;
        font-size: 1rem;
    }

    .currency-code {
        color: #6c757d;
        font-weight: 500;
        font-size: 0.8rem;
        margin-left: 0.25rem;
    }

    /* Description Section */
    .description-section {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .description-content {
        background: #f8f9fa;
        padding: 1rem;
        border-left: 3px solid #6c757d;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    /* System Timestamps */
    .system-timestamps {
        border-top: 1px solid #e9ecef;
        padding-top: 1rem;
    }

    .timestamp-grid {
        display: flex;
        gap: 2rem;
    }

    .timestamp-item {
        flex: 1;
    }

    .timestamp-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .timestamp-value {
        color: #6c757d;
        font-size: 0.85rem;
        font-family: 'Courier New', monospace;
    }

    /* System Table */
    .system-table-container {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        overflow: hidden;
    }

    .system-table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-header {
        background: #f8f9fa;
    }

    .table-header th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 700;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #dee2e6;
    }

    .table-header th:last-child {
        text-align: center;
    }

    .table-row {
        border-bottom: 1px solid #f1f3f4;
    }

    .table-row:hover {
        background: #f8f9fa;
    }

    .table-row td {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }

    .table-row td:last-child {
        text-align: center;
    }

    /* System Buttons */
    .btn-system-primary {
        background: #007bff;
        border: 1px solid #007bff;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .btn-system-primary:hover {
        background: #0056b3;
        border-color: #0056b3;
        color: white;
    }

    .btn-system-success {
        background: #28a745;
        border: 1px solid #28a745;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .btn-system-success:hover {
        background: #1e7e34;
        border-color: #1e7e34;
        color: white;
    }

    .btn-system-secondary {
        background: #6c757d;
        border: 1px solid #6c757d;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .btn-system-secondary:hover {
        background: #545b62;
        border-color: #545b62;
        color: white;
    }

    .btn-system-link {
        background: transparent;
        border: 1px solid #007bff;
        color: #007bff;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .btn-system-link:hover {
        background: #007bff;
        color: white;
    }

    /* Statistics */
    .stats-container {
        text-align: center;
    }

    .stat-item {
        padding: 1rem 0;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }

    .stat-divider {
        border: none;
        border-top: 1px solid #dee2e6;
        margin: 0.5rem 0;
    }

    /* Location Info */
    .location-info {
        text-align: center;
    }

    .location-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .coord-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .coord-values {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #212529;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .data-row {
            flex-direction: column;
            gap: 1rem;
        }

        .timestamp-grid {
            flex-direction: column;
            gap: 1rem;
        }

        .system-header .d-flex {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .status-bar .d-flex {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }

        .panel-body {
            padding: 1rem;
        }

        .table-row td {
            padding: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips for system interface
        $('[title]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });

        // System interface initialization
        console.log('Bus Stop Management System - Record Details View Loaded');

        // Add focus states for better accessibility
        $('.btn-system-primary, .btn-system-success, .btn-system-secondary').on('focus', function() {
            $(this).addClass('focused');
        }).on('blur', function() {
            $(this).removeClass('focused');
        });

        // Simple table row highlighting
        $('.table-row').on('mouseenter', function() {
            $(this).addClass('hover');
        }).on('mouseleave', function() {
            $(this).removeClass('hover');
        });
    });
</script>
@endpush