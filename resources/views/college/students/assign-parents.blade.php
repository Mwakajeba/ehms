@extends('layouts.main')

@section('title', 'Assign Parents - ' . $student->full_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Students', 'url' => route('college.students.index'), 'icon' => 'bx bx-user'],
            ['label' => $student->full_name, 'url' => route('college.students.show', \Vinkla\Hashids\Facades\Hashids::encode($student->id)), 'icon' => 'bx bx-user'],
            ['label' => 'Assign Parents', 'url' => '#', 'icon' => 'bx bx-user-plus']
        ]" />

        <h6 class="mb-0 text-uppercase">ASSIGN PARENTS TO STUDENT</h6>
        <hr />

        <!-- Student Info Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-user me-2 text-primary"></i>
                            Student Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Student Number:</strong><br>
                                {{ $student->student_number }}
                            </div>
                            <div class="col-md-3">
                                <strong>Full Name:</strong><br>
                                {{ $student->full_name }}
                            </div>
                            <div class="col-md-3">
                                <strong>Program:</strong><br>
                                {{ $student->program->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Department:</strong><br>
                                {{ $student->program->department->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Parents -->
        @if($student->parents->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-users me-2 text-success"></i>
                            Current Parents/Guardians ({{ $student->parents->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($student->parents as $guardian)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">{{ $guardian->name }}</h6>
                                                <p class="text-muted mb-2">
                                                    <i class="bx bx-link me-1"></i>
                                                    {{ ucfirst($student->parents->find($guardian->id)->pivot->relationship) }}
                                                </p>
                                                <div class="small">
                                                    <div><i class="bx bx-phone me-1"></i>{{ $guardian->phone }}</div>
                                                    @if($guardian->email)
                                                    <div><i class="bx bx-envelope me-1"></i>{{ $guardian->email }}</div>
                                                    @endif
                                                    @if($guardian->occupation)
                                                    <div><i class="bx bx-briefcase me-1"></i>{{ $guardian->occupation }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <form action="{{ route('college.students.remove-parent', [\Vinkla\Hashids\Facades\Hashids::encode($student->id), $guardian->id]) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to remove this parent?')">
                                                    <i class="bx bx-trash"></i> Remove
                                                </button>
                                            </form>
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

        <!-- Assign New Parents -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-plus-circle me-2 text-primary"></i>
                            Assign New Parents
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" id="parentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="new-parent-tab" data-bs-toggle="tab"
                                        data-bs-target="#new-parent" type="button" role="tab">
                                    <i class="bx bx-plus me-1"></i>Add New Parent
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="existing-parent-tab" data-bs-toggle="tab"
                                        data-bs-target="#existing-parent" type="button" role="tab">
                                    <i class="bx bx-search me-1"></i>Assign Existing Parent
                                </button>
                            </li>
                        </ul>

                        <!-- Tab content -->
                        <div class="tab-content mt-3" id="parentTabsContent">
                            <!-- New Parent Tab -->
                            <div class="tab-pane fade show active" id="new-parent" role="tabpanel">
                                <form action="{{ route('college.students.store-assigned-parents', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}"
                                      method="POST" id="newParentForm">
                                    @csrf
                                    <div id="new-parents-container">
                                        <div class="parent-entry border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="parents[0][name]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="parents[0][relationship]" required>
                                                            <option value="">Select Relationship</option>
                                                            <option value="father">Father</option>
                                                            <option value="mother">Mother</option>
                                                            <option value="guardian">Guardian</option>
                                                            <option value="uncle">Uncle</option>
                                                            <option value="aunt">Aunt</option>
                                                            <option value="grandfather">Grandfather</option>
                                                            <option value="grandmother">Grandmother</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="parents[0][phone]" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Alternative Phone</label>
                                                        <input type="text" class="form-control" name="parents[0][alt_phone]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Email Address</label>
                                                        <input type="email" class="form-control" name="parents[0][email]">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Occupation</label>
                                                        <input type="text" class="form-control" name="parents[0][occupation]">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" name="parents[0][address]" rows="2" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-parent">
                                                <i class="bx bx-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-primary" id="addNewParent">
                                            <i class="bx bx-plus me-1"></i>Add Another Parent
                                        </button>
                                        <div>
                                            <a href="{{ route('college.students.show', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" class="btn btn-outline-secondary me-2">
                                                <i class="bx bx-arrow-back me-1"></i>Back to Student
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Assign Parents
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Existing Parent Tab -->
                            <div class="tab-pane fade" id="existing-parent" role="tabpanel">
                                <form id="existingParentForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Search Parent</label>
                                                <input type="text" class="form-control" id="parentSearch"
                                                       placeholder="Type parent name, phone, or email...">
                                                <div class="form-text">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Search for existing parents in the system
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Relationship</label>
                                                <select class="form-control" id="relationshipSelect">
                                                    <option value="">Select Relationship</option>
                                                    <option value="father">Father</option>
                                                    <option value="mother">Mother</option>
                                                    <option value="guardian">Guardian</option>
                                                    <option value="uncle">Uncle</option>
                                                    <option value="aunt">Aunt</option>
                                                    <option value="grandfather">Grandfather</option>
                                                    <option value="grandmother">Grandmother</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="searchResults" class="mb-3" style="display: none;">
                                        <h6>Search Results:</h6>
                                        <div id="resultsContainer"></div>
                                    </div>

                                    <div id="selectedParents" class="mb-3" style="display: none;">
                                        <h6>Selected Parents:</h6>
                                        <div id="selectedContainer"></div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-success" id="assignSelectedParents" style="display: none;">
                                            <i class="bx bx-check me-1"></i>Assign Selected Parents
                                        </button>
                                        <a href="{{ route('college.students.show', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Back to Student
                                        </a>
                                    </div>
                                </form>
                            </div>
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
    .parent-entry {
        position: relative;
    }

    .remove-parent {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .parent-card {
        cursor: pointer;
        transition: all 0.2s;
    }

    .parent-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .parent-card.selected {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }

    .selected-parent {
        background-color: #e7f3ff;
        border: 1px solid #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let parentIndex = 1;
    let selectedParents = [];

    // Add new parent entry
    $('#addNewParent').click(function() {
        const template = `
            <div class="parent-entry border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="parents[${parentIndex}][name]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                            <select class="form-control" name="parents[${parentIndex}][relationship]" required>
                                <option value="">Select Relationship</option>
                                <option value="father">Father</option>
                                <option value="mother">Mother</option>
                                <option value="guardian">Guardian</option>
                                <option value="uncle">Uncle</option>
                                <option value="aunt">Aunt</option>
                                <option value="grandfather">Grandfather</option>
                                <option value="grandmother">Grandmother</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="parents[${parentIndex}][phone]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alternative Phone</label>
                            <input type="text" class="form-control" name="parents[${parentIndex}][alt_phone]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" name="parents[${parentIndex}][email]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Occupation</label>
                            <input type="text" class="form-control" name="parents[${parentIndex}][occupation]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="parents[${parentIndex}][address]" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-parent">
                    <i class="bx bx-trash me-1"></i>Remove
                </button>
            </div>
        `;

        $('#new-parents-container').append(template);
        parentIndex++;
    });

    // Remove parent entry
    $(document).on('click', '.remove-parent', function() {
        $(this).closest('.parent-entry').remove();
    });

    // Search existing parents
    let searchTimeout;
    $('#parentSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#searchResults').hide();
            return;
        }

        searchTimeout = setTimeout(() => {
            searchParents(query);
        }, 300);
    });

    function searchParents(query) {
        $.ajax({
            url: '{{ route("college.api.students.parents.search", $student->id) }}',
            method: 'GET',
            data: { q: query },
            success: function(response) {
                displaySearchResults(response.parents);
            },
            error: function() {
                $('#searchResults').hide();
            }
        });
    }

    function displaySearchResults(parents) {
        if (parents.length === 0) {
            $('#searchResults').hide();
            return;
        }

        let html = '';
        parents.forEach(parent => {
            const isSelected = selectedParents.some(p => p.id === parent.id);
            html += `
                <div class="card parent-card mb-2 ${isSelected ? 'selected' : ''}" data-parent-id="${parent.id}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${parent.name}</h6>
                                <small class="text-muted">
                                    <i class="bx bx-phone me-1"></i>${parent.phone}
                                    ${parent.email ? `<i class="bx bx-envelope ms-2 me-1"></i>${parent.email}` : ''}
                                </small>
                            </div>
                            <button type="button" class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'} select-parent-btn"
                                    data-parent-id="${parent.id}" data-parent-name="${parent.name}">
                                ${isSelected ? 'Selected' : 'Select'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#resultsContainer').html(html);
        $('#searchResults').show();
    }

    // Select/deselect parent
    $(document).on('click', '.select-parent-btn, .parent-card', function() {
        const parentId = $(this).data('parent-id');
        const parentName = $(this).data('parent-name') || $(this).find('.select-parent-btn').data('parent-name');

        if (!parentId) return;

        const index = selectedParents.findIndex(p => p.id === parentId);

        if (index > -1) {
            // Remove from selected
            selectedParents.splice(index, 1);
            $(this).closest('.parent-card').removeClass('selected');
            $(this).closest('.parent-card').find('.select-parent-btn').removeClass('btn-success').addClass('btn-outline-primary').text('Select');
        } else {
            // Add to selected
            const relationship = $('#relationshipSelect').val();
            if (!relationship) {
                alert('Please select a relationship first.');
                return;
            }

            selectedParents.push({
                id: parentId,
                name: parentName,
                relationship: relationship
            });
            $(this).closest('.parent-card').addClass('selected');
            $(this).closest('.parent-card').find('.select-parent-btn').removeClass('btn-outline-primary').addClass('btn-success').text('Selected');
        }

        updateSelectedParentsDisplay();
    });

    function updateSelectedParentsDisplay() {
        if (selectedParents.length === 0) {
            $('#selectedParents').hide();
            $('#assignSelectedParents').hide();
            return;
        }

        let html = '';
        selectedParents.forEach(parent => {
            html += `
                <div class="selected-parent border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${parent.name}</strong>
                            <span class="badge bg-primary ms-2">${parent.relationship}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-selected"
                                data-parent-id="${parent.id}">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        $('#selectedContainer').html(html);
        $('#selectedParents').show();
        $('#assignSelectedParents').show();
    }

    // Remove selected parent
    $(document).on('click', '.remove-selected', function() {
        const parentId = $(this).data('parent-id');
        selectedParents = selectedParents.filter(p => p.id !== parentId);

        // Update UI
        $(`.parent-card[data-parent-id="${parentId}"]`).removeClass('selected');
        $(`.parent-card[data-parent-id="${parentId}"] .select-parent-btn`).removeClass('btn-success').addClass('btn-outline-primary').text('Select');

        updateSelectedParentsDisplay();
    });

    // Assign selected parents
    $('#assignSelectedParents').click(function() {
        if (selectedParents.length === 0) {
            alert('No parents selected.');
            return;
        }

        // Show loading
        $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Assigning...');

        $.ajax({
            url: '{{ route("college.students.assign-existing-parents", \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                parents: selectedParents
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while assigning parents.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            },
            complete: function() {
                $('#assignSelectedParents').prop('disabled', false).html('<i class="bx bx-check me-1"></i>Assign Selected Parents');
            }
        });
    });
});
</script>
@endpush