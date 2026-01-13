@extends('layouts.main')

@section('title', 'Document Categories')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Document Categories', 'url' => '#', 'icon' => 'bx bx-folder']
        ]" />

        <h6 class="mb-0 text-uppercase">DOCUMENT CATEGORIES MANAGEMENT</h6>
        <hr />

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-folder me-2 text-primary"></i>Document Categories
                </h5>
                <a href="{{ route('college.document-categories.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i>Add New Category
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="document-categories-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: 2px solid #dee2e6;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#document-categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.document-categories.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'creator.name', name: 'creator.name', defaultContent: 'N/A' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });
});

// Handle delete confirmation
$(document).on('click', '.delete-btn', function(e) {
    e.preventDefault();
    const deleteUrl = $(this).data('url');
    const categoryName = $(this).data('name');

    if (confirm(`Are you sure you want to delete the document category "${categoryName}"? This action cannot be undone.`)) {
        const form = $('<form>', {
            'method': 'POST',
            'action': deleteUrl
        });
        form.append('<input type="hidden" name="_method" value="DELETE">');
        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
        $('body').append(form);
        form.submit();
    }
});
</script>
@endpush