@extends('layouts.main')

@section('title', 'Edit Exam Class Assignment Group')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Edit Group', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-target-lock me-1 font-22 text-warning"></i>
                                <span class="h5 mb-0 text-warning">Edit Exam Class Assignment Group</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Group Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Exam Type</h6>
                                        <h4 class="text-primary">{{ $examType->name ?? 'N/A' }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Class</h6>
                                        <h4 class="text-success">{{ $class->name ?? 'N/A' }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Academic Year</h6>
                                        <h4 class="text-warning">{{ $academicYear->year_name ?? 'N/A' }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Edit Form -->
                        <form action="{{ route('school.exam-class-assignments.update-group', [
                            'exam_type_hash' => \Vinkla\Hashids\Facades\Hashids::encode($examType->id ?? 0),
                            'class_hash' => \Vinkla\Hashids\Facades\Hashids::encode($class->id ?? 0),
                            'academic_year_hash' => \Vinkla\Hashids\Facades\Hashids::encode($academicYear->id ?? 0)
                        ]) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="due_date">Due Date</label>
                                        <input type="date" class="form-control" id="due_date" name="due_date"
                                               value="{{ old('due_date', $assignments->first()->due_date ? $assignments->first()->due_date->format('Y-m-d') : '') }}">
                                        @error('due_date')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="assigned" {{ old('status', $assignments->first()->status ?? '') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                            <option value="in_progress" {{ old('status', $assignments->first()->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ old('status', $assignments->first()->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status', $assignments->first()->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $assignments->first()->notes ?? '') }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bx bx-save me-1"></i> Update All Assignments in Group
                                </button>
                            </div>
                        </form>

                        <!-- Assignments Table -->
                        <div class="mt-4">
                            <h6>Individual Assignments in this Group:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Stream</th>
                                            <th>Status</th>
                                            <th>Assigned Date</th>
                                            <th>Due Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments as $index => $assignment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $assignment->subject->name ?? 'N/A' }}</td>
                                            <td>{{ $assignment->stream->name ?? 'N/A' }}</td>
                                            <td>{!! $assignment->getStatusBadge() !!}</td>
                                            <td>{{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('school.exam-class-assignments.show', $assignment) }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('school.exam-class-assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
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