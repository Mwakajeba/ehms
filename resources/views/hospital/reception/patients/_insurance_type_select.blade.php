@php
    $selectedId = old('insurance_type_id', $selectedInsuranceTypeId ?? null);
    if (!$selectedId && isset($patient)) {
        $selectedId = $patient->insurance_type_id
            ?? $insuranceTypes->firstWhere('name', $patient->insurance_type)?->id
            ?? $insuranceTypes->firstWhere('is_none', true)?->id;
    }
    if (!$selectedId) {
        $selectedId = $insuranceTypes->firstWhere('is_none', true)?->id;
    }
@endphp
<select class="form-select @error('insurance_type_id') is-invalid @enderror" id="insurance_type_id" name="insurance_type_id">
    @foreach($insuranceTypes as $type)
        <option value="{{ $type->id }}" {{ (string) $selectedId === (string) $type->id ? 'selected' : '' }}>
            {{ $type->name }}
        </option>
    @endforeach
</select>
@error('insurance_type_id')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
@if($insuranceTypes->isEmpty())
    <div class="form-text text-warning">
        No insurance types configured.
        <a href="{{ route('settings.insurance-types.index') }}">Add insurance types in Settings</a>.
    </div>
@else
    <div class="form-text text-muted">
        Manage options in <a href="{{ route('settings.insurance-types.index') }}">Settings → Insurance Types</a>.
    </div>
@endif
