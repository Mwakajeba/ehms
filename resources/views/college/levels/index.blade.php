@extends('layouts.main')

@section('title', 'Academic Levels')

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
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .breadcrumb-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }

    .breadcrumb-btn.active {
        background: white;
        border-color: #8b5cf6;
        color: #6d28d9;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
        font-size: 18px;
    }

    .page-header-card {
        border-radius: 20px;
        padding: 30px;
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        margin-bottom: 25px;
        box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
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

    .btn-create {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: white;
        color: #6d28d9;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        color: #5b21b6;
    }

    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 15px 20px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
    }

    .stat-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .table-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .levels-table {
        width: 100%;
        border-collapse: collapse;
    }

    .levels-table thead th {
        background: #f8fafc;
        padding: 14px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }

    .levels-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #334155;
    }

    .levels-table tbody tr:hover {
        background: #f8fafc;
    }

    .level-name {
        font-weight: 600;
        color: #1e293b;
    }

    .level-code {
        display: inline-block;
        padding: 4px 10px;
        background: #f1f5f9;
        border-radius: 6px;
        font-family: monospace;
        font-size: 12px;
        color: #64748b;
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
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

    .action-btns {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-view {
        background: #e0f2fe;
        color: #0369a1;
    }

    .btn-view:hover {
        background: #0ea5e9;
        color: white;
    }

    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-edit:hover {
        background: #f59e0b;
        color: white;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #64748b;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #94a3b8;
        margin-bottom: 20px;
    }

    .pagination-wrapper {
        padding: 15px 20px;
        border-top: 1px solid #e2e8f0;
    }

    .table-search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .table-search-icon {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 18px;
    }

    .table-search-input {
        padding: 8px 12px 8px 38px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        width: 220px;
        transition: all 0.2s ease;
        background: #f8fafc;
    }

    .table-search-input:focus {
        outline: none;
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .table-search-input::placeholder {
        color: #94a3b8;
    }

    .no-results-row {
        text-align: center;
        padding: 40px 20px;
        color: #64748b;
        font-size: 14px;
    }

    .no-results-row i {
        font-size: 32px;
        color: #cbd5e1;
        margin-bottom: 10px;
        display: block;
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
        <span class="breadcrumb-btn active">
            <i class="bx bx-layer"></i> Academic Levels
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <div>
                <h1 class="header-title">
                    <i class="bx bx-layer"></i>
                    Academic Levels
                </h1>
                <p class="header-subtitle">Manage qualification levels: Certificate, Diploma, Degree, Masters, PhD</p>
            </div>
            <a href="{{ route('college.levels.create') }}" class="btn-create">
                <i class="bx bx-plus-circle"></i> Add New Level
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 20px;">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 20px;">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="{{ route('college.levels.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $value)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-search"></i> Search
                    </button>
                    <a href="{{ route('college.levels.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number" style="color: #8b5cf6;">{{ $levels->total() }}</div>
            <div class="stat-label">Total Levels</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;">{{ $levels->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #3b82f6;">{{ $levels->where('category', 'certificate')->count() }}</div>
            <div class="stat-label">Certificate</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #f59e0b;">{{ $levels->where('category', 'diploma')->count() }}</div>
            <div class="stat-label">Diploma</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #ef4444;">{{ $levels->where('category', 'degree')->count() }}</div>
            <div class="stat-label">Degree</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bx bx-list-ul"></i> All Academic Levels
            </h3>
            <div class="d-flex align-items-center gap-3">
                <span style="font-weight: 600; color: #64748b;">Search</span>
                <div class="table-search-wrapper">
                    <i class="bx bx-search table-search-icon"></i>
                    <input type="text" id="tableSearch" class="table-search-input" placeholder="Search in table...">
                </div>
                <span class="text-muted">{{ $levels->total() }} records found</span>
            </div>
        </div>
        
        @if($levels->count() > 0)
        <div class="table-responsive">
            <table class="levels-table" id="levelsTable">
                <thead>
                    <tr>
                        <th class="bg-dark text-white">#</th>
                        <th class="bg-dark text-white">Level Name</th>
                        <th class="bg-dark text-white">Short Name</th>
                        <th class="bg-dark text-white">Code</th>
                        <th class="bg-dark text-white">Category</th>
                        <th class="bg-dark text-white">Sort Order</th>
                        <th class="bg-dark text-white">Status</th>
                        <th class="bg-dark text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($levels as $index => $level)
                    <tr>
                        <td>{{ $levels->firstItem() + $index }}</td>
                        <td>
                            <span class="level-name">{{ $level->name }}</span>
                        </td>
                        <td>
                            <strong>{{ $level->short_name }}</strong>
                        </td>
                        <td>
                            @if($level->code)
                                <span class="level-code">{{ $level->code }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="category-badge" style="background: {{ $level->category_color }}20; color: {{ $level->category_color }};">
                                <i class="bx bx-certification"></i>
                                {{ $level->category_name }}
                            </span>
                        </td>
                        <td>{{ $level->sort_order }}</td>
                        <td>
                            @if($level->is_active)
                                <span class="status-badge status-active">
                                    <i class="bx bx-check-circle"></i> Active
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    <i class="bx bx-x-circle"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('college.levels.show', $level) }}" class="btn-action btn-view" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="{{ route('college.levels.edit', $level) }}" class="btn-action btn-edit" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <form action="{{ route('college.levels.destroy', $level) }}" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this level?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="pagination-wrapper">
            {{ $levels->links() }}
        </div>
        @else
        <div class="empty-state">
            <i class="bx bx-layer"></i>
            <h4>No Academic Levels Found</h4>
            <p>Get started by creating your first academic level.</p>
            <a href="{{ route('college.levels.create') }}" class="btn btn-primary">
                <i class="bx bx-plus-circle me-1"></i> Create Level
            </a>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    const table = document.getElementById('levelsTable');
    
    if (searchInput && table) {
        const tbody = table.querySelector('tbody');
        const rows = tbody ? tbody.querySelectorAll('tr') : [];
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            // Remove existing no results message
            const existingNoResults = tbody.querySelector('.no-results-row');
            if (existingNoResults) {
                existingNoResults.remove();
            }
            
            rows.forEach(function(row) {
                if (row.classList.contains('no-results-row')) return;
                
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show no results message if needed
            if (visibleCount === 0 && searchTerm !== '') {
                const noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="8" class="no-results-row">
                        <i class="bx bx-search-alt"></i>
                        No matching records found for "<strong>${searchTerm}</strong>"
                    </td>
                `;
                tbody.appendChild(noResultsRow);
            }
        });
    }
});
</script>
@endsection
