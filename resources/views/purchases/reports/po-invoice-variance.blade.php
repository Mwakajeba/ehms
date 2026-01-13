@extends('layouts.main')

@section('title','PO vs Invoice Variance')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Purchases', 'url' => route('purchases.index'), 'icon' => 'bx bx-shopping-bag'],
            ['label' => 'Reports', 'url' => route('purchases.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'PO vs Invoice', 'url' => '#', 'icon' => 'bx bx-git-compare']
        ]" />
        <div class="alert alert-info">This report will compare ordered/received vs invoiced amounts. Coming soon.</div>
    </div>
</div>
@endsection


