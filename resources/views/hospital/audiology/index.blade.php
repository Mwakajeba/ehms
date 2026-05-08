@extends('layouts.main')

@section('title', 'Audiology')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Audiology', 'url' => '#', 'icon' => 'bx bx-volume-full']
            ]" />
            <h6 class="mb-0 text-uppercase">AUDIOLOGY</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card border-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['waiting'] ?? 0 }}</h3>
                            <small class="text-muted">Waiting</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['in_service'] ?? 0 }}</h3>
                            <small class="text-muted">In Service</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['ready_results'] ?? 0 }}</h3>
                            <small class="text-muted">Ready Results</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['completed_today'] ?? 0 }}</h3>
                            <small class="text-muted">Completed Today</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bx bx-time me-2"></i>Waiting Queue</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>MRN</th>
                                            <th>Visit Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($waitingVisits as $visit)
                                            <tr>
                                                <td><strong>{{ $visit->visit_number }}</strong></td>
                                                <td>{{ $visit->patient->full_name ?? 'N/A' }}</td>
                                                <td>{{ $visit->patient->mrn ?? 'N/A' }}</td>
                                                <td>{{ $visit->visit_date ? $visit->visit_date->format('d M Y, H:i') : 'N/A' }}</td>
                                                <td>
                                                    <form action="{{ route('hospital.audiology.start-service', $visit->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-info">
                                                            <i class="bx bx-play me-1"></i>Start
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('hospital.audiology.create', $visit->id) }}" class="btn btn-sm btn-dark">
                                                        <i class="bx bx-edit me-1"></i>Enter Results
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No visits waiting for audiology.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-loader-circle me-2"></i>In Service</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>MRN</th>
                                            <th>Visit Date</th>
                                            <th>Results</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($inServiceVisits as $visit)
                                            <tr>
                                                <td><strong>{{ $visit->visit_number }}</strong></td>
                                                <td>{{ $visit->patient->full_name ?? 'N/A' }}</td>
                                                <td>{{ $visit->patient->mrn ?? 'N/A' }}</td>
                                                <td>{{ $visit->visit_date ? $visit->visit_date->format('d M Y, H:i') : 'N/A' }}</td>
                                                <td>{{ $visit->audiologyResults ? $visit->audiologyResults->count() : 0 }}</td>
                                                <td>
                                                    <a href="{{ route('hospital.audiology.create', $visit->id) }}" class="btn btn-sm btn-info">
                                                        <i class="bx bx-edit me-1"></i>Update Results
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No visits currently in service.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Ready Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Result #</th>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>Test Type</th>
                                            <th>Completed At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($readyResults as $result)
                                            <tr>
                                                <td><strong>{{ $result->result_number }}</strong></td>
                                                <td>{{ $result->visit->visit_number ?? 'N/A' }}</td>
                                                <td>{{ $result->patient->full_name ?? 'N/A' }}</td>
                                                <td>{{ $result->test_type ?? 'N/A' }}</td>
                                                <td>{{ $result->completed_at ? $result->completed_at->format('d M Y, H:i') : 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('hospital.audiology.show', $result->id) }}" class="btn btn-sm btn-outline-success">
                                                        <i class="bx bx-show me-1"></i>View
                                                    </a>
                                                    <a href="{{ route('hospital.audiology.print', $result->id) }}" class="btn btn-sm btn-success" target="_blank">
                                                        <i class="bx bx-printer me-1"></i>Print
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No ready results.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

