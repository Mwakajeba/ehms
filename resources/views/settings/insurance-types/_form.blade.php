<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label">Insurance Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                   value="{{ old('name', $insuranceType->name ?? '') }}" placeholder="e.g. NHIF, Jubilee, AAR" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="code" class="form-label">Code (optional)</label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                   value="{{ old('code', $insuranceType->code ?? '') }}" placeholder="Short code">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="sort_order" class="form-label">Sort Order</label>
            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order"
                   value="{{ old('sort_order', $insuranceType->sort_order ?? 0) }}">
            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3 pt-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $insuranceType->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (show on patient form)</label>
            </div>
        </div>
    </div>
    <div class="col-md-12" id="receivable-account-wrap">
        <div class="mb-3">
            <label for="receivable_chart_account_id" class="form-label">Receivable Chart Account <span class="text-danger receivable-required">*</span></label>
            <select class="form-select select2-single @error('receivable_chart_account_id') is-invalid @enderror"
                    id="receivable_chart_account_id" name="receivable_chart_account_id">
                <option value="">Select receivable account</option>
                @foreach($chartAccounts ?? [] as $account)
                    <option value="{{ $account->id }}"
                        {{ (string) old('receivable_chart_account_id', $insuranceType->receivable_chart_account_id ?? '') === (string) $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('receivable_chart_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Used when recording invoice payments by this insurance provider (debit this receivable).</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3 pt-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_none" name="is_none" value="1"
                       {{ old('is_none', $insuranceType->is_none ?? false) ? 'checked' : '' }}
                       @if(!empty($insuranceType?->is_none)) disabled @endif>
                @if(!empty($insuranceType?->is_none))
                    <input type="hidden" name="is_none" value="1">
                @endif
                <label class="form-check-label" for="is_none">No insurance (default option)</label>
            </div>
            <div class="form-text">Only one “No insurance” option per company.</div>
        </div>
    </div>
</div>
