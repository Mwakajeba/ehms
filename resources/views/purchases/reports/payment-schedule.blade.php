@extends('layouts.main')

@section('title','Supplier Payment Schedule')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Payment Schedule', 'url' => '#', 'icon' => 'bx bx-calendar-event']
        ]" />
        <div class="alert alert-info">This report will forecast upcoming supplier payments. Coming soon.</div>
    </div>
</div>
@endsection


