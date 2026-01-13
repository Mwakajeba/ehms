@extends('layouts.main')

@section('title','Purchase Analysis by Supplier')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Analysis by Supplier', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <div class="alert alert-info">This report will summarize spend per supplier and ranking. Coming soon.</div>
    </div>
</div>
@endsection


