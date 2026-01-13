@extends('layouts.main')

@section('title','Outstanding Supplier Invoices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Outstanding Invoices', 'url' => '#', 'icon' => 'bx bx-hourglass']
        ]" />
        <div class="alert alert-info">This report will list unpaid or partial invoices. Coming soon.</div>
    </div>
</div>
@endsection


