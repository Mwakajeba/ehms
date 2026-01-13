@extends('layouts.main')

@section('title','Purchase Analysis by Item/Category')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Analysis by Item/Category', 'url' => '#', 'icon' => 'bx bx-basket']
        ]" />
        <div class="alert alert-info">This report will evaluate spend by items and categories. Coming soon.</div>
    </div>
</div>
@endsection


