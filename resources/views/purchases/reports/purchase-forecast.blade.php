@extends('layouts.main')

@section('title','Purchase Forecast')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Purchase Forecast', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />
        <div class="alert alert-info">This report will project future purchasing needs. Coming soon.</div>
    </div>
</div>
@endsection


