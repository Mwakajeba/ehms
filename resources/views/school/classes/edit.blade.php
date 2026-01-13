@extends('layouts.main')

@section('title', 'Edit Class')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Classes', 'url' => route('school.classes.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT CLASS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit School Class</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.classes.update', $classe) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $classe->name) }}"
                                               placeholder="e.g., Grade 1, Class 10, Nursery" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter the name of the school class (e.g., Grade 1, Class 10, Nursery)</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Associated Streams</label>
                                        <div class="row">
                                            @forelse($streams as $stream)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="stream_{{ $stream->id }}" name="streams[]"
                                                               value="{{ $stream->id }}"
                                                               {{ in_array($stream->id, old('streams', $classe->streams->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="stream_{{ $stream->id }}">
                                                            {{ $stream->name }}
                                                            @if($stream->description)
                                                                <br><small class="text-muted">{{ $stream->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        No streams available. <a href="{{ route('school.streams.create') }}">Create a stream first</a>.
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="form-text">Select the academic streams that this class can offer (e.g., Science, Arts, Commerce)</div>
                                        @error('streams')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.classes.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Classes
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Class
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Class Details
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <strong>Created:</strong><br>
                            <span class="text-muted">{{ $classe->created_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @if($classe->updated_at != $classe->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            <span class="text-muted">{{ $classe->updated_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @endif
                        <div class="mb-3">
                            <h6>What are School Classes?</h6>
                            <p class="small text-muted">
                                School classes represent different grade levels or educational stages in the school system.
                            </p>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-capitalize first letter
        $('#name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        console.log('Edit class form loaded');
    });
</script>
@endpush