@extends('layouts.main')

@section('title', 'Edit Insurance Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Insurance Types', 'url' => route('settings.insurance-types.index'), 'icon' => 'bx bx-shield-quarter'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT INSURANCE TYPE</h6>
        <hr/>

        <div class="card">
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('settings.insurance-types.update', $insuranceType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('settings.insurance-types._form')
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Update</button>
                        <a href="{{ route('settings.insurance-types.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    if ($.fn.select2) {
        $('.select2-single').select2({ width: '100%', placeholder: 'Search chart accounts...' });
    }
    function toggleReceivableField() {
        const isNone = $('#is_none').is(':checked') || $('#is_none').is(':disabled');
        $('#receivable-account-wrap').toggle(!isNone);
        $('#receivable_chart_account_id').prop('required', !isNone);
        $('.receivable-required').toggle(!isNone);
    }
    $('#is_none').on('change', toggleReceivableField);
    toggleReceivableField();
});
</script>
@endpush
