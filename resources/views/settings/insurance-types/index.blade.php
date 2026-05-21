@extends('layouts.main')

@section('title', 'Insurance Types')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Insurance Types', 'url' => '#', 'icon' => 'bx bx-shield-quarter']
        ]" />
        <h6 class="mb-0 text-uppercase">HOSPITAL INSURANCE TYPES</h6>
        <hr/>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="card-title mb-1">Insurance Agents / Providers</h4>
                                <p class="text-muted mb-0 small">These options appear when registering patients at reception.</p>
                            </div>
                            <a href="{{ route('settings.insurance-types.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add Insurance Type
                            </a>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="insurance-types-table" class="table table-striped table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Sort</th>
                                        <th>Type</th>
                                        <th>Patients</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Settings
                            </a>
                        </div>
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
    const table = $('#insurance-types-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('settings.insurance-types.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'type_label', name: 'name' },
            { data: 'code', name: 'code', defaultContent: '—' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'none_flag', name: 'is_none', orderable: false, searchable: false },
            { data: 'patients_count_display', name: 'patients_count', searchable: false },
            { data: 'status', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[3, 'asc']],
    });

    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        if (!confirm('Delete insurance type "' + name + '"?')) {
            return;
        }
        $.ajax({
            url: '{{ url('settings/insurance-types') }}/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    table.ajax.reload();
                    alert(res.message);
                } else {
                    alert(res.message || 'Delete failed');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Delete failed';
                alert(msg);
            }
        });
    });
});
</script>
@endpush
