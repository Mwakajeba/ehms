@extends('layouts.main')

@section('title', 'Assign Routes to Bus')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- System Header -->
        <div class="system-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="system-title">
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="bx bx-route me-2 text-primary"></i>ASSIGN ROUTES TO BUS
                    </h4>
                    <p class="text-muted mb-0 small">Manage transportation routes for bus assignments</p>
                </div>
                <div class="system-actions">
                    <a href="{{ route('school.buses.show', $bus) }}" class="btn btn-system-secondary btn-sm me-2">
                        <i class="bx bx-arrow-back me-1"></i>Back to Bus
                    </a>
                    <a href="{{ route('school.buses.index') }}" class="btn btn-system-secondary btn-sm">
                        <i class="bx bx-list-ul me-1"></i>All Buses
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="record-info">
                    <span class="badge badge-system-status {{ $bus->is_active ? 'bg-success' : 'bg-danger' }}">
                        <i class="bx {{ $bus->is_active ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                        {{ $bus->is_active ? 'ACTIVE BUS' : 'INACTIVE BUS' }}
                    </span>
                    <span class="ms-3 text-muted small">
                        <strong>Bus:</strong> {{ $bus->bus_number }} |
                        <strong>Capacity:</strong> {{ $bus->capacity }} |
                        <strong>Driver:</strong> {{ $bus->driver_name }}
                    </span>
                </div>
                <div class="timestamp-info">
                    <small class="text-muted">
                        Record ID: {{ $bus->id }}
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-12 col-xl-8">
                <!-- Route Assignment Form -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-plus-circle me-2"></i>SELECT ROUTES TO ASSIGN
                        </h6>
                    </div>
                    <div class="panel-body">
                        <form action="{{ route('school.buses.update-assigned-routes', $bus) }}" method="POST" id="assignRoutesForm">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Instructions:</strong> Select one or more routes to assign to this bus. The bus will serve all selected routes for student transportation.
                                </div>
                            </div>

                            @if($routes->count() > 0)
                                <div class="routes-selection-grid">
                                    @foreach($routes as $route)
                                    <div class="route-card {{ in_array($route->id, $assignedRoutes) ? 'route-assigned' : '' }}">
                                        <div class="route-header">
                                            <div class="form-check">
                                                <input class="form-check-input route-checkbox" type="checkbox"
                                                       id="route_{{ $route->id }}" name="routes[]" value="{{ $route->id }}"
                                                       {{ in_array($route->id, $assignedRoutes) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="route_{{ $route->id }}">
                                                    <span class="route-code">{{ $route->route_code }}</span>
                                                </label>
                                            </div>
                                            <div class="route-status">
                                                @if(in_array($route->id, $assignedRoutes))
                                                    <span class="badge bg-success">Assigned</span>
                                                @else
                                                    <span class="badge bg-secondary">Available</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="route-body">
                                            <h6 class="route-name">{{ $route->route_name }}</h6>
                                            @if($route->description)
                                                <p class="route-description small text-muted">{{ $route->description }}</p>
                                            @endif
                                            <div class="route-stats">
                                                <small class="text-muted">
                                                    <i class="bx bx-group me-1"></i>{{ $route->students->count() }} Students |
                                                    <i class="bx bx-map-pin me-1"></i>{{ $route->busStops->count() }} Stops
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="selection-summary">
                                        <span id="selectedCount" class="badge bg-primary">0 routes selected</span>
                                    </div>
                                    <div class="form-actions">
                                        <a href="{{ route('school.buses.show', $bus) }}" class="btn btn-system-secondary">
                                            <i class="bx bx-x me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-system-primary ms-2">
                                            <i class="bx bx-save me-1"></i>Update Route Assignments
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-route bx-lg text-muted mb-3"></i>
                                    <h5 class="text-muted">No Routes Available</h5>
                                    <p class="text-muted">There are no transportation routes created yet.</p>
                                    <a href="{{ route('school.routes.create') }}" class="btn btn-system-primary">
                                        <i class="bx bx-plus me-1"></i>Create First Route
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Sidebar -->
            <div class="col-12 col-xl-4">
                <!-- Current Assignments Panel -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-check-circle me-2"></i>CURRENT ASSIGNMENTS
                            <span class="record-count">({{ $bus->routes->count() }} routes)</span>
                        </h6>
                    </div>
                    <div class="panel-body">
                        @if($bus->routes->count() > 0)
                            <div class="assigned-routes-list">
                                @foreach($bus->routes as $route)
                                <div class="assigned-route-item">
                                    <div class="route-info">
                                        <div class="route-code-badge">{{ $route->route_code }}</div>
                                        <div class="route-details">
                                            <div class="route-name-small">{{ $route->route_name }}</div>
                                            <small class="text-muted">{{ $route->students->count() }} students</small>
                                        </div>
                                    </div>
                                    <div class="route-actions">
                                        <a href="{{ route('school.routes.show', $route) }}" class="btn btn-sm btn-outline-info" title="View Route">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-route-x bx-lg text-muted mb-2"></i>
                                <p class="text-muted small mb-0">No routes currently assigned to this bus</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats Panel -->
                <div class="system-panel mb-4">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>ASSIGNMENT SUMMARY
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="stats-container">
                            <div class="stat-item">
                                <div class="stat-value text-primary">{{ $bus->routes->count() }}</div>
                                <div class="stat-label">Routes Assigned</div>
                            </div>
                            <hr class="stat-divider">
                            <div class="stat-item">
                                <div class="stat-value text-success">{{ $bus->routes->sum(fn($route) => $route->students->count()) }}</div>
                                <div class="stat-label">Total Students</div>
                            </div>
                            <hr class="stat-divider">
                            <div class="stat-item">
                                <div class="stat-value text-info">{{ $routes->count() }}</div>
                                <div class="stat-label">Available Routes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Panel -->
                <div class="system-panel">
                    <div class="panel-header">
                        <h6 class="panel-title mb-0">
                            <i class="bx bx-help-circle me-2"></i>HELP & TIPS
                        </h6>
                    </div>
                    <div class="panel-body">
                        <div class="help-content">
                            <h6>How Route Assignment Works:</h6>
                            <ul class="small mb-0">
                                <li>Each bus can serve multiple routes</li>
                                <li>Routes determine student pickup/drop-off points</li>
                                <li>Bus capacity should accommodate assigned students</li>
                                <li>Changes take effect immediately</li>
                            </ul>
                        </div>
                    </div>
                </div>
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

    /* Routes Selection Grid */
    .routes-selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .route-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        background: #ffffff;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .route-card:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }

    .route-card.route-assigned {
        border-color: #28a745;
        background: #f8fff9;
    }

    .route-card.route-assigned:hover {
        border-color: #28a745;
        box-shadow: 0 2px 8px rgba(40,167,69,0.1);
    }

    .route-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .route-code {
        background: #007bff;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .route-assigned .route-code {
        background: #28a745;
    }

    .route-body h6 {
        margin: 0 0 0.5rem 0;
        font-weight: 600;
        color: #2c3e50;
    }

    .route-description {
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .route-stats {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f0f0f0;
    }

    /* Assigned Routes List */
    .assigned-routes-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .assigned-route-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }

    .route-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .route-code-badge {
        background: #007bff;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .route-name-small {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.125rem;
    }

    .route-actions {
        display: flex;
        gap: 0.25rem;
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

    /* Help Content */
    .help-content ul {
        padding-left: 1rem;
    }

    .help-content li {
        margin-bottom: 0.25rem;
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

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 0.5rem;
    }

    .selection-summary {
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .routes-selection-grid {
            grid-template-columns: 1fr;
        }

        .route-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .assigned-route-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .route-info {
            width: 100%;
        }

        .route-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .panel-body {
            padding: 1rem;
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
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });

        // System interface initialization
        console.log('Bus Route Assignment System - Professional Interface Loaded');

        // Update selection count
        function updateSelectionCount() {
            const selectedCount = $('.route-checkbox:checked').length;
            $('#selectedCount').text(selectedCount + ' route' + (selectedCount !== 1 ? 's' : '') + ' selected');
        }

        // Initial count
        updateSelectionCount();

        // Update count on checkbox change
        $('.route-checkbox').on('change', function() {
            updateSelectionCount();

            // Visual feedback for card selection
            const card = $(this).closest('.route-card');
            if ($(this).is(':checked')) {
                card.addClass('route-assigned');
                card.find('.route-code').css('background', '#28a745');
                card.find('.route-status .badge').removeClass('bg-secondary').addClass('bg-success').text('Assigned');
            } else {
                card.removeClass('route-assigned');
                card.find('.route-code').css('background', '#007bff');
                card.find('.route-status .badge').removeClass('bg-success').addClass('bg-secondary').text('Available');
            }
        });

        // Make entire card clickable
        $('.route-card').on('click', function(e) {
            // Don't toggle if clicking on checkbox or label
            if ($(e.target).is('input[type="checkbox"], label')) {
                return;
            }

            const checkbox = $(this).find('.route-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        });

        // Form submission with loading state
        $('#assignRoutesForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...');

            // Re-enable after 3 seconds (in case of error)
            setTimeout(function() {
                submitBtn.prop('disabled', false).html(originalText);
            }, 3000);
        });

        // Add keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl+S to save
            if (e.ctrlKey && e.keyCode === 83) {
                e.preventDefault();
                $('#assignRoutesForm').submit();
            }

            // Ctrl+B to go back
            if (e.ctrlKey && e.keyCode === 66) {
                e.preventDefault();
                window.location.href = '{{ route("school.buses.show", $bus) }}';
            }
        });

        console.log('Bus route assignment interface initialized successfully');
    });
</script>
@endpush