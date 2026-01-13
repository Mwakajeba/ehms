@extends('layouts.main')

@section('content')
<style>
    .exam-view-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f9ff 0%, #ecfdf5 50%, #fffbeb 100%);
        min-height: 100vh;
    }

    /* Breadcrumb Navigation */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        margin-top: 70px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .breadcrumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .breadcrumb-btn:hover {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        border-color: transparent;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .breadcrumb-btn i {
        font-size: 16px;
    }

    .breadcrumb-btn.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border-color: transparent;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .breadcrumb-separator {
        color: #f59e0b;
        font-size: 20px;
        font-weight: bold;
    }

    /* Header Card */
    .page-header-card {
        background: linear-gradient(135deg, #85dcc0ff 0%, #75dcd0ff 100%);
        border-radius: 16px;
        padding: 24px 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.2);
        position: relative;
        overflow: hidden;
    }

    .header-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .header-left {
        flex: 1;
    }

    .header-title {
        font-size: 22px;
        font-weight: 600;
        color: white;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-title i {
        font-size: 24px;
        opacity: 0.9;
    }

    .header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-subtitle span {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .header-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .header-btn.btn-white {
        background: white;
        color: #059669;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .header-btn.btn-white:hover {
        background: #fef3c7;
        color: #f59e0b;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
    }

    .header-btn.btn-orange {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .header-btn.btn-orange:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    }

    .stat-card.green {
        border-left-color: #10b981;
    }

    .stat-card.blue {
        border-left-color: #3b82f6;
    }

    .stat-card.orange {
        border-left-color: #f59e0b;
    }

    .stat-card.purple {
        border-left-color: #8b5cf6;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    }

    .stat-content h4 {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 6px;
    }

    .stat-content p {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    /* View Layout */
    .view-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .view-layout {
            grid-template-columns: 1fr;
        }
    }

    /* View Card */
    .view-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-bottom: 25px;
        transition: all 0.3s ease;
    }

    .view-card:hover {
        box-shadow: 0 8px 35px rgba(0, 0, 0, 0.1);
    }

    .view-card:last-child {
        margin-bottom: 0;
    }

    .view-card-header {
        padding: 22px 28px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .view-card-header.green-header {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-bottom: 2px solid #10b981;
    }

    .view-card-header.blue-header {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-bottom: 2px solid #3b82f6;
    }

    .view-card-header.orange-header {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-bottom: 2px solid #f59e0b;
    }

    .card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .card-icon.green {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .card-icon.blue {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .card-icon.orange {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .view-card-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .view-card-body {
        padding: 28px;
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-item {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        padding: 18px 22px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .detail-item:hover {
        border-color: #10b981;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
    }

    .detail-item.full-width {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .detail-item.full-width {
            grid-column: span 1;
        }
    }

    .detail-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .detail-label i {
        font-size: 14px;
        color: #f59e0b;
    }

    .detail-value {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-value i {
        color: #3b82f6;
        font-size: 20px;
    }

    /* Exam Type Badge */
    .exam-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .status-badge.draft { 
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); 
        color: #475569; 
    }
    .status-badge.scheduled { 
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); 
        color: #1e40af; 
    }
    .status-badge.ongoing { 
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); 
        color: #92400e; 
    }
    .status-badge.completed { 
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); 
        color: #166534; 
    }
    .status-badge.postponed { 
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); 
        color: #0369a1; 
    }
    .status-badge.cancelled { 
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); 
        color: #991b1b; 
    }

    /* Materials Grid */
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }

    .material-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px solid #10b981;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .material-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.2);
    }

    .material-item i {
        color: #059669;
        font-size: 20px;
    }

    .material-item span {
        font-size: 13px;
        color: #065f46;
        font-weight: 600;
    }

    .no-data {
        padding: 30px;
        text-align: center;
        color: #64748b;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
    }

    .no-data i {
        font-size: 32px;
        color: #f59e0b;
        display: block;
        margin-bottom: 10px;
    }

    /* Instructions Box */
    .instructions-box {
        font-size: 15px;
        color: #374151;
        line-height: 1.8;
        padding: 20px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-radius: 14px;
        border-left: 5px solid #f59e0b;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.1);
    }

    /* Summary Card */
    .summary-card {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #bbf7d0 100%);
        border: 2px solid #10b981;
    }

    .summary-card .view-card-header {
        background: transparent;
        border-bottom: 2px dashed #10b981;
    }

    .summary-icon {
        width: 54px;
        height: 54px;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 26px;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.3);
    }

    .summary-title h3 {
        font-size: 18px;
        font-weight: 700;
        color: #065f46;
        margin: 0;
    }

    .summary-title p {
        font-size: 13px;
        color: #059669;
        margin: 4px 0 0;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 2px dashed #a7f3d0;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-item-label {
        font-size: 13px;
        color: #047857;
        font-weight: 500;
    }

    .summary-item-value {
        font-size: 15px;
        font-weight: 700;
        color: #065f46;
    }

    /* Quick Actions Card */
    .quick-actions-card {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 25px rgba(59, 130, 246, 0.15);
        border: 2px solid #3b82f6;
        margin-top: 25px;
    }

    .quick-actions-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e40af;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quick-actions-title i {
        color: #3b82f6;
        font-size: 22px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 16px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        margin-bottom: 12px;
        border: none;
        cursor: pointer;
    }

    .action-btn:last-child {
        margin-bottom: 0;
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .action-btn.edit:hover {
        transform: translateX(6px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }

    .action-btn.print {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .action-btn.print:hover {
        transform: translateX(6px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .action-btn.delete:hover {
        transform: translateX(6px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .action-btn.back {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }

    .action-btn.back:hover {
        transform: translateX(6px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
    }

    .action-btn i {
        font-size: 20px;
    }

    /* Timestamps Card */
    .timestamps-card {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-radius: 16px;
        padding: 20px;
        margin-top: 20px;
        border: 2px solid #f59e0b;
    }

    .timestamps-title {
        font-size: 14px;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .timestamps-title i {
        color: #f59e0b;
    }

    .timestamp-item {
        font-size: 13px;
        color: #78350f;
        padding: 8px 0;
        border-bottom: 1px dashed #fcd34d;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .timestamp-item:last-child {
        border-bottom: none;
    }

    .timestamp-item i {
        color: #f59e0b;
        font-size: 16px;
    }

    /* Delete Confirmation Modal */
    .delete-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .delete-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .delete-modal {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 420px;
        margin: 20px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
        transform: scale(0.8) translateY(-20px);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .delete-modal-overlay.active .delete-modal {
        transform: scale(1) translateY(0);
    }

    .delete-modal-header {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 50%, #fca5a5 100%);
        padding: 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .delete-modal-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .delete-modal-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        position: relative;
        z-index: 1;
    }

    .delete-modal-icon i {
        font-size: 40px;
        color: white;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }

    .delete-modal-header h3 {
        color: white;
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .delete-modal-body {
        padding: 30px;
        text-align: center;
    }

    .delete-modal-body p {
        color: #64748b;
        font-size: 15px;
        line-height: 1.7;
        margin: 0 0 10px;
    }

    .delete-modal-body .exam-name {
        color: #1e293b;
        font-weight: 700;
        font-size: 16px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 10px;
        margin: 16px 0;
        border: 1px solid #fca5a5;
    }

    .delete-modal-body .warning-text {
        color: #dc2626;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 16px;
    }

    .delete-modal-body .warning-text i {
        font-size: 18px;
    }

    .delete-modal-footer {
        padding: 0 30px 30px;
        display: flex;
        gap: 12px;
    }

    .modal-btn {
        flex: 1;
        padding: 16px 24px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .modal-btn.cancel {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #475569;
        border: 2px solid #cbd5e1;
    }

    .modal-btn.cancel:hover {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        transform: translateY(-2px);
    }

    .modal-btn.confirm-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }

    .modal-btn.confirm-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .modal-btn i {
        font-size: 18px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .exam-view-container {
            margin-left: 0;
            padding: 15px;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .header-actions {
            justify-content: center;
        }

        .header-title {
            justify-content: center;
            font-size: 24px;
        }

        .delete-modal-footer {
            flex-direction: column;
        }
    }
</style>

<div class="exam-view-container">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i>
            Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-book-reader'></i>
            Exam Management
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.exam-schedules.index') }}" class="breadcrumb-btn">
            <i class='bx bx-calendar'></i>
            Exam Schedules
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-show'></i>
            View Details
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <div class="header-left">
                <h1 class="header-title">
                    <i class='bx bx-calendar-check'></i>
                    {{ $examSchedule->course->name ?? 'Exam Schedule Details' }}
                </h1>
                <p class="header-subtitle">
                    <span><i class='bx bx-code-alt'></i> {{ $examSchedule->course->code ?? 'N/A' }}</span>
                    @if($examSchedule->academicYear || $examSchedule->semester)
                        <span><i class='bx bx-calendar'></i> {{ $examSchedule->academicYear->name ?? '' }} - {{ $examSchedule->semester->name ?? '' }}</span>
                    @endif
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('college.exam-schedules.edit', $examSchedule) }}" class="header-btn btn-white">
                    <i class='bx bx-edit'></i>
                    Edit
                </a>
                <a href="{{ route('college.exam-schedules.print', $examSchedule) }}" class="header-btn btn-orange" target="_blank">
                    <i class='bx bx-printer'></i>
                    Print
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    @php
        $examTypes = \App\Models\College\ExamSchedule::EXAM_TYPES;
        $examTypeIcons = \App\Models\College\ExamSchedule::EXAM_TYPE_ICONS;
        $examTypeColors = \App\Models\College\ExamSchedule::EXAM_TYPE_COLORS;
        $examType = $examSchedule->exam_type;
        $materials = $examSchedule->materials_allowed ?? [];
    @endphp
    
    <div class="stats-row">
        <div class="stat-card green">
            <div class="stat-icon green">
                <i class='bx bx-calendar-event'></i>
            </div>
            <div class="stat-content">
                <h4>Exam Date</h4>
                <p>{{ $examSchedule->exam_date ? $examSchedule->exam_date->format('M d, Y') : 'Not Set' }}</p>
            </div>
        </div>
        
        <div class="stat-card blue">
            <div class="stat-icon blue">
                <i class='bx bx-time-five'></i>
            </div>
            <div class="stat-content">
                <h4>Duration</h4>
                <p>{{ $examSchedule->duration_minutes ?? 'N/A' }} Minutes</p>
            </div>
        </div>
        
        <div class="stat-card orange">
            <div class="stat-icon orange">
                <i class='bx bx-group'></i>
            </div>
            <div class="stat-content">
                <h4>Students</h4>
                <p>{{ $examSchedule->number_of_students ?? $enrolledStudents ?? 'N/A' }}</p>
            </div>
        </div>
        
        <div class="stat-card purple">
            <div class="stat-icon purple">
                <i class='bx bx-trophy'></i>
            </div>
            <div class="stat-content">
                <h4>Total Marks</h4>
                <p>{{ $examSchedule->total_marks ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="view-layout">
        <!-- Left Column -->
        <div class="main-column">
            <!-- Basic Information -->
            <div class="view-card">
                <div class="view-card-header green-header">
                    <div class="card-icon green">
                        <i class='bx bx-info-circle'></i>
                    </div>
                    <h3 class="view-card-title">Basic Information</h3>
                </div>
                <div class="view-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-category'></i> Exam Type</div>
                            <div class="detail-value">
                                @php
                                    $icon = $examTypeIcons[$examType] ?? 'bx-file';
                                    $color = $examTypeColors[$examType] ?? '#6b7280';
                                    $label = $examTypes[$examType] ?? $examType;
                                @endphp
                                <span class="exam-type-badge" style="background: {{ $color }}20; color: {{ $color }};">
                                    <i class='bx {{ $icon }}'></i>
                                    {{ $label }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-check-shield'></i> Status</div>
                            <div class="detail-value">
                                @php
                                    $status = strtolower($examSchedule->status ?? 'scheduled');
                                @endphp
                                <span class="status-badge {{ $status }}">
                                    <i class='bx bx-radio-circle-marked'></i>
                                    {{ ucfirst($examSchedule->status ?? 'Scheduled') }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-book'></i> Course / Subject</div>
                            <div class="detail-value">
                                <i class='bx bx-book-open'></i>
                                {{ $examSchedule->course->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-hash'></i> Course Code</div>
                            <div class="detail-value">
                                <i class='bx bx-code-alt'></i>
                                {{ $examSchedule->course->code ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-building'></i> Program</div>
                            <div class="detail-value">
                                <i class='bx bx-buildings'></i>
                                {{ $examSchedule->program->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-calendar-alt'></i> Academic Year</div>
                            <div class="detail-value">
                                <i class='bx bx-calendar'></i>
                                {{ $examSchedule->academicYear->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-time'></i> Semester</div>
                            <div class="detail-value">
                                <i class='bx bx-bookmark'></i>
                                {{ $examSchedule->semester->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-check-double'></i> Pass Marks</div>
                            <div class="detail-value">
                                <i class='bx bx-target-lock'></i>
                                {{ $examSchedule->pass_marks ?? 'N/A' }}
                            </div>
                        </div>

                        @if($examSchedule->description)
                        <div class="detail-item full-width">
                            <div class="detail-label"><i class='bx bx-detail'></i> Description</div>
                            <div class="detail-value" style="display: block;">
                                {{ $examSchedule->description }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Schedule Details -->
            <div class="view-card">
                <div class="view-card-header blue-header">
                    <div class="card-icon blue">
                        <i class='bx bx-time-five'></i>
                    </div>
                    <h3 class="view-card-title">Schedule Details</h3>
                </div>
                <div class="view-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-calendar-event'></i> Exam Date</div>
                            <div class="detail-value">
                                <i class='bx bx-calendar-check'></i>
                                {{ $examSchedule->exam_date ? $examSchedule->exam_date->format('l, F d, Y') : 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-hourglass'></i> Duration</div>
                            <div class="detail-value">
                                <i class='bx bx-timer'></i>
                                {{ $examSchedule->duration_minutes ?? 'N/A' }} minutes
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-play-circle'></i> Start Time</div>
                            <div class="detail-value">
                                <i class='bx bx-time'></i>
                                {{ $examSchedule->start_time ? \Carbon\Carbon::parse($examSchedule->start_time)->format('h:i A') : 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-stop-circle'></i> End Time</div>
                            <div class="detail-value">
                                <i class='bx bx-time-five'></i>
                                {{ $examSchedule->end_time ? \Carbon\Carbon::parse($examSchedule->end_time)->format('h:i A') : 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-map-pin'></i> Venue / Room</div>
                            <div class="detail-value">
                                <i class='bx bx-door-open'></i>
                                {{ $examSchedule->venue ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-building-house'></i> Building</div>
                            <div class="detail-value">
                                <i class='bx bx-buildings'></i>
                                {{ $examSchedule->building ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-user-check'></i> Invigilator</div>
                            <div class="detail-value">
                                <i class='bx bx-user-circle'></i>
                                {{ $examSchedule->invigilator->name ?? $examSchedule->invigilator_name ?? 'Not Assigned' }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label"><i class='bx bx-chair'></i> Room Capacity</div>
                            <div class="detail-value">
                                <i class='bx bx-expand'></i>
                                {{ $examSchedule->capacity ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Materials Allowed -->
            <div class="view-card">
                <div class="view-card-header green-header">
                    <div class="card-icon green">
                        <i class='bx bx-check-shield'></i>
                    </div>
                    <h3 class="view-card-title">Materials Allowed</h3>
                </div>
                <div class="view-card-body">
                    @if(!empty($materials) && is_array($materials))
                        <div class="materials-grid">
                            @foreach($materials as $material)
                                <div class="material-item">
                                    <i class='bx bx-check-circle'></i>
                                    <span>{{ ucfirst(str_replace('_', ' ', $material)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-data">
                            <i class='bx bx-info-circle'></i>
                            No materials allowed for this exam
                        </div>
                    @endif
                </div>
            </div>

            <!-- Special Instructions -->
            <div class="view-card">
                <div class="view-card-header orange-header">
                    <div class="card-icon orange">
                        <i class='bx bx-notepad'></i>
                    </div>
                    <h3 class="view-card-title">Special Instructions</h3>
                </div>
                <div class="view-card-body">
                    @if($examSchedule->instructions)
                        <div class="instructions-box">
                            {!! nl2br(e($examSchedule->instructions)) !!}
                        </div>
                    @else
                        <div class="no-data">
                            <i class='bx bx-notepad'></i>
                            No special instructions provided
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="sidebar-column">
            <!-- Summary Card -->
            <div class="view-card summary-card">
                <div class="view-card-header" style="display: flex; align-items: center; gap: 14px;">
                    <div class="summary-icon">
                        <i class='bx bx-calendar-star'></i>
                    </div>
                    <div class="summary-title">
                        <h3>Exam Summary</h3>
                        <p>Quick overview of details</p>
                    </div>
                </div>
                <div class="view-card-body">
                    <div class="summary-item">
                        <span class="summary-item-label">Exam Type</span>
                        <span class="summary-item-value">{{ $examTypes[$examSchedule->exam_type] ?? $examSchedule->exam_type }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Date</span>
                        <span class="summary-item-value">{{ $examSchedule->exam_date ? $examSchedule->exam_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Time</span>
                        <span class="summary-item-value">
                            {{ $examSchedule->start_time ? \Carbon\Carbon::parse($examSchedule->start_time)->format('h:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Duration</span>
                        <span class="summary-item-value">{{ $examSchedule->duration_minutes ?? 'N/A' }} mins</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Venue</span>
                        <span class="summary-item-value">{{ $examSchedule->venue ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Students</span>
                        <span class="summary-item-value">{{ $examSchedule->number_of_students ?? $enrolledStudents ?? 'N/A' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Materials</span>
                        <span class="summary-item-value">
                            {{ is_array($materials) ? count($materials) : 0 }} item(s)
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-item-label">Published</span>
                        <span class="summary-item-value">
                            @if($examSchedule->is_published)
                                <span style="color: #059669;"><i class='bx bx-check-circle'></i> Yes</span>
                            @else
                                <span style="color: #f59e0b;"><i class='bx bx-x-circle'></i> No</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <div class="quick-actions-title">
                    <i class='bx bx-zap'></i>
                    Quick Actions
                </div>
                
                <a href="{{ route('college.exam-schedules.edit', $examSchedule) }}" class="action-btn edit">
                    <i class='bx bx-edit'></i>
                    Edit Schedule
                </a>
                
                <a href="{{ route('college.exam-schedules.print', $examSchedule) }}" class="action-btn print" target="_blank">
                    <i class='bx bx-printer'></i>
                    Print Schedule
                </a>
                
                <button type="button" class="action-btn delete" onclick="openDeleteModal()">
                    <i class='bx bx-trash'></i>
                    Delete Schedule
                </button>

                <a href="{{ route('college.exam-schedules.index') }}" class="action-btn back">
                    <i class='bx bx-arrow-back'></i>
                    Back to List
                </a>
            </div>

            <!-- Timestamps -->
            <div class="timestamps-card">
                <div class="timestamps-title">
                    <i class='bx bx-history'></i>
                    Activity Timeline
                </div>
                <div class="timestamp-item">
                    <i class='bx bx-plus-circle'></i>
                    <strong>Created:</strong> {{ $examSchedule->created_at ? $examSchedule->created_at->format('M d, Y h:i A') : 'N/A' }}
                    @if($examSchedule->createdBy)
                        by {{ $examSchedule->createdBy->name }}
                    @endif
                </div>
                <div class="timestamp-item">
                    <i class='bx bx-edit-alt'></i>
                    <strong>Updated:</strong> {{ $examSchedule->updated_at ? $examSchedule->updated_at->format('M d, Y h:i A') : 'N/A' }}
                    @if($examSchedule->updatedBy)
                        by {{ $examSchedule->updatedBy->name }}
                    @endif
                </div>
                @if($examSchedule->is_published && $examSchedule->published_at)
                <div class="timestamp-item">
                    <i class='bx bx-broadcast'></i>
                    <strong>Published:</strong> {{ $examSchedule->published_at->format('M d, Y h:i A') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="delete-modal-overlay" id="deleteModal">
    <div class="delete-modal">
        <div class="delete-modal-header">
            <div class="delete-modal-icon">
                <i class='bx bx-trash-alt'></i>
            </div>
            <h3>Delete Exam Schedule?</h3>
        </div>
        <div class="delete-modal-body">
            <p>You are about to delete this exam schedule:</p>
            <div class="exam-name">
                {{ $examSchedule->course->name ?? 'Exam Schedule' }}
            </div>
            <p>This action cannot be undone. All associated data will be permanently removed from the system.</p>
            <div class="warning-text">
                <i class='bx bx-error-circle'></i>
                This is a permanent action!
            </div>
        </div>
        <div class="delete-modal-footer">
            <button type="button" class="modal-btn cancel" onclick="closeDeleteModal()">
                <i class='bx bx-x'></i>
                Cancel
            </button>
            <form action="{{ route('college.exam-schedules.destroy', $examSchedule) }}" method="POST" style="flex: 1; margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="modal-btn confirm-delete" style="width: 100%;">
                    <i class='bx bx-trash'></i>
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal() {
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endsection
