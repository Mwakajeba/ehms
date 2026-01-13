<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== PUBLIC API ROUTES (No Authentication) ====================

// Parent Authentication
Route::prefix('parent')->group(function () {
    Route::post('/login', [ParentAuthController::class, 'login'])->middleware('throttle.api');
    Route::post('/register', [ParentAuthController::class, 'register'])->middleware('throttle.api');
    Route::post('/forgot-password', [ParentAuthController::class, 'forgotPassword'])->middleware('throttle.api');
    Route::post('/reset-password', [ParentAuthController::class, 'resetPassword'])->middleware('throttle.api');
});

// ==================== PROTECTED API ROUTES (Require Authentication) ====================

Route::middleware('auth:sanctum')->group(function () {
    
    // ==================== PARENT ROUTES ====================
    Route::prefix('parent')->group(function () {
        // Authentication & Profile
        Route::post('/logout', [ParentAuthController::class, 'logout']);
        Route::get('/me', [ParentAuthController::class, 'me']);
        Route::put('/profile', [ParentAuthController::class, 'updateProfile']);
        Route::put('/change-password', [ParentAuthController::class, 'changePassword']);
        
        // Students
        Route::get('/students', [ParentAuthController::class, 'getStudents']);
        Route::get('/students/{studentId}', [ParentAuthController::class, 'getStudent']);
        Route::get('/students/{studentId}/subjects', [ParentAuthController::class, 'getStudentSubjects']);
        
        // Assignments
        Route::get('/students/{studentId}/assignments', [ParentAuthController::class, 'getStudentAssignments']);
        Route::get('/students/{studentId}/assignments/{assignmentId}', [ParentAuthController::class, 'getAssignmentDetails']);
        Route::post('/students/{studentId}/assignments/{assignmentId}/submit', [ParentAuthController::class, 'submitAssignment']);
        
        // Attendance
        Route::get('/students/{studentId}/attendance', [ParentAuthController::class, 'getStudentAttendance']);
        Route::get('/students/{studentId}/attendance/stats', [ParentAuthController::class, 'getStudentAttendanceStats']);
        Route::get('/students/{studentId}/attendance/calendar', [ParentAuthController::class, 'getStudentAttendanceCalendar']);
        
        // Exams and Results
        Route::get('/students/{studentId}/exams', [ParentAuthController::class, 'getStudentExams']);
        Route::get('/students/{studentId}/exams/{examTypeId}/{academicYearId}', [ParentAuthController::class, 'getExamDetails']);
        Route::get('/students/{studentId}/results', [ParentAuthController::class, 'getStudentResults']);
        Route::get('/students/{studentId}/results/{examTypeId}', [ParentAuthController::class, 'getResultsByExamType']);
        
        // Fees and Payments
        Route::get('/students/{studentId}/fees', [ParentAuthController::class, 'getStudentFees']);
        Route::get('/students/{studentId}/fees/invoices', [ParentAuthController::class, 'getStudentInvoices']);
        Route::get('/students/{studentId}/fees/invoices/{invoiceId}', [ParentAuthController::class, 'getInvoiceDetails']);
        Route::get('/students/{studentId}/fees/payments', [ParentAuthController::class, 'getStudentPayments']);
        Route::get('/students/{studentId}/fees/balance', [ParentAuthController::class, 'getStudentFeeBalance']);
        Route::get('/students/{studentId}/fees/prepaid-transactions', [ParentAuthController::class, 'getPrepaidAccountTransactions']);
        Route::post('/students/{studentId}/fees/payment', [ParentAuthController::class, 'makePayment']);
        
        // Notifications
        Route::get('/notifications', [ParentAuthController::class, 'getNotifications']);
        Route::get('/notifications/unread', [ParentAuthController::class, 'getUnreadNotifications']);
        Route::put('/notifications/{notificationId}/read', [ParentAuthController::class, 'markNotificationAsRead']);
        Route::put('/notifications/read-all', [ParentAuthController::class, 'markAllNotificationsAsRead']);
        
        // Academic Information
        Route::get('/students/{studentId}/academic-info', [ParentAuthController::class, 'getAcademicInfo']);
        Route::get('/students/{studentId}/timetable', [ParentAuthController::class, 'getTimetable']);
        Route::get('/students/{studentId}/events', [ParentAuthController::class, 'getEvents']);
        
        // Library Materials
        Route::get('/students/{studentId}/library', [ParentAuthController::class, 'getLibraryMaterials']);
    });
    
    // ==================== TEACHER ROUTES (Future Implementation) ====================
    Route::prefix('teacher')->group(function () {
        // Will be implemented when teacher mobile app is needed
    });
    
    // ==================== ADMIN ROUTES (Future Implementation) ====================
    Route::prefix('admin')->group(function () {
        // Will be implemented when admin mobile app is needed
    });
});

// ==================== BIOMETRIC DEVICE API ROUTES (API Key/Secret Authentication) ====================
Route::prefix('biometric')->group(function () {
    Route::post('/punch', [App\Http\Controllers\Api\BiometricApiController::class, 'receivePunch']);
    Route::post('/punches', [App\Http\Controllers\Api\BiometricApiController::class, 'receiveBulkPunches']);
    Route::get('/status', [App\Http\Controllers\Api\BiometricApiController::class, 'getStatus']);
});

// ==================== WEBHOOK ROUTES (No Authentication Required) ====================
Route::prefix('webhooks')->group(function () {
    Route::post('/lipisha', [App\Http\Controllers\WebhookController::class, 'lipisha']);
});
