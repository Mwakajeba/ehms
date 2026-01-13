@extends('layouts.main')

@section('title', 'View Student Opening Balance')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Opening Balance', 'url' => route('school.student-fee-opening-balance.index'), 'icon' => 'bx bx-wallet'],
            ['label' => 'View Opening Balance', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW STUDENT OPENING BALANCE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-show me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Student Opening Balance Details</span>
                            </div>
                            <div>
                                @if($openingBalance->status === 'draft')
                                    <a href="{{ route('school.student-fee-opening-balance.edit', $openingBalance->getRouteKey()) }}" class="btn btn-primary">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                @endif
                                <a href="{{ route('school.student-fee-opening-balance.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Student Name:</th>
                                        <td>{{ $openingBalance->student->first_name }} {{ $openingBalance->student->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Admission Number:</th>
                                        <td>{{ $openingBalance->student->admission_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Class:</th>
                                        <td>{{ $openingBalance->student->class ? $openingBalance->student->class->name : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Stream:</th>
                                        <td>{{ $openingBalance->student->stream ? $openingBalance->student->stream->name : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Academic Year:</th>
                                        <td>{{ $openingBalance->academicYear->year_name }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Opening Date:</th>
                                        <td>{{ $openingBalance->opening_date->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount:</th>
                                        <td><strong>{{ number_format($openingBalance->amount, 2) }} {{ config('app.currency', 'TZS') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Paid Amount:</th>
                                        <td>{{ number_format($openingBalance->paid_amount, 2) }} {{ config('app.currency', 'TZS') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Balance Due:</th>
                                        <td><strong class="text-danger">{{ number_format($openingBalance->balance_due, 2) }} {{ config('app.currency', 'TZS') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if($openingBalance->status === 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif($openingBalance->status === 'posted')
                                                <span class="badge bg-success">Posted</span>
                                            @else
                                                <span class="badge bg-danger">Closed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($openingBalance->reference)
                                    <tr>
                                        <th>Reference:</th>
                                        <td>{{ $openingBalance->reference }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            @if($openingBalance->notes)
                            <div class="col-12 mt-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $openingBalance->notes }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

