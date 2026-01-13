<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="employee_id" class="form-select select2-single @error('employee_id') is-invalid @enderror"
                required>
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id', $loan->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->full_name }}
                    </option>
                @endforeach
            </select>
            @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Institution Name <span class="text-danger">*</span></label>
            <input type="text" name="institution_name"
                class="form-control @error('institution_name') is-invalid @enderror"
                value="{{ old('institution_name', $loan->institution_name ?? '') }}" required />
            @error('institution_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Total Loan <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="total_loan"
                class="form-control @error('total_loan') is-invalid @enderror"
                value="{{ old('total_loan', $loan->total_loan ?? '') }}" required />
            @error('total_loan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Monthly Deduction <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="monthly_deduction"
                class="form-control @error('monthly_deduction') is-invalid @enderror"
                value="{{ old('monthly_deduction', $loan->monthly_deduction ?? '') }}" required />
            @error('monthly_deduction')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                value="{{ old('date', optional($loan->date ?? null)->format('Y-m-d')) }}" required />
            @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">End Date</label>
            <input type="date" name="date_end_of_loan"
                class="form-control @error('date_end_of_loan') is-invalid @enderror"
                value="{{ old('date_end_of_loan', optional($loan->date_end_of_loan ?? null)->format('Y-m-d')) }}" />
            @error('date_end_of_loan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', $loan->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Save</button>
        <a href="{{ route('hr.external-loans.index') }}" class="btn btn-secondary"><i class="bx bx-x"></i> Cancel</a>
    </div>
</form>