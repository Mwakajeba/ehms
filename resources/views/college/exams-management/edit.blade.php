@extends('layouts.main')

@section('title', 'Edit College Exam')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Exam</h3>
                    <div class="card-tools">
                        <a href="{{ route('college.exams-management.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This feature is currently under development. Please check back later.
                    </div>

                    <form action="{{ route('college.exams-management.update', $id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Exam Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter exam name" disabled>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter exam description" disabled></textarea>
                        </div>

                        <div class="form-group">
                            <label for="exam_date">Exam Date</label>
                            <input type="date" class="form-control" id="exam_date" name="exam_date" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary" disabled>
                            <i class="fas fa-save"></i> Update Exam
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection