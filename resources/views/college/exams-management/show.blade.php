@extends('layouts.main')

@section('title', 'College Exam Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Exam Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('college.exams-management.edit', $id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('college.exams-management.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Exam details view is under development.
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>Sample Exam Name</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>Sample exam description</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge badge-info">Under Development</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection