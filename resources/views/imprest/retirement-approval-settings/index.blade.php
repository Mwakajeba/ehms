@extends('layouts.main')

@section('title', 'Retirement Approval Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Imprest Management', 'url' => route('imprest.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Retirement Approval Settings', 'url' => route('imprest.retirement-approval-settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Multi-Level Settings', 'url' => '#', 'icon' => 'bx bx-git-branch']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0 text-primary">Retirement Multi-Level Approval Settings</h5>
                <small class="text-muted">Configure flexible approval workflows for retirement requests</small>
            </div>
            <div>
                <a href="{{ route('imprest.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Imprest
                </a>
            </div>
        </div>

        <div class="alert alert-info border-0">
            <div class="d-flex align-items-center">
                <i class="bx bx-info-circle fs-4 me-3"></i>
                <div>
                    <h6 class="mb-1">Retirement Approval System</h6>
                    <p class="mb-0">Configure multi-level approval workflows for retirement processing with 1-5 configurable approval levels, amount thresholds, and multiple approvers per level.</p>
                </div>
            </div>
        </div>

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bx bx-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('imprest.retirement-approval-settings.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Basic Settings Column -->
                        <div class="col-lg-4">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-primary">
                                        <i class="bx bx-cog me-2"></i>Basic Configuration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="approval_required" 
                                                   name="approval_required" value="1"
                                                   {{ old('approval_required', $settings?->approval_required) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="approval_required">
                                                <strong>Enable Multi-Level Approval</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Turn on to require multiple approvals before retirement processing</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="approval_levels" class="form-label">
                                            <i class="bx bx-layer-plus me-1"></i>Number of Approval Levels
                                        </label>
                                        <select class="form-select" id="approval_levels" name="approval_levels">
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ old('approval_levels', $settings?->approval_levels ?? 1) == $i ? 'selected' : '' }}>
                                                    {{ $i }} Level{{ $i > 1 ? 's' : '' }}
                                                </option>
                                            @endfor
                                        </select>
                                        <small class="text-muted">How many approval levels before retirement processing</small>
                                    </div>

                                    <div class="mb-3" id="preset_section">
                                        <label class="form-label">
                                            <i class="bx bx-magic-wand me-1"></i>Quick Setup (Optional)
                                        </label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="applyPreset('supervisor')">
                                                <i class="bx bx-check me-1"></i>Supervisor (1 Level)
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="applyPreset('department_manager')">
                                                <i class="bx bx-check-double me-1"></i>Department + Manager (2 Levels)
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="applyPreset('three_tier')">
                                                <i class="bx bx-shield-check me-1"></i>Three-Tier (3 Levels)
                                            </button>
                                        </div>
                                        <small class="text-muted">Use preset configurations for common retirement approval workflows</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">
                                            <i class="bx bx-note me-1"></i>Notes
                                        </label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                  placeholder="Additional notes about the retirement approval workflow">{{ old('notes', $settings?->notes) }}</textarea>
                                    </div>

                                    @if($settings)
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6 class="text-info mb-2">Current Status</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Status:</span>
                                                <span class="badge {{ $settings->approval_required ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $settings->approval_required ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Levels:</span>
                                                <span class="fw-bold">{{ $settings->approval_levels ?? 1 }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Last Updated:</span>
                                                <span class="text-muted small">{{ $settings->updated_at?->diffForHumans() ?? 'Never' }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Approval Levels Configuration -->
                        <div class="col-lg-8">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-primary">
                                        <i class="bx bx-user-check me-2"></i>Level Configuration
                                    </h6>
                                </div>
                                <div class="card-body" id="approval_levels_config" style="{{ old('approval_required', $settings?->approval_required) ? '' : 'display: none;' }}">
                                    @for($level = 1; $level <= 5; $level++)
                                        <div class="approval-level-config mb-4 p-3 border rounded" id="level_{{ $level }}_config" 
                                             style="{{ $level <= old('approval_levels', $settings?->approval_levels ?? 1) ? '' : 'display: none;' }}">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="badge bg-primary me-2">{{ $level }}</div>
                                                <h6 class="mb-0 text-primary">Level {{ $level }} Approval</h6>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-5 mb-3">
                                                    <label for="level{{ $level }}_amount_threshold" class="form-label">
                                                        <i class="bx bx-money me-1"></i>Minimum Amount Threshold
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">{{ auth()->user()->company->currency ?? 'TZS' }}</span>
                                                        <input type="number" class="form-control" 
                                                               id="level{{ $level }}_amount_threshold" 
                                                               name="level{{ $level }}_amount_threshold" 
                                                               min="0" step="0.01"
                                                               value="{{ old('level' . $level . '_amount_threshold', $settings?->{'level' . $level . '_amount_threshold'}) }}"
                                                               placeholder="0.00">
                                                    </div>
                                                    <small class="text-muted">Retirements above this amount need this level approval</small>
                                                </div>
                                                
                                                <div class="col-md-7 mb-3">
                                                    <label for="level{{ $level }}_approvers" class="form-label">
                                                        <i class="bx bx-user-circle me-1"></i>Approvers for Level {{ $level }}
                                                    </label>
                                                    <select class="form-select" multiple 
                                                            id="level{{ $level }}_approvers" 
                                                            name="level{{ $level }}_approvers[]"
                                                            size="4">
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}" 
                                                                {{ in_array($user->id, old('level' . $level . '_approvers', $settings?->{'level' . $level . '_approvers'} ?? [])) ? 'selected' : '' }}>
                                                                {{ $user->name }} ({{ $user->email }})
                                                                @if($user->branch)
                                                                    - {{ $user->branch->name }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                                                </div>
                                            </div>

                                            @if($settings && $settings->approval_required)
                                                @php
                                                    $threshold = $settings->{'level' . $level . '_amount_threshold'};
                                                    $approvers = $settings->{'level' . $level . '_approvers'} ?? [];
                                                @endphp
                                                @if(!empty($approvers))
                                                    <div class="alert alert-info mb-0">
                                                        <small>
                                                            <strong>Current:</strong> 
                                                            @if($threshold)
                                                                Amounts ≥ {{ number_format($threshold, 2) }}
                                                            @else
                                                                All amounts
                                                            @endif
                                                            | Approvers: 
                                                            @foreach($users->whereIn('id', $approvers) as $user)
                                                                {{ $user->name }}{{ !$loop->last ? ', ' : '' }}
                                                            @endforeach
                                                        </small>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endfor

                                    <div class="alert alert-warning mt-3" id="no_levels_warning" style="display: none;">
                                        <small><i class="bx bx-warning me-1"></i>Please enable approval requirement and configure at least one approval level.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('imprest.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>{{ $settings ? 'Update' : 'Save' }} Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($settings)
        <!-- Current Settings Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bx bx-list-check me-2"></i>Current Configuration Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h4 class="{{ $settings->approval_required ? 'text-success' : 'text-secondary' }}">
                                <i class="bx {{ $settings->approval_required ? 'bx-check-shield' : 'bx-shield-x' }}"></i>
                            </h4>
                            <p class="mb-0">Multi-Level Approval</p>
                            <small class="text-muted">{{ $settings->approval_required ? 'Enabled' : 'Disabled' }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-primary">{{ $settings->approval_levels }}</h4>
                            <p class="mb-0">Approval Levels</p>
                            <small class="text-muted">Configured</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            @php 
                                $totalApprovers = 0;
                                for($i = 1; $i <= $settings->approval_levels; $i++) {
                                    $approvers = $settings->{'level' . $i . '_approvers'} ?? [];
                                    $totalApprovers += count($approvers);
                                }
                            @endphp
                            <h4 class="text-info">{{ $totalApprovers }}</h4>
                            <p class="mb-0">Total Approvers</p>
                            <small class="text-muted">Across all levels</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <h4 class="text-warning">
                                <i class="bx bx-time"></i>
                            </h4>
                            <p class="mb-0">Last Updated</p>
                            <small class="text-muted">{{ $settings->updated_at?->diffForHumans() ?? 'Never' }}</small>
                        </div>
                    </div>
                </div>

                @if($settings->approval_required && $settings->approval_levels > 0)
                <div class="mt-4">
                    <h6 class="text-primary">Level Details:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Level</th>
                                    <th>Amount Threshold</th>
                                    <th>Approvers</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 1; $i <= $settings->approval_levels; $i++)
                                    @php
                                        $threshold = $settings->{'level' . $i . '_amount_threshold'};
                                        $approvers = $settings->{'level' . $i . '_approvers'} ?? [];
                                    @endphp
                                    <tr>
                                        <td><strong>Level {{ $i }}</strong></td>
                                        <td>
                                            @if($threshold)
                                                TZS {{ number_format($threshold, 2) }}
                                            @else
                                                <em class="text-muted">No threshold</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if(count($approvers) > 0)
                                                @foreach($users->whereIn('id', $approvers) as $user)
                                                    <span class="badge bg-primary me-1">{{ $user->name }}</span>
                                                @endforeach
                                            @else
                                                <em class="text-muted">No approvers set</em>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.approval-level-config {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.approval-level-config:hover {
    background-color: #e9ecef;
}

.badge {
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const approvalRequiredCheckbox = document.getElementById('approval_required');
    const approvalLevelsSelect = document.getElementById('approval_levels');
    const approvalLevelsConfig = document.getElementById('approval_levels_config');
    const noLevelsWarning = document.getElementById('no_levels_warning');

    // Toggle approval configuration visibility
    function toggleApprovalSections() {
        if (approvalRequiredCheckbox.checked) {
            approvalLevelsConfig.style.display = 'block';
            noLevelsWarning.style.display = 'none';
            updateLevelVisibility();
        } else {
            approvalLevelsConfig.style.display = 'none';
            noLevelsWarning.style.display = 'block';
        }
    }

    // Show/hide approval levels based on selection
    function updateLevelVisibility() {
        const selectedLevels = parseInt(approvalLevelsSelect.value);
        
        for (let i = 1; i <= 5; i++) {
            const levelDiv = document.getElementById(`level_${i}_config`);
            if (i <= selectedLevels) {
                levelDiv.style.display = 'block';
            } else {
                levelDiv.style.display = 'none';
                // Clear hidden level data
                document.getElementById(`level${i}_amount_threshold`).value = '';
                const selectElement = document.getElementById(`level${i}_approvers`);
                Array.from(selectElement.options).forEach(option => option.selected = false);
            }
        }
    }

    // Preset configurations
    window.applyPreset = function(type) {
        // Enable approval first
        approvalRequiredCheckbox.checked = true;
        toggleApprovalSections();
        
        if (type === 'supervisor') {
            // 1 Level: Supervisor approval only
            approvalLevelsSelect.value = 1;
            updateLevelVisibility();
            document.getElementById('level1_amount_threshold').value = '50000';
            
        } else if (type === 'department_manager') {
            // 2 Levels: Department > Manager
            approvalLevelsSelect.value = 2;
            updateLevelVisibility();
            document.getElementById('level1_amount_threshold').value = '25000';
            document.getElementById('level2_amount_threshold').value = '100000';
            
        } else if (type === 'three_tier') {
            // 3 Levels: Department > Manager > Finance
            approvalLevelsSelect.value = 3;
            updateLevelVisibility();
            document.getElementById('level1_amount_threshold').value = '10000';
            document.getElementById('level2_amount_threshold').value = '50000';
            document.getElementById('level3_amount_threshold').value = '200000';
        }
        
        // Show notification
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
            <i class="bx bx-info-circle me-2"></i>
            Preset "${type}" configuration applied. Please select approvers for each level and save.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('form'));
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Event listeners
    approvalRequiredCheckbox.addEventListener('change', toggleApprovalSections);
    approvalLevelsSelect.addEventListener('change', updateLevelVisibility);

    // Initialize visibility
    toggleApprovalSections();

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (approvalRequiredCheckbox.checked) {
            const selectedLevels = parseInt(approvalLevelsSelect.value);
            let hasValidConfig = false;
            
            for (let i = 1; i <= selectedLevels; i++) {
                const approvers = document.getElementById(`level${i}_approvers`);
                if (approvers.selectedOptions.length > 0) {
                    hasValidConfig = true;
                    break;
                }
            }
            
            if (!hasValidConfig) {
                e.preventDefault();
                alert('Please select at least one approver for the configured levels.');
                return false;
            }
        }
    });
});
</script>
@endpush