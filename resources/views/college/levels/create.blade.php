@extends('layouts.main')

@section('title', 'Create Academic Level')

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

    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .form-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-card-title i {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .form-card-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .form-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }

    .category-card {
        position: relative;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .category-card:hover {
        border-color: #8b5cf6;
        background: #faf5ff;
    }

    .category-card.selected {
        border-color: #8b5cf6;
        background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
    }

    .category-card input[type="radio"] {
        display: none;
    }

    .category-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        margin: 0 auto 10px;
    }

    .category-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    .category-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        background: #8b5cf6;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
    }

    .category-card.selected .category-check {
        display: flex;
    }

    .toggle-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
        background: #cbd5e1;
        border-radius: 13px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .toggle-switch.active {
        background: #8b5cf6;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s;
    }

    .toggle-switch.active::after {
        left: 27px;
    }

    .toggle-switch input {
        display: none;
    }

    .toggle-label {
        font-size: 14px;
        font-weight: 500;
        color: #475569;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding: 20px 25px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 6px;
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
            <i class="bx bx-plus-circle"></i> Create New
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-layer-plus"></i>
                Create Academic Level
            </h1>
            <p class="header-subtitle">Add a new qualification level to your system</p>
        </div>
    </div>

    <form action="{{ route('college.levels.store') }}" method="POST">
        @csrf

        <div class="form-card">
            <div class="form-card-header">
                <h3 class="form-card-title">
                    <i class="bx bx-info-circle"></i>
                    Level Information
                </h3>
            </div>
            <div class="form-card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Level Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="e.g., Bachelor's Degree" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Full name of the academic level</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Short Name <span class="required">*</span></label>
                        <input type="text" name="short_name" class="form-control @error('short_name') is-invalid @enderror" 
                               value="{{ old('short_name') }}" placeholder="e.g., BSc" required maxlength="20">
                        @error('short_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Abbreviation (max 20 characters)</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code') }}" placeholder="e.g., BSCDEG" maxlength="20">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Unique identifier code (optional)</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint">Display order (lower = first)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <div class="category-grid">
                        @php
                            $categoryIcons = [
                                'foundation' => 'bx-book-reader',
                                'certificate' => 'bx-award',
                                'diploma' => 'bx-certification',
                                'degree' => 'bx-trophy',
                                'postgraduate' => 'bx-crown',
                                'professional' => 'bx-briefcase-alt-2',
                            ];
                            $categoryColors = [
                                'foundation' => '#6b7280',
                                'certificate' => '#3b82f6',
                                'diploma' => '#8b5cf6',
                                'degree' => '#10b981',
                                'postgraduate' => '#ef4444',
                                'professional' => '#f59e0b',
                            ];
                        @endphp
                        @foreach($categories as $key => $value)
                            <label class="category-card {{ old('category') == $key ? 'selected' : '' }}">
                                <input type="radio" name="category" value="{{ $key }}" 
                                       {{ old('category') == $key ? 'checked' : '' }} required>
                                <div class="category-icon" style="background: {{ $categoryColors[$key] ?? '#6b7280' }};">
                                    <i class="bx {{ $categoryIcons[$key] ?? 'bx-layer' }}"></i>
                                </div>
                                <div class="category-name">{{ $value }}</div>
                                <div class="category-check">
                                    <i class="bx bx-check"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('category')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              placeholder="Brief description of this level...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-container">
                        <div class="toggle-switch {{ old('is_active', true) ? 'active' : '' }}" onclick="toggleStatus(this)">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        </div>
                        <span class="toggle-label">Active (available for selection in forms)</span>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="{{ route('college.levels.index') }}" class="btn-cancel">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bx bx-save"></i> Save Level
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Category Card Selection
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Toggle Switch
    function toggleStatus(element) {
        const checkbox = element.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        element.classList.toggle('active', checkbox.checked);
    }
</script>
@endsection
