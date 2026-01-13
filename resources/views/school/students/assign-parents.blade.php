@extends('layouts.main')

@section('title', 'Assign Parents - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => route('school.students.index'), 'icon' => 'bx bx-id-card'],
            ['label' => 'Assign Parents', 'url' => '#', 'icon' => 'bx bx-user-plus']
        ]" />

        <h6 class="mb-0 text-uppercase">ASSIGN PARENTS TO STUDENT</h6>
        <hr />

        <!-- Student Info Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            @if($student->passport_photo)
                                <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Student Photo" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bx bx-user fs-2 text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h5>
                                <p class="text-muted mb-0">
                                    Admission No: {{ $student->admission_number ?? 'N/A' }} |
                                    Class: {{ $student->class->name ?? 'N/A' }} |
                                    Stream: {{ $student->stream->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="ms-auto">
                                <a href="{{ route('school.students.edit', $student) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit Student
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Parents -->
        @if($student->guardians && $student->guardians->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Current Parents/Guardians</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($student->guardians as $guardian)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $guardian->name }}</h6>
                                                <p class="text-muted mb-1">{{ $guardian->pivot->relationship }}</p>
                                                <small class="text-muted">
                                                    <i class="bx bx-phone me-1"></i>{{ $guardian->phone }}
                                                    @if($guardian->alt_phone)
                                                        <br><i class="bx bx-phone me-1"></i>{{ $guardian->alt_phone }}
                                                    @endif
                                                    @if($guardian->email)
                                                        <br><i class="bx bx-envelope me-1"></i>{{ $guardian->email }}
                                                    @endif
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-parent-btn"
                                                    data-guardian-id="{{ $guardian->id }}"
                                                    data-guardian-name="{{ $guardian->name }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Remove this guardian">
                                                <i class="bx bx-trash-alt fs-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Assign New Parents Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Add Parents/Guardians</h6>
                    </div>
                    <div class="card-body">
                        <!-- Search Existing Parents Section -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="bx bx-search me-2"></i>Search Existing Parents
                            </h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" id="parentSearch" class="form-control" placeholder="Search by name, phone, or email...">
                                        <button class="btn btn-outline-primary" type="button" id="searchBtn">
                                            <i class="bx bx-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-success w-100" id="addNewParentBtn">
                                        <i class="bx bx-plus me-1"></i> Add New Parent
                                    </button>
                                </div>
                            </div>

                            <!-- Search Results -->
                            <div id="searchResults" class="mt-3" style="display: none;">
                                <div class="card border-info">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-info">Search Results</h6>
                                    </div>
                                    <div class="card-body" id="resultsContainer">
                                        <!-- Results will be loaded here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Parents Display -->
                            <div id="selectedParentsContainer" class="mt-3" style="display: none;">
                                <!-- Selected parents will be displayed here -->
                            </div>

                            <!-- Assign Selected Parents Button -->
                            <div class="mt-3 text-end" id="assignSelectedContainer" style="display: none;">
                                <button type="button" class="btn btn-primary" id="assignSelectedBtn">
                                    <i class="bx bx-save me-1"></i> Assign Selected Parents
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- New Parents Form -->
                        <div id="newParentsSection" style="display: none;">
                            <h6 class="text-success mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Add New Parents
                            </h6>
                            <form action="{{ route('school.students.store-assigned-parents', $student) }}" method="POST" id="newParentsForm">
                                @csrf

                                <div id="parentsContainer">
                                    <div class="parent-entry border rounded p-3 mb-3" data-parent-index="0">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="guardians[0][name]" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="guardians[0][relationship]" required>
                                                        <option value="">Select Relationship</option>
                                                        <option value="Father">Father</option>
                                                        <option value="Mother">Mother</option>
                                                        <option value="Guardian">Guardian</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                                    <input type="tel" class="form-control" name="guardians[0][phone]" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Alternative Phone</label>
                                                    <input type="tel" class="form-control" name="guardians[0][alt_phone]">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" name="guardians[0][email]" placeholder="guardian@example.com">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Occupation</label>
                                                    <input type="text" class="form-control" name="guardians[0][occupation]" placeholder="e.g., Teacher, Engineer, Business">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Parent/Guardian Address <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="guardians[0][address]" rows="2" required placeholder="Enter complete address"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-parent-btn d-none">
                                                <i class="bx bx-trash me-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-primary" id="addParentBtn">
                                        <i class="bx bx-plus me-1"></i> Add Another Parent
                                    </button>

                                    <div>
                                        <button type="button" class="btn btn-secondary me-2" id="cancelNewParents">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bx bx-save me-1"></i> Save New Parents
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Remove Parent Confirmation Modal -->
<div class="modal fade" id="removeParentModal" tabindex="-1" aria-labelledby="removeParentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeParentModalLabel">Remove Parent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="parentName"></strong> from this student?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveParent">Remove Parent</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }

    h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    .parent-entry {
        position: relative;
    }

    .parent-entry:not(:last-child) {
        margin-bottom: 1rem;
    }

    .remove-parent-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.3s ease;
        border: 2px solid #dc3545;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .remove-parent-btn:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .remove-parent-btn:hover i {
        color: white;
    }

    .remove-parent-btn i {
        color: #dc3545;
        transition: color 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let parentIndex = 1;
        let selectedParents = [];

        // Toggle new parents form
        $('#addNewParentBtn').on('click', function() {
            $('#newParentsSection').slideDown();
            $('#searchResults').hide();
            $('#parentSearch').val('');
        });

        $('#cancelNewParents').on('click', function() {
            $('#newParentsSection').slideUp();
            $('#parentsContainer').html(`
                <div class="parent-entry border rounded p-3 mb-3" data-parent-index="0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="guardians[0][name]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select" name="guardians[0][relationship]" required>
                                    <option value="">Select Relationship</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="guardians[0][phone]" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Alternative Phone</label>
                                <input type="tel" class="form-control" name="guardians[0][alt_phone]">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="guardians[0][email]" placeholder="guardian@example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" name="guardians[0][occupation]" placeholder="e.g., Teacher, Engineer, Business">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent/Guardian Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="guardians[0][address]" rows="2" required placeholder="Enter complete address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm remove-parent-btn d-none">
                            <i class="bx bx-trash me-1"></i> Remove
                        </button>
                    </div>
                </div>
            `);
            parentIndex = 1;
        });

        // Search functionality
        $('#searchBtn').on('click', function() {
            performSearch();
        });

        $('#parentSearch').on('keypress', function(e) {
            if (e.which === 13) {
                performSearch();
            }
        });

        function performSearch() {
            const query = $('#parentSearch').val().trim();
            if (!query) {
                alert('Please enter a search term');
                return;
            }

            $('#searchBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Searching...');

            $.ajax({
                url: '{{ route("school.api.parents.search", $student) }}',
                method: 'GET',
                data: { q: query },
                success: function(response) {
                    displaySearchResults(response.parents || []);
                },
                error: function(xhr) {
                    console.error('Search error:', xhr);
                    alert('Error searching parents. Please try again.');
                },
                complete: function() {
                    $('#searchBtn').prop('disabled', false).html('<i class="bx bx-search"></i> Search');
                }
            });
        }

        function displaySearchResults(parents) {
            if (parents.length === 0) {
                $('#resultsContainer').html('<p class="text-muted mb-0">No parents found matching your search.</p>');
                $('#searchResults').show();
                return;
            }

            let html = '<div class="row">';
            parents.forEach(function(parent) {
                const isSelected = selectedParents.some(p => p.id === parent.id);
                const badgeClass = isSelected ? 'success' : 'outline-primary';
                const badgeText = isSelected ? 'Selected' : 'Select';

                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">${parent.name}</h6>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bx bx-phone me-1"></i>${parent.phone}
                                        ${parent.alt_phone ? '<br><i class="bx bx-phone me-1"></i>' + parent.alt_phone : ''}
                                        ${parent.email ? '<br><i class="bx bx-envelope me-1"></i>' + parent.email : ''}
                                    </small>
                                </div>
                                ${parent.occupation ? '<div class="mb-2"><small class="text-muted"><i class="bx bx-briefcase me-1"></i>' + parent.occupation + '</small></div>' : ''}
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bx bx-map me-1"></i>${parent.address.substring(0, 50)}${parent.address.length > 50 ? '...' : ''}
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <select class="form-select form-select-sm relationship-select" data-parent-id="${parent.id}" style="width: auto;">
                                        <option value="">Relationship</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <button type="button" class="btn btn-${badgeClass} btn-sm select-parent-btn"
                                            data-parent-id="${parent.id}"
                                            data-parent-name="${parent.name}"
                                            ${isSelected ? 'disabled' : ''}>
                                        <i class="bx bx-${isSelected ? 'check' : 'plus'} me-1"></i>${badgeText}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            $('#resultsContainer').html(html);
            $('#searchResults').show();
        }

        // Handle parent selection
        $(document).on('click', '.select-parent-btn', function() {
            const parentId = $(this).data('parent-id');
            const parentName = $(this).data('parent-name');
            const relationshipSelect = $(this).closest('.card-body').find('.relationship-select');
            const relationship = relationshipSelect.val();

            if (!relationship) {
                alert('Please select a relationship first');
                relationshipSelect.focus();
                return;
            }

            // Check if already selected
            if (selectedParents.some(p => p.id === parentId)) {
                alert('This parent is already selected');
                return;
            }

            // Add to selected parents
            selectedParents.push({
                id: parseInt(parentId), // Ensure ID is a number
                name: parentName,
                relationship: relationship
            });

            console.log('Parent selected:', { id: parentId, name: parentName, relationship: relationship });
            console.log('Current selected parents:', selectedParents);

            // Update UI
            $(this).removeClass('btn-outline-primary').addClass('btn-success').prop('disabled', true)
                   .html('<i class="bx bx-check me-1"></i>Selected');

            updateSelectedParentsDisplay();
        });

        // Update selected parents display
        function updateSelectedParentsDisplay() {
            console.log('Updating selected parents display. Current count:', selectedParents.length);

            if (selectedParents.length === 0) {
                $('#selectedParentsContainer').hide();
                $('#assignSelectedContainer').hide();
                return;
            }

            let html = '<div class="alert alert-success"><h6>Selected Parents (' + selectedParents.length + '):</h6><ul class="mb-0">';
            selectedParents.forEach(function(parent, index) {
                html += `<li>${parent.name} (${parent.relationship})
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-selected" data-index="${index}">
                        <i class="bx bx-trash"></i>
                    </button>
                </li>`;
            });
            html += '</ul></div>';

            if (!$('#selectedParentsContainer').length) {
                $('#searchResults').after('<div id="selectedParentsContainer"></div>');
            }
            $('#selectedParentsContainer').html(html).show();
            $('#assignSelectedContainer').show();
        }

        // Handle relationship change for already selected parents
        $(document).on('change', '.relationship-select', function() {
            const parentId = $(this).data('parent-id');
            const newRelationship = $(this).val();

            // Update the relationship in selectedParents array
            const selectedParent = selectedParents.find(p => p.id === parseInt(parentId));
            if (selectedParent) {
                selectedParent.relationship = newRelationship;
                console.log('Updated relationship for parent', parentId, 'to', newRelationship);
                updateSelectedParentsDisplay();
            }
        });

        // Assign selected parents
        $('#assignSelectedBtn').on('click', function() {
            if (selectedParents.length === 0) {
                alert('Please select at least one parent');
                return;
            }

            // Validate that all selected parents have relationships
            const invalidParents = selectedParents.filter(p => !p.relationship);
            if (invalidParents.length > 0) {
                alert('Please select a relationship for all selected parents');
                return;
            }

            $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Assigning...');

            console.log('Assigning parents:', selectedParents);

            $.ajax({
                url: '{{ route("school.students.assign-existing-parents", $student) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    guardians: selectedParents
                },
                success: function(response) {
                    console.log('Assignment response:', response);

                    if (response.success) {
                        // Show success message
                        const successAlert = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>${response.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        $('#searchResults').before(successAlert);

                        // Clear selected parents
                        selectedParents = [];
                        updateSelectedParentsDisplay();

                        // Reset all select buttons
                        $('.select-parent-btn').removeClass('btn-success').addClass('btn-outline-primary')
                                               .prop('disabled', false)
                                               .html('<i class="bx bx-plus me-1"></i>Select');

                        // Redirect to students index with refresh parameter after a short delay
                        setTimeout(function() {
                            window.location.href = '{{ route("school.students.index", ["refresh" => "1"]) }}';
                        }, 2000);

                    } else {
                        alert('Error: ' + (response.message || 'Failed to assign parents'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Assignment error:', xhr, status, error);

                    let errorMessage = 'An error occurred while assigning parents. Please try again.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = 'Validation errors: ' + errors.join(', ');
                    } else if (xhr.status === 401) {
                        errorMessage = 'You are not authorized to perform this action. Please log in again.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to assign parents to this student.';
                    }

                    alert('Error: ' + errorMessage);
                },
                complete: function() {
                    $('#assignSelectedBtn').prop('disabled', false).html('<i class="bx bx-save me-1"></i> Assign Selected Parents');
                }
            });
        });

        // Add parent functionality
        $('#addParentBtn').on('click', function() {
            let parentHtml = `
                <div class="parent-entry border rounded p-3 mb-3" data-parent-index="${parentIndex}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="guardians[${parentIndex}][name]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select" name="guardians[${parentIndex}][relationship]" required>
                                    <option value="">Select Relationship</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="guardians[${parentIndex}][phone]" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Alternative Phone</label>
                                <input type="tel" class="form-control" name="guardians[${parentIndex}][alt_phone]">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="guardians[${parentIndex}][email]" placeholder="guardian@example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" name="guardians[${parentIndex}][occupation]" placeholder="e.g., Teacher, Engineer, Business">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent/Guardian Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="guardians[${parentIndex}][address]" rows="2" required placeholder="Enter complete address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm remove-parent-btn">
                            <i class="bx bx-trash me-1"></i> Remove
                        </button>
                    </div>
                </div>
            `;

            $('#parentsContainer').append(parentHtml);
            parentIndex++;

            // Show remove buttons for all entries if more than one
            if ($('.parent-entry').length > 1) {
                $('.remove-parent-btn').show();
            }
        });

        // Remove parent functionality
        $(document).on('click', '.remove-parent-btn', function() {
            const guardianId = $(this).data('guardian-id');
            const guardianName = $(this).data('guardian-name');

            $('#parentName').text(guardianName);
            $('#confirmRemoveParent').data('guardian-id', guardianId);
            $('#removeParentModal').modal('show');
        });

        // Handle confirm remove parent
        $('#confirmRemoveParent').on('click', function() {
            const guardianId = $(this).data('guardian-id');

            // Create and submit form
            const form = $('<form>', {
                action: '{{ url("school/students") }}/{{ $student->getRouteKey() }}/remove-parent/' + guardianId,
                method: 'POST'
            });

            form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
            form.append('<input type="hidden" name="_method" value="DELETE">');

            $('body').append(form);
            form.submit();

            $('#removeParentModal').modal('hide');
        });

        console.log('Assign parents form loaded');

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush