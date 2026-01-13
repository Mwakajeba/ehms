@extends('layouts.main')

@section('title', 'Fee Settings Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Settings', 'url' => '#', 'icon' => 'bx bx-cog']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE SETTINGS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-cog me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Fee Settings</h5>
                            </div>
                            <a href="{{ route('school.fee-settings.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Fee Setting
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="fee-settings-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Fee Period</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
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

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#fee-settings-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.fee-settings.data") }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', xhr, error, thrown);
                    alert('Error loading data: ' + error + ' - ' + thrown);
                }
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    width: '5%'
                },
                {
                    data: 'class_name',
                    name: 'class_name'
                },
                {
                    data: 'fee_period',
                    name: 'fee_period'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'status_badge',
                    name: 'status_badge',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '15%'
                }
            ],
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

        console.log('Fee Settings DataTable script loaded');
    });
</script>
@endpush