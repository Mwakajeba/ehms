@extends('layouts.main')

@section('title','Supplier Invoice Tax')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Supplier Invoice Tax', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />
        <div class="alert alert-info">This report will summarize input tax from supplier invoices. Coming soon.</div>
    </div>
</div>
@endsection


