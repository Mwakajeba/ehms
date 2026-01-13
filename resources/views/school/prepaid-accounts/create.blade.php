@extends('layouts.main')

@section('title', 'Add Prepaid Account Credit')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Prepaid Accounts', 'url' => route('school.prepaid-accounts.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Add Credit', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">ADD PREPAID ACCOUNT CREDIT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add Prepaid Account Credit</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.prepaid-accounts.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                    <select class="form-select select2 @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="student_id" class="form-label fw-bold">Student <span class="text-danger">*</span></label>
                                    <select class="form-select select2 @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required disabled>
                                        <option value="">Select Student</option>
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select a class first to load students</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="amount" class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="bank_account_id" class="form-label fw-bold">Received In (Bank Account) <span class="text-danger">*</span></label>
                                    <select class="form-select select2 @error('bank_account_id') is-invalid @enderror" id="bank_account_id" name="bank_account_id" required>
                                        <option value="">Select Bank Account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} 
                                                @if($bankAccount->chartAccount)
                                                    - {{ $bankAccount->chartAccount->account_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="reference" class="form-label fw-bold">Reference</label>
                                    <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}" placeholder="Payment reference number">
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="notes" class="form-label fw-bold">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Additional notes">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Add Credit
                                    </button>
                                    <a href="{{ route('school.prepaid-accounts.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for class dropdown with search
    $('#class_id').select2({
        placeholder: 'Select Class',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });

    // Initialize Select2 for student dropdown with search
    $('#student_id').select2({
        placeholder: 'Select Student',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0,
        disabled: true
    });

    // Initialize Select2 for bank account dropdown with search
    $('#bank_account_id').select2({
        placeholder: 'Select Bank Account',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });

    $('#class_id').on('change', function() {
        var classId = $(this).val();
        var studentSelect = $('#student_id');
        
        if (classId) {
            studentSelect.prop('disabled', true);
            studentSelect.html('<option value="">Loading...</option>').trigger('change');
            
            $.ajax({
                url: '{{ route("school.prepaid-accounts.get-students") }}',
                method: 'GET',
                data: { class_id: classId },
                success: function(response) {
                    studentSelect.html('<option value="">Select Student</option>');
                    $.each(response, function(index, student) {
                        var displayText = (student.admission_number || '') + ' - ' + 
                                        student.first_name + ' ' + student.last_name;
                        studentSelect.append(
                            '<option value="' + student.id + '">' + displayText + '</option>'
                        );
                    });
                    studentSelect.prop('disabled', false).trigger('change');
                },
                error: function() {
                    studentSelect.html('<option value="">Error loading students</option>');
                    studentSelect.prop('disabled', true).trigger('change');
                }
            });
        } else {
            studentSelect.html('<option value="">Select Student</option>');
            studentSelect.prop('disabled', true).trigger('change');
        }
    });
});
</script>
@endpush

