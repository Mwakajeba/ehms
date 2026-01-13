@extends('layouts.main')

@section('title','Paid Supplier Invoices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Paid Invoices', 'url' => '#', 'icon' => 'bx bx-check-circle']
        ]" />
        <div class="alert alert-info">This report will display fully paid invoices and settlement details. Coming soon.</div>
    </div>
</div>
@endsection


