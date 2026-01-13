@extends('layouts.main')

@section('title', 'Streams Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Streams', 'url' => '#', 'icon' => 'bx bx-book-open']
        ]" />
        <h6 class="mb-0 text-uppercase">STREAMS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Academic Streams</h5>
                            </div>
                            <a href="{{ route('school.streams.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Stream
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="streams-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Stream Name</th>
                                        <th>Classes Count</th>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete the stream "<strong id="streamName"></strong>"? This action cannot be undone.
                        <div class="alert alert-warning mt-3" id="deleteWarning" style="display: none;">
                            <i class="bx bx-error-circle me-1"></i>
                            <strong>Warning:</strong> This stream has <span id="classesCount"></span> class(es) assigned to it. Deleting this stream will also remove all associated class assignments.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" action="" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#streams-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.streams.data") }}',
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
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'classes_count',
                    name: 'classes_count',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at_formatted',
                    name: 'created_at'
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
            drawCallback: function() {
                // Attach event listeners to delete buttons after table draw
                $('.delete-stream-btn').on('click', function() {
                    var streamId = $(this).data('stream-id');
                    var streamName = $(this).data('stream-name');
                    var classesCount = $(this).data('classes-count');

                    $('#streamName').text(streamName);
                    $('#deleteForm').attr('action', '{{ url("school/streams") }}/' + streamId);

                    // Show warning if stream has classes
                    if (classesCount > 0) {
                        $('#classesCount').text(classesCount);
                        $('#deleteWarning').show();
                    } else {
                        $('#deleteWarning').hide();
                    }

                    $('#deleteModal').modal('show');
                });
            },
            initComplete: function() {
                // Add export buttons if needed
                this.api().buttons().container().appendTo('#streams-table_wrapper .col-md-6:eq(0)');
            }
        });

        console.log('Streams DataTable loaded');
    });
</script>
@endpush