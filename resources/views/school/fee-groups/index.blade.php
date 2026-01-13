@extends('layouts.main')

@section('title', 'Fee Groups Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Groups', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE GROUPS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-group me-1 font-22 text-warning"></i></div>
                                <h5 class="mb-0 text-warning">Fee Groups</h5>
                            </div>
                            <a href="{{ route('school.fee-groups.create') }}" class="btn btn-warning">
                                <i class="bx bx-plus me-1"></i> Add New Fee Group
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="fee-groups-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fee Code</th>
                                        <th>Name</th>
                                        <th>Receivable Account</th>
                                        <th>Income Account</th>
                                        <th>Transport Income Account</th>
                                        <th>Discount Account</th>
                                        <th>Opening Balance Account</th>
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
        $('#fee-groups-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.fee-groups.data") }}',
                type: 'GET'
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'fee_code',
                    name: 'fee_code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'receivable_account',
                    name: 'receivable_account'
                },
                {
                    data: 'income_account',
                    name: 'income_account'
                },
                {
                    data: 'transport_income_account',
                    name: 'transport_income_account'
                },
                {
                    data: 'discount_account',
                    name: 'discount_account'
                },
                {
                    data: 'opening_balance_account',
                    name: 'opening_balance_account'
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
                    searchable: false
                }
            ],
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            initComplete: function() {
                // Add export buttons if needed
                this.api().buttons().container().appendTo('#fee-groups-table_wrapper .col-md-6:eq(0)');
            }
        });

        console.log('Fee Groups DataTable loaded');
    });
</script>
@endpush