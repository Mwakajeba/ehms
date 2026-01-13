@extends('layouts.main')

@section('title', 'Department Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Departments', 'url' => route('hospital.admin.departments.index'), 'icon' => 'bx bx-buildings'],
                ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">DEPARTMENT DETAILS</h6>
            <hr />

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-buildings me-2"></i>{{ $department->name }}
                            </h5>
                            <div>
                                <a href="{{ route('hospital.admin.departments.edit', $department->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('hospital.admin.departments.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td><strong>{{ $department->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Code:</th>
                                            <td><code>{{ $department->code }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Type:</th>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $department->type)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($department->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Company:</th>
                                            <td>{{ $department->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Branch:</th>
                                            <td>{{ $department->branch->name ?? 'All Branches' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created:</th>
                                            <td>{{ $department->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated:</th>
                                            <td>{{ $department->updated_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($department->description)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6>Description:</h6>
                                        <p>{{ $department->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-stats me-2"></i>Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Total Visits:</th>
                                    <td><strong>{{ $department->visitDepartments->count() }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Associated Services:</th>
                                    <td><strong>{{ $department->services->count() }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
