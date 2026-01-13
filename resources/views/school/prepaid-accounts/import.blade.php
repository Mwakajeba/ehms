@extends('layouts.main')

@section('title', 'Import Prepaid Accounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Prepaid Accounts', 'url' => route('school.prepaid-accounts.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Import', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />
        <h6 class="mb-0 text-uppercase">IMPORT PREPAID ACCOUNTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-upload me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Import Prepaid Accounts</h5>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('import_errors'))
                            <div class="alert alert-danger">
                                <h6 class="fw-bold">Import Errors:</h6>
                                <ul class="mb-0">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <h6 class="fw-bold"><i class="bx bx-info-circle me-1"></i> Instructions:</h6>
                            <ol class="mb-0">
                                <li>Select a class to download the Excel template</li>
                                <li>The template will contain all students from the selected class</li>
                                <li>Fill in the Amount column (required)</li>
                                <li>Optionally fill in Reference and Notes columns</li>
                                <li>Upload the completed file</li>
                            </ol>
                        </div>

                        <form action="{{ route('school.prepaid-accounts.process-import') }}" method="POST" enctype="multipart/form-data">
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

                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" id="downloadTemplate">
                                        <i class="bx bx-download me-1"></i> Download Template
                                    </button>
                                </div>

                                <div class="col-12">
                                    <label for="excel_file" class="form-label fw-bold">Excel File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('excel_file') is-invalid @enderror" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                    @error('excel_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Accepted formats: .xlsx, .xls (Max: 10MB)</small>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-upload me-1"></i> Import File
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

    // Initialize Select2 for bank account dropdown with search
    $('#bank_account_id').select2({
        placeholder: 'Select Bank Account',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });

    $('#downloadTemplate').on('click', function() {
        var classId = $('#class_id').val();
        
        if (!classId) {
            alert('Please select a class first');
            return;
        }

        // Create a form and submit it to download the template
        var form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("school.prepaid-accounts.export-template") }}'
        });

        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': '{{ csrf_token() }}'
        }));

        form.append($('<input>', {
            'type': 'hidden',
            'name': 'class_id',
            'value': classId
        }));

        $('body').append(form);
        form.submit();
        form.remove();
    });
});
</script>
@endpush

