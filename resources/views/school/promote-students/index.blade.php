@extends('layouts.main')

@section('title', 'Promote Students')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Promote Students', 'url' => '#', 'icon' => 'bx bx-up-arrow-alt']
        ]" />
        <h6 class="mb-0 text-uppercase">PROMOTE STUDENTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-up-arrow-alt me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Promote Students</h5>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('errors'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Some errors occurred:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Promotion Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-user-check fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Selective Promotion</h6>
                                        <p class="card-text small">Promote specific students to a new class</p>
                                        <a href="{{ route('school.promote-students.create') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i> Select Students
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-group fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Bulk Promotion</h6>
                                        <p class="card-text small">Promote all students from one class to another</p>
                                        <a href="{{ route('school.promote-students.bulk-select') }}" class="btn btn-success">
                                            <i class="bx bx-transfer me-1"></i> Bulk Promote
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

