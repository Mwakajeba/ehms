@extends('layouts.main')

@section('title', 'View Academic Level')

@section('content')
<style>
    .levels-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        margin-top: 70px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .breadcrumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .breadcrumb-btn:hover {
        background: #f8fafc;
        color: #475569;
    }

    .breadcrumb-btn.active {
        background: white;
        border-color: #0ea5e9;
        color: #0369a1;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
        font-size: 18px;
    }

    .page-header-card {
        border-radius: 20px;
        padding: 30px;
        background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        margin-bottom: 25px;
        box-shadow: 0 10px 40px rgba(14, 165, 233, 0.3);
        position: relative;
        overflow: hidden;
    }

    .page-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .header-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-title i {
        font-size: 32px;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px;
        border-radius: 12px;
    }

    .header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 15px;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-header {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        color: #0369a1;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-header.edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-header.delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }

    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .detail-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-card-title i {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .detail-card-body {
        padding: 25px;
    }

    .detail-item {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-label {
        width: 150px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        flex: 1;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
    }

    .level-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .stat-box {
        text-align: center;
        padding: 25px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        margin-bottom: 15px;
    }

    .stat-number {
        font-size: 36px;
        font-weight: 700;
        color: #0ea5e9;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    .meta-info {
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        margin-top: 10px;
    }

    .meta-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 13px;
    }

    .meta-item .label {
        color: #64748b;
    }

    .meta-item .value {
        color: #1e293b;
        font-weight: 500;
    }
</style>

<div class="levels-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class="bx bx-home"></i> Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class="bx bx-building"></i> College
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.levels.index') }}" class="breadcrumb-btn">
            <i class="bx bx-layer"></i> Academic Levels
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class="bx bx-show"></i> {{ $level->short_name }}
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <div>
                <h1 class="header-title">
                    <i class="bx bx-layer"></i>
                    {{ $level->name }}
                </h1>
                <p class="header-subtitle">{{ $level->category_name }} • {{ $level->short_name }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('college.levels.edit', $level) }}" class="btn-header edit">
                    <i class="bx bx-edit"></i> Edit
                </a>
                <form action="{{ route('college.levels.destroy', $level) }}" method="POST" style="display: inline;" 
                      onsubmit="return confirm('Are you sure you want to delete this level?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-header delete">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <!-- Main Details -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">
                    <i class="bx bx-info-circle"></i>
                    Level Information
                </h3>
            </div>
            <div class="detail-card-body">
                <div class="detail-item">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">{{ $level->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Short Name</div>
                    <div class="detail-value"><strong>{{ $level->short_name }}</strong></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Code</div>
                    <div class="detail-value">
                        @if($level->code)
                            <code style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">{{ $level->code }}</code>
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Category</div>
                    <div class="detail-value">
                        <span class="level-badge" style="background: {{ $level->category_color }}20; color: {{ $level->category_color }};">
                            <i class="bx bx-certification me-1"></i>
                            {{ $level->category_name }}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Sort Order</div>
                    <div class="detail-value">{{ $level->sort_order }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        @if($level->is_active)
                            <span class="status-badge status-active">
                                <i class="bx bx-check-circle"></i> Active
                            </span>
                        @else
                            <span class="status-badge status-inactive">
                                <i class="bx bx-x-circle"></i> Inactive
                            </span>
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Description</div>
                    <div class="detail-value">{{ $level->description ?: 'No description provided' }}</div>
                </div>
            </div>
        </div>

        <!-- Side Panel -->
        <div>
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3 class="detail-card-title">
                        <i class="bx bx-bar-chart-alt-2"></i>
                        Statistics
                    </h3>
                </div>
                <div class="detail-card-body">
                    <div class="stat-box">
                        <div class="stat-number">{{ $examCount }}</div>
                        <div class="stat-label">Exam Schedules</div>
                    </div>

                    <div class="meta-info">
                        <div class="meta-item">
                            <span class="label">Created</span>
                            <span class="value">{{ $level->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="label">Updated</span>
                            <span class="value">{{ $level->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($level->createdBy)
                        <div class="meta-item">
                            <span class="label">Created By</span>
                            <span class="value">{{ $level->createdBy->name }}</span>
                        </div>
                        @endif
                        @if($level->updatedBy)
                        <div class="meta-item">
                            <span class="label">Updated By</span>
                            <span class="value">{{ $level->updatedBy->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <a href="{{ route('college.levels.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
