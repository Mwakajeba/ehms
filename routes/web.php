<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpEmailController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Production\ItemBatchController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\CashCollateralTypeController;
use App\Http\Controllers\CashCollateralController;
use App\Http\Controllers\AccountClassGroupController;
use App\Http\Controllers\Accounting\BankReconciliationController;

// Temporary route to set location
Route::get('/set-location/{locationId}', function ($locationId) {
    $user = auth()->user();
    $location = $user->locations()->find($locationId);

    if ($location) {
        session(['location_id' => $locationId, 'branch_id' => $location->branch_id]);
        return redirect()->back()->with('success', 'Location set to: ' . $location->name);
    }

    return redirect()->back()->with('error', 'Invalid location');
})->middleware('auth');

// Debug route for POS
Route::get('/debug-pos', function () {
    $user = auth()->user();
    $sessionLocationId = session('location_id');

    echo "User: " . $user->name . "<br>";
    echo "Session location_id: " . ($sessionLocationId ?? 'NULL') . "<br>";
    echo "Session branch_id: " . (session('branch_id') ?? 'NULL') . "<br>";

    if ($sessionLocationId) {
        $stockService = new \App\Services\InventoryStockService();
        $availableItems = $stockService->getAvailableItemsForSales($user->company_id, $sessionLocationId);
        $inventoryItems = $availableItems->filter(function ($item) {
            return !$item->is_service;
        })->values();

        echo "Available items count: " . $inventoryItems->count() . "<br>";
        foreach ($inventoryItems as $item) {
            echo "- " . $item->name . " (" . $item->code . ")<br>";
        }
    } else {
        echo "No session location set!<br>";
    }
})->middleware('auth');

// Temporary debug routes for session and stock testing
Route::get('/debug-session', function () {
    $user = Auth::user();
    if (!$user) {
        return 'Not authenticated';
    }

    $sessionLocationId = session('location_id');
    $sessionBranchId = session('branch_id');

    $defaultLocation = $user->defaultLocation()->first();
    $firstLocation = $user->locations()->first();

    $calculator = \App\Models\Inventory\Item::where('code', 'CALC001')->first();
    $currentStock = $calculator ? $calculator->current_stock : 'N/A';

    return [
        'user' => $user->name,
        'session_location_id' => $sessionLocationId,
        'session_branch_id' => $sessionBranchId,
        'default_location' => $defaultLocation ? ['id' => $defaultLocation->id, 'name' => $defaultLocation->name] : null,
        'first_location' => $firstLocation ? ['id' => $firstLocation->id, 'name' => $firstLocation->name] : null,
        'calculator_current_stock' => $currentStock,
        'all_locations' => $user->locations()->get(['id', 'name', 'branch_id'])->toArray()
    ];
});

Route::get('/set-location/{locationId}', function ($locationId) {
    $user = Auth::user();
    if (!$user) {
        return 'Not authenticated';
    }

    // Verify user has access to this location
    $location = $user->locations()->where('inventory_locations.id', $locationId)->first();
    if (!$location) {
        return 'Location not accessible to user';
    }

    session(['location_id' => $locationId, 'branch_id' => $location->branch_id]);

    return redirect()->route('inventory.movements.index')->with('success', 'Location set to: ' . $location->name);
});

// Auto-set location route
Route::get('/auto-set-location', function () {
    $user = Auth::user();
    if (!$user) {
        return 'Not authenticated';
    }

    $defaultLocation = $user->defaultLocation()->first();

    if ($defaultLocation) {
        session(['location_id' => $defaultLocation->id, 'branch_id' => $defaultLocation->branch_id]);
        $locationName = $defaultLocation->name;
    } else {
        $firstLocation = $user->locations()->first();
        if ($firstLocation) {
            session(['location_id' => $firstLocation->id, 'branch_id' => $firstLocation->branch_id]);
            $locationName = $firstLocation->name;
        } else {
            return 'No locations available for user';
        }
    }

    return redirect()->route('inventory.movements.index')->with('success', 'Location automatically set to: ' . $locationName);
});
use App\Http\Controllers\Accounting\BillPurchaseController;
use App\Http\Controllers\Accounting\BudgetController;

use App\Http\Controllers\Accounting\JournalEntryController;
use App\Http\Controllers\Accounting\PaymentVoucherController;
use App\Http\Controllers\Accounting\ReceiptVoucherController;
use App\Http\Controllers\Accounting\Reports\BankReconciliationReportController;
use App\Http\Controllers\Accounting\SupplierController;
use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\ChartAccountController;
use App\Http\Controllers\BankAccountController;
// use App\Http\Controllers\CashDepositController; // Controller missing - commented out
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\JournalController;


use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\MovementController;
use App\Http\Controllers\Inventory\TransferController;
use App\Http\Controllers\Inventory\WriteOffController;
use App\Http\Controllers\TransferRequestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Purchase\QuotationController;
use App\Http\Controllers\Purchase\OrderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Sales\DeliveryController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\CreditNoteController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesProformaController;
use App\Http\Controllers\Sales\CashSaleController;
use App\Http\Controllers\Sales\PosSaleController;
use App\Http\Controllers\ChangeBranchController;
use App\Http\Controllers\Inventory\OpeningBalanceController;
use App\Http\Controllers\ProjectController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.login');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration');

Route::get('/verify-sms', [AuthController::class, 'showVerificationForm'])->name('verify-sms');
Route::post('/verify-sms', [AuthController::class, 'verifySmsCode'])->middleware('throttle:otp');

Route::get('/forgotPassword', [AuthController::class, 'showForgotPasswordForm'])->name('forgotPassword');
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword'])->middleware('throttle:password_reset');

Route::get('/verify-otp-password', [AuthController::class, 'showVerificationForm'])->name('verify-otp-password');
Route::post('/verify-otp-password', [AuthController::class, 'verifyPasswordCode'])->middleware('throttle:otp');

Route::get('/reset-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password-form');
Route::post('/reset-password', [AuthController::class, 'storeNewPassword'])->middleware('throttle:password_reset');

Route::get('/resend-otp/{phone}', [AuthController::class, 'resendOtp'])->name('resend.otp')->middleware('throttle:otp');

// Subscription expired page
Route::get('/subscription-expired', function () {
    return view('auth.subscription-expired');
})->name('subscription.expired');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
// Test language route
Route::get('/test-language', function () {
    return view('test-language');
})->name('test.language');

Route::get('/request-email-otp', [OtpEmailController::class, 'showEmailForm'])->name('email-otp-form');
Route::post('/send-email-otp', [OtpEmailController::class, 'sendOtpEmail'])->name('email-otp-send');

Route::get('/global-search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->middleware(['auth', 'throttle:search'])->name('global-search');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'require.branch'])->name('dashboard');

// Approval Queue
Route::get('/approvals/queue', [App\Http\Controllers\ApprovalQueueController::class, 'index'])->middleware(['auth'])->name('approvals.queue');
Route::post('/approvals/bulk-approve', [App\Http\Controllers\ApprovalQueueController::class, 'bulkApprove'])->middleware(['auth'])->name('approvals.bulk-approve');
Route::post('/approvals/bulk-reject', [App\Http\Controllers\ApprovalQueueController::class, 'bulkReject'])->middleware(['auth'])->name('approvals.bulk-reject');
Route::get('/analytics', [AnalyticsController::class, 'analytics'])->middleware(['auth', 'require.branch'])->name('analytics.index');
Route::get('/analytics/dashboard-data', [AnalyticsController::class, 'getDashboardData'])->middleware(['auth', 'require.branch'])->name('analytics.dashboard-data');
Route::get('/analytics/drill-down', [AnalyticsController::class, 'getDrillDown'])->middleware(['auth', 'require.branch'])->name('analytics.drill-down');
Route::get('/analytics/export-pdf', [AnalyticsController::class, 'exportPdf'])->middleware(['auth', 'require.branch'])->name('analytics.export-pdf');
Route::get('/expiry-alerts', [DashboardController::class, 'expiryAlerts'])->middleware(['auth'])->name('expiry-alerts');
Route::get('/expiry-alerts/data', [DashboardController::class, 'expiryAlertsData'])->middleware(['auth'])->name('expiry-alerts.data');
Route::get('/dashboard/top-items-sold', [DashboardController::class, 'topItemsSoldYear'])->middleware(['auth']);
Route::get('/dashboard/gross-profit-trend', [DashboardController::class, 'grossProfitTrend'])->middleware(['auth']);
Route::get('/dashboard/kpis', [DashboardController::class, 'dashboardKpis'])->middleware(['auth']);
Route::get('/dashboard/enhanced-kpis', [DashboardController::class, 'enhancedKpis'])->middleware(['auth']);
Route::get('/dashboard/revenue-trend', [DashboardController::class, 'revenueTrend'])->middleware(['auth']);
Route::get('/dashboard/order-status', [DashboardController::class, 'orderStatusDistribution'])->middleware(['auth']);
Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts'])->middleware(['auth']);
Route::get('/dashboard/profit-by-year', [DashboardController::class, 'profitByYear'])->middleware(['auth']);
Route::get('/dashboard/revenue-by-location', [DashboardController::class, 'revenueByLocation'])->middleware(['auth']);
Route::get('/dashboard/labor-trend', [DashboardController::class, 'laborTrend'])->middleware(['auth']);
Route::get('/dashboard/operating-expense-by-department', [DashboardController::class, 'operatingExpenseByDepartment'])->middleware(['auth']);
Route::get('/dashboard/company-performance', [DashboardController::class, 'companyPerformance'])->middleware(['auth']);
Route::get('/dashboard/ebitda-trend', [DashboardController::class, 'ebitdaTrend'])->middleware(['auth']);
Route::get('/dashboard/net-income-trend', [DashboardController::class, 'netIncomeTrend'])->middleware(['auth']);

// Change Branch Routes (excluded from require.branch middleware to avoid infinite redirects)
Route::get('/change-branch', [ChangeBranchController::class, 'show'])->middleware('auth')->name('change-branch');
Route::post('/change-branch', [ChangeBranchController::class, 'change'])->middleware('auth')->name('change-branch.submit');
Route::get('/change-branch/locations', [ChangeBranchController::class, 'locations'])->middleware('auth')->name('change-branch.locations');

// Reports Route
Route::get('/reports', [ReportsController::class, 'index'])->middleware(['auth', 'require.branch'])->name('reports.index');
Route::get('/reports/customers', [ReportsController::class, 'customers'])->middleware(['auth', 'require.branch'])->name('reports.customers');
Route::get('/reports/accounting', [ReportsController::class, 'accounting'])->middleware(['auth', 'require.branch'])->name('reports.accounting');

// School Management Routes
Route::prefix('school')->name('school.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
                                                                Route::get('exam-schedules/api/get-streams/{id}', [App\Http\Controllers\School\ExamSchedulesController::class, 'getStreams'])->name('exam-schedules.api.get-streams');
                                                            Route::post('exam-schedules', [App\Http\Controllers\School\ExamSchedulesController::class, 'store'])->name('exam-schedules.store');
                                                        Route::get('exam-schedules-data', [App\Http\Controllers\School\ExamSchedulesController::class, 'data'])->name('exam-schedules.data');
                                                    Route::post('exam-schedules/{hashid}/publish', [App\Http\Controllers\School\ExamSchedulesController::class, 'publish'])->name('exam-schedules.publish');
                                                Route::get('exam-schedules/create', [App\Http\Controllers\School\ExamSchedulesController::class, 'create'])->name('exam-schedules.create');
                                            Route::post('assignments', [App\Http\Controllers\School\AssignmentController::class, 'store'])->name('assignments.store');
                                        Route::get('assignments-data', [App\Http\Controllers\School\AssignmentController::class, 'data'])->name('assignments.data');
                                    Route::get('assignments/create', [App\Http\Controllers\School\AssignmentController::class, 'create'])->name('assignments.create');
                                Route::post('timetables/get-streams', [App\Http\Controllers\School\TimetableController::class, 'getStreams'])->name('timetables.get-streams');
                            Route::post('timetables', [App\Http\Controllers\School\TimetableController::class, 'store'])->name('timetables.store');
                        Route::post('timetables/generate-teacher-timetables', [App\Http\Controllers\School\TimetableController::class, 'generateTeacherTimetables'])->name('timetables.generate-teacher-timetables');
                    Route::get('timetables-data', [App\Http\Controllers\School\TimetableController::class, 'data'])->name('timetables.data');
                // Exam Schedules Management
                Route::get('exam-schedules', [App\Http\Controllers\School\ExamSchedulesController::class, 'index'])->name('exam-schedules.index');
            // Assignments Management
            Route::get('assignments', [App\Http\Controllers\School\AssignmentController::class, 'index'])->name('assignments.index');
        // Timetables Management
        Route::get('timetables', [App\Http\Controllers\School\TimetableController::class, 'index'])->name('timetables.index');
        Route::get('timetables/create', [App\Http\Controllers\School\TimetableController::class, 'create'])->name('timetables.create');
    Route::get('/', [App\Http\Controllers\School\SchoolController::class, 'index'])->name('index');
    Route::get('/dashboard', [App\Http\Controllers\School\SchoolDashboardController::class, 'index'])->name('dashboard');
    // Student Informations
    Route::resource('student-informations', App\Http\Controllers\School\StudentInformationsController::class)->names('student-informations');
    Route::get('student-informations-data', [App\Http\Controllers\School\StudentInformationsController::class, 'data'])->name('student-informations.data');
    // Student Information API routes
    Route::get('api/academic-years', [App\Http\Controllers\School\StudentInformationsController::class, 'getAcademicYears'])->name('api.academic-years');
    // Student Information Submodules
    Route::get('streams', [App\Http\Controllers\School\StreamsController::class, 'index'])->name('streams.index');
    Route::get('streams/create', [App\Http\Controllers\School\StreamsController::class, 'create'])->name('streams.create');
    Route::post('streams', [App\Http\Controllers\School\StreamsController::class, 'store'])->name('streams.store');
    Route::get('streams/{encodedId}', [App\Http\Controllers\School\StreamsController::class, 'show'])->name('streams.show');
    Route::get('streams/{encodedId}/edit', [App\Http\Controllers\School\StreamsController::class, 'edit'])->name('streams.edit');
    Route::put('streams/{encodedId}', [App\Http\Controllers\School\StreamsController::class, 'update'])->name('streams.update');
    Route::delete('streams/{encodedId}', [App\Http\Controllers\School\StreamsController::class, 'destroy'])->name('streams.destroy');
    Route::get('streams-data', [App\Http\Controllers\School\StreamsController::class, 'data'])->name('streams.data');
    Route::get('classes', [App\Http\Controllers\School\ClassesController::class, 'index'])->name('classes.index');
    Route::get('classes/create', [App\Http\Controllers\School\ClassesController::class, 'create'])->name('classes.create');
    Route::post('classes', [App\Http\Controllers\School\ClassesController::class, 'store'])->name('classes.store');
    Route::get('classes/{classe}', [App\Http\Controllers\School\ClassesController::class, 'show'])->name('classes.show');
    Route::get('classes/{classe}/edit', [App\Http\Controllers\School\ClassesController::class, 'edit'])->name('classes.edit');
    Route::put('classes/{classe}', [App\Http\Controllers\School\ClassesController::class, 'update'])->name('classes.update');
    Route::delete('classes/{classe}', [App\Http\Controllers\School\ClassesController::class, 'destroy'])->name('classes.destroy');
    Route::get('classes-data', [App\Http\Controllers\School\ClassesController::class, 'data'])->name('classes.data');
    Route::get('classes/streams/{classId}', [App\Http\Controllers\School\ClassesController::class, 'getStreams'])->name('classes.streams');
    Route::get('routes', [App\Http\Controllers\School\RoutesController::class, 'index'])->name('routes.index');
    Route::get('routes/create', [App\Http\Controllers\School\RoutesController::class, 'create'])->name('routes.create');
    Route::post('routes', [App\Http\Controllers\School\RoutesController::class, 'store'])->name('routes.store');
    Route::get('routes/{route}', [App\Http\Controllers\School\RoutesController::class, 'show'])->name('routes.show');
    Route::get('routes/{route}/edit', [App\Http\Controllers\School\RoutesController::class, 'edit'])->name('routes.edit');
    Route::put('routes/{route}', [App\Http\Controllers\School\RoutesController::class, 'update'])->name('routes.update');
    Route::delete('routes/{route}', [App\Http\Controllers\School\RoutesController::class, 'destroy'])->name('routes.destroy');
    Route::get('bus-stops', [App\Http\Controllers\School\BusStopsController::class, 'index'])->name('bus-stops.index');
    Route::get('bus-stops/create', [App\Http\Controllers\School\BusStopsController::class, 'create'])->name('bus-stops.create');
    Route::post('bus-stops', [App\Http\Controllers\School\BusStopsController::class, 'store'])->name('bus-stops.store');
    Route::get('bus-stops/{busStop}', [App\Http\Controllers\School\BusStopsController::class, 'show'])->name('bus-stops.show');
    Route::get('bus-stops/{busStop}/edit', [App\Http\Controllers\School\BusStopsController::class, 'edit'])->name('bus-stops.edit');
    Route::put('bus-stops/{busStop}', [App\Http\Controllers\School\BusStopsController::class, 'update'])->name('bus-stops.update');
    Route::delete('bus-stops/{busStop}', [App\Http\Controllers\School\BusStopsController::class, 'destroy'])->name('bus-stops.destroy');
    Route::get('buses', [App\Http\Controllers\School\BusesController::class, 'index'])->name('buses.index');
    Route::get('buses/create', [App\Http\Controllers\School\BusesController::class, 'create'])->name('buses.create');
    Route::post('buses', [App\Http\Controllers\School\BusesController::class, 'store'])->name('buses.store');
    Route::get('buses/{bus}', [App\Http\Controllers\School\BusesController::class, 'show'])->name('buses.show');
    Route::get('buses/{bus}/edit', [App\Http\Controllers\School\BusesController::class, 'edit'])->name('buses.edit');
    Route::put('buses/{bus}', [App\Http\Controllers\School\BusesController::class, 'update'])->name('buses.update');
    Route::delete('buses/{bus}', [App\Http\Controllers\School\BusesController::class, 'destroy'])->name('buses.destroy');
    Route::get('buses/{bus}/assign-routes', [App\Http\Controllers\School\BusesController::class, 'assignRoutes'])->name('buses.assign-routes');
    Route::put('buses/{bus}/assign-routes', [App\Http\Controllers\School\BusesController::class, 'updateAssignedRoutes'])->name('buses.update-assigned-routes');
    Route::delete('buses/{bus}/routes/{route}', [App\Http\Controllers\School\BusesController::class, 'removeAssignedRoute'])->name('buses.remove-assigned-route');
    Route::resource('students', App\Http\Controllers\School\StudentsController::class)->names('students')->parameters(['students' => 'encodedId']);
    Route::get('students-data', [App\Http\Controllers\School\StudentsController::class, 'data'])->name('students.data');
    Route::get('students/{encodedId}/assign-parents', [App\Http\Controllers\School\StudentsController::class, 'assignParents'])->name('students.assign-parents');
    Route::post('students/{encodedId}/assign-parents', [App\Http\Controllers\School\StudentsController::class, 'storeAssignedParents'])->name('students.store-assigned-parents');
    Route::post('students/{encodedId}/assign-existing-parents', [App\Http\Controllers\School\StudentsController::class, 'assignExistingParents'])->name('students.assign-existing-parents');
    Route::delete('students/{encodedId}/remove-parent/{parent}', [App\Http\Controllers\School\StudentsController::class, 'removeParent'])->name('students.remove-parent');
    // Guardian management routes
    Route::put('students/{encodedId}/guardians/{guardian}', [App\Http\Controllers\School\StudentsController::class, 'updateGuardian'])->name('students.update-guardian');
    Route::delete('students/{encodedId}/guardians/{guardian}', [App\Http\Controllers\School\StudentsController::class, 'removeGuardian'])->name('students.remove-guardian');
    Route::post('students/{encodedId}/guardians', [App\Http\Controllers\School\StudentsController::class, 'addGuardian'])->name('students.add-guardian');
    // API route for getting streams by class
    Route::get('api/students/streams-by-class', [App\Http\Controllers\School\StudentsController::class, 'getStreamsByClass'])->name('api.students.streams-by-class');
    // API route for getting bus stops
    Route::get('api/students/bus-stops', [App\Http\Controllers\School\StudentsController::class, 'getBusStops'])->name('api.students.bus-stops');
    // API route for searching parents
    Route::get('api/students/{encodedId}/parents/search', [App\Http\Controllers\School\StudentsController::class, 'searchParents'])->name('api.parents.search');
    // Student export routes
    Route::get('students-export/excel/{hashId}', [App\Http\Controllers\School\StudentsController::class, 'exportExcel'])->name('students.export.excel');
    Route::get('students-export/pdf/{hashId}', [App\Http\Controllers\School\StudentsController::class, 'exportPdf'])->name('students.export.pdf');
    // Student import routes
    Route::get('students-import', [App\Http\Controllers\School\StudentsController::class, 'import'])->name('students.import');
    Route::post('students-import', [App\Http\Controllers\School\StudentsController::class, 'processImport'])->name('students.import.process');
    Route::get('students-import/template', [App\Http\Controllers\School\StudentsController::class, 'downloadTemplate'])->name('students.import.template');
    // Promote Students Management
    Route::get('promote-students', [App\Http\Controllers\School\PromoteStudentsController::class, 'index'])->name('promote-students.index');
    Route::get('promote-students/create', [App\Http\Controllers\School\PromoteStudentsController::class, 'create'])->name('promote-students.create');
    Route::get('promote-students/bulk-select', [App\Http\Controllers\School\PromoteStudentsController::class, 'bulkSelect'])->name('promote-students.bulk-select');
    Route::post('promote-students', [App\Http\Controllers\School\PromoteStudentsController::class, 'store'])->name('promote-students.store');
    // Student Transfers Management
    Route::get('student-transfers/data', [App\Http\Controllers\School\StudentTransfersController::class, 'data'])->name('student-transfers.data');
    Route::get('student-transfers/get-students', [App\Http\Controllers\School\StudentTransfersController::class, 'getStudents'])->name('student-transfers.get-students');
    Route::post('student-transfers/transfer-in/{encodedId}', [App\Http\Controllers\School\StudentTransfersController::class, 'completeTransferIn'])->name('student-transfers.transfer-in');
    Route::get('student-transfers/export/excel', [App\Http\Controllers\School\StudentTransfersController::class, 'exportExcel'])->name('student-transfers.export.excel');
    Route::get('student-transfers/export/pdf', [App\Http\Controllers\School\StudentTransfersController::class, 'exportPdf'])->name('student-transfers.export.pdf');
    Route::get('student-transfers/{encodedId}/print', [App\Http\Controllers\School\StudentTransfersController::class, 'print'])->name('student-transfers.print');
    Route::get('student-transfers/{encodedId}/pdf', [App\Http\Controllers\School\StudentTransfersController::class, 'print'])->name('student-transfers.pdf');
    Route::get('student-transfers/{encodedId}/pdf-preview', [App\Http\Controllers\School\StudentTransfersController::class, 'previewPdf'])->name('student-transfers.pdf-preview');
    Route::resource('student-transfers', App\Http\Controllers\School\StudentTransfersController::class)->names('student-transfers')->parameters(['student-transfers' => 'encodedId']);
    // Promote Students Management
    Route::get('promote-students/get-students', [App\Http\Controllers\School\PromoteStudentsController::class, 'getStudents'])->name('promote-students.get-students');
    Route::get('promote-students/bulk-select', [App\Http\Controllers\School\PromoteStudentsController::class, 'bulkSelect'])->name('promote-students.bulk-select');
    Route::post('promote-students/bulk-promote', [App\Http\Controllers\School\PromoteStudentsController::class, 'bulkPromote'])->name('promote-students.bulk-promote');
    Route::resource('promote-students', App\Http\Controllers\School\PromoteStudentsController::class)->names('promote-students')->only(['index', 'create', 'store']);
    // Academic Years Management
    Route::resource('academic-years', App\Http\Controllers\School\AcademicYearController::class)->names('academic-years');
    Route::post('academic-years/{academic_year}/set-current', [App\Http\Controllers\School\AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
    // API route for checking stream existence
    Route::get('api/streams/check-name', function (Request $request) {
        $name = $request->get('name');
        $excludeId = $request->get('exclude_id');
        if (!$name) {
            return response()->json(['exists' => false]);
        }
        $query = \App\Models\School\Stream::where('name', trim($name));
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        $exists = $query->exists();
        return response()->json(['exists' => $exists]);
    })->name('api.streams.check-name');
    // Academics & Examinations
    // Marks Entry Management - MUST come before resource route
    Route::get('marks-entry', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'marksEntry'])->name('marks-entry');
    // Redirect old URL to new URL for backward compatibility
    Route::get('academics-examinations/marks-entry', function () {
        return redirect()->route('school.marks-entry');
    });
    Route::post('academics-examinations/get-classes-for-marks-entry', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getClassesForMarksEntry'])->name('academics-examinations.get-classes-for-marks-entry');
    Route::get('academics-examinations/get-marks-entry-data', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getMarksEntryData'])->name('academics-examinations.get-marks-entry-data');
    Route::post('academics-examinations/save-marks', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'saveMarks'])->name('academics-examinations.save-marks');
    Route::get('academics-examinations/download-marks-sample', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'downloadMarksSample'])->name('academics-examinations.download-marks-sample');
    Route::post('academics-examinations/import-marks', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'importMarks'])->name('academics-examinations.import-marks');

    // Grade Scales Management
    Route::get('grade-scales', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'gradeScales'])->name('grade-scales.index');
    Route::get('grade-scales/create', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'createGradeScale'])->name('grade-scales.create');
    Route::post('grade-scales', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'storeGradeScale'])->name('grade-scales.store');
    Route::get('grade-scales/{gradeScale}', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'showGradeScale'])->name('grade-scales.show');
    Route::get('grade-scales/{gradeScale}/edit', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'editGradeScale'])->name('grade-scales.edit');
    Route::put('grade-scales/{gradeScale}', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'updateGradeScale'])->name('grade-scales.update');
    Route::patch('grade-scales/{gradeScale}/toggle-status', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'toggleGradeScaleStatus'])->name('grade-scales.toggle-status');
    Route::delete('grade-scales/{gradeScale}', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'destroyGradeScale'])->name('grade-scales.destroy');
    Route::get('grade-scales-data', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'gradeScalesData'])->name('grade-scales.data');
    Route::get('grade-scales-by-academic-year', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getGradeScalesByAcademicYear'])->name('grade-scales.by-academic-year');
    Route::post('get-grade-for-mark', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getGradeForMark'])->name('grade-scales.get-grade-for-mark');
    // Exam Types Management
    Route::resource('exam-types', App\Http\Controllers\SchoolExamTypeController::class)->names('exam-types');
    Route::patch('exam-types/{id}/toggle-status', [App\Http\Controllers\SchoolExamTypeController::class, 'toggleStatus'])->name('exam-types.toggle-status');
    Route::patch('exam-types/{id}/toggle-publish', [App\Http\Controllers\SchoolExamTypeController::class, 'togglePublish'])->name('exam-types.toggle-publish');
    Route::get('exam-types-data', [App\Http\Controllers\SchoolExamTypeController::class, 'data'])->name('exam-types.data');
    // Exams Management
    Route::resource('exams', App\Http\Controllers\SchoolExamController::class)->names('exams');
    Route::patch('exams/{exam}/update-status', [App\Http\Controllers\SchoolExamController::class, 'updateStatus'])->name('exams.update-status');
    Route::get('exams/api/streams', [App\Http\Controllers\SchoolExamController::class, 'getStreams'])->name('exams.api.streams');
    // Exam Class Assignments Management
    Route::resource('exam-class-assignments', App\Http\Controllers\School\ExamClassAssignmentController::class)->names('exam-class-assignments');
    Route::get('exam-class-assignments-data', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'data'])->name('exam-class-assignments.data');
    Route::get('exam-class-assignments/api/subjects/{classId}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'getSubjects'])->name('exam-class-assignments.api.subjects');
    Route::get('exam-class-assignments/api/streams/{classId}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'getStreams'])->name('exam-class-assignments.api.streams');
    Route::get('exam-class-assignments/show-group/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'showGroup'])->name('exam-class-assignments.show-group');
    Route::get('exam-class-assignments/edit-group/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'editGroup'])->name('exam-class-assignments.edit-group');
    Route::patch('exam-class-assignments/update-group/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'updateGroup'])->name('exam-class-assignments.update-group');
    Route::delete('exam-class-assignments/destroy-group/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'destroyGroup'])->name('exam-class-assignments.destroy-group');
    Route::patch('exam-class-assignments/{examClassAssignment}/update-status', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'updateStatus'])->name('exam-class-assignments.update-status');
    Route::get('exam-class-assignments/manage-registration/{exam_type_hash}/{class_hash}/{academic_year_hash}/{student_id}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'manageStudentRegistration'])->name('exam-class-assignments.manage-registration');
    Route::post('exam-class-assignments/save-registration/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'saveStudentRegistration'])->name('exam-class-assignments.save-registration');
    Route::get('exam-class-assignments/bulk-manage-registration/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'bulkManageRegistration'])->name('exam-class-assignments.bulk-manage-registration');
    Route::post('exam-class-assignments/bulk-save-registration/{exam_type_hash}/{class_hash}/{academic_year_hash}', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'bulkSaveRegistration'])->name('exam-class-assignments.bulk-save-registration');
    Route::post('exam-class-assignments/check-duplicates', [App\Http\Controllers\School\ExamClassAssignmentController::class, 'checkDuplicates'])->name('exam-class-assignments.check-duplicates');
    // Exam Schedule Management
    Route::resource('exam-schedules', App\Http\Controllers\School\ExamScheduleController::class)->names('exam-schedules');
    Route::post('exam-schedules/{hashid}/auto-schedule', [App\Http\Controllers\School\ExamScheduleController::class, 'autoSchedule'])->name('exam-schedules.auto-schedule');
    Route::post('exam-schedules/{hashid}/publish', [App\Http\Controllers\School\ExamScheduleController::class, 'publish'])->name('exam-schedules.publish');
    Route::get('exam-schedules/api/get-classes', [App\Http\Controllers\School\ExamScheduleController::class, 'getClasses'])->name('exam-schedules.api.get-classes');
    Route::get('exam-schedules/api/get-streams/{classId}', [App\Http\Controllers\School\ExamScheduleController::class, 'getStreams'])->name('exam-schedules.api.get-streams');
    Route::get('exam-schedules/api/get-courses', [App\Http\Controllers\School\ExamScheduleController::class, 'getCourses'])->name('exam-schedules.api.get-courses');
    Route::resource('subjects', App\Http\Controllers\School\SubjectsController::class)->names('subjects');
    Route::get('subjects-data', [App\Http\Controllers\School\SubjectsController::class, 'data'])->name('subjects.data');
    // Subject Groups Management
    Route::resource('subject-groups', App\Http\Controllers\School\SubjectGroupsController::class)->names('subject-groups');
    Route::get('subject-groups-data', [App\Http\Controllers\School\SubjectGroupsController::class, 'data'])->name('subject-groups.data');
    // Teacher Assignment Management
    Route::resource('class-teachers', App\Http\Controllers\School\ClassTeachersController::class)->names('class-teachers');
    Route::patch('class-teachers/{classTeacher}/toggle-status', [App\Http\Controllers\School\ClassTeachersController::class, 'toggleStatus'])->name('class-teachers.toggle-status');
    Route::get('class-teachers/streams/{classId}', [App\Http\Controllers\School\ClassTeachersController::class, 'getStreams'])->name('class-teachers.streams');
    Route::get('class-teachers-data', [App\Http\Controllers\School\ClassTeachersController::class, 'data'])->name('class-teachers.data');
    Route::resource('subject-teachers', App\Http\Controllers\School\SubjectTeachersController::class)->names('subject-teachers');
    Route::patch('subject-teachers/{subjectTeacher}/toggle-status', [App\Http\Controllers\School\SubjectTeachersController::class, 'toggleStatus'])->name('subject-teachers.toggle-status');
    Route::post('subject-teachers/check-duplicate', [App\Http\Controllers\School\SubjectTeachersController::class, 'checkDuplicate'])->name('subject-teachers.check-duplicate');
    Route::get('subject-teachers/streams/{classId}', [App\Http\Controllers\School\SubjectTeachersController::class, 'getStreams'])->name('subject-teachers.streams');
    Route::get('subject-teachers/subjects/{classId}', [App\Http\Controllers\School\SubjectTeachersController::class, 'getSubjects'])->name('subject-teachers.subjects');
    // Fee Management
    Route::resource('fee-management', App\Http\Controllers\School\FeeManagementController::class)->names('fee-management');
    // Fee Groups
    Route::resource('fee-groups', App\Http\Controllers\School\FeeGroupController::class)->names('fee-groups');
    Route::get('fee-groups-data', [App\Http\Controllers\School\FeeGroupController::class, 'data'])->name('fee-groups.data');
    // Fee Settings
    Route::resource('fee-settings', App\Http\Controllers\School\FeeSettingController::class)->names('fee-settings')->withoutMiddleware('require.branch');
    Route::get('fee-settings-data', [App\Http\Controllers\School\FeeSettingController::class, 'data'])->name('fee-settings.data')->withoutMiddleware('require.branch');
    Route::resource('fee-invoices', App\Http\Controllers\School\FeeInvoiceController::class)->names('fee-invoices');
    Route::get('fee-invoices-data', [App\Http\Controllers\School\FeeInvoiceController::class, 'data'])->name('fee-invoices.data');
    Route::get('fee-invoices/get-students', [App\Http\Controllers\School\FeeInvoiceController::class, 'getStudents'])->name('fee-invoices.get-students');
    Route::get('fee-invoices/get-streams', [App\Http\Controllers\School\FeeInvoiceController::class, 'getStreams'])->name('fee-invoices.get-streams');
    Route::post('fee-invoices/validate-invoices', [App\Http\Controllers\School\FeeInvoiceController::class, 'validateInvoices'])->name('fee-invoices.validate-invoices');
    Route::get('fee-invoices/export/excel/{hashId}', [App\Http\Controllers\School\FeeInvoiceController::class, 'exportExcel'])->name('fee-invoices.export.excel');
    Route::get('fee-invoices/export/pdf/{hashId}', [App\Http\Controllers\School\FeeInvoiceController::class, 'exportPdf'])->name('fee-invoices.export.pdf');
    Route::post('fee-invoices/bulk-remaining-balance', [App\Http\Controllers\School\FeeInvoiceController::class, 'bulkRemainingBalance'])->name('fee-invoices.bulk-remaining-balance');
    Route::post('fee-invoices/generate-bulk-outstanding', [App\Http\Controllers\School\FeeInvoiceController::class, 'generateBulkInvoicesForOutstandingStudents'])->name('fee-invoices.generate-bulk-outstanding');
    Route::get('fee-invoices/student/{encodedId}', [App\Http\Controllers\School\FeeInvoiceController::class, 'studentInvoices'])->name('fee-invoices.student');
    Route::get('fee-invoices/student/{encodedId}/quarter/{quarter}/bulk-payment', [App\Http\Controllers\School\FeeInvoiceController::class, 'createBulkPayment'])->name('fee-invoices.bulk-payment');
    Route::get('fee-invoices/student/{encodedId}/quarter/{quarter}/single-payment', [App\Http\Controllers\School\FeeInvoiceController::class, 'createSinglePayment'])->name('fee-invoices.single-payment');
    Route::post('fee-invoices/student/{encodedId}/quarter/{quarter}/bulk-payment', [App\Http\Controllers\School\FeeInvoiceController::class, 'storeBulkPayment'])->name('fee-invoices.bulk-payment.store');
    Route::post('fee-invoices/student/{encodedId}/quarter/{quarter}/single-payment', [App\Http\Controllers\School\FeeInvoiceController::class, 'storeSinglePayment'])->name('fee-invoices.single-payment.store');
    Route::post('fee-invoices/student/{encodedId}/single-payment-modal', [App\Http\Controllers\School\FeeInvoiceController::class, 'storeSinglePaymentModal'])->name('fee-invoices.single-payment-modal.store');
    Route::get('fee-invoices/student/{encodedId}/single-payment-data', [App\Http\Controllers\School\FeeInvoiceController::class, 'getSinglePaymentData'])->name('fee-invoices.single-payment-data');
    Route::post('fee-invoices/student/{encodedId}/opening-balance-payment', [App\Http\Controllers\School\FeeInvoiceController::class, 'storeOpeningBalancePayment'])->name('fee-invoices.opening-balance-payment.store');
    // Prepaid Accounts - Define specific routes before resource route to avoid conflicts
    Route::get('prepaid-accounts-data', [App\Http\Controllers\School\PrepaidAccountController::class, 'data'])->name('prepaid-accounts.data');
    Route::get('prepaid-accounts/import', [App\Http\Controllers\School\PrepaidAccountController::class, 'import'])->name('prepaid-accounts.import');
    Route::post('prepaid-accounts/process-import', [App\Http\Controllers\School\PrepaidAccountController::class, 'processImport'])->name('prepaid-accounts.process-import');
    Route::post('prepaid-accounts/export-template', [App\Http\Controllers\School\PrepaidAccountController::class, 'exportTemplate'])->name('prepaid-accounts.export-template');
    Route::get('prepaid-accounts/settings', [App\Http\Controllers\School\PrepaidAccountController::class, 'settings'])->name('prepaid-accounts.settings');
    Route::post('prepaid-accounts/update-settings', [App\Http\Controllers\School\PrepaidAccountController::class, 'updateSettings'])->name('prepaid-accounts.update-settings');
    Route::get('prepaid-accounts/get-students', [App\Http\Controllers\School\PrepaidAccountController::class, 'getStudentsByClass'])->name('prepaid-accounts.get-students');
    Route::post('prepaid-accounts/{encodedId}/add-credit', [App\Http\Controllers\School\PrepaidAccountController::class, 'addCredit'])->name('prepaid-accounts.add-credit');
    Route::post('prepaid-accounts/bulk-create', [App\Http\Controllers\School\PrepaidAccountController::class, 'bulkCreate'])->name('prepaid-accounts.bulk-create');
    Route::post('prepaid-accounts/sync-gl-transactions', [App\Http\Controllers\School\PrepaidAccountController::class, 'syncGlTransactions'])->name('prepaid-accounts.sync-gl-transactions');
    // Resource route - only include methods we need (exclude destroy)
    Route::resource('prepaid-accounts', App\Http\Controllers\School\PrepaidAccountController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->names('prepaid-accounts');
    // Explicit DELETE route - after resource route but with different parameter name to avoid conflict
    Route::delete('prepaid-accounts/{hashId}/delete', [App\Http\Controllers\School\PrepaidAccountController::class, 'destroy'])->name('prepaid-accounts.destroy');
    Route::put('fee-invoices/payment/{id}/update', [App\Http\Controllers\School\FeeInvoiceController::class, 'updatePayment'])->name('fee-invoices.payment.update');
    Route::delete('fee-invoices/payment/{id}/delete', [App\Http\Controllers\School\FeeInvoiceController::class, 'deletePayment'])->name('fee-invoices.payment.delete');
    Route::get('fee-invoices/{fee_invoice}/details', [App\Http\Controllers\School\FeeInvoiceController::class, 'getInvoiceDetails'])->name('fee-invoices.details');

    // Student Fee Opening Balance
    Route::get('student-fee-opening-balance/data', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'data'])->name('student-fee-opening-balance.data');
    Route::get('student-fee-opening-balance/export/pdf', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'exportPdf'])->name('student-fee-opening-balance.export.pdf');
    Route::get('student-fee-opening-balance/export/excel', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'exportExcel'])->name('student-fee-opening-balance.export.excel');
    Route::get('student-fee-opening-balance/import', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'import'])->name('student-fee-opening-balance.import');
    Route::post('student-fee-opening-balance/import', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'processImport'])->name('student-fee-opening-balance.import.process');
    Route::get('student-fee-opening-balance/import/template', [App\Http\Controllers\School\StudentFeeOpeningBalanceController::class, 'downloadTemplate'])->name('student-fee-opening-balance.import.template');
    Route::resource('student-fee-opening-balance', App\Http\Controllers\School\StudentFeeOpeningBalanceController::class)->names('student-fee-opening-balance')->parameters(['student-fee-opening-balance' => 'encodedId']);

    // Other Income
    Route::resource('other-income', App\Http\Controllers\School\OtherIncomeController::class)->names('other-income');
    Route::get('other-income-data', [App\Http\Controllers\School\OtherIncomeController::class, 'data'])->name('other-income.data');
    Route::get('other-income/{encodedId}/export-pdf', [App\Http\Controllers\School\OtherIncomeController::class, 'exportPdf'])->name('other-income.export-pdf');
    Route::get('other-income/export-list-pdf', [App\Http\Controllers\School\OtherIncomeController::class, 'exportListPdf'])->name('other-income.export-list-pdf');
    Route::get('other-income/export-list-excel', [App\Http\Controllers\School\OtherIncomeController::class, 'exportListExcel'])->name('other-income.export-list-excel');

    // Specific report routes must come BEFORE the resource route
    Route::get('reports/student-report', [App\Http\Controllers\School\SchoolReportsController::class, 'studentReport'])->name('reports.student-report');
    Route::match(['get', 'post'], 'reports/fee-report', [App\Http\Controllers\School\SchoolReportsController::class, 'feeReport'])->name('reports.fee-report');
    Route::get('reports/fee-report/streams-by-class', [App\Http\Controllers\School\SchoolReportsController::class, 'getStreamsByClass'])->name('reports.fee-report.streams-by-class');
    Route::get('reports/academic-report', [App\Http\Controllers\School\SchoolReportsController::class, 'academicReport'])->name('reports.academic-report');
    Route::get('reports/gender-distribution', [App\Http\Controllers\School\SchoolReportsController::class, 'genderDistribution'])->name('reports.gender-distribution');
    Route::get('reports/examination-results', [App\Http\Controllers\School\SchoolReportsController::class, 'examinationResults'])->name('reports.examination-results');
    Route::get('reports/subject-wise-analysis', [App\Http\Controllers\School\SchoolReportsController::class, 'subjectWiseAnalysis'])->name('reports.subject-wise-analysis');
    Route::get('reports/subject-wise-analysis/export', [App\Http\Controllers\School\SchoolReportsController::class, 'exportSubjectWiseAnalysis'])->name('reports.subject-wise-analysis.export');
    Route::get('reports/subject-wise-attendance', [App\Http\Controllers\School\SchoolReportsController::class, 'subjectWiseAttendanceReport'])->name('reports.subject-wise-attendance');
    Route::get('reports/comparative-subject-performance', [App\Http\Controllers\School\SchoolReportsController::class, 'comparativeSubjectPerformance'])->name('reports.comparative-subject-performance');
    Route::get('reports/comparative-subject-performance/pdf', [App\Http\Controllers\School\SchoolReportsController::class, 'exportComparativeSubjectPerformancePdf'])->name('reports.comparative-subject-performance.pdf');
    Route::get('reports/comparative-subject-performance/excel', [App\Http\Controllers\School\SchoolReportsController::class, 'exportComparativeSubjectPerformanceExcel'])->name('reports.comparative-subject-performance.excel');
    Route::get('reports/student-subject-performance', [App\Http\Controllers\School\SchoolReportsController::class, 'studentSubjectPerformance'])->name('reports.student-subject-performance');
    Route::get('reports/student-subject-performance/pdf', [App\Http\Controllers\School\SchoolReportsController::class, 'exportStudentSubjectPerformancePdf'])->name('reports.student-subject-performance.pdf');
    Route::get('reports/student-subject-performance/excel', [App\Http\Controllers\School\SchoolReportsController::class, 'exportStudentSubjectPerformanceExcel'])->name('reports.student-subject-performance.excel');
    Route::get('reports/overall-analysis', [App\Http\Controllers\School\SchoolReportsController::class, 'overallAnalysis'])->name('reports.overall-analysis');
    Route::get('reports/performance-by-class', [App\Http\Controllers\School\SchoolReportsController::class, 'performanceByClass'])->name('reports.performance-by-class');
    Route::match(['get', 'post'], 'reports/detailed-fee-collection', [App\Http\Controllers\School\SchoolReportsController::class, 'detailedFeeCollection'])->name('reports.detailed-fee-collection');
    Route::match(['get', 'post'], 'reports/other-income-collection', [App\Http\Controllers\School\SchoolReportsController::class, 'otherIncomeCollection'])->name('reports.other-income-collection');
    Route::match(['get', 'post'], 'reports/fee-aging', [App\Http\Controllers\School\SchoolReportsController::class, 'feeAgingReport'])->name('reports.fee-aging');
    Route::match(['get', 'post'], 'reports/fee-aging/export', [App\Http\Controllers\School\SchoolReportsController::class, 'exportFeeAgingReport'])->name('reports.fee-aging.export');
    Route::match(['get', 'post'], 'reports/class-wise-revenue-collection', [App\Http\Controllers\School\SchoolReportsController::class, 'classWiseRevenueCollection'])->name('reports.class-wise-revenue-collection');
    Route::post('reports/class-wise-revenue-collection/export', [App\Http\Controllers\School\SchoolReportsController::class, 'classWiseRevenueCollection'])->name('reports.class-wise-revenue-collection.export');
    Route::match(['get', 'post'], 'reports/fee-waivers-discounts', [App\Http\Controllers\School\SchoolReportsController::class, 'feeWaiversDiscountsReport'])->name('reports.fee-waivers-discounts');
    Route::post('reports/fee-waivers-discounts/export', [App\Http\Controllers\School\SchoolReportsController::class, 'feeWaiversDiscountsReport'])->name('reports.fee-waivers-discounts.export');
    Route::get('reports/attendance-report', [App\Http\Controllers\School\AttendanceReportsController::class, 'index'])->name('reports.attendance-report');
    Route::get('reports/attendance/summary-data', [App\Http\Controllers\School\AttendanceReportsController::class, 'getSummaryData'])->name('reports.attendance.summary-data');
    Route::get('reports/attendance/trends', [App\Http\Controllers\School\AttendanceReportsController::class, 'getTrendsData'])->name('reports.attendance.trends');
    Route::get('reports/attendance/class-wise', [App\Http\Controllers\School\AttendanceReportsController::class, 'getClassWiseStats'])->name('reports.attendance.class-wise');
    Route::get('reports/attendance/session/{sessionId}/students', [App\Http\Controllers\School\AttendanceReportsController::class, 'getSessionStudents'])->name('reports.attendance.session.students');
    Route::get('reports/attendance/student/{studentId}', [App\Http\Controllers\School\AttendanceReportsController::class, 'getStudentReport'])->name('reports.attendance.student');
    Route::get('reports/attendance/export', [App\Http\Controllers\School\AttendanceReportsController::class, 'export'])->name('reports.attendance.export');
    Route::get('reports/attendance/export-pdf', [App\Http\Controllers\School\AttendanceReportsController::class, 'export'])->name('reports.attendance.export-pdf');
    Route::get('reports/attendance/export-excel', [App\Http\Controllers\School\AttendanceReportsController::class, 'export'])->name('reports.attendance.export-excel');
    Route::match(['get', 'post'], 'reports/monthly-attendance-trend', [App\Http\Controllers\School\AttendanceReportsController::class, 'monthlyAttendanceTrend'])->name('reports.monthly-attendance-trend');
    
    // Assignment Reports
    Route::get('reports/assignment-completion-rate', [App\Http\Controllers\School\AssignmentReportsController::class, 'completionRate'])->name('reports.assignment-completion-rate');
    Route::get('reports/late-submissions', [App\Http\Controllers\School\AssignmentReportsController::class, 'lateSubmissions'])->name('reports.late-submissions');
    Route::get('reports/average-marks-assignment', [App\Http\Controllers\School\AssignmentReportsController::class, 'averageMarks'])->name('reports.average-marks-assignment');
    Route::get('reports/weak-topic-analysis', [App\Http\Controllers\School\AssignmentReportsController::class, 'weakTopicAnalysis'])->name('reports.weak-topic-analysis');
    Route::get('reports/subject-homework-performance', [App\Http\Controllers\School\AssignmentReportsController::class, 'subjectHomeworkPerformance'])->name('reports.subject-homework-performance');
    Route::get('reports/improvement-tracking', [App\Http\Controllers\School\AssignmentReportsController::class, 'improvementTracking'])->name('reports.improvement-tracking');
    Route::get('reports/teacher-assignment-frequency', [App\Http\Controllers\School\AssignmentReportsController::class, 'teacherAssignmentFrequency'])->name('reports.teacher-assignment-frequency');
    Route::get('reports/class-workload-balance', [App\Http\Controllers\School\AssignmentReportsController::class, 'classWorkloadBalance'])->name('reports.class-workload-balance');
    Route::get('reports/school-compliance', [App\Http\Controllers\School\AssignmentReportsController::class, 'schoolCompliance'])->name('reports.school-compliance');

    // Resource route comes AFTER specific routes
    Route::resource('reports', App\Http\Controllers\School\SchoolReportsController::class)->names('reports');
    // Attendance Management
    Route::get('attendance-management', [App\Http\Controllers\School\AttendanceManagementController::class, 'index'])->name('attendance-management.index');
    Route::resource('attendance', App\Http\Controllers\School\AttendanceController::class)->names('attendance')->parameters(['attendance' => 'attendanceSession']);
    Route::get('attendance-data', [App\Http\Controllers\School\AttendanceController::class, 'data'])->name('attendance.data');
    Route::post('attendance/{attendanceSession}/mark-attendance', [App\Http\Controllers\School\AttendanceController::class, 'markAttendance'])->name('attendance.mark-attendance');
    Route::get('api/attendance/streams-by-class', [App\Http\Controllers\School\AttendanceController::class, 'getStreamsByClass'])->name('api.attendance.streams-by-class');
});

////////////////////////////////////////COLLEGE MANAGEMENT - UNAUTHENTICATED IMPORT ROUTES /////////////////////////////////////////////
// College import routes (unauthenticated for testing)
Route::prefix('college')->name('college.')->group(function () {
    Route::get('students/import', [App\Http\Controllers\College\StudentController::class, 'import'])->name('students.import');
    Route::post('students/process-import', [App\Http\Controllers\College\StudentController::class, 'processImport'])->name('students.process-import');
    Route::get('students/download-template', [App\Http\Controllers\College\StudentController::class, 'downloadTemplate'])->name('students.download-template');

    // Fee Groups Data (unauthenticated for testing)
    Route::get('fee-groups-data', [App\Http\Controllers\College\FeeGroupController::class, 'data'])->name('fee-groups.data');
});
////////////////////////////////////////COLLEGE MANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::prefix('college')->name('college.')->group(function () {
        Route::get('/', [App\Http\Controllers\College\CollegeController::class, 'index'])->name('index');
        Route::resource('academic-years', App\Http\Controllers\College\AcademicYearController::class)->names('academic-years');
        Route::post('academic-years/{academic_year}/set-current', [App\Http\Controllers\College\AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
        Route::post('academic-years/{academic_year}/mark-completed', [App\Http\Controllers\College\AcademicYearController::class, 'markCompleted'])->name('academic-years.mark-completed');
        Route::get('academic-years-data', [App\Http\Controllers\College\AcademicYearController::class, 'data'])->name('academic-years.data');
        Route::get('academic-years/generate-next', [App\Http\Controllers\College\AcademicYearController::class, 'generateNext'])->name('academic-years.generate-next');

        // Semesters Management
        Route::resource('semesters', App\Http\Controllers\College\SemesterController::class)->names('semesters');
        Route::get('semesters-data', [App\Http\Controllers\College\SemesterController::class, 'data'])->name('semesters.data');
        Route::post('semesters/{semester}/toggle-status', [App\Http\Controllers\College\SemesterController::class, 'toggleStatus'])->name('semesters.toggle-status');

        // Academic Levels Management
        Route::resource('levels', App\Http\Controllers\College\LevelController::class)->names('levels');
        Route::post('levels/{level}/toggle-status', [App\Http\Controllers\College\LevelController::class, 'toggleStatus'])->name('levels.toggle-status');
        Route::get('levels-data', [App\Http\Controllers\College\LevelController::class, 'data'])->name('levels.data');

        // Document Categories
        Route::resource('document-categories', App\Http\Controllers\College\DocumentCategoryController::class)->names('document-categories');
        Route::get('document-categories-data', [App\Http\Controllers\College\DocumentCategoryController::class, 'data'])->name('document-categories.data');

        // Fee Management
        Route::resource('fee-management', App\Http\Controllers\College\FeeManagementController::class)->names('fee-management');

        // Fee Groups
        Route::resource('fee-groups', App\Http\Controllers\College\FeeGroupController::class)->names('fee-groups');
        Route::get('fee-groups-data', [App\Http\Controllers\College\FeeGroupController::class, 'data'])->name('fee-groups.data');

        // Fee Settings
        Route::resource('fee-settings', App\Http\Controllers\College\FeeSettingController::class)->names('fee-settings');
        Route::get('fee-settings-data', [App\Http\Controllers\College\FeeSettingController::class, 'data'])->name('fee-settings.data');

        // Fee Invoices
        Route::get('fee-invoices/statistics', [App\Http\Controllers\College\FeeInvoiceController::class, 'statistics'])->name('fee-invoices.statistics');
        Route::resource('fee-invoices', App\Http\Controllers\College\FeeInvoiceController::class)->names('fee-invoices');
        Route::get('fee-invoices-data', [App\Http\Controllers\College\FeeInvoiceController::class, 'data'])->name('fee-invoices.data');
        Route::get('fee-invoices/get-students', [App\Http\Controllers\College\FeeInvoiceController::class, 'getStudents'])->name('fee-invoices.get-students');
        Route::post('fee-invoices/validate-invoices', [App\Http\Controllers\College\FeeInvoiceController::class, 'validateInvoices'])->name('fee-invoices.validate-invoices');
        Route::post('fee-invoices/preview', [App\Http\Controllers\College\FeeInvoiceController::class, 'preview'])->name('fee-invoices.preview');
        Route::post('fee-invoices/{fee_invoice}/send', [App\Http\Controllers\College\FeeInvoiceController::class, 'send'])->name('fee-invoices.send');
        Route::get('fee-invoices/{fee_invoice}/send', function () {
            return redirect()->route('college.fee-invoices.index')->with('error', 'Invalid request method. Please use the Send button from the fee invoices list.');
        });
        Route::post('fee-invoices/bulk-send', [App\Http\Controllers\College\FeeInvoiceController::class, 'bulkSend'])->name('fee-invoices.bulk-send');
        Route::post('fee-invoices/bulk-send-preview', [App\Http\Controllers\College\FeeInvoiceController::class, 'bulkSendPreview'])->name('fee-invoices.bulk-send-preview');
        Route::get('fee-invoices/bulk-send-form', [App\Http\Controllers\College\FeeInvoiceController::class, 'bulkSendForm'])->name('fee-invoices.bulk-send-form');
        Route::match(['GET', 'POST'], 'fee-invoices/{fee_invoice}/pay', [App\Http\Controllers\College\FeeInvoiceController::class, 'pay'])->name('fee-invoices.pay');

        // Exams Management
        Route::resource('exams-management', App\Http\Controllers\College\ExamsManagementController::class)->names('exams-management');

        // Students Management
        Route::get('students-data', [App\Http\Controllers\College\StudentController::class, 'data'])->name('students.data');
        // Student Course and Program Management Routes
        Route::post('students/{encodedId}/add-courses', [App\Http\Controllers\College\StudentController::class, 'addCourses'])->name('students.add-courses');
        Route::post('students/{encodedId}/change-program', [App\Http\Controllers\College\StudentController::class, 'changeProgram'])->name('students.change-program');
        // Course Enrollment Management Routes
        Route::put('course-enrollments/{enrollment}', [App\Http\Controllers\College\StudentController::class, 'updateEnrollment'])->name('course-enrollments.update');
        Route::delete('course-enrollments/{enrollment}', [App\Http\Controllers\College\StudentController::class, 'deleteEnrollment'])->name('course-enrollments.destroy');
        // Student Parent Assignment Routes
        Route::get('students/{encodedId}/assign-parents', [App\Http\Controllers\College\StudentController::class, 'assignParents'])->name('students.assign-parents');
        Route::post('students/{encodedId}/assign-parents', [App\Http\Controllers\College\StudentController::class, 'storeAssignedParents'])->name('students.store-assigned-parents');
        Route::post('students/{encodedId}/assign-existing-parents', [App\Http\Controllers\College\StudentController::class, 'assignExistingParents'])->name('students.assign-existing-parents');
        Route::delete('students/{encodedId}/remove-parent/{parentId}', [App\Http\Controllers\College\StudentController::class, 'removeParent'])->name('students.remove-parent');
        Route::get('students/search-parents/{encodedId}', [App\Http\Controllers\College\StudentController::class, 'searchParents'])->name('students.search-parents');
        Route::resource('students', App\Http\Controllers\College\StudentController::class)->names('students')->parameters(['students' => 'encodedId']);
        // API route for searching parents
        Route::get('api/students/{encodedId}/parents/search', [App\Http\Controllers\College\StudentController::class, 'searchParents'])->name('api.students.parents.search');

        // Departments Management
        Route::resource('departments', App\Http\Controllers\College\DepartmentController::class)->names('departments');
        Route::get('departments-data', [App\Http\Controllers\College\DepartmentController::class, 'data'])->name('departments.data');

        // Programs Management
        Route::resource('programs', App\Http\Controllers\College\ProgramController::class)->names('programs');
        Route::get('programs-data', [App\Http\Controllers\College\ProgramController::class, 'data'])->name('programs.data');
        Route::post('programs/{program}/assign-instructor', [App\Http\Controllers\College\ProgramController::class, 'assignInstructor'])->name('programs.assign-instructor');
        Route::delete('programs/{program}/remove-instructor/{programDetail}', [App\Http\Controllers\College\ProgramController::class, 'removeInstructor'])->name('programs.remove-instructor');

        // Courses Management
        Route::resource('courses', App\Http\Controllers\College\CourseController::class)->names('courses');
        Route::get('courses-data', [App\Http\Controllers\College\CourseController::class, 'getCoursesData'])->name('courses.data');
        Route::get('courses/by-program/{program}', [App\Http\Controllers\College\CourseController::class, 'byProgram'])->name('courses.by-program');
        Route::post('courses/bulk-delete', [App\Http\Controllers\College\CourseController::class, 'bulkDelete'])->name('courses.bulk-delete');
        Route::post('courses/{course}/assign-instructor', [App\Http\Controllers\College\CourseController::class, 'assignInstructor'])->name('courses.assign-instructor');
        Route::delete('courses/{course}/remove-instructor/{courseDetail}', [App\Http\Controllers\College\CourseController::class, 'removeInstructor'])->name('courses.remove-instructor');

        // Exams & Academics Management
        Route::get('exams-management', [App\Http\Controllers\College\ExamsManagementController::class, 'dashboard'])->name('exams-management.dashboard');

        // Exam Schedules
        Route::resource('exam-schedules', App\Http\Controllers\College\ExamScheduleController::class)->names('exam-schedules');
        Route::post('exam-schedules/{examSchedule}/publish', [App\Http\Controllers\College\ExamScheduleController::class, 'publish'])->name('exam-schedules.publish');
        Route::post('exam-schedules/{examSchedule}/unpublish', [App\Http\Controllers\College\ExamScheduleController::class, 'unpublish'])->name('exam-schedules.unpublish');
        Route::post('exam-schedules/{examSchedule}/mark-ongoing', [App\Http\Controllers\College\ExamScheduleController::class, 'markOngoing'])->name('exam-schedules.mark-ongoing');
        Route::post('exam-schedules/{examSchedule}/mark-completed', [App\Http\Controllers\College\ExamScheduleController::class, 'markCompleted'])->name('exam-schedules.mark-completed');
        Route::post('exam-schedules/{examSchedule}/postpone', [App\Http\Controllers\College\ExamScheduleController::class, 'postpone'])->name('exam-schedules.postpone');
        Route::post('exam-schedules/{examSchedule}/cancel', [App\Http\Controllers\College\ExamScheduleController::class, 'cancel'])->name('exam-schedules.cancel');
        Route::post('exam-schedules/{examSchedule}/reschedule', [App\Http\Controllers\College\ExamScheduleController::class, 'reschedule'])->name('exam-schedules.reschedule');
        Route::get('exam-schedules-calendar', [App\Http\Controllers\College\ExamScheduleController::class, 'calendar'])->name('exam-schedules.calendar');
        Route::get('exam-schedules/{examSchedule}/print', [App\Http\Controllers\College\ExamScheduleController::class, 'print'])->name('exam-schedules.print');
        Route::get('exam-schedules-bulk-print', [App\Http\Controllers\College\ExamScheduleController::class, 'bulkPrint'])->name('exam-schedules.bulk-print');
        Route::get('exam-schedules-master-timetable', [App\Http\Controllers\College\ExamScheduleController::class, 'masterTimetable'])->name('exam-schedules.master-timetable');
        Route::get('exam-schedules-master-timetable-pdf', [App\Http\Controllers\College\ExamScheduleController::class, 'masterTimetablePdf'])->name('exam-schedules.master-timetable-pdf');
        Route::get('get-courses-by-program', [App\Http\Controllers\College\ExamScheduleController::class, 'getCoursesByProgram'])->name('exam-schedules.courses-by-program');

        // ============================================
        // ASSESSMENT & RESULTS MANAGEMENT
        // ============================================

        // Assessment Scores (Continuous Assessment - CA)
        Route::resource('assessment-scores', App\Http\Controllers\College\AssessmentScoreController::class)->names('assessment-scores');
        Route::get('assessment-scores-bulk-entry', [App\Http\Controllers\College\AssessmentScoreController::class, 'bulkEntry'])->name('assessment-scores.bulk-entry');
        Route::post('assessment-scores-bulk-store', [App\Http\Controllers\College\AssessmentScoreController::class, 'storeBulk'])->name('assessment-scores.store-bulk');
        Route::post('assessment-scores/{assessmentScore}/publish', [App\Http\Controllers\College\AssessmentScoreController::class, 'publish'])->name('assessment-scores.publish');
        Route::post('assessment-scores/bulk-publish', [App\Http\Controllers\College\AssessmentScoreController::class, 'bulkPublish'])->name('assessment-scores.bulk-publish');
        Route::get('assessment-scores-students', [App\Http\Controllers\College\AssessmentScoreController::class, 'getStudentsForCourse'])->name('assessment-scores.students');

        // Final Exam Scores
        Route::resource('final-exam-scores', App\Http\Controllers\College\FinalExamScoreController::class)->names('final-exam-scores');
        Route::get('final-exam-scores-bulk-entry', [App\Http\Controllers\College\FinalExamScoreController::class, 'bulkEntry'])->name('final-exam-scores.bulk-entry');
        Route::post('final-exam-scores-bulk-store', [App\Http\Controllers\College\FinalExamScoreController::class, 'storeBulk'])->name('final-exam-scores.store-bulk');
        Route::post('final-exam-scores/{finalExamScore}/publish', [App\Http\Controllers\College\FinalExamScoreController::class, 'publish'])->name('final-exam-scores.publish');
        Route::post('final-exam-scores/bulk-publish', [App\Http\Controllers\College\FinalExamScoreController::class, 'bulkPublish'])->name('final-exam-scores.bulk-publish');

        // Course Results (Final Combined Results)
        Route::resource('course-results', App\Http\Controllers\College\CourseResultController::class)->names('course-results')->except(['create', 'store', 'edit', 'update', 'destroy']);
        Route::post('course-results', [App\Http\Controllers\College\CourseResultController::class, 'store'])->name('course-results.store');
        Route::get('course-results-generate', [App\Http\Controllers\College\CourseResultController::class, 'generate'])->name('course-results.generate');
        Route::post('course-results-process', [App\Http\Controllers\College\CourseResultController::class, 'processGeneration'])->name('course-results.process');
        Route::post('course-results/generate-all', [App\Http\Controllers\College\CourseResultController::class, 'generateAll'])->name('course-results.generate-all');
        Route::post('course-results/{courseResult}/publish', [App\Http\Controllers\College\CourseResultController::class, 'publish'])->name('course-results.publish');
        Route::post('course-results/{courseResult}/approve', [App\Http\Controllers\College\CourseResultController::class, 'approve'])->name('course-results.approve');
        Route::post('course-results/bulk-publish', [App\Http\Controllers\College\CourseResultController::class, 'bulkPublish'])->name('course-results.bulk-publish');
        Route::post('course-results/bulk-approve', [App\Http\Controllers\College\CourseResultController::class, 'bulkApprove'])->name('course-results.bulk-approve');

        // Student Portal Routes
        Route::get('student-portal/ca-results', [App\Http\Controllers\College\AssessmentScoreController::class, 'myResults'])->name('student-portal.ca-results');
        Route::get('student-portal/exam-results', [App\Http\Controllers\College\FinalExamScoreController::class, 'myResults'])->name('student-portal.exam-results');
        Route::get('student-portal/final-results', [App\Http\Controllers\College\CourseResultController::class, 'myResults'])->name('student-portal.final-results');
        Route::get('student-portal/transcript/{studentId?}', [App\Http\Controllers\College\CourseResultController::class, 'transcript'])->name('student-portal.transcript');

        // API Routes for AJAX calls
        Route::prefix('api')->group(function () {
            Route::get('courses-by-program', [App\Http\Controllers\College\AssessmentScoreController::class, 'getCoursesByProgram'])->name('api.courses-by-program');
            Route::get('course-assessments', [App\Http\Controllers\College\AssessmentScoreController::class, 'getCourseAssessments'])->name('api.course-assessments');
            Route::get('registered-students', [App\Http\Controllers\College\AssessmentScoreController::class, 'getRegisteredStudents'])->name('api.registered-students');
            Route::get('assessment-students', [App\Http\Controllers\College\AssessmentScoreController::class, 'getStudentsForAssessment'])->name('api.assessment-students');
            Route::get('final-exams', [App\Http\Controllers\College\FinalExamScoreController::class, 'getFinalExams'])->name('api.final-exams');
            Route::get('exam-students', [App\Http\Controllers\College\FinalExamScoreController::class, 'getStudentsForExam'])->name('api.exam-students');
            Route::get('calculate-results-preview', [App\Http\Controllers\College\CourseResultController::class, 'calculatePreview'])->name('api.calculate-results-preview');
        });

        // Exam Results (Legacy - kept for compatibility)
        Route::get('exam-results', [App\Http\Controllers\College\ExamResultController::class, 'index'])->name('exam-results.index');
        Route::get('exam-results/{id}', [App\Http\Controllers\College\ExamResultController::class, 'show'])->name('exam-results.show');

        // ============================================
        // TIMETABLE MANAGEMENT
        // ============================================

        // Venues Management
        Route::resource('venues', App\Http\Controllers\College\VenueController::class)->names('venues');
        Route::get('venues-data', [App\Http\Controllers\College\VenueController::class, 'getData'])->name('venues.data');
        Route::get('venues-active', [App\Http\Controllers\College\VenueController::class, 'getActiveVenues'])->name('venues.active');

        // Timetables Management
        Route::resource('timetables', App\Http\Controllers\College\TimetableController::class)->names('timetables');
        Route::get('timetables-data', [App\Http\Controllers\College\TimetableController::class, 'getData'])->name('timetables.data');
        Route::post('timetables/store-entry', [App\Http\Controllers\College\TimetableController::class, 'storeEntry'])->name('timetables.store-entry');
        Route::get('timetables/{timetable}/print', [App\Http\Controllers\College\TimetableController::class, 'print'])->name('timetables.print');
        Route::get('timetables/{timetable}/export-pdf', [App\Http\Controllers\College\TimetableController::class, 'exportPdf'])->name('timetables.export-pdf');
        Route::post('timetables/{timetable}/publish', [App\Http\Controllers\College\TimetableController::class, 'publish'])->name('timetables.publish');
        Route::post('timetables/{timetable}/archive', [App\Http\Controllers\College\TimetableController::class, 'archive'])->name('timetables.archive');
        Route::post('timetables/{timetable}/duplicate', [App\Http\Controllers\College\TimetableController::class, 'duplicate'])->name('timetables.duplicate');
        Route::get('timetables/get-courses/{programId}', [App\Http\Controllers\College\TimetableController::class, 'getCourses'])->name('timetables.get-courses');
        Route::post('timetables/check-venue', [App\Http\Controllers\College\TimetableController::class, 'checkVenueAvailability'])->name('timetables.check-venue');
        Route::post('timetables/check-instructor', [App\Http\Controllers\College\TimetableController::class, 'checkInstructorAvailability'])->name('timetables.check-instructor');

        // Timetable Slots (AJAX endpoints)
        Route::get('timetables/{timetable}/slots/{slot}', [App\Http\Controllers\College\TimetableController::class, 'getSlot'])->name('timetables.slots.show');
        Route::post('timetables/{timetable}/slots', [App\Http\Controllers\College\TimetableController::class, 'addSlot'])->name('timetables.slots.store');
        Route::put('timetables/{timetable}/slots/{slot}', [App\Http\Controllers\College\TimetableController::class, 'updateSlot'])->name('timetables.slots.update');
        Route::delete('timetables/{timetable}/slots/{slot}', [App\Http\Controllers\College\TimetableController::class, 'deleteSlot'])->name('timetables.slots.destroy');

        // Teacher Timetables
        Route::get('teacher-timetables', [App\Http\Controllers\College\TeacherTimetableController::class, 'index'])->name('teacher-timetables.index');
        Route::get('teacher-timetables/{employee}', [App\Http\Controllers\College\TeacherTimetableController::class, 'show'])->name('teacher-timetables.show');
        Route::get('teacher-timetables/{employee}/export-pdf', [App\Http\Controllers\College\TeacherTimetableController::class, 'exportPdf'])->name('teacher-timetables.export-pdf');
        Route::get('teacher-timetables/{employee}/print', [App\Http\Controllers\College\TeacherTimetableController::class, 'print'])->name('teacher-timetables.print');

        // Exam Types - Temporarily commented out due to missing controller
        // Route::resource('exam-types', App\Http\Controllers\College\ExamTypeController::class)->names('exam-types');
        // Route::get('exam-types-data', [App\Http\Controllers\College\ExamTypeController::class, 'data'])->name('exam-types.data');

        // Exams - Temporarily commented out due to missing controller
        // Route::resource('exams', App\Http\Controllers\College\ExamController::class)->names('exams');
        // Route::get('exams-data', [App\Http\Controllers\College\ExamController::class, 'data'])->name('exams.data');

        // Exam Results - Temporarily commented out due to missing controller
        // Route::resource('exam-results', App\Http\Controllers\College\ExamResultController::class)->names('exam-results');
        // Route::get('exam-results-data', [App\Http\Controllers\College\ExamResultController::class, 'data'])->name('exam-results.data');
        // Route::post('exam-results/publish', [App\Http\Controllers\College\ExamResultController::class, 'publish'])->name('exam-results.publish');

        // Academic Reports - Temporarily commented out due to missing controller
        // Route::get('academic-reports', [App\Http\Controllers\College\AcademicReportController::class, 'index'])->name('academic-reports.index');
        // Route::get('academic-reports/student/{student}', [App\Http\Controllers\College\AcademicReportController::class, 'studentReport'])->name('academic-reports.student');
        // Route::get('academic-reports/program/{program}', [App\Http\Controllers\College\AcademicReportController::class, 'programReport'])->name('academic-reports.program');
    });
});

////////////////////////////////////////ROLES & PERMISSIONSMANAGEMENT /////////////////////////////////////////////
Route::middleware(['auth', 'require.branch'])->group(function () {
    // Explicit route model binding for Role (supports Hashids, numeric ID, or name)
    Route::bind('role', function ($value) {
        $query = \App\Models\Role::query();

        $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($value)[0] ?? null;
        if ($decodedId) {
            return $query->where('id', $decodedId)->firstOrFail();
        }

        if (is_numeric($value)) {
            return $query->where('id', $value)->firstOrFail();
        }

        return $query->where('name', $value)->firstOrFail();
    });
    Route::model('paymentVoucher', \App\Models\Payment::class);
    Route::model('payment_voucher', \App\Models\Payment::class);

    // Roles management
    Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RolePermissionController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}', [RolePermissionController::class, 'show'])->name('roles.show');
    Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::match(['PUT', 'PATCH'], 'roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');

    // Menu management for roles
    Route::get('roles/{role}/menus', [RolePermissionController::class, 'manageMenus'])->name('roles.menus');
    Route::post('roles/{role}/menus/assign', [RolePermissionController::class, 'assignMenus'])->name('roles.menus.assign');
    Route::delete('roles/{role}/menus/remove', [RolePermissionController::class, 'removeMenu'])->name('roles.menus.remove');
    Route::delete('roles/{role}/menus/remove-all-submenus', [RolePermissionController::class, 'removeAllSubmenus'])->name('roles.menus.remove-all-submenus');
    Route::delete('roles/{role}/menus/remove-all', [RolePermissionController::class, 'removeAllMenus'])->name('roles.menus.remove-all');

    // Permissions management
    Route::get('permissions', [RolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::post('permissions', [RolePermissionController::class, 'createPermission'])->name('permissions.store');
    Route::delete('permissions/{permission}', [RolePermissionController::class, 'deletePermission'])->name('permissions.destroy');



    // User role assignment
    Route::post('users/{user}/assign-roles', [RolePermissionController::class, 'assignToUser'])->name('users.assign-roles');
    Route::delete('users/{user}/remove-role', [RolePermissionController::class, 'removeFromUser'])->name('users.remove-role');
    Route::post('users/{user}/assign-branches', [\App\Http\Controllers\UserController::class, 'assignBranches'])->name('users.assign-branches');
    Route::post('users/{user}/assign-locations', [\App\Http\Controllers\UserController::class, 'assignLocations'])->name('users.assign-locations');
    Route::delete('users/{user}/locations/{location}', [\App\Http\Controllers\UserController::class, 'removeLocation'])->name('users.locations.remove');
    Route::patch('users/{user}/locations/default', [\App\Http\Controllers\UserController::class, 'setDefaultLocation'])->name('users.locations.default');

    // Role statistics
    Route::get('roles-stats', [RolePermissionController::class, 'getStats'])->name('roles.stats');
});
////////////////////////////////////////////// END ROLES & PERMISSIONS MANAGEMENT //////////////////////////////////////////

////////////////////////////////////////////// USER MANAGEMENT /////////////////////////////////////////////////////

// Additional user routes (must come BEFORE resource route)
Route::get('/users/profile', [UserController::class, 'profile'])->name('users.profile')->middleware(['auth', 'require.branch']);
Route::put('/users/profile', [UserController::class, 'updateProfile'])->name('users.profile.update')->middleware(['auth', 'require.branch']);
Route::get('/users/employees', [UserController::class, 'employees'])->name('users.employees')->middleware(['auth']);
Route::get('/users/data', [UserController::class, 'data'])->name('users.data')->middleware(['auth', 'company.scope', 'require.branch']);

Route::resource('users', UserController::class)->middleware(['auth', 'company.scope', 'require.branch']);

// Additional user routes that require user parameter
Route::patch('/users/{user}/status', [UserController::class, 'changeStatus'])->name('users.status')->middleware(['auth', 'company.scope', 'require.branch']);
Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.roles')->middleware(['auth', 'company.scope', 'require.branch']);

////////////////////////////////////////////// HR & PAYROLL ROUTES ////////////////////////////////////////////////

Route::get('/hr-payroll', [App\Http\Controllers\HrPayrollController::class, 'index'])->name('hr-payroll.index')->middleware(['auth', 'company.scope', 'require.branch']);
Route::get('/hr-payroll/payroll-settings', [App\Http\Controllers\PayrollSettingsController::class, 'index'])->name('hr.payroll-settings.index')->middleware(['auth', 'company.scope', 'require.branch']);

////////////////////////////////////////////// HOSPITAL MANAGEMENT ROUTES ////////////////////////////////////////////////

Route::get('/hospital', [App\Http\Controllers\Hospital\HospitalController::class, 'index'])->name('hospital.index')->middleware(['auth', 'company.scope', 'require.branch']);

// Reception Routes
Route::prefix('hospital/reception')->name('hospital.reception.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Hospital\ReceptionController::class, 'index'])->name('index');
    
    // Patient routes
    Route::get('/patients/create', [App\Http\Controllers\Hospital\ReceptionController::class, 'createPatient'])->name('patients.create');
    Route::post('/patients', [App\Http\Controllers\Hospital\ReceptionController::class, 'storePatient'])->name('patients.store');
    Route::get('/patients/{id}', [App\Http\Controllers\Hospital\ReceptionController::class, 'showPatient'])->name('patients.show');
    Route::get('/patients/{id}/edit', [App\Http\Controllers\Hospital\ReceptionController::class, 'editPatient'])->name('patients.edit');
    Route::put('/patients/{id}', [App\Http\Controllers\Hospital\ReceptionController::class, 'updatePatient'])->name('patients.update');
    Route::post('/patients/{id}/request-deletion', [App\Http\Controllers\Hospital\ReceptionController::class, 'requestPatientDeletion'])->name('patients.request-deletion');
    Route::get('/patients-search', [App\Http\Controllers\Hospital\ReceptionController::class, 'searchPatients'])->name('patients.search');
    
    // Visit routes
    Route::get('/visits/create/{patientId}', [App\Http\Controllers\Hospital\ReceptionController::class, 'createVisit'])->name('visits.create');
    Route::post('/visits/create/{patientId}', [App\Http\Controllers\Hospital\ReceptionController::class, 'storeVisit'])->name('visits.store');
    Route::get('/visits/{id}', [App\Http\Controllers\Hospital\ReceptionController::class, 'showVisit'])->name('visits.show');
    Route::get('/visits/{id}/location', [App\Http\Controllers\Hospital\ReceptionController::class, 'getPatientLocation'])->name('visits.location');
    Route::get('/visits/{visit}/print-results', [App\Http\Controllers\Hospital\ReceptionController::class, 'printResults'])->name('visits.print-results');
    Route::post('/visits/{visit}/send-to-doctor', [App\Http\Controllers\Hospital\ReceptionController::class, 'sendToDoctor'])->name('visits.send-to-doctor');
    Route::get('/visits/{visit}/create-bill', [App\Http\Controllers\Hospital\ReceptionController::class, 'createBill'])->name('visits.create-bill');
    
    // Reports routes
    Route::get('/reports', [App\Http\Controllers\Hospital\ReceptionReportController::class, 'index'])->name('reports.index');
});

// Hospital Admin Routes
Route::prefix('hospital/admin')->name('hospital.admin.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    // Departments CRUD
    Route::resource('departments', App\Http\Controllers\Hospital\HospitalDepartmentController::class);
});

// Cashier Routes
Route::prefix('hospital/cashier')->name('hospital.cashier.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Hospital\CashierController::class, 'index'])->name('index');
    Route::get('/bills/{id}', [App\Http\Controllers\Hospital\CashierController::class, 'showBill'])->name('bills.show');
    Route::get('/payments/create/{billId}', [App\Http\Controllers\Hospital\CashierController::class, 'createPayment'])->name('payments.create');
    Route::post('/payments/{billId}', [App\Http\Controllers\Hospital\CashierController::class, 'storePayment'])->name('payments.store');
    Route::post('/bills/{billId}/clear', [App\Http\Controllers\Hospital\CashierController::class, 'clearBill'])->name('clear-bill');
    Route::get('/search', [App\Http\Controllers\Hospital\CashierController::class, 'search'])->name('search');
});

// Placeholder routes for other hospital modules (to be implemented)
Route::prefix('hospital')->name('hospital.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    
    // Triage Routes
    Route::prefix('triage')->name('triage.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\TriageController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\TriageController::class, 'create'])->name('create');
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\TriageController::class, 'store'])->name('store');
        Route::get('/visits/{visitId}', [App\Http\Controllers\Hospital\TriageController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\TriageController::class, 'startService'])->name('start-service');
    });
    
    // Doctor Routes
    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\DoctorController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\DoctorController::class, 'create'])->name('create');
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\DoctorController::class, 'store'])->name('store');
        Route::get('/visits/{visitId}', [App\Http\Controllers\Hospital\DoctorController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\DoctorController::class, 'startService'])->name('start-service');
    });
    
    // Lab Routes
    Route::prefix('lab')->name('lab.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\LabController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\LabController::class, 'create'])->name('create');
        Route::get('/visits/{visitId}', [App\Http\Controllers\Hospital\LabController::class, 'create'])->name('visits.show'); // Redirect to create form
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\LabController::class, 'store'])->name('store');
        Route::get('/results/{id}', [App\Http\Controllers\Hospital\LabController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\LabController::class, 'startService'])->name('start-service');
        Route::post('/results/{id}/mark-ready', [App\Http\Controllers\Hospital\LabController::class, 'markReady'])->name('mark-ready');
        Route::get('/results/{id}/print', [App\Http\Controllers\Hospital\LabController::class, 'printResult'])->name('print');
    });
    
    // Ultrasound Routes
    Route::prefix('ultrasound')->name('ultrasound.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\UltrasoundController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\UltrasoundController::class, 'create'])->name('create');
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\UltrasoundController::class, 'store'])->name('store');
        Route::get('/results/{id}', [App\Http\Controllers\Hospital\UltrasoundController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\UltrasoundController::class, 'startService'])->name('start-service');
        Route::post('/results/{id}/mark-ready', [App\Http\Controllers\Hospital\UltrasoundController::class, 'markReady'])->name('mark-ready');
        Route::get('/results/{id}/print', [App\Http\Controllers\Hospital\UltrasoundController::class, 'printResult'])->name('print');
    });
    
    // Pharmacy Routes
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\PharmacyController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\PharmacyController::class, 'create'])->name('create');
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\PharmacyController::class, 'store'])->name('store');
        Route::get('/dispensations/{id}', [App\Http\Controllers\Hospital\PharmacyController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\PharmacyController::class, 'startService'])->name('start-service');
        Route::post('/dispensations/{id}/dispense', [App\Http\Controllers\Hospital\PharmacyController::class, 'dispense'])->name('dispense');
        Route::get('/product-stock', [App\Http\Controllers\Hospital\PharmacyController::class, 'getProductStock'])->name('product-stock');
    });
    
    // Dental Routes
    Route::prefix('dental')->name('dental.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\DentalController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\DentalController::class, 'create'])->name('create');
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\DentalController::class, 'store'])->name('store');
        Route::get('/records/{id}', [App\Http\Controllers\Hospital\DentalController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\DentalController::class, 'startService'])->name('start-service');
        Route::post('/records/{id}/mark-completed', [App\Http\Controllers\Hospital\DentalController::class, 'markCompleted'])->name('mark-completed');
    });
    
    // RCH Routes
    Route::prefix('rch')->name('rch.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hospital\RCHController::class, 'index'])->name('index');
        Route::get('/visits/{visitId}/create', [App\Http\Controllers\Hospital\RCHController::class, 'create'])->name('create');
        Route::get('/visits/{visitId}', [App\Http\Controllers\Hospital\RCHController::class, 'create'])->name('visits.show'); // Redirect to create form
        Route::post('/visits/{visitId}', [App\Http\Controllers\Hospital\RCHController::class, 'store'])->name('store');
        Route::get('/records/{id}', [App\Http\Controllers\Hospital\RCHController::class, 'show'])->name('show');
        Route::post('/visits/{visitId}/start-service', [App\Http\Controllers\Hospital\RCHController::class, 'startService'])->name('start-service');
        Route::post('/records/{id}/mark-completed', [App\Http\Controllers\Hospital\RCHController::class, 'markCompleted'])->name('mark-completed');
    });
    
    // Vaccine
    Route::get('/vaccine', function () {
        return view('hospital.coming-soon', ['module' => 'Vaccine', 'description' => 'Vaccination tracking and immunization records']);
    })->name('vaccine.index');
    
    // Injection
    Route::get('/injection', function () {
        return view('hospital.coming-soon', ['module' => 'Injection', 'description' => 'Injection services and medication administration']);
    })->name('injection.index');
    
    // Family Planning
    Route::get('/family-planning', function () {
        return view('hospital.coming-soon', ['module' => 'Family Planning', 'description' => 'Family planning services and counseling']);
    })->name('family-planning.index');
    
    // Hospital Admin
    Route::get('/admin', function () {
        return redirect()->route('hospital.admin.departments.index');
    })->name('admin.index');
    
    // Hospital Reports
    Route::get('/reports', function () {
        return view('hospital.coming-soon', ['module' => 'Hospital Reports', 'description' => 'Clinical, financial, and operational reports']);
    })->name('reports.index');
});

// Payroll Approval Settings
Route::prefix('hr-payroll')->name('hr-payroll.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/approval-settings', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'index'])->name('approval-settings.index');
    Route::post('/approval-settings', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'store'])->name('approval-settings.store');
    Route::get('/approval-settings/users-by-branch', [App\Http\Controllers\PayrollApprovalSettingsController::class, 'getUsersByBranch'])->name('approval-settings.users-by-branch');

    // Payment Approval Settings
    Route::get('/payment-approval-settings', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'index'])->name('payment-approval-settings.index');
    Route::post('/payment-approval-settings', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'store'])->name('payment-approval-settings.store');
    Route::get('/payment-approval-settings/users-by-branch', [App\Http\Controllers\Hr\PayrollPaymentApprovalSettingsController::class, 'getUsersByBranch'])->name('payment-approval-settings.users-by-branch');

    // Overtime Approval Settings
    Route::get('/overtime-approval-settings', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'index'])->name('overtime-approval-settings.index');
    Route::post('/overtime-approval-settings', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'store'])->name('overtime-approval-settings.store');
    Route::get('/overtime-approval-settings/users-by-branch', [App\Http\Controllers\Hr\OvertimeApprovalSettingsController::class, 'getUsersByBranch'])->name('overtime-approval-settings.users-by-branch');
});

Route::prefix('hr-payroll')->name('hr.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::resource('departments', App\Http\Controllers\Hr\DepartmentController::class);
    Route::resource('positions', App\Http\Controllers\Hr\PositionController::class);

    // Phase 1: Core HR Enhancement routes
    Route::resource('job-grades', App\Http\Controllers\Hr\JobGradeController::class);
    Route::resource('contracts', App\Http\Controllers\Hr\ContractController::class);
    Route::post('contracts/{contract}/attachments', [App\Http\Controllers\Hr\ContractController::class, 'storeAttachment'])->name('contracts.attachments.store');
    Route::delete('contracts/{contract}/attachments/{attachment}', [App\Http\Controllers\Hr\ContractController::class, 'deleteAttachment'])->name('contracts.attachments.destroy');
    Route::get('employee-compliance/check-existing', [App\Http\Controllers\Hr\EmployeeComplianceController::class, 'checkExisting'])->name('employee-compliance.check-existing');
    Route::resource('employee-compliance', App\Http\Controllers\Hr\EmployeeComplianceController::class);

    // Phase 2: Time, Attendance & Leave Enhancement routes
    Route::resource('work-schedules', App\Http\Controllers\Hr\WorkScheduleController::class);
    Route::resource('shifts', App\Http\Controllers\Hr\ShiftController::class);
    Route::resource('employee-schedules', App\Http\Controllers\Hr\EmployeeScheduleController::class);
    Route::resource('attendance', App\Http\Controllers\Hr\AttendanceController::class);
    Route::post('attendance/{attendance}/approve', [App\Http\Controllers\Hr\AttendanceController::class, 'approve'])->name('attendance.approve');
    Route::resource('overtime-rules', App\Http\Controllers\Hr\OvertimeRuleController::class);
    Route::get('overtime-requests/get-overtime-rate', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'getOvertimeRate'])->name('overtime-requests.get-overtime-rate');
    Route::resource('overtime-requests', App\Http\Controllers\Hr\OvertimeRequestController::class);
    Route::post('overtime-requests/{overtimeRequest}/approve', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
    Route::post('overtime-requests/{overtimeRequest}/reject', [App\Http\Controllers\Hr\OvertimeRequestController::class, 'reject'])->name('overtime-requests.reject');
    Route::resource('holiday-calendars', App\Http\Controllers\Hr\HolidayCalendarController::class);
    Route::post('holiday-calendars/{holidayCalendar}/add-holiday', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'addHoliday'])->name('holiday-calendars.add-holiday');
    Route::post('holiday-calendars/{holidayCalendar}/seed-tanzania', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'seedTanzaniaHolidays'])->name('holiday-calendars.seed-tanzania');
    Route::delete('holiday-calendars/holidays/{holidayCalendarDate}', [App\Http\Controllers\Hr\HolidayCalendarController::class, 'removeHoliday'])->name('holiday-calendars.remove-holiday');

    // Phase 3: Payroll Enhancement & Statutory Compliance routes
    Route::resource('payroll-calendars', App\Http\Controllers\Hr\PayrollCalendarController::class);
    Route::post('payroll-calendars/{payrollCalendar}/lock', [App\Http\Controllers\Hr\PayrollCalendarController::class, 'lock'])->name('payroll-calendars.lock');
    Route::post('payroll-calendars/{payrollCalendar}/unlock', [App\Http\Controllers\Hr\PayrollCalendarController::class, 'unlock'])->name('payroll-calendars.unlock');
    Route::resource('pay-groups', App\Http\Controllers\Hr\PayGroupController::class);
    Route::resource('salary-components', App\Http\Controllers\Hr\SalaryComponentController::class);
    // Custom routes must come BEFORE resource route to avoid conflicts
    Route::get('employee-salary-structure/bulk-assign', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'bulkAssignForm'])->name('employee-salary-structure.bulk-assign-form');
    Route::post('employee-salary-structure/bulk-assign', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'bulkAssign'])->name('employee-salary-structure.bulk-assign');
    Route::get('employee-salary-structure/apply-template', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'applyTemplateForm'])->name('employee-salary-structure.apply-template-form');
    Route::post('employee-salary-structure/apply-template', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'applyTemplate'])->name('employee-salary-structure.apply-template');
    Route::delete('employee-salary-structure/{employee}/component/{structure}', [App\Http\Controllers\Hr\EmployeeSalaryStructureController::class, 'destroy'])->name('employee-salary-structure.destroy-component');
    Route::resource('employee-salary-structure', App\Http\Controllers\Hr\EmployeeSalaryStructureController::class)->parameters([
        'employee-salary-structure' => 'employee'
    ]);
    Route::resource('salary-structure-templates', App\Http\Controllers\Hr\SalaryStructureTemplateController::class);
    Route::resource('statutory-rules', App\Http\Controllers\Hr\StatutoryRuleController::class);
    Route::get('statutory-rules/category-options', [App\Http\Controllers\Hr\StatutoryRuleController::class, 'getCategoryOptions'])->name('statutory-rules.category-options');

    // Payroll Reports
    Route::get('payroll-reports', [App\Http\Controllers\Hr\PayrollReportController::class, 'index'])->name('payroll-reports.index');
    Route::get('payroll-reports/payroll-by-department', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByDepartment'])->name('payroll-reports.payroll-by-department');
    Route::get('payroll-reports/payroll-by-pay-group', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollByPayGroup'])->name('payroll-reports.payroll-by-pay-group');
    Route::get('payroll-reports/statutory-compliance', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryCompliance'])->name('payroll-reports.statutory-compliance');
    Route::get('payroll-reports/statutory-compliance-enhanced', [App\Http\Controllers\Hr\PayrollReportController::class, 'statutoryComplianceEnhanced'])->name('payroll-reports.statutory-compliance-enhanced');
    Route::get('payroll-reports/employee-payroll-history', [App\Http\Controllers\Hr\PayrollReportController::class, 'employeePayrollHistory'])->name('payroll-reports.employee-payroll-history');
    Route::get('payroll-reports/payroll-cost-analysis', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollCostAnalysis'])->name('payroll-reports.payroll-cost-analysis');
    Route::get('payroll-reports/payroll-audit-trail', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollAuditTrail'])->name('payroll-reports.payroll-audit-trail');
    Route::get('payroll-reports/year-to-date-summary', [App\Http\Controllers\Hr\PayrollReportController::class, 'yearToDateSummary'])->name('payroll-reports.year-to-date-summary');
    Route::get('payroll-reports/payroll-variance', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollVariance'])->name('payroll-reports.payroll-variance');
    Route::get('payroll-reports/bank-payment', [App\Http\Controllers\Hr\PayrollReportController::class, 'bankPayment'])->name('payroll-reports.bank-payment');
    Route::get('payroll-reports/overtime', [App\Http\Controllers\Hr\PayrollReportController::class, 'overtimeReport'])->name('payroll-reports.overtime');
    Route::get('payroll-reports/payroll-summary', [App\Http\Controllers\Hr\PayrollReportController::class, 'payrollSummary'])->name('payroll-reports.payroll-summary');
    Route::get('payroll-reports/leave', [App\Http\Controllers\Hr\PayrollReportController::class, 'leaveReport'])->name('payroll-reports.leave');
    Route::get('payroll-reports/paye-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'payeRemittance'])->name('payroll-reports.paye-remittance');
    Route::get('payroll-reports/nssf-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'nssfRemittance'])->name('payroll-reports.nssf-remittance');
    Route::get('payroll-reports/nhif-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'nhifRemittance'])->name('payroll-reports.nhif-remittance');
    Route::get('payroll-reports/wcf-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'wcfRemittance'])->name('payroll-reports.wcf-remittance');
    Route::get('payroll-reports/sdl-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'sdlRemittance'])->name('payroll-reports.sdl-remittance');
    Route::get('payroll-reports/heslb-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'heslbRemittance'])->name('payroll-reports.heslb-remittance');
    Route::get('payroll-reports/combined-statutory-remittance', [App\Http\Controllers\Hr\PayrollReportController::class, 'combinedStatutoryRemittance'])->name('payroll-reports.combined-statutory-remittance');

    // Biometric Device Management
    Route::resource('biometric-devices', App\Http\Controllers\Hr\BiometricDeviceController::class);
    Route::post('biometric-devices/{biometricDevice}/sync', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'sync'])->name('biometric-devices.sync');
    Route::post('biometric-devices/{biometricDevice}/regenerate-api-key', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'regenerateApiKey'])->name('biometric-devices.regenerate-api-key');
    Route::post('biometric-devices/{biometricDevice}/map-employee', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'mapEmployee'])->name('biometric-devices.map-employee');
    Route::delete('biometric-devices/{biometricDevice}/unmap-employee/{employee}', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'unmapEmployee'])->name('biometric-devices.unmap-employee');
    Route::post('biometric-devices/{biometricDevice}/process-logs', [App\Http\Controllers\Hr\BiometricDeviceController::class, 'processPendingLogs'])->name('biometric-devices.process-logs');

    // Employee import routes (must be before resource route)
    Route::get('employees/import', [App\Http\Controllers\Hr\EmployeeController::class, 'showImport'])->name('employees.import');
    Route::post('employees/import', [App\Http\Controllers\Hr\EmployeeController::class, 'import'])->name('employees.import.post');
    Route::get('employees/template/download', [App\Http\Controllers\Hr\EmployeeController::class, 'downloadTemplate'])->name('employees.template');
    // Employee validation routes
    Route::post('employees/check-email', [App\Http\Controllers\Hr\EmployeeController::class, 'checkEmailUnique'])->name('employees.check-email');
    Route::post('employees/check-phone', [App\Http\Controllers\Hr\EmployeeController::class, 'checkPhoneUnique'])->name('employees.check-phone');
    Route::resource('employees', App\Http\Controllers\Hr\EmployeeController::class);

    Route::resource('payrolls', App\Http\Controllers\Hr\PayrollController::class)->parameters([
        'payrolls' => 'payroll:hash_id'
    ]);
    Route::post('payrolls/{payroll:hash_id}/process', [App\Http\Controllers\Hr\PayrollController::class, 'process'])->name('payrolls.process');
    Route::post('payrolls/{payroll:hash_id}/approve', [App\Http\Controllers\Hr\PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::get('payrolls/{payroll:hash_id}/audit-logs', [App\Http\Controllers\Hr\PayrollController::class, 'auditLogs'])->name('payrolls.audit-logs');
    Route::get('payrolls/{payroll:hash_id}/reverse', [App\Http\Controllers\Hr\PayrollController::class, 'showReverseForm'])->name('payrolls.reverse');
    Route::post('payrolls/{payroll:hash_id}/reject', [App\Http\Controllers\Hr\PayrollController::class, 'reject'])->name('payrolls.reject');
    Route::post('payrolls/{payroll:hash_id}/request-payment-approval', [App\Http\Controllers\Hr\PayrollController::class, 'requestPaymentApproval'])->name('payrolls.request-payment-approval');
    Route::post('payrolls/{payroll:hash_id}/approve-payment', [App\Http\Controllers\Hr\PayrollController::class, 'approvePayment'])->name('payrolls.approve-payment');
    Route::post('payrolls/{payroll:hash_id}/reject-payment', [App\Http\Controllers\Hr\PayrollController::class, 'rejectPayment'])->name('payrolls.reject-payment');
    Route::post('payrolls/{payroll:hash_id}/lock', [App\Http\Controllers\Hr\PayrollController::class, 'lock'])->name('payrolls.lock');
    Route::post('payrolls/{payroll:hash_id}/unlock', [App\Http\Controllers\Hr\PayrollController::class, 'unlock'])->name('payrolls.unlock');
    Route::post('payrolls/{payroll:hash_id}/reverse', [App\Http\Controllers\Hr\PayrollController::class, 'reverse'])->name('payrolls.reverse');

    Route::get('payrolls/{payroll:hash_id}/payment', [App\Http\Controllers\Hr\PayrollController::class, 'showPaymentForm'])->name('payrolls.payment');
    Route::post('payrolls/{payroll:hash_id}/process-payment', [App\Http\Controllers\Hr\PayrollController::class, 'processPayment'])->name('payrolls.process-payment');
    Route::get('payrolls/{payroll:hash_id}/employees', [App\Http\Controllers\Hr\PayrollController::class, 'getEmployees'])->name('payrolls.employees');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}', [App\Http\Controllers\Hr\PayrollController::class, 'slip'])->name('payrolls.slip');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}/print', [App\Http\Controllers\Hr\PayrollController::class, 'slipPrint'])->name('payrolls.slip.print');
    Route::get('payrolls/{payroll:hash_id}/slip/{employee}/pdf', [App\Http\Controllers\Hr\PayrollController::class, 'slipPdf'])->name('payrolls.slip.pdf');
    Route::get('payrolls/{payroll:hash_id}/export-all-slips', [App\Http\Controllers\Hr\PayrollController::class, 'exportAllSlips'])->name('payrolls.export-all-slips');
    Route::resource('trade-unions', App\Http\Controllers\Hr\TradeUnionController::class);
    Route::get('trade-unions/data', [App\Http\Controllers\Hr\TradeUnionController::class, 'data'])->name('trade-unions.data');
    Route::get('trade-unions/ajax/list', [App\Http\Controllers\Hr\TradeUnionController::class, 'getActiveTradeUnions'])->name('trade-unions.ajax.list');
    Route::resource('file-types', App\Http\Controllers\Hr\FileTypeController::class);
    Route::resource('allowance-types', App\Http\Controllers\Hr\AllowanceTypeController::class);
    Route::resource('allowances', App\Http\Controllers\Hr\AllowanceController::class);
    Route::resource('external-loans', App\Http\Controllers\Hr\ExternalLoanController::class)->parameters([
        'external-loans' => 'encodedId'
    ]);
    Route::resource('salary-advances', App\Http\Controllers\Hr\SalaryAdvanceController::class)->parameters([
        'salary-advances' => 'salaryAdvance'
    ]);
    Route::resource('heslb-loans', App\Http\Controllers\Hr\HeslbLoanController::class);

    // Payroll Chart Account Settings
    Route::get('payroll-settings/chart-accounts', [App\Http\Controllers\Hr\PayrollChartAccountSettingsController::class, 'index'])->name('payroll.chart-accounts.index');
    Route::put('payroll-settings/chart-accounts', [App\Http\Controllers\Hr\PayrollChartAccountSettingsController::class, 'update'])->name('payroll.chart-accounts.update');

    // Employee documents
    Route::post('employees/{employee}/documents', [App\Http\Controllers\Hr\EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
    Route::get('documents/{document}/download', [App\Http\Controllers\Hr\EmployeeController::class, 'downloadDocument'])->name('documents.download');
    Route::delete('documents/{document}', [App\Http\Controllers\Hr\EmployeeController::class, 'deleteDocument'])->name('documents.delete');

    Route::prefix('leave')->name('leave.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Hr\LeaveManagementController::class, 'index'])->name('index');

        // Leave Types
        Route::get('types', [App\Http\Controllers\Hr\LeaveTypeController::class, 'index'])->name('types.index');
        Route::get('types/create', [App\Http\Controllers\Hr\LeaveTypeController::class, 'create'])->name('types.create');
        Route::post('types', [App\Http\Controllers\Hr\LeaveTypeController::class, 'store'])->name('types.store');
        Route::get('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'show'])->name('types.show');
        Route::get('types/{type}/edit', [App\Http\Controllers\Hr\LeaveTypeController::class, 'edit'])->name('types.edit');
        Route::put('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'update'])->name('types.update');
        Route::delete('types/{type}', [App\Http\Controllers\Hr\LeaveTypeController::class, 'destroy'])->name('types.destroy');

        // Leave Requests
        Route::get('requests', [App\Http\Controllers\Hr\LeaveRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [App\Http\Controllers\Hr\LeaveRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [App\Http\Controllers\Hr\LeaveRequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'show'])->name('requests.show');
        Route::get('requests/{request}/edit', [App\Http\Controllers\Hr\LeaveRequestController::class, 'edit'])->name('requests.edit');
        Route::put('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'update'])->name('requests.update');
        Route::delete('requests/{request}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'destroy'])->name('requests.destroy');

        // Leave Request Actions
        Route::post('requests/{request}/submit', [App\Http\Controllers\Hr\LeaveRequestController::class, 'submit'])->name('requests.submit');
        Route::post('requests/{request}/approve', [App\Http\Controllers\Hr\LeaveRequestController::class, 'approve'])->name('requests.approve');
        Route::post('requests/{request}/reject', [App\Http\Controllers\Hr\LeaveRequestController::class, 'reject'])->name('requests.reject');
        Route::post('requests/{request}/return', [App\Http\Controllers\Hr\LeaveRequestController::class, 'returnForEdit'])->name('requests.return');
        Route::post('requests/{request}/cancel', [App\Http\Controllers\Hr\LeaveRequestController::class, 'cancel'])->name('requests.cancel');
        Route::post('requests/{request}/attachments', [App\Http\Controllers\Hr\LeaveRequestController::class, 'addAttachment'])->name('requests.attachments.store');
        Route::delete('requests/{request}/attachments/{attachment}', [App\Http\Controllers\Hr\LeaveRequestController::class, 'deleteAttachment'])->name('requests.attachments.destroy');

        // Leave Balances
        Route::get('balances', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'index'])->name('balances.index');
        Route::get('balances/{employee}', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'show'])->name('balances.show');
        Route::get('balances/{employee}/edit', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'edit'])->name('balances.edit');
        Route::put('balances/{employee}', [App\Http\Controllers\Hr\LeaveBalanceController::class, 'update'])->name('balances.update');
    });
});

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

/////////////////////////////////////////////ASSETS MANAGEMENT ROUTES /////////////////////////////////////////////



////////////////////////////////////////////// SETTINGS ROUTES ////////////////////////////////////////////////

Route::prefix('settings')->name('settings.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {



    Route::get('/', [SettingsController::class, 'index'])->name('index');

    // Company Settings
    Route::get('/company', [SettingsController::class, 'companySettings'])->name('company');
    Route::put('/company', [SettingsController::class, 'updateCompanySettings'])->name('company.update');

    // Branch Settings
    Route::get('/branches', [SettingsController::class, 'branchSettings'])->name('branches');
    Route::get('/branches/data', [SettingsController::class, 'branchesData'])->name('branches.data');
    Route::get('/branches/create', [SettingsController::class, 'createBranch'])->name('branches.create');
    Route::post('/branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
    Route::get('/branches/{branch}/edit', [SettingsController::class, 'editBranch'])->name('branches.edit');
    Route::put('/branches/{branch}', [SettingsController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy');

    // User Settings
    Route::get('/user', [SettingsController::class, 'userSettings'])->name('user');
    Route::put('/user', [SettingsController::class, 'updateUserSettings'])->name('user.update');

    // System Settings
    Route::get('/system', [SettingsController::class, 'systemSettings'])->name('system');
    Route::put('/system', [SettingsController::class, 'updateSystemSettings'])->name('system.update');
    Route::post('/system/reset', [SettingsController::class, 'resetSystemSettings'])->name('system.reset');
    Route::post('/system/test-email', [SettingsController::class, 'testEmailConfig'])->name('system.test-email');

    // SMS Settings
    Route::get('/sms', [SettingsController::class, 'smsSettings'])->name('sms');
    Route::put('/sms', [SettingsController::class, 'updateSmsSettings'])->name('sms.update');
    Route::post('/sms/test', [SettingsController::class, 'testSmsSettings'])->name('sms.test');

    // Backup Settings
    Route::get('/backup', [SettingsController::class, 'backupSettings'])->name('backup');
    Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('backup.restore');
    Route::get('/backup/{hash_id}/download', [SettingsController::class, 'downloadBackup'])->name('backup.download');
    Route::delete('/backup/{hash_id}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
    Route::post('/backup/clean', [SettingsController::class, 'cleanOldBackups'])->name('backup.clean');

    // AI Assistant Settings
    Route::get('/ai', [SettingsController::class, 'aiAssistantSettings'])->name('ai');
    Route::post('/ai/chat', [SettingsController::class, 'aiChat'])->name('ai.chat')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    // Budget Settings
    Route::get('/budget', [SettingsController::class, 'budgetSettings'])->name('budget');
    Route::put('/budget', [SettingsController::class, 'updateBudgetSettings'])->name('budget.update');

    // Petty Cash Settings
    Route::get('/petty-cash', [SettingsController::class, 'pettyCashSettings'])->name('petty-cash');
    Route::put('/petty-cash', [SettingsController::class, 'updatePettyCashSettings'])->name('petty-cash.update');

    // LIPISHA Payment Gateway Settings
    Route::get('/lipisha', [SettingsController::class, 'lipishaSettings'])->name('lipisha');
    Route::put('/lipisha', [SettingsController::class, 'updateLipishaSettings'])->name('lipisha.update');
    Route::post('/lipisha/test-network', [SettingsController::class, 'testLipishaNetwork'])->name('lipisha.test-network');

    // Queue Worker Management
    Route::post('/queue-worker/start', [SettingsController::class, 'startQueueWorker'])->name('queue-worker.start');

    // Approval Levels Management
    Route::get('/approval-levels', [App\Http\Controllers\ApprovalLevelsController::class, 'index'])->name('approval-levels.index');
    Route::post('/approval-levels', [App\Http\Controllers\ApprovalLevelsController::class, 'store'])->name('approval-levels.store');
    Route::put('/approval-levels/{approvalLevel}', [App\Http\Controllers\ApprovalLevelsController::class, 'update'])->name('approval-levels.update');
    Route::delete('/approval-levels/{approvalLevel}', [App\Http\Controllers\ApprovalLevelsController::class, 'destroy'])->name('approval-levels.destroy');
    Route::post('/approval-levels/assignments', [App\Http\Controllers\ApprovalLevelsController::class, 'storeAssignment'])->name('approval-levels.assignments.store');
    Route::delete('/approval-levels/assignments/{assignment}', [App\Http\Controllers\ApprovalLevelsController::class, 'destroyAssignment'])->name('approval-levels.assignments.destroy');
    Route::post('/approval-levels/reorder', [App\Http\Controllers\ApprovalLevelsController::class, 'reorder'])->name('approval-levels.reorder');
    Route::get('/ai/test', function () {
        return response()->json([
            'csrf_token' => csrf_token(),
            'status' => 'success',
            'message' => 'AI Assistant connection test successful'
        ]);
    })->name('ai.test');

    // Activity Logs
    Route::get('/logs', [ActivityLogsController::class, 'index'])->name('logs.index');
    Route::get('/logs/data', [ActivityLogsController::class, 'data'])->name('logs.data');
    Route::get('/logs/{id}', [ActivityLogsController::class, 'show'])->name('logs.show');

    // Fees Settings
    Route::get('/fees', [SettingsController::class, 'feesSettings'])->name('fees');
    Route::put('/fees', [SettingsController::class, 'updateFeesSettings'])->name('fees.update');

    Route::get('/subscription', [SettingsController::class, 'subscriptionSettings'])->name('subscription');
    Route::put('/subscription', [SettingsController::class, 'updateSubscriptionSettings'])->name('subscription.update');


    // Inventory Settings
    Route::get('/inventory', [SettingsController::class, 'inventorySettings'])->name('inventory');
    Route::put('/inventory', [SettingsController::class, 'updateInventorySettings'])->name('inventory.update');

    // Inventory Locations
    Route::get('/inventory-settings/locations', [SettingsController::class, 'inventoryLocations'])->name('inventory.locations.index')->middleware('check.inventory.cost.method');
    Route::get('/inventory-settings/locations/create', [SettingsController::class, 'createInventoryLocation'])->name('inventory.locations.create')->middleware('check.inventory.cost.method');
    Route::post('/inventory-settings/locations', [SettingsController::class, 'storeInventoryLocation'])->name('inventory.locations.store')->middleware('check.inventory.cost.method');
    Route::get('/inventory-settings/locations/{location}', [SettingsController::class, 'showInventoryLocation'])->name('inventory.locations.show')->middleware('check.inventory.cost.method');
    Route::get('/inventory-settings/locations/{location}/edit', [SettingsController::class, 'editInventoryLocation'])->name('inventory.locations.edit')->middleware('check.inventory.cost.method');
    Route::put('/inventory-settings/locations/{location}', [SettingsController::class, 'updateInventoryLocation'])->name('inventory.locations.update')->middleware('check.inventory.cost.method');
    Route::delete('/inventory-settings/locations/{location}', [SettingsController::class, 'destroyInventoryLocation'])->name('inventory.locations.destroy')->middleware('check.inventory.cost.method');
});


////////////////////////////////////////////// END SETTINGS ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// SUBSCRIPTION MANAGEMENT ///////////////////////////////////////////

Route::prefix('subscriptions')->name('subscriptions.')->middleware(['auth', 'role:super-admin'])->group(function () {
    // Subscription Dashboard
    Route::get('/dashboard', [SubscriptionController::class, 'dashboard'])->name('dashboard');

    // Subscription CRUD
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
    Route::get('/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{subscription}', [SubscriptionController::class, 'update'])->name('update');
    Route::delete('/{subscription}', [SubscriptionController::class, 'destroy'])->name('destroy');

    // Subscription Actions
    Route::post('/{subscription}/mark-paid', [SubscriptionController::class, 'markAsPaid'])->name('mark-paid');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('renew');
    Route::post('/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('extend');
});

// Ticker Messages API - Only for subscription expiry alerts
Route::get('/api/ticker-messages', function () {
    // Get subscription alerts - only show ticker if there are expiring subscriptions
    $expiringSubscriptions = \App\Models\Subscription::where('status', 'active')
        ->where('end_date', '<=', now()->addDays(5))
        ->where('end_date', '>=', now())
        ->with('company')
        ->get();

    // If no expiring subscriptions, return empty messages to hide ticker
    if ($expiringSubscriptions->count() == 0) {
        return response()->json([
            'success' => true,
            'messages' => [],
            'show_ticker' => false,
            'timestamp' => now()->toISOString()
        ]);
    }

    $messages = [];
    $now = now();

    // Build subscription expiry messages
    foreach ($expiringSubscriptions as $subscription) {
        $daysLeft = floor($now->diffInDays($subscription->end_date, false));
        $urgency = $daysLeft <= 1 ? 'urgent' : ($daysLeft <= 3 ? 'warning' : 'info');

        $daysText = $daysLeft == 0 ? 'expires today' : ($daysLeft == 1 ? 'expires tomorrow' : "expires in {$daysLeft} days");

        $messages[] = [
            'text' => "⚠️ URGENT: {$subscription->company->name} subscription ({$subscription->plan_name}) {$daysText} - Amount: " . number_format($subscription->amount, 2) . " {$subscription->currency}",
            'type' => $urgency,
            'icon' => 'bx-credit-card',
            'subscription_id' => $subscription->id,
            'company_name' => $subscription->company->name,
            'days_left' => $daysLeft,
            'expiry_date' => $subscription->end_date->format('M d, Y')
        ];
    }

    // Add a general reminder message
    $messages[] = [
        'text' => "🔔 Action Required: Please renew expiring subscriptions to avoid service interruption",
        'type' => 'urgent',
        'icon' => 'bx-bell'
    ];

    return response()->json([
        'success' => true,
        'messages' => $messages,
        'show_ticker' => true,
        'expiring_count' => $expiringSubscriptions->count(),
        'timestamp' => $now->toISOString()
    ]);
})->middleware('auth');
////////////////////////////////////////////// BRANCH MANAGEMENT ///////////////////////////////////////////////////

//Route::resource('branches', BranchController::class)->middleware('auth');

//Route::resource('companies', CompanyController::class)->middleware('auth');

// Route::resource('cash_deposit_accounts', CashDepositAccountController::class)->middleware(['auth', 'require.branch']); // Controller deleted

////////////////////////////////////////////// END /////////////////////////////////////////////////////////////////

////////////////////////////////////////////// SUPER ADMIN ROUTES ////////////////////////////////////////////////

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Companies
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/create', [SuperAdminController::class, 'createCompany'])->name('companies.create');
    Route::post('/companies', [SuperAdminController::class, 'storeCompany'])->name('companies.store');
    Route::get('/companies/{company}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminController::class, 'editCompany'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('companies.update');
    Route::delete('/companies/{company}', [SuperAdminController::class, 'destroyCompany'])->name('companies.destroy');

    // Branches
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');

    // Users
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
});

////////////////////////////////////////////// END SUPER ADMIN ROUTES /////////////////////////////////////////////

////////////////////////////////////////////// ACCOUNTING MANAGEMENT ///////////////////////////////////////////////

Route::prefix('accounting')->name('accounting.')->middleware(['auth', 'require.branch'])->group(function () {
     Route::get('/', [App\Http\Controllers\AccountingController::class, 'index'])->name('index');

    // Main Groups
    Route::get('/main-groups', [App\Http\Controllers\MainGroupController::class, 'index'])->name('main-groups.index');
    Route::get('/main-groups/create', [App\Http\Controllers\MainGroupController::class, 'create'])->name('main-groups.create');
    Route::post('/main-groups', [App\Http\Controllers\MainGroupController::class, 'store'])->name('main-groups.store');
    Route::get('/main-groups/{encodedId}', [App\Http\Controllers\MainGroupController::class, 'show'])->name('main-groups.show');
    Route::get('/main-groups/{encodedId}/edit', [App\Http\Controllers\MainGroupController::class, 'edit'])->name('main-groups.edit');
    Route::put('/main-groups/{encodedId}', [App\Http\Controllers\MainGroupController::class, 'update'])->name('main-groups.update');
    Route::delete('/main-groups/{encodedId}', [App\Http\Controllers\MainGroupController::class, 'destroy'])->name('main-groups.destroy');

    // Account Class Groups
    Route::get('/account-class-groups', [AccountClassGroupController::class, 'index'])->name('account-class-groups.index');
    Route::get('/account-class-groups/create', [AccountClassGroupController::class, 'create'])->name('account-class-groups.create');
    Route::post('/account-class-groups', [AccountClassGroupController::class, 'store'])->name('account-class-groups.store');
    Route::get('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'show'])->name('account-class-groups.show');
    Route::get('/account-class-groups/{encodedId}/edit', [AccountClassGroupController::class, 'edit'])->name('account-class-groups.edit');
    Route::put('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'update'])->name('account-class-groups.update');
    Route::delete('/account-class-groups/{encodedId}', [AccountClassGroupController::class, 'destroy'])->name('account-class-groups.destroy');

    // Chart Accounts
    Route::get('/chart-accounts', [ChartAccountController::class, 'index'])->name('chart-accounts.index');
    Route::get('/chart-accounts/create', [ChartAccountController::class, 'create'])->name('chart-accounts.create');
    Route::post('/chart-accounts', [ChartAccountController::class, 'store'])->name('chart-accounts.store');
    Route::get('/chart-accounts/{encodedId}', [ChartAccountController::class, 'show'])->name('chart-accounts.show');
    Route::get('/chart-accounts/{encodedId}/edit', [ChartAccountController::class, 'edit'])->name('chart-accounts.edit');
    Route::put('/chart-accounts/{encodedId}', [ChartAccountController::class, 'update'])->name('chart-accounts.update');
    Route::delete('/chart-accounts/{encodedId}', [ChartAccountController::class, 'destroy'])->name('chart-accounts.destroy');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{encodedId}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{encodedId}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{encodedId}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/suppliers/{encodedId}/status', [SupplierController::class, 'changeStatus'])->name('suppliers.changeStatus');
    Route::delete('/suppliers/{encodedId}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');


    // Payment Vouchers
    Route::get('payment-vouchers/datatable', [App\Http\Controllers\Accounting\PaymentVoucherController::class, 'getPaymentVouchersData'])->name('payment-vouchers.datatable');
    Route::get('/payment-vouchers/data', [PaymentVoucherController::class, 'data'])->name('payment-vouchers.data');
    Route::resource('payment-vouchers', PaymentVoucherController::class);
    Route::get('payment-vouchers/{paymentVoucher}/approve', [PaymentVoucherController::class, 'showApproval'])->name('payment-vouchers.approve');
    Route::post('payment-vouchers/{paymentVoucher}/approve', [PaymentVoucherController::class, 'approve'])->name('payment-vouchers.approve.submit');
    Route::post('payment-vouchers/{paymentVoucher}/reject', [PaymentVoucherController::class, 'reject'])->name('payment-vouchers.reject');
    Route::get('/payment-vouchers/{paymentVoucher}/download-attachment', [PaymentVoucherController::class, 'downloadAttachment'])->name('payment-vouchers.download-attachment');
    Route::delete('/payment-vouchers/{paymentVoucher}/remove-attachment', [PaymentVoucherController::class, 'removeAttachment'])->name('payment-vouchers.remove-attachment');
    Route::get('/payment-vouchers/{paymentVoucher}/export-pdf', [PaymentVoucherController::class, 'exportPdf'])->name('payment-vouchers.export-pdf');
    Route::get('payment-vouchers/customer/{customerId}/cash-deposits', [PaymentVoucherController::class, 'getCustomerCashDeposits'])->name('payment-vouchers.customer-cash-deposits');

    // Bill and Payment PDF Export Routes
    Route::get('/bill-purchases/{billPurchase}/export-pdf', [BillPurchaseController::class, 'exportPdf'])->name('bill-purchases.export-pdf');
    Route::get('/payments/{payment}/export-pdf', [BillPurchaseController::class, 'exportPaymentPdf'])->name('bill-payments.export-pdf');

    // Receipt Vouchers
    Route::get('/receipt-vouchers', [ReceiptVoucherController::class, 'index'])->name('receipt-vouchers.index');
    Route::get('/receipt-vouchers/data', [ReceiptVoucherController::class, 'data'])->name('receipt-vouchers.data');
    Route::get('/receipt-vouchers/create', [ReceiptVoucherController::class, 'create'])->name('receipt-vouchers.create');
    Route::post('/receipt-vouchers', [ReceiptVoucherController::class, 'store'])->name('receipt-vouchers.store');
    Route::get('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'show'])->name('receipt-vouchers.show');
    Route::get('/receipt-vouchers/{encodedId}/edit', [ReceiptVoucherController::class, 'edit'])->name('receipt-vouchers.edit');
    Route::put('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'update'])->name('receipt-vouchers.update');
    Route::delete('/receipt-vouchers/{encodedId}', [ReceiptVoucherController::class, 'destroy'])->name('receipt-vouchers.destroy');
    Route::get('/receipt-vouchers/{encodedId}/download-attachment', [ReceiptVoucherController::class, 'downloadAttachment'])->name('receipt-vouchers.download-attachment');
    Route::delete('/receipt-vouchers/{encodedId}/remove-attachment', [ReceiptVoucherController::class, 'removeAttachment'])->name('receipt-vouchers.remove-attachment');
    Route::get('/receipt-vouchers/{encodedId}/export-pdf', [ReceiptVoucherController::class, 'exportPdf'])->name('receipt-vouchers.export-pdf');
    Route::post('/receipt-vouchers/{encodedId}/deposit-cheque', [ReceiptVoucherController::class, 'depositCheque'])->name('receipt-vouchers.deposit-cheque');
    Route::get('/receipt-vouchers-debug', [ReceiptVoucherController::class, 'debug'])->name('receipt-vouchers.debug');

    // Bank Accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
    Route::get('/bank-accounts/data', [BankAccountController::class, 'getData'])->name('bank-accounts.data');
    Route::get('/bank-accounts/create', [BankAccountController::class, 'create'])->name('bank-accounts.create');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{encodedId}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::get('/bank-accounts/{encodedId}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit');
    Route::put('/bank-accounts/{encodedId}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::delete('/bank-accounts/{encodedId}', [BankAccountController::class, 'destroy'])->name('bank-accounts.destroy');

    // FX Rates Management
    Route::get('/fx-rates', [App\Http\Controllers\Accounting\FxRateController::class, 'index'])->name('fx-rates.index');
    Route::get('/fx-rates/data', [App\Http\Controllers\Accounting\FxRateController::class, 'data'])->name('fx-rates.data');
    Route::get('/fx-rates/create', [App\Http\Controllers\Accounting\FxRateController::class, 'create'])->name('fx-rates.create');
    Route::post('/fx-rates', [App\Http\Controllers\Accounting\FxRateController::class, 'store'])->name('fx-rates.store');
    Route::get('/fx-rates/{id}/edit', [App\Http\Controllers\Accounting\FxRateController::class, 'edit'])->name('fx-rates.edit');
    Route::put('/fx-rates/{id}', [App\Http\Controllers\Accounting\FxRateController::class, 'update'])->name('fx-rates.update');
    Route::post('/fx-rates/{id}/lock', [App\Http\Controllers\Accounting\FxRateController::class, 'lock'])->name('fx-rates.lock');
    Route::post('/fx-rates/{id}/unlock', [App\Http\Controllers\Accounting\FxRateController::class, 'unlock'])->name('fx-rates.unlock');
    Route::get('/fx-rates/import', [App\Http\Controllers\Accounting\FxRateController::class, 'import'])->name('fx-rates.import');
    Route::post('/fx-rates/import', [App\Http\Controllers\Accounting\FxRateController::class, 'processImport'])->name('fx-rates.process-import');
    Route::get('/fx-rates/download-sample', [App\Http\Controllers\Accounting\FxRateController::class, 'downloadSample'])->name('fx-rates.download-sample');
    Route::get('/api/fx-rates/get-rate', [App\Http\Controllers\Accounting\FxRateController::class, 'getRate'])->name('fx-rates.get-rate');

    // FX Rate Override Routes
    Route::post('/fx-rates/override', [App\Http\Controllers\Accounting\FxRateOverrideController::class, 'requestOverride'])->name('fx-rates.override');
    Route::post('/fx-rates/override/{id}/approve', [App\Http\Controllers\Accounting\FxRateOverrideController::class, 'approve'])->name('fx-rates.override.approve');
    Route::post('/fx-rates/override/{id}/reject', [App\Http\Controllers\Accounting\FxRateOverrideController::class, 'reject'])->name('fx-rates.override.reject');

    // FX Revaluation Routes
    Route::get('/fx-revaluation', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'index'])->name('fx-revaluation.index');
    Route::get('/fx-revaluation/data', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'data'])->name('fx-revaluation.data');
    Route::get('/fx-revaluation/create', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'create'])->name('fx-revaluation.create');
    Route::post('/fx-revaluation/preview', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'preview'])->name('fx-revaluation.preview');
    Route::post('/fx-revaluation', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'store'])->name('fx-revaluation.store');
    Route::get('/fx-revaluation/{id}', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'show'])->name('fx-revaluation.show');
    Route::post('/fx-revaluation/{id}/reverse', [App\Http\Controllers\Accounting\FxRevaluationController::class, 'reverse'])->name('fx-revaluation.reverse');

    // FX Settings Routes
    Route::get('/fx-settings', [App\Http\Controllers\Accounting\FxSettingsController::class, 'index'])->name('fx-settings.index');
    Route::put('/fx-settings', [App\Http\Controllers\Accounting\FxSettingsController::class, 'update'])->name('fx-settings.update');

    // Share Capital Management Routes
    Route::prefix('share-capital')->name('share-capital.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\ShareCapitalController::class, 'index'])->name('index');
        // Future routes:
        // Route::get('/shareholders', ...)->name('shareholders.index');
        // Route::get('/issues', ...)->name('issues.index');
        // Route::get('/dividends', ...)->name('dividends.index');
    });

    // Accruals & Prepayments Routes
    Route::prefix('accruals-prepayments')->name('accruals-prepayments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'show'])->name('show');
        Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'edit'])->name('edit');
        Route::put('/{encodedId}', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'update'])->name('update');
        Route::delete('/{encodedId}', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'destroy'])->name('destroy');
        Route::post('/{encodedId}/submit', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'submit'])->name('submit');
        Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'approve'])->name('approve');
        Route::post('/{encodedId}/reject', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'reject'])->name('reject');
        Route::post('/{encodedId}/post-journal/{journalId}', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'postJournal'])->name('post-journal');
        Route::post('/{encodedId}/post-all-pending', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'postAllPending'])->name('post-all-pending');
        Route::get('/{encodedId}/amortisation-schedule', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'amortisationSchedule'])->name('amortisation-schedule');
        Route::get('/{encodedId}/export-pdf', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{encodedId}/export-excel', [App\Http\Controllers\Accounting\AccrualsPrepaymentsController::class, 'exportExcel'])->name('export-excel');
    });

    // IAS 37 Provisions & Contingencies
        Route::prefix('provisions')->name('provisions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ProvisionController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ProvisionController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ProvisionController::class, 'store'])->name('store');
            Route::post('/compute', [App\Http\Controllers\Accounting\ProvisionController::class, 'compute'])->name('compute');
            Route::get('/disclosure', [App\Http\Controllers\Accounting\ProvisionDisclosureController::class, 'index'])->name('disclosure');
            Route::get('/disclosure/export-json', [App\Http\Controllers\Accounting\ProvisionDisclosureController::class, 'exportJson'])->name('disclosure.export-json');
            Route::get('/disclosure/export-excel', [App\Http\Controllers\Accounting\ProvisionDisclosureController::class, 'exportExcel'])->name('disclosure.export-excel');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ProvisionController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ProvisionController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ProvisionController::class, 'update'])->name('update');
            Route::post('/{encodedId}/submit', [App\Http\Controllers\Accounting\ProvisionController::class, 'submitForApproval'])->name('submit');
            Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\ProvisionController::class, 'approve'])->name('approve');
            Route::post('/{encodedId}/reject', [App\Http\Controllers\Accounting\ProvisionController::class, 'reject'])->name('reject');
            Route::post('/{encodedId}/remeasure', [App\Http\Controllers\Accounting\ProvisionController::class, 'remeasure'])->name('remeasure');
            Route::post('/{encodedId}/unwind', [App\Http\Controllers\Accounting\ProvisionController::class, 'unwind'])->name('unwind');
        });

    Route::prefix('contingencies')->name('contingencies.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\ContingencyController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Accounting\ContingencyController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Accounting\ContingencyController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ContingencyController::class, 'show'])->name('show');
    });

    // Share Capital Management Routes
    Route::prefix('share-capital')->name('share-capital.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\ShareCapitalController::class, 'index'])->name('index');

        // Share Classes
        Route::prefix('share-classes')->name('share-classes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ShareClassController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ShareClassController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ShareClassController::class, 'store'])->name('store');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ShareClassController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ShareClassController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ShareClassController::class, 'update'])->name('update');
            Route::delete('/{encodedId}', [App\Http\Controllers\Accounting\ShareClassController::class, 'destroy'])->name('destroy');
        });

        // Shareholders
        Route::prefix('shareholders')->name('shareholders.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ShareholderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ShareholderController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ShareholderController::class, 'store'])->name('store');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ShareholderController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ShareholderController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ShareholderController::class, 'update'])->name('update');
            Route::delete('/{encodedId}', [App\Http\Controllers\Accounting\ShareholderController::class, 'destroy'])->name('destroy');
        });

        // Share Issues
        Route::prefix('share-issues')->name('share-issues.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ShareIssueController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ShareIssueController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ShareIssueController::class, 'store'])->name('store');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ShareIssueController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ShareIssueController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ShareIssueController::class, 'update'])->name('update');
            Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\ShareIssueController::class, 'approve'])->name('approve');
            Route::post('/{encodedId}/post-to-gl', [App\Http\Controllers\Accounting\ShareIssueController::class, 'postToGl'])->name('post-to-gl');
        });

        // Dividends
        Route::prefix('dividends')->name('dividends.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ShareDividendController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ShareDividendController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ShareDividendController::class, 'store'])->name('store');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ShareDividendController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ShareDividendController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ShareDividendController::class, 'update'])->name('update');
            Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\ShareDividendController::class, 'approve'])->name('approve');
            Route::post('/{encodedId}/declare', [App\Http\Controllers\Accounting\ShareDividendController::class, 'declare'])->name('declare');
            Route::post('/{encodedId}/process-payment', [App\Http\Controllers\Accounting\ShareDividendController::class, 'processPayment'])->name('process-payment');
        });

        // Corporate Actions
        Route::prefix('corporate-actions')->name('corporate-actions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'store'])->name('store');
            Route::get('/{encodedId}', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'show'])->name('show');
            Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'edit'])->name('edit');
            Route::put('/{encodedId}', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'update'])->name('update');
            Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'approve'])->name('approve');
            Route::post('/{encodedId}/execute', [App\Http\Controllers\Accounting\ShareCorporateActionController::class, 'execute'])->name('execute');
        });
    });

    // Bank Reconciliation
    Route::get('/bank-reconciliation/data', [BankReconciliationController::class, 'data'])->name('bank-reconciliation.data');
    Route::resource('bank-reconciliation', BankReconciliationController::class);

    Route::post('/bank-reconciliation/{bankReconciliation}/add-bank-statement-item', [BankReconciliationController::class, 'addBankStatementItem'])->name('bank-reconciliation.add-bank-statement-item');
    Route::post('/bank-reconciliation/{bankReconciliation}/match-items', [BankReconciliationController::class, 'matchItems'])->name('bank-reconciliation.match-items');
    Route::post('/bank-reconciliation/{bankReconciliation}/unmatch-items', [BankReconciliationController::class, 'unmatchItems'])->name('bank-reconciliation.unmatch-items');
    Route::post('/bank-reconciliation/{bankReconciliation}/confirm-book-item', [BankReconciliationController::class, 'confirmBookItem'])->name('bank-reconciliation.confirm-book-item');
    Route::post('/bank-reconciliation/{bankReconciliation}/mark-previous-month-reconciled', [BankReconciliationController::class, 'markPreviousMonthItemReconciled'])->name('bank-reconciliation.mark-previous-month-reconciled');
    Route::post('/bank-reconciliation/{bankReconciliation}/complete', [BankReconciliationController::class, 'completeReconciliation'])->name('bank-reconciliation.complete');
    Route::post('/bank-reconciliation/{bankReconciliation}/update-book-balance', [BankReconciliationController::class, 'updateBookBalance'])->name('bank-reconciliation.update-book-balance');
    Route::post('/bank-reconciliation/refresh-all', [BankReconciliationController::class, 'refreshAllReconciliations'])->name('bank-reconciliation.refresh-all');
    Route::get('/bank-reconciliation/{bankReconciliation}/statement', [BankReconciliationController::class, 'generateStatement'])->name('bank-reconciliation.statement');
    Route::get('/bank-reconciliation/{bankReconciliation}/export-statement', [BankReconciliationController::class, 'exportStatement'])->name('bank-reconciliation.export-statement');

    // Bank Reconciliation Approval Routes
    Route::post('/bank-reconciliation/{bankReconciliation}/submit-for-approval', [BankReconciliationController::class, 'submitForApproval'])->name('bank-reconciliation.submit-for-approval');
    Route::post('/bank-reconciliation/{bankReconciliation}/approve', [BankReconciliationController::class, 'approve'])->name('bank-reconciliation.approve');
    Route::post('/bank-reconciliation/{bankReconciliation}/reject', [BankReconciliationController::class, 'reject'])->name('bank-reconciliation.reject');
    Route::post('/bank-reconciliation/{bankReconciliation}/reassign', [BankReconciliationController::class, 'reassign'])->name('bank-reconciliation.reassign');
    Route::get('/bank-reconciliation/{bankReconciliation}/approval-history', [BankReconciliationController::class, 'approvalHistory'])->name('bank-reconciliation.approval-history');

    // Bill Purchases
    Route::get('/bill-purchases', [BillPurchaseController::class, 'index'])->name('bill-purchases');
    Route::get('/bill-purchases/create', [BillPurchaseController::class, 'create'])->name('bill-purchases.create');
    Route::post('/bill-purchases', [BillPurchaseController::class, 'store'])->name('bill-purchases.store');

    // Bill Payment Management (must come before bill-purchases/{billPurchase} routes)
    Route::get('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'showPayment'])->name('bill-purchases.payment.show');
    Route::get('/bill-purchases/payment/{payment}/edit', [BillPurchaseController::class, 'editPayment'])->name('bill-purchases.payment.edit');
    Route::put('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'updatePayment'])->name('bill-purchases.payment.update');
    Route::delete('/bill-purchases/payment/{payment}', [BillPurchaseController::class, 'deletePayment'])->name('bill-purchases.payment.delete');

    Route::get('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'show'])->name('bill-purchases.show');
    Route::get('/bill-purchases/{billPurchase}/edit', [BillPurchaseController::class, 'edit'])->name('bill-purchases.edit');
    Route::put('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'update'])->name('bill-purchases.update');
    Route::delete('/bill-purchases/{billPurchase}', [BillPurchaseController::class, 'destroy'])->name('bill-purchases.destroy');
    Route::get('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'showPaymentForm'])->name('bill-purchases.payment');
    Route::post('/bill-purchases/{billPurchase}/payment', [BillPurchaseController::class, 'processPayment'])->name('bill-purchases.process-payment');

    // Budget
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    Route::post('/budgets/import', [BudgetController::class, 'storeImport'])->name('budgets.store-import');
    Route::get('/budgets/template/download', [BudgetController::class, 'downloadTemplate'])->name('budgets.download-template');
    Route::get('/budgets/{budget}/export/excel', [BudgetController::class, 'exportExcel'])->name('budgets.export-excel');
    Route::get('/budgets/{budget}/export/pdf', [BudgetController::class, 'exportPdf'])->name('budgets.export-pdf');


    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::get('/budgets/{budget}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::get('/budgets/{budget}/reallocate', [BudgetController::class, 'showReallocate'])->name('budgets.reallocate');
    Route::post('/budgets/{budget}/reallocate', [BudgetController::class, 'reallocate'])->name('budgets.reallocate.store');

    // Budget Approval Routes
    Route::post('/budgets/{budget}/submit-for-approval', [BudgetController::class, 'submitForApproval'])->name('budgets.submit-for-approval');
    Route::post('/budgets/{budget}/approve', [BudgetController::class, 'approve'])->name('budgets.approve');
    Route::post('/budgets/{budget}/reject', [BudgetController::class, 'reject'])->name('budgets.reject');
    Route::post('/budgets/{budget}/reassign', [BudgetController::class, 'reassign'])->name('budgets.reassign');
    Route::get('/budgets/{budget}/approval-history', [BudgetController::class, 'approvalHistory'])->name('budgets.approval-history');



    // Journal Entries CRUD
    Route::get('/journals', [JournalController::class, 'index'])->name('journals.index');
    Route::get('/journals/data', [JournalController::class, 'data'])->name('journals.data');
    Route::get('/journals/statistics', [JournalController::class, 'statistics'])->name('journals.statistics');
    Route::get('/journals/create', [JournalController::class, 'create'])->name('journals.create');
    Route::post('/journals', [JournalController::class, 'store'])->name('journals.store');
    Route::get('/journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
    Route::get('/journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
    Route::put('/journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
    Route::delete('/journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');
    Route::get('/journals/{journal}/export-pdf', [JournalController::class, 'exportPdf'])->name('journals.export-pdf');
    Route::get('/journals/{journal}/approve', [JournalController::class, 'showApproval'])->name('journals.approve');
    Route::post('/journals/{journal}/approve', [JournalController::class, 'approve'])->name('journals.approve.store');
    Route::post('/journals/{journal}/reject', [JournalController::class, 'reject'])->name('journals.reject');

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        // Consolidated Management Report (Landing)
        Route::get('/consolidated-management-report', [App\Http\Controllers\AccountingController::class, 'consolidatedManagementReport'])->name('consolidated-management-report');
        Route::get('/consolidated-management-report/export', [App\Http\Controllers\AccountingController::class, 'exportConsolidatedManagementReport'])->name('consolidated-management-report.export');
        Route::get('/consolidated-management-report/export-word', [App\Http\Controllers\AccountingController::class, 'exportConsolidatedManagementReportWord'])->name('consolidated-management-report.export-word');
        Route::post('/consolidated-management-report/kpis', [App\Http\Controllers\AccountingController::class, 'updateCmrKpis'])->name('consolidated-management-report.kpis');
        Route::get('/other-income', [App\Http\Controllers\Accounting\Reports\OtherIncomeReportController::class, 'index'])->name('other-income');
        Route::get('/other-income/export', [App\Http\Controllers\Accounting\Reports\OtherIncomeReportController::class, 'export'])->name('other-income.export');
        // Trial Balance Report
        Route::get('/trial-balance', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'index'])->name('trial-balance');
        Route::get('/trial-balance/export', [App\Http\Controllers\Accounting\Reports\TrialBalanceReportController::class, 'export'])->name('trial-balance.export');
        Route::get('/income-statement', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'index'])->name('income-statement');
        Route::get('/income-statement/export', [App\Http\Controllers\Accounting\Reports\IncomeStatementReportController::class, 'export'])->name('income-statement.export');
        Route::get('/cash-book', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'index'])->name('cash-book');
        Route::get('/cash-book/export', [App\Http\Controllers\Accounting\Reports\CashBookReportController::class, 'export'])->name('cash-book.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/accounting-notes/export', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'export'])->name('accounting-notes.export');
        Route::get('/balance-sheet', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'index'])->name('balance-sheet');
        Route::get('/balance-sheet/export', [App\Http\Controllers\Accounting\Reports\BalanceSheetReportController::class, 'export'])->name('balance-sheet.export');
        Route::get('/cash-flow', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'index'])->name('cash-flow');
        Route::match(['GET', 'POST'], '/cash-flow/export', [App\Http\Controllers\Accounting\Reports\CashFlowReportController::class, 'export'])->name('cash-flow.export');
        Route::get('/general-ledger', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'index'])->name('general-ledger');
        Route::get('/general-ledger/export', [App\Http\Controllers\Accounting\Reports\GeneralLedgerReportController::class, 'export'])->name('general-ledger.export');
        Route::get('/expenses-summary', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'index'])->name('expenses-summary');
        Route::get('/expenses-summary/export', [App\Http\Controllers\Accounting\Reports\ExpensesSummaryReportController::class, 'export'])->name('expenses-summary.export');
        Route::get('/accounting-notes', [App\Http\Controllers\Accounting\Reports\AccountingNotesReportController::class, 'index'])->name('accounting-notes');
        Route::get('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'index'])->name('changes-equity');
        Route::post('/changes-equity', [App\Http\Controllers\Accounting\Reports\ChangesEquityReportController::class, 'export'])->name('changes-equity.export');
        Route::get('/bank-reconciliation', [BankReconciliationReportController::class, 'index'])->name('bank-reconciliation-report');
        Route::get('/bank-reconciliation/reports', [BankReconciliationReportController::class, 'reportsIndex'])->name('bank-reconciliation-report.reports-index');
        Route::get('/bank-reconciliation/generate', [BankReconciliationReportController::class, 'generate'])->name('bank-reconciliation-report.generate');
        Route::get('/bank-reconciliation/{bankReconciliation}/show', [BankReconciliationReportController::class, 'show'])->name('bank-reconciliation-report.show');
        Route::get('/bank-reconciliation/{bankReconciliation}/export', [BankReconciliationReportController::class, 'exportReconciliation'])->name('bank-reconciliation-report.export');
        Route::get('/bank-reconciliation/uncleared-items-aging', [BankReconciliationReportController::class, 'unclearedItemsAging'])->name('bank-reconciliation-report.uncleared-items-aging');
        Route::get('/bank-reconciliation/unreconciled-items-aging', [BankReconciliationReportController::class, 'unreconciledItemsAging'])->name('bank-reconciliation-report.unreconciled-items-aging');
        Route::get('/bank-reconciliation/cleared-items', [BankReconciliationReportController::class, 'clearedItemsFromPreviousMonth'])->name('bank-reconciliation-report.cleared-items');
        Route::get('/bank-reconciliation/cleared-transactions', [BankReconciliationReportController::class, 'clearedTransactions'])->name('bank-reconciliation-report.cleared-transactions');
        Route::get('/bank-reconciliation/adjustments', [BankReconciliationReportController::class, 'bankReconciliationAdjustments'])->name('bank-reconciliation-report.adjustments');
        Route::get('/bank-reconciliation/exception-report', [BankReconciliationReportController::class, 'exceptionReport'])->name('bank-reconciliation-report.exception');
        Route::get('/bank-reconciliation/approval-audit-trail', [BankReconciliationReportController::class, 'approvalAuditTrail'])->name('bank-reconciliation-report.approval-audit-trail');
        Route::get('/bank-reconciliation/full-pack', [BankReconciliationReportController::class, 'fullReconciliationPackSelect'])->name('bank-reconciliation-report.full-pack');
        Route::post('/bank-reconciliation/full-pack/download', [BankReconciliationReportController::class, 'fullReconciliationPack'])->name('bank-reconciliation-report.full-pack-download');
        Route::get('/bank-reconciliation/{bankReconciliation}/full-pack', [BankReconciliationReportController::class, 'fullReconciliationPack'])->name('bank-reconciliation-report.full-pack-reconciliation');
        Route::get('/bank-reconciliation/summary-movement', [BankReconciliationReportController::class, 'reconciliationSummaryMovement'])->name('bank-reconciliation-report.summary-movement');
        Route::get('/budget-report', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'index'])->name('budget-report');
        Route::get('/budget-report/export', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'export'])->name('budget-report.export');
        Route::get('/budget-report/export-pdf', [App\Http\Controllers\Accounting\Reports\BudgetReportController::class, 'exportPdf'])->name('budget-report.export-pdf');
    });

    // Transaction Routes
    Route::get('/transactions/double-entries/{accountId}', [App\Http\Controllers\TransactionController::class, 'doubleEntries'])->name('transactions.doubleEntries');
    Route::get('/transactions/details/{transactionId}/{transactionType?}', [App\Http\Controllers\TransactionController::class, 'showTransactionDetails'])->name('transactions.details');

    // Petty Cash Management Routes
    Route::prefix('petty-cash')->name('petty-cash.')->group(function () {
        // Petty Cash Units - Use resource except for routes we define explicitly with encodedId
        Route::resource('units', App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class)->except(['show', 'edit', 'update', 'destroy']);
        Route::get('units/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'show'])->name('units.show');
        Route::get('download-guide', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'downloadGuide'])->name('download-guide');
        Route::get('units/{encodedId}/edit', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'edit'])->name('units.edit');
        Route::put('units/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'update'])->name('units.update');
        Route::delete('units/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'destroy'])->name('units.destroy');
        Route::get('units/{encodedId}/transactions', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'getTransactions'])->name('units.transactions');
        Route::get('units/{encodedId}/replenishments', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'getReplenishments'])->name('units.replenishments');
        Route::get('units/{encodedId}/export-pdf', [App\Http\Controllers\Accounting\PettyCash\PettyCashUnitController::class, 'exportPdf'])->name('units.export-pdf');

        // Expense Categories - Use resource except for routes we define explicitly with encodedId
        Route::resource('categories', App\Http\Controllers\Accounting\PettyCash\PettyCashExpenseCategoryController::class)->except(['edit', 'update', 'destroy']);
        Route::get('categories/{encodedId}/edit', [App\Http\Controllers\Accounting\PettyCash\PettyCashExpenseCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashExpenseCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashExpenseCategoryController::class, 'destroy'])->name('categories.destroy');

        // Transactions
        Route::get('transactions', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/create', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'create'])->name('transactions.create');
        Route::get('transactions/categories', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'getCategories'])->name('transactions.categories');
        Route::get('transactions/expense-accounts', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'getExpenseAccounts'])->name('transactions.expense-accounts');
        Route::post('transactions', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'show'])->name('transactions.show');
        Route::get('transactions/{encodedId}/edit', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('transactions/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'update'])->name('transactions.update');
        Route::delete('transactions/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::post('transactions/{encodedId}/approve', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('transactions/{encodedId}/reject', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'reject'])->name('transactions.reject');
        Route::post('transactions/{encodedId}/disburse', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'disburse'])->name('transactions.disburse');
        Route::post('transactions/{encodedId}/upload-receipt', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'uploadReceipt'])->name('transactions.upload-receipt');
        Route::post('transactions/{encodedId}/verify-receipt', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'verifyReceipt'])->name('transactions.verify-receipt');
        Route::post('transactions/{encodedId}/post-to-gl', [App\Http\Controllers\Accounting\PettyCash\PettyCashTransactionController::class, 'postToGL'])->name('transactions.post-to-gl');

        // Replenishments
        Route::get('replenishments', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'index'])->name('replenishments.index');
        Route::get('replenishments/create', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'create'])->name('replenishments.create');
        Route::get('replenishments/bank-accounts', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'getBankAccounts'])->name('replenishments.bank-accounts');
        Route::post('replenishments', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'store'])->name('replenishments.store');
        Route::get('replenishments/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'show'])->name('replenishments.show');
        Route::get('replenishments/{encodedId}/edit', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'edit'])->name('replenishments.edit');
        Route::put('replenishments/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'update'])->name('replenishments.update');
        Route::post('replenishments/{encodedId}/approve', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'approve'])->name('replenishments.approve');
        Route::post('replenishments/{encodedId}/reject', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'reject'])->name('replenishments.reject');
        Route::delete('replenishments/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashReplenishmentController::class, 'destroy'])->name('replenishments.destroy');

        // Petty Cash Register
        Route::get('register/{encodedId}', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'index'])->name('register.index');
        Route::get('reconciliation', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'reconciliationIndex'])->name('reconciliation.index');
        Route::get('reconciliation/export/pdf', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportReconciliationIndexPdf'])->name('reconciliation.export.pdf');
        Route::get('reconciliation/export/excel', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportReconciliationIndexExcel'])->name('reconciliation.export.excel');
        Route::get('register/{encodedId}/reconciliation', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'reconciliation'])->name('register.reconciliation');
        Route::post('register/{encodedId}/reconciliation', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'saveReconciliation'])->name('register.reconciliation.save');
        Route::get('register/{encodedId}/reconciliation/export/pdf', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportReconciliationPdf'])->name('register.reconciliation.export.pdf');
        Route::get('register/{encodedId}/reconciliation/export/excel', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportReconciliationExcel'])->name('register.reconciliation.export.excel');
        Route::get('register/{encodedId}/export/pdf', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportPdf'])->name('register.export.pdf');
        Route::get('register/{encodedId}/export/excel', [App\Http\Controllers\Accounting\PettyCash\PettyCashRegisterController::class, 'exportExcel'])->name('register.export.excel');
    });

    // Inter-Account Transfers Routes
    Route::prefix('account-transfers')->name('account-transfers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\AccountTransferController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Accounting\AccountTransferController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Accounting\AccountTransferController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [App\Http\Controllers\Accounting\AccountTransferController::class, 'show'])->name('show');
        Route::get('/{encodedId}/edit', [App\Http\Controllers\Accounting\AccountTransferController::class, 'edit'])->name('edit');
        Route::put('/{encodedId}', [App\Http\Controllers\Accounting\AccountTransferController::class, 'update'])->name('update');
        Route::delete('/{encodedId}', [App\Http\Controllers\Accounting\AccountTransferController::class, 'destroy'])->name('destroy');
        Route::post('/{encodedId}/approve', [App\Http\Controllers\Accounting\AccountTransferController::class, 'approve'])->name('approve');
        Route::post('/{encodedId}/reject', [App\Http\Controllers\Accounting\AccountTransferController::class, 'reject'])->name('reject');
        Route::post('/{encodedId}/post-to-gl', [App\Http\Controllers\Accounting\AccountTransferController::class, 'postToGL'])->name('post-to-gl');
        Route::get('/{encodedId}/export-pdf', [App\Http\Controllers\Accounting\AccountTransferController::class, 'exportPdf'])->name('export-pdf');
    });

    // API Routes for Account Transfers
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/bank-accounts/{id}/balance', [App\Http\Controllers\Accounting\AccountTransferController::class, 'getBankAccountBalance'])->name('bank-accounts.balance');
    });

    // Cashflow Forecasting Routes
    Route::prefix('cashflow-forecasts')->name('cashflow-forecasts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'create'])->name('create');
        Route::post('/calculate-balance', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'calculateBalance'])->name('calculate-balance');
        Route::post('/', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'show'])->name('show');
        Route::post('/{encodedId}/regenerate', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'regenerate'])->name('regenerate');
        Route::get('/{encodedId}/export/pdf', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{encodedId}/export/excel', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'exportExcel'])->name('export.excel');
        Route::get('/{encodedId}/ap-ar-impact', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'apArCashImpact'])->name('ap-ar-impact');
        Route::get('/{encodedId}/scenario-comparison', [App\Http\Controllers\Accounting\CashflowForecastController::class, 'scenarioComparison'])->name('scenario-comparison');
    });
});

////////////////////////////////////////////// END ACCOUNTING MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// LOAN MANAGEMENT ///////////////////////////////////////////////

Route::prefix('loans')->name('loans.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Loan\LoanController::class, 'index'])->name('index');
    Route::get('/data', [App\Http\Controllers\Loan\LoanController::class, 'data'])->name('data');
    Route::get('/create', [App\Http\Controllers\Loan\LoanController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Loan\LoanController::class, 'store'])->name('store');
    Route::get('/{encodedId}', [App\Http\Controllers\Loan\LoanController::class, 'show'])->name('show');
    Route::get('/{encodedId}/edit', [App\Http\Controllers\Loan\LoanController::class, 'edit'])->name('edit');
    Route::put('/{encodedId}', [App\Http\Controllers\Loan\LoanController::class, 'update'])->name('update');
    Route::delete('/{encodedId}', [App\Http\Controllers\Loan\LoanController::class, 'destroy'])->name('destroy');
    Route::post('/{encodedId}/disburse', [App\Http\Controllers\Loan\LoanController::class, 'disburse'])->name('disburse');
    Route::post('/{encodedId}/generate-schedule', [App\Http\Controllers\Loan\LoanController::class, 'generateSchedule'])->name('schedule.generate');
    Route::get('/{encodedId}/payments/create', [App\Http\Controllers\Loan\LoanController::class, 'createPayment'])->name('payments.create');
    Route::post('/{encodedId}/payments', [App\Http\Controllers\Loan\LoanController::class, 'storePayment'])->name('payments.store');
    Route::post('/{encodedId}/accrue-interest', [App\Http\Controllers\Loan\LoanController::class, 'accrueInterest'])->name('accrue.interest');
    Route::post('/{encodedId}/stop-capitalisation', [App\Http\Controllers\Loan\LoanController::class, 'stopCapitalisation'])->name('stop-capitalisation');
    Route::post('/{encodedId}/prepayment', [App\Http\Controllers\Loan\LoanController::class, 'prepayment'])->name('prepayment');
    Route::post('/{encodedId}/restructure', [App\Http\Controllers\Loan\LoanController::class, 'restructure'])->name('restructure');
    Route::post('/{encodedId}/post-to-gl', [App\Http\Controllers\Loan\LoanController::class, 'postToGl'])->name('post.to.gl');
    Route::get('/{encodedId}/export/pdf', [App\Http\Controllers\Loan\LoanController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/{encodedId}/export/excel', [App\Http\Controllers\Loan\LoanController::class, 'exportExcel'])->name('export.excel');
});

////////////////////////////////////////////// END LOAN MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// INVENTORY MANAGEMENT ///////////////////////////////////////////

Route::prefix('inventory')->name('inventory.')->middleware(['auth', 'company.scope', 'check.inventory.cost.method', 'require.branch'])->group(function () {
    // Inventory Management Dashboard
    Route::get('/', [InventoryController::class, 'index'])->name('index');

    // Inventory Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
    Route::get('/items/download-template', [ItemController::class, 'downloadTemplate'])->name('items.download-template');
    Route::get('/items/{encodedId}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{encodedId}/movements', [ItemController::class, 'movements'])->name('items.movements');
    Route::get('/items/{encodedId}/stock', [ItemController::class, 'getItemStock'])->name('items.stock');
    Route::get('/items/{encodedId}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{encodedId}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{encodedId}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Stock Reports
    Route::get('/stock-report', [ItemController::class, 'getStockReport'])->name('stock.report');
    Route::get('/location/{locationId}/stock', [ItemController::class, 'getLocationStock'])->name('location.stock');

    // Inventory Categories (use hash ids)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{encodedId}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{encodedId}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{encodedId}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{encodedId}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Stock Movements
    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create', [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}', [MovementController::class, 'show'])->name('movements.show');
    Route::get('/movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('/movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
    Route::delete('/movements/{movement}', [MovementController::class, 'destroy'])->name('movements.destroy');

    // Write-offs
    Route::get('/write-offs', [WriteOffController::class, 'index'])->name('write-offs.index');
    Route::get('/write-offs/create', [WriteOffController::class, 'create'])->name('write-offs.create');
    Route::post('/write-offs', [WriteOffController::class, 'store'])->name('write-offs.store');
    Route::get('/write-offs/{movement}', [WriteOffController::class, 'show'])->name('write-offs.show');
    Route::get('/write-offs/{movement}/edit', [WriteOffController::class, 'edit'])->name('write-offs.edit');
    Route::put('/write-offs/{movement}', [WriteOffController::class, 'update'])->name('write-offs.update');
    Route::delete('/write-offs/{movement}', [WriteOffController::class, 'destroy'])->name('write-offs.destroy');

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    Route::get('/transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
    Route::put('/transfers/{transfer}', [TransferController::class, 'update'])->name('transfers.update');
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');

    // Bulk Transfer Operations
    Route::post('/transfers/bulk-delete', [TransferController::class, 'bulkDelete'])->name('transfers.bulk-delete');
    Route::get('/transfers/bulk-edit', [TransferController::class, 'bulkEdit'])->name('transfers.bulk-edit');
    Route::put('/transfers/bulk-update', [TransferController::class, 'bulkUpdate'])->name('transfers.bulk-update');

    // Transfer Requests
    Route::get('/transfer-requests', [TransferRequestController::class, 'index'])->name('transfer-requests.index');
    Route::get('/transfer-requests/create', [TransferRequestController::class, 'create'])->name('transfer-requests.create');
    Route::post('/transfer-requests', [TransferRequestController::class, 'store'])->name('transfer-requests.store');
    Route::get('/transfer-requests/{transferRequest}', [TransferRequestController::class, 'show'])->name('transfer-requests.show');
    Route::get('/transfer-requests/{transferRequest}/edit', [TransferRequestController::class, 'edit'])->name('transfer-requests.edit');
    Route::put('/transfer-requests/{transferRequest}', [TransferRequestController::class, 'update'])->name('transfer-requests.update');
    Route::post('/transfer-requests/{transferRequest}/approve', [TransferRequestController::class, 'approve'])->name('transfer-requests.approve');
    Route::post('/transfer-requests/{transferRequest}/reject', [TransferRequestController::class, 'reject'])->name('transfer-requests.reject');


    // Inventory Count Routes
    Route::prefix('counts')->name('counts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryCountController::class, 'index'])->name('index');

        // Count Periods
        Route::get('/periods/create', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createPeriod'])->name('periods.create');
        Route::post('/periods', [App\Http\Controllers\Inventory\InventoryCountController::class, 'storePeriod'])->name('periods.store');
        Route::get('/periods/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showPeriod'])->name('periods.show');

        // Count Sessions
        Route::get('/sessions/create/{periodEncodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createSession'])->name('sessions.create');
        Route::post('/sessions/{periodEncodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'storeSession'])->name('sessions.store');
        Route::get('/sessions/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showSession'])->name('sessions.show');
        Route::post('/sessions/{encodedId}/freeze', [App\Http\Controllers\Inventory\InventoryCountController::class, 'freezeSession'])->name('sessions.freeze');
        Route::post('/sessions/{encodedId}/start-counting', [App\Http\Controllers\Inventory\InventoryCountController::class, 'startCounting'])->name('sessions.start-counting');
        Route::post('/sessions/{encodedId}/complete-counting', [App\Http\Controllers\Inventory\InventoryCountController::class, 'completeCounting'])->name('sessions.complete-counting');
        Route::post('/sessions/{encodedId}/approve', [App\Http\Controllers\Inventory\InventoryCountController::class, 'approveCountSession'])->name('sessions.approve');
        Route::post('/sessions/{encodedId}/reject', [App\Http\Controllers\Inventory\InventoryCountController::class, 'rejectCountSession'])->name('sessions.reject');
        Route::get('/sessions/{encodedId}/variances', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showVariances'])->name('sessions.variances');
        Route::get('/sessions/{encodedId}/export-counting-sheets-pdf', [App\Http\Controllers\Inventory\InventoryCountController::class, 'exportCountingSheetsPdf'])->name('sessions.export-counting-sheets-pdf');
        Route::get('/sessions/{encodedId}/export-counting-sheets-excel', [App\Http\Controllers\Inventory\InventoryCountController::class, 'exportCountingSheetsExcel'])->name('sessions.export-counting-sheets-excel');
        Route::get('/sessions/{encodedId}/assign-team', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showTeamAssignment'])->name('sessions.assign-team');
        Route::post('/sessions/{encodedId}/assign-team', [App\Http\Controllers\Inventory\InventoryCountController::class, 'assignTeam'])->name('sessions.assign-team.store');
        Route::get('/sessions/{encodedId}/download-counting-template', [App\Http\Controllers\Inventory\InventoryCountController::class, 'downloadCountingTemplate'])->name('sessions.download-counting-template');
        Route::post('/sessions/{encodedId}/upload-counting-excel', [App\Http\Controllers\Inventory\InventoryCountController::class, 'uploadCountingExcel'])->name('sessions.upload-counting-excel');

        // Count Entries
        Route::get('/entries/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showEntry'])->name('entries.show');
        Route::post('/entries/{encodedId}/update-physical-qty', [App\Http\Controllers\Inventory\InventoryCountController::class, 'updatePhysicalQuantity'])->name('entries.update-physical-qty');
        Route::post('/entries/{encodedId}/recount', [App\Http\Controllers\Inventory\InventoryCountController::class, 'requestRecount'])->name('entries.recount');
        Route::post('/entries/{encodedId}/verify', [App\Http\Controllers\Inventory\InventoryCountController::class, 'verifyEntry'])->name('entries.verify');

        // Variances
        Route::post('/variances/{encodedId}/investigation', [App\Http\Controllers\Inventory\InventoryCountController::class, 'updateVarianceInvestigation'])->name('variances.investigation');

        // Adjustments
        Route::get('/sessions/{encodedId}/adjustments', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showAdjustments'])->name('sessions.adjustments');
        Route::get('/adjustments/create/{varianceId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createAdjustmentForm'])->name('adjustments.create-form');
        Route::post('/adjustments/create/{varianceId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'createAdjustment'])->name('adjustments.create');
        Route::post('/adjustments/bulk-create/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkCreateAdjustments'])->name('adjustments.bulk-create');
        Route::post('/adjustments/bulk-approve/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkApproveAdjustments'])->name('adjustments.bulk-approve');
        Route::post('/adjustments/bulk-post/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'bulkPostAdjustmentsToGL'])->name('adjustments.bulk-post');
        Route::get('/adjustments/{encodedId}', [App\Http\Controllers\Inventory\InventoryCountController::class, 'showAdjustment'])->name('adjustments.show');
        Route::post('/adjustments/{encodedId}/approve', [App\Http\Controllers\Inventory\InventoryCountController::class, 'approveAdjustment'])->name('adjustments.approve');
        Route::post('/adjustments/{encodedId}/reject', [App\Http\Controllers\Inventory\InventoryCountController::class, 'rejectAdjustment'])->name('adjustments.reject');
        Route::post('/adjustments/{encodedId}/post-to-gl', [App\Http\Controllers\Inventory\InventoryCountController::class, 'postAdjustmentToGL'])->name('adjustments.post-to-gl');
    });

    // Inventory Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Inventory\InventoryReportController::class, 'index'])->name('index');
        Route::get('/stock-on-hand', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHand'])->name('stock-on-hand');
        Route::get('/stock-on-hand/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHandExportExcel'])->name('stock-on-hand.export.excel');
        Route::get('/stock-on-hand/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockOnHandExportPdf'])->name('stock-on-hand.export.pdf');
        Route::get('/stock-valuation', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockValuation'])->name('stock-valuation');
        Route::get('/movement-register', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegister'])->name('movement-register');
        Route::get('/movement-register/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegisterExportExcel'])->name('movement-register.export.excel');
        Route::get('/movement-register/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'movementRegisterExportPdf'])->name('movement-register.export.pdf');
        Route::get('/aging-stock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'agingStock'])->name('aging-stock');
        Route::get('/reorder', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReport'])->name('reorder');
        Route::get('/reorder/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReportExportExcel'])->name('reorder.export.excel');
        Route::get('/reorder/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'reorderReportExportPdf'])->name('reorder.export.pdf');
        Route::get('/over-understock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstock'])->name('over-understock');
        Route::get('/over-understock/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstockExportExcel'])->name('over-understock.export.excel');
        Route::get('/over-understock/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'overUnderstockExportPdf'])->name('over-understock.export.pdf');
        Route::get('/item-ledger', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedger'])->name('item-ledger');
        Route::get('/item-ledger/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedgerExportExcel'])->name('item-ledger.export.excel');
        Route::get('/item-ledger/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'itemLedgerExportPdf'])->name('item-ledger.export.pdf');
        Route::get('/cost-changes', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChanges'])->name('cost-changes');
        Route::get('/cost-changes/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChangesExportExcel'])->name('cost-changes.export.excel');
        Route::get('/cost-changes/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'costChangesExportPdf'])->name('cost-changes.export.pdf');
        Route::get('/stock-take-variance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'stockTakeVariance'])->name('stock-take-variance');
        Route::get('/full-inventory-count', [App\Http\Controllers\Inventory\InventoryReportController::class, 'fullInventoryCountReport'])->name('full-inventory-count');
        Route::get('/variance-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'varianceSummaryReport'])->name('variance-summary');
        Route::get('/variance-value', [App\Http\Controllers\Inventory\InventoryReportController::class, 'varianceValueReport'])->name('variance-value');
        Route::get('/high-value-scorecard', [App\Http\Controllers\Inventory\InventoryReportController::class, 'highValueItemsScorecard'])->name('high-value-scorecard');
        Route::get('/expiry-damaged-stock', [App\Http\Controllers\Inventory\InventoryReportController::class, 'expiryDamagedStockReport'])->name('expiry-damaged-stock');
        Route::get('/cycle-count-performance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'cycleCountPerformanceReport'])->name('cycle-count-performance');
        Route::get('/year-end-stock-valuation', [App\Http\Controllers\Inventory\InventoryReportController::class, 'yearEndStockValuationReport'])->name('year-end-stock-valuation');
        Route::get('/location-bin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'locationBin'])->name('location-bin');
        Route::get('/category-brand-mix', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMix'])->name('category-brand-mix');
        Route::get('/category-brand-mix/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMixExportExcel'])->name('category-brand-mix.export.excel');
        Route::get('/category-brand-mix/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryBrandMixExportPdf'])->name('category-brand-mix.export.pdf');
        Route::get('/profit-margin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMargin'])->name('profit-margin');
        Route::get('/profit-margin/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMarginExportExcel'])->name('profit-margin.export.excel');
        Route::get('/profit-margin/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'profitMarginExportPdf'])->name('profit-margin.export.pdf');
        Route::get('/inventory-value-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummary'])->name('inventory-value-summary');
        Route::get('/inventory-value-summary/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummaryExportPdf'])->name('inventory-value-summary.export.pdf');
        Route::get('/inventory-value-summary/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryValueSummaryExportExcel'])->name('inventory-value-summary.export.excel');

        // Inventory Quantity Summary
        Route::get('/inventory-quantity-summary', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummary'])->name('inventory-quantity-summary');
        Route::get('/inventory-quantity-summary/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummaryExportPdf'])->name('inventory-quantity-summary.export.pdf');
        Route::get('/inventory-quantity-summary/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantitySummaryExportExcel'])->name('inventory-quantity-summary.export.excel');

        // Inventory Profit Margin
        Route::get('/inventory-profit-margin', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMargin'])->name('inventory-profit-margin');
        Route::get('/inventory-profit-margin/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMarginExportPdf'])->name('inventory-profit-margin.export.pdf');
        Route::get('/inventory-profit-margin/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryProfitMarginExportExcel'])->name('inventory-profit-margin.export.excel');

        // Inventory Price List
        Route::get('/inventory-price-list', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceList'])->name('inventory-price-list');
        Route::get('/inventory-price-list/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceListExportPdf'])->name('inventory-price-list.export.pdf');
        Route::get('/inventory-price-list/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryPriceListExportExcel'])->name('inventory-price-list.export.excel');

        // Inventory Costing Calculation Worksheet
        Route::get('/inventory-costing-worksheet', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheet'])->name('inventory-costing-worksheet');
        Route::get('/inventory-costing-worksheet/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheetExportPdf'])->name('inventory-costing-worksheet.export.pdf');
        Route::get('/inventory-costing-worksheet/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryCostingWorksheetExportExcel'])->name('inventory-costing-worksheet.export.excel');

        // Inventory Quantity by Location
        Route::get('/inventory-quantity-by-location', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocation'])->name('inventory-quantity-by-location');
        Route::get('/inventory-quantity-by-location/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocationExportPdf'])->name('inventory-quantity-by-location.export.pdf');
        Route::get('/inventory-quantity-by-location/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryQuantityByLocationExportExcel'])->name('inventory-quantity-by-location.export.excel');

        // Inventory Transfer Movement Report
        Route::get('/inventory-transfer-movement', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovement'])->name('inventory-transfer-movement');
        Route::get('/inventory-transfer-movement/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovementExportPdf'])->name('inventory-transfer-movement.export.pdf');
        Route::get('/inventory-transfer-movement/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryTransferMovementExportExcel'])->name('inventory-transfer-movement.export.excel');

        // Inventory Aging Report
        Route::get('/inventory-aging', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAging'])->name('inventory-aging');
        Route::get('/inventory-aging/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAgingExportPdf'])->name('inventory-aging.export.pdf');
        Route::get('/inventory-aging/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'inventoryAgingExportExcel'])->name('inventory-aging.export.excel');

        // Category Performance Report
        Route::get('/category-performance', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformance'])->name('category-performance');
        Route::get('/category-performance/export/pdf', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformanceExportPdf'])->name('category-performance.export.pdf');
        Route::get('/category-performance/export/excel', [App\Http\Controllers\Inventory\InventoryReportController::class, 'categoryPerformanceExportExcel'])->name('category-performance.export.excel');

        // Expiry Reports
        Route::prefix('expiry')->name('expiry.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'index'])->name('index');
            Route::get('/expiring-soon', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'expiringSoon'])->name('expiring-soon');
            Route::get('/expired', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'expired'])->name('expired');
            Route::post('/stock-details', [\App\Http\Controllers\Inventory\ExpiryReportController::class, 'stockDetails'])->name('stock-details');
        });
    });
});

// API Routes for AJAX calls
Route::get('/api/branches/{branch}/locations', function ($branchId) {
    $locations = \App\Models\InventoryLocation::where('branch_id', $branchId)
        ->where('is_active', true)
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($locations);
})->name('api.branches.locations');

Route::get('/api/branches/{branch}/users', function ($branchId) {
    $users = \App\Models\User::where('company_id', auth()->user()->company_id)
        ->whereHas('branches', function ($query) use ($branchId) {
            $query->where('branches.id', $branchId);
        })
        ->orderBy('name')
        ->get(['id', 'name', 'email']);

    return response()->json($users);
})->middleware('auth')->name('api.branches.users');

// Debug route to test users
Route::get('/debug/users/{branchId}', function ($branchId) {
    $users = \App\Models\User::where('company_id', 1)
        ->whereHas('branches', function ($query) use ($branchId) {
            $query->where('branches.id', $branchId);
        })
        ->orderBy('name')
        ->get(['id', 'name', 'email']);

    return response()->json([
        'branch_id' => $branchId,
        'users_count' => $users->count(),
        'users' => $users
    ]);
})->name('debug.users');

////////////////////////////////////////////// END INVENTORY MANAGEMENT ///////////////////////////////////////////

// Sales Reports Routes
Route::prefix('sales/reports')->name('sales.reports.')->middleware(['auth', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Sales\SalesReportController::class, 'index'])->name('index');
    Route::get('/sales-summary', [App\Http\Controllers\Sales\SalesReportController::class, 'salesSummary'])->name('sales-summary');
    Route::get('/sales-by-product', [App\Http\Controllers\Sales\SalesReportController::class, 'salesByProduct'])->name('sales-by-product');
    Route::get('/sales-by-customer', [App\Http\Controllers\Sales\SalesReportController::class, 'salesByCustomer'])->name('sales-by-customer');
    Route::get('/sales-by-branch', [App\Http\Controllers\Sales\SalesReportController::class, 'salesByBranch'])->name('sales-by-branch');
    Route::get('/branch-profitability', [App\Http\Controllers\Sales\SalesReportController::class, 'branchProfitability'])->name('branch-profitability');
    Route::get('/sales-trend', [App\Http\Controllers\Sales\SalesReportController::class, 'salesTrend'])->name('sales-trend');
    Route::get('/sales-by-salesperson', [App\Http\Controllers\Sales\SalesReportController::class, 'salesBySalesperson'])->name('sales-by-salesperson');
    Route::get('/discount-effectiveness', [App\Http\Controllers\Sales\SalesReportController::class, 'discountEffectiveness'])->name('discount-effectiveness');
    Route::get('/sales-return', [App\Http\Controllers\Sales\SalesReportController::class, 'salesReturn'])->name('sales-return');
    Route::get('/profitability-by-product', [App\Http\Controllers\Sales\SalesReportController::class, 'profitabilityByProduct'])->name('profitability-by-product');
    Route::get('/receivables-aging', [App\Http\Controllers\Sales\SalesReportController::class, 'receivablesAging'])->name('receivables-aging');
    Route::get('/collection-efficiency', [App\Http\Controllers\Sales\SalesReportController::class, 'collectionEfficiency'])->name('collection-efficiency');
    Route::get('/invoice-register', [App\Http\Controllers\Sales\SalesReportController::class, 'invoiceRegister'])->name('invoice-register');
    Route::get('/customer-statement', [App\Http\Controllers\Sales\SalesReportController::class, 'customerStatement'])->name('customer-statement');
    Route::get('/customer-statement/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCustomerStatementPdf'])->name('customer-statement.export.pdf');
    Route::get('/customer-statement/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCustomerStatementExcel'])->name('customer-statement.export.excel');
    Route::get('/sales-return/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesReturnPdf'])->name('sales-return.export.pdf');
    Route::get('/sales-return/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesReturnExcel'])->name('sales-return.export.excel');
    Route::get('/paid-invoice', [App\Http\Controllers\Sales\SalesReportController::class, 'paidInvoice'])->name('paid-invoice');
    Route::get('/credit-note', [App\Http\Controllers\Sales\SalesReportController::class, 'creditNote'])->name('credit-note');
    Route::get('/tax-invoice', [App\Http\Controllers\Sales\SalesReportController::class, 'taxInvoice'])->name('tax-invoice');

    // Export routes
    Route::get('/sales-summary/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesSummaryPdf'])->name('sales-summary.export.pdf');
    Route::get('/sales-summary/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesSummaryExcel'])->name('sales-summary.export.excel');
    Route::get('/sales-by-product/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByProductPdf'])->name('sales-by-product.export.pdf');
    Route::get('/sales-by-product/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByProductExcel'])->name('sales-by-product.export.excel');
    Route::get('/sales-by-customer/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByCustomerPdf'])->name('sales-by-customer.export.pdf');
    Route::get('/sales-by-customer/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByCustomerExcel'])->name('sales-by-customer.export.excel');
    Route::get('/sales-by-branch/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByBranchPdf'])->name('sales-by-branch.export.pdf');
    Route::get('/sales-by-branch/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesByBranchExcel'])->name('sales-by-branch.export.excel');
    Route::get('/branch-profitability/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportBranchProfitabilityPdf'])->name('branch-profitability.export.pdf');
    Route::get('/branch-profitability/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportBranchProfitabilityExcel'])->name('branch-profitability.export.excel');
    Route::get('/receivables-aging/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportReceivablesAgingPdf'])->name('receivables-aging.export.pdf');
    Route::get('/receivables-aging/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportReceivablesAgingExcel'])->name('receivables-aging.export.excel');
    Route::get('/tax-invoice/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportTaxInvoicePdf'])->name('tax-invoice.export.pdf');
    Route::get('/tax-invoice/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportTaxInvoiceExcel'])->name('tax-invoice.export.excel');
    Route::get('/sales-by-salesperson/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesBySalespersonPdf'])->name('sales-by-salesperson.export.pdf');
    Route::get('/sales-by-salesperson/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportSalesBySalespersonExcel'])->name('sales-by-salesperson.export.excel');
    Route::get('/discount-effectiveness/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportDiscountEffectivenessPdf'])->name('discount-effectiveness.export.pdf');
    Route::get('/discount-effectiveness/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportDiscountEffectivenessExcel'])->name('discount-effectiveness.export.excel');
    Route::get('/profitability-by-product/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportProfitabilityByProductPdf'])->name('profitability-by-product.export.pdf');
    Route::get('/profitability-by-product/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportProfitabilityByProductExcel'])->name('profitability-by-product.export.excel');
    Route::get('/collection-efficiency/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCollectionEfficiencyPdf'])->name('collection-efficiency.export.pdf');
    Route::get('/collection-efficiency/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCollectionEfficiencyExcel'])->name('collection-efficiency.export.excel');
    Route::get('/invoice-register/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportInvoiceRegisterPdf'])->name('invoice-register.export.pdf');
    Route::get('/invoice-register/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportInvoiceRegisterExcel'])->name('invoice-register.export.excel');
    Route::get('/paid-invoice/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportPaidInvoicePdf'])->name('paid-invoice.export.pdf');
    Route::get('/paid-invoice/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportPaidInvoiceExcel'])->name('paid-invoice.export.excel');
    Route::get('/credit-note/export/pdf', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCreditNotePdf'])->name('credit-note.export.pdf');
    Route::get('/credit-note/export/excel', [App\Http\Controllers\Sales\SalesReportController::class, 'exportCreditNoteExcel'])->name('credit-note.export.excel');
});

////////////////////////////////////////////// PURCHASE MANAGEMENT ///////////////////////////////////////////

Route::prefix('purchases')->name('purchases.')->middleware(['auth', 'company.scope', 'check.inventory.cost.method', 'require.branch'])->group(function () {
    Route::get('/', [PurchaseController::class, 'index'])->name('index');

    // Purchase Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [PurchaseRequisitionController::class, 'index'])->name('index');
        Route::get('/data', [PurchaseRequisitionController::class, 'data'])->name('data');
        Route::get('/create', [PurchaseRequisitionController::class, 'create'])->name('create');
        Route::post('/', [PurchaseRequisitionController::class, 'store'])->name('store');
        Route::post('/check-budget', [PurchaseRequisitionController::class, 'checkBudget'])->name('check-budget');
        Route::get('/{requisition}', [PurchaseRequisitionController::class, 'show'])->name('show');
        Route::post('/{requisition}/submit', [PurchaseRequisitionController::class, 'submit'])->name('submit');
        Route::post('/{requisition}/choose-supplier-create-po', [PurchaseRequisitionController::class, 'chooseSupplierAndCreatePo'])->name('choose-supplier-create-po');
        Route::post('/{requisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('reject');
        Route::post('/{requisition}/set-preferred-supplier', [PurchaseRequisitionController::class, 'setPreferredSupplierFromQuotation'])->name('set-preferred-supplier');
        Route::delete('/{requisition}', [PurchaseRequisitionController::class, 'destroy'])->name('destroy');
    });

    // Purchase Quotations
    Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/data', [QuotationController::class, 'data'])->name('quotations.data');
    Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::put('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');
    Route::post('quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
    Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

    // Purchase Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    // GRN from Order
    Route::get('orders/{encodedId}/grn/create', [OrderController::class, 'createGrnForm'])->name('orders.grn.create');
    Route::post('orders/{encodedId}/grn', [OrderController::class, 'storeGrn'])->name('orders.grn.store');

    // Standalone GRN
    Route::get('grn/create', [OrderController::class, 'createGrnForm'])->name('grn.create');
    Route::post('grn/standalone', [OrderController::class, 'storeStandaloneGrn'])->name('grn.store-standalone');

    // GRN CRUD
    Route::get('grn/{grn}', [OrderController::class, 'grnShow'])->name('grn.show');
    Route::get('grn/{grn}/print', [OrderController::class, 'grnPrint'])->name('grn.print');
    Route::get('grn/{grn}/edit', [OrderController::class, 'grnEdit'])->name('grn.edit');
    Route::put('grn/{grn}', [OrderController::class, 'grnUpdate'])->name('grn.update');
    Route::put('grn/{grn}/qc-items', [OrderController::class, 'grnUpdateLineQc'])->name('grn.qc-items.update');
    Route::put('grn/{grn}/qc', [OrderController::class, 'grnUpdateQc'])->name('grn.qc.update');
    Route::delete('grn/{grn}', [OrderController::class, 'grnDestroy'])->name('grn.destroy');
    Route::get('orders/{encodedId}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{encodedId}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{encodedId}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{encodedId}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::put('orders/{encodedId}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('orders/{encodedId}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('orders/convert-from-quotation/{quotation}', [OrderController::class, 'convertFromQuotation'])->name('orders.convert-from-quotation');

    // GRN Management
    Route::get('grn', [OrderController::class, 'grnIndex'])->name('grn.index');

    // Cash Purchases
    Route::prefix('cash-purchases')->name('cash-purchases.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'store'])->name('store');
        Route::get('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'show'])->name('show');
        Route::get('/{encodedId}/edit', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'edit'])->name('edit');
        Route::get('/{encodedId}/export-pdf', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'exportPdf'])->name('export-pdf');
        Route::put('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'update'])->name('update');
        Route::delete('/{encodedId}', [\App\Http\Controllers\Purchase\CashPurchaseController::class, 'destroy'])->name('destroy');
    });

    // Opening Balances (Purchases)
    Route::get('opening-balances', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'index'])->name('opening-balances.index');
    Route::get('opening-balances/create', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'create'])->name('opening-balances.create');
    Route::post('opening-balances', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'store'])->name('opening-balances.store');
    Route::get('opening-balances/{encodedId}', [\App\Http\Controllers\Purchase\OpeningBalanceController::class, 'show'])->name('opening-balances.show');

    // Debit Notes
    Route::prefix('debit-notes')->name('debit-notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'store'])->name('store');
        Route::get('/invoice-items/{invoice}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'invoiceItemsJson'])->name('invoice-items');
        Route::get('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'show'])->name('show');
        Route::get('/{debitNote}/edit', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'edit'])->name('edit');
        Route::put('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'update'])->name('update');
        Route::delete('/{debitNote}', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{debitNote}/approve', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'approve'])->name('approve');
        Route::post('/{debitNote}/apply', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'apply'])->name('apply');
        Route::post('/{debitNote}/cancel', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'cancel'])->name('cancel');
        Route::get('/api/inventory-item', [\App\Http\Controllers\Purchase\DebitNoteController::class, 'getInventoryItem'])->name('api.inventory-item');
    });
});

// Purchases Reports
Route::prefix('purchases/reports')->name('purchases.reports.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'index'])->name('index');
    Route::get('/po-register', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseOrderRegister'])->name('purchase-order-register');
    Route::get('/po-register/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPurchaseOrderRegisterPdf'])->name('purchase-order-register.export.pdf');
    Route::get('/po-register/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPurchaseOrderRegisterExcel'])->name('purchase-order-register.export.excel');
    Route::get('/po-vs-grn', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'poVsGrn'])->name('po-vs-grn');
    Route::get('/po-vs-grn/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPoVsGrnPdf'])->name('po-vs-grn.export.pdf');
    Route::get('/po-vs-grn/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPoVsGrnExcel'])->name('po-vs-grn.export.excel');
    Route::get('/grn-variance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'grnVariance'])->name('grn-variance');
    Route::get('/grn-variance/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportGrnVariancePdf'])->name('grn-variance.export.pdf');
    Route::get('/grn-variance/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportGrnVarianceExcel'])->name('grn-variance.export.excel');
    Route::get('/invoice-register', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'invoiceRegister'])->name('invoice-register');
    Route::get('/invoice-register/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportInvoiceRegisterPdf'])->name('invoice-register.export.pdf');
    Route::get('/invoice-register/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportInvoiceRegisterExcel'])->name('invoice-register.export.excel');
    Route::get('/supplier-statement', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierStatement'])->name('supplier-statement');
    Route::get('/supplier-statement/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportSupplierStatementPdf'])->name('supplier-statement.export.pdf');
    Route::get('/supplier-statement/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportSupplierStatementExcel'])->name('supplier-statement.export.excel');
    Route::get('/supplier-statement-old', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'index'])->name('supplier-statement.index');
    Route::post('/supplier-statement', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'generate'])->name('supplier-statement.generate');
    Route::post('/supplier-statement/export-pdf', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'exportPdf'])->name('supplier-statement.export-pdf');
    Route::post('/supplier-statement/export-excel', [\App\Http\Controllers\Purchase\SupplierStatementController::class, 'exportExcel'])->name('supplier-statement.export-excel');
    Route::get('/payables-aging', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'payablesAging'])->name('payables-aging');
    Route::get('/payables-aging/export/pdf', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPayablesAgingPdf'])->name('payables-aging.export.pdf');
    Route::get('/payables-aging/export/excel', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'exportPayablesAgingExcel'])->name('payables-aging.export.excel');
    Route::get('/outstanding-invoices', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'outstandingInvoices'])->name('outstanding-invoices');
    Route::get('/paid-invoices', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'paidInvoices'])->name('paid-invoices');
    Route::get('/supplier-credit-notes', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierCreditNotes'])->name('supplier-credit-notes');
    Route::get('/po-invoice-variance', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'poInvoiceVariance'])->name('po-invoice-variance');
    Route::get('/purchase-by-supplier', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseBySupplier'])->name('purchase-by-supplier');
    Route::get('/purchase-by-item', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseByItem'])->name('purchase-by-item');
    Route::get('/purchase-forecast', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'purchaseForecast'])->name('purchase-forecast');
    Route::get('/supplier-tax', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'supplierTax'])->name('supplier-tax');
    Route::get('/payment-schedule', [\App\Http\Controllers\Purchase\PurchasesReportController::class, 'paymentSchedule'])->name('payment-schedule');
});

// Purchase Invoices
Route::middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/purchases/purchase-invoices', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'index'])->name('purchases.purchase-invoices.index');
    Route::get('/purchases/purchase-invoices/create', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'create'])->name('purchases.purchase-invoices.create');
    Route::post('/purchases/purchase-invoices', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'store'])->name('purchases.purchase-invoices.store');
    Route::get('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'show'])->name('purchases.purchase-invoices.show');
    Route::get('/purchases/purchase-invoices/{encodedId}/edit', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'edit'])->name('purchases.purchase-invoices.edit');
    Route::put('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'update'])->name('purchases.purchase-invoices.update');
    Route::delete('/purchases/purchase-invoices/{encodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'destroy'])->name('purchases.purchase-invoices.destroy');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'paymentForm'])->name('purchases.purchase-invoices.payment-form');
    Route::post('/purchases/purchase-invoices/{encodedId}/payment', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'recordPayment'])->name('purchases.purchase-invoices.record-payment');
    Route::get('/purchases/purchase-invoices/{encodedId}/export-pdf', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'exportPdf'])->name('purchases.purchase-invoices.export-pdf');
    Route::post('/purchases/purchase-invoices/{encodedId}/send-email', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'sendEmail'])->name('purchases.purchase-invoices.send-email');
    Route::delete('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'destroyPayment'])->name('purchases.purchase-invoices.payment.destroy');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}/edit', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'editPayment'])->name('purchases.purchase-invoices.payment.edit');
    Route::put('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'updatePayment'])->name('purchases.purchase-invoices.payment.update');
    Route::get('/purchases/purchase-invoices/{encodedId}/payment/{paymentEncodedId}/print', [\App\Http\Controllers\Purchase\PurchaseInvoiceController::class, 'printPaymentReceipt'])->name('purchases.purchase-invoices.payment.print');
});

////////////////////////////////////////////// END PURCHASE MANAGEMENT ///////////////////////////////////////////

////////////////////////////////////////////// CUSTOMER MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth', 'require.branch'])->group(function () {
    // Customer routes
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::get('customers/penalty', [CustomerController::class, 'penaltList'])->name('customers.penalty');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');

    // Bulk upload routes (must come before parameterized routes)
    Route::get('customers/bulk-upload', [CustomerController::class, 'bulkUpload'])->name('customers.bulk-upload');
    Route::post('customers/bulk-upload', [CustomerController::class, 'bulkUploadStore'])->name('customers.bulk-upload.store');
    Route::get('customers/download-sample', [CustomerController::class, 'downloadSample'])->name('customers.download-sample');

    // Parameterized routes (must come after specific routes)
    Route::get('customers/{encodedId}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('customers/{encodedId}/send-sms', [\App\Http\Controllers\DashboardController::class, 'sendBulkSmsToSingleCustomer'])->name('customers.send-sms');
    Route::get('customers/{encodedId}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{encodedId}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{encodedId}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Customer DataTable routes
    Route::get('customers/{encodedId}/deposits-datatable', [CustomerController::class, 'cashDepositsDataTable'])->name('customers.deposits.datatable');
    Route::get('customers/{encodedId}/invoices-datatable', [CustomerController::class, 'unpaidInvoicesDataTable'])->name('customers.invoices.datatable');
    Route::get('api/customers/{id}/cash-deposits', [CustomerController::class, 'getCashDeposits'])->name('api.customers.cash-deposits');
});

////////////////////////////////////////////// END CUSTOMER MANAGEMENT ///////////////////////////////////////////


// Chat routes
Route::middleware(['auth', 'require.branch'])->group(function () {
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages/{user}', [App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/mark-read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::post('/chat/clear', [App\Http\Controllers\ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/chat/online-users', [App\Http\Controllers\ChatController::class, 'getOnlineUsers'])->name('chat.online-users');
    Route::get('/chat/download/{messageId}', [App\Http\Controllers\ChatController::class, 'downloadFile'])->name('chat.download');
});

////////////////////////////////////////////// SALES MANAGEMENT ///////////////////////////////////////////

Route::prefix('sales')->name('sales.')->middleware(['auth', 'company.scope', 'check.inventory.cost.method', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Sales\SalesController::class, 'index'])->name('index');

    // Test endpoint for debugging
    Route::get('/test-auth', function () {
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => auth()->user(),
            'proformas_count' => App\Models\Sales\SalesProforma::forBranch(auth()->user()->branch_id)->count()
        ]);
    })->name('test-auth');

    // Sales Proforma Routes
    Route::resource('proformas', SalesProformaController::class);
    Route::get('proformas/item-details/{id}', [SalesProformaController::class, 'getItemDetails'])->name('proformas.item-details');
    Route::patch('proformas/{id}/status', [SalesProformaController::class, 'updateStatus'])->name('proformas.update-status');
    Route::post('proformas/{id}/convert', [SalesProformaController::class, 'convertToDocument'])->name('proformas.convert');
    Route::get('proformas/{id}/export-pdf', [SalesProformaController::class, 'exportPdf'])->name('proformas.export-pdf');

    // Sales Order Routes
    Route::resource('orders', SalesOrderController::class);
    Route::get('orders/item-details/{id}', [SalesOrderController::class, 'getItemDetails'])->name('orders.item-details');
    Route::patch('orders/{id}/update-status', [SalesOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/convert-from-proforma/{proformaId}', [SalesOrderController::class, 'convertFromProforma'])->name('orders.convert-from-proforma');
    Route::post('orders/{id}/convert-to-invoice', [SalesOrderController::class, 'convertToInvoice'])->name('orders.convert-to-invoice');
    Route::post('orders/{id}/convert-to-delivery', [SalesOrderController::class, 'convertToDelivery'])->name('orders.convert-to-delivery');
    Route::post('orders/{id}/convert-to-cash', [SalesOrderController::class, 'convertToCash'])->name('orders.convert-to-cash');

    // Sales Invoice Routes
    // IMPORTANT: More specific routes must come before parameterized routes
    Route::get('invoices/customer-credit-info', [SalesInvoiceController::class, 'getCustomerCreditInfo'])->name('invoices.customer-credit-info');
    Route::get('invoices', [SalesInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [SalesInvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [SalesInvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{encodedId}', [SalesInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{encodedId}/edit', [SalesInvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{encodedId}', [SalesInvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('invoices/{encodedId}', [SalesInvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{encodedId}/send-email', [SalesInvoiceController::class, 'sendEmail'])->name('invoices.send-email');

    Route::get('invoices/item-details/{id}', [SalesInvoiceController::class, 'getInventoryItem'])->name('invoices.item-details');
    Route::get('invoices/sales-order/{orderId}/details', [SalesInvoiceController::class, 'getSalesOrderDetails'])->name('invoices.sales-order-details');
    Route::post('invoices/convert-from-order/{orderId}', [SalesInvoiceController::class, 'convertFromOrder'])->name('invoices.convert-from-order');
    Route::get('invoices/{encodedId}/payment', [SalesInvoiceController::class, 'showPaymentForm'])->name('invoices.payment-form');
    Route::post('invoices/{encodedId}/payment', [SalesInvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
    Route::get('invoices/{encodedId}/export-pdf', [SalesInvoiceController::class, 'exportPdf'])->name('invoices.export-pdf');
    Route::get('invoices/{encodedId}/print', [SalesInvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::get('invoices/payment/{paymentId}/edit', [SalesInvoiceController::class, 'editPayment'])->name('invoices.payment.edit');
    Route::put('invoices/payment/{paymentId}', [SalesInvoiceController::class, 'updatePayment'])->name('invoices.payment.update');
    Route::get('invoices/receipt/{encodedId}/edit', [SalesInvoiceController::class, 'editReceipt'])->name('invoices.receipt.edit');
    Route::put('invoices/receipt/{encodedId}', [SalesInvoiceController::class, 'updateReceipt'])->name('invoices.receipt.update');
    Route::post('invoices/{encodedId}/reverse-payment', [SalesInvoiceController::class, 'reversePayment'])->name('invoices.reverse-payment');
    Route::delete('invoices/{encodedId}/payment', [SalesInvoiceController::class, 'deletePayment'])->name('invoices.delete-payment');
    Route::get('invoices/receipt/{encodedId}/print', [App\Http\Controllers\Sales\SalesInvoiceController::class, 'printReceipt'])->name('invoices.print-receipt');
    Route::delete('invoices/payment/{encodedId}', [SalesInvoiceController::class, 'deleteInvoicePayment'])->name('invoices.delete-invoice-payment');
    Route::get('invoices/customer/{customerId}/cash-deposits', [SalesInvoiceController::class, 'getCustomerCashDeposits'])->name('invoices.customer-cash-deposits');
    Route::post('invoices/{encodedId}/apply-late-fees', [SalesInvoiceController::class, 'applyLatePaymentFees'])->name('invoices.apply-late-fees');
});

// Credit info route moved to sales routes group above to avoid route conflicts

// Sales routes that require branch selection
Route::prefix('sales')->name('sales.')->middleware(['auth', 'company.scope', 'check.inventory.cost.method', 'require.branch'])->group(function () {
    // Opening Balances (Sales)
    Route::get('opening-balances', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'index'])->name('opening-balances.index');
    Route::get('opening-balances/create', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'create'])->name('opening-balances.create');
    Route::post('opening-balances', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'store'])->name('opening-balances.store');

    // Opening Balances Import (must be before parameterized routes)
    Route::get('opening-balances/import', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'import'])->name('opening-balances.import');
    Route::post('opening-balances/import', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'processImport'])->name('opening-balances.process-import');
    Route::get('opening-balances/template', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'downloadTemplate'])->name('opening-balances.download-template');

    // Parameterized routes (must be after specific routes)
    Route::get('opening-balances/{encodedId}', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'show'])->name('opening-balances.show');
    Route::get('opening-balances/{encodedId}/edit', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'edit'])->name('opening-balances.edit');
    Route::put('opening-balances/{encodedId}', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'update'])->name('opening-balances.update');
    Route::delete('opening-balances/{encodedId}', [\App\Http\Controllers\Sales\OpeningBalanceController::class, 'destroy'])->name('opening-balances.destroy');

    // Credit Note Routes
    Route::resource('credit-notes', CreditNoteController::class);
    Route::get('credit-notes/{encodedId}/pdf', [CreditNoteController::class, 'exportPdf'])->name('credit-notes.pdf');
    Route::post('credit-notes/{encodedId}/approve', [CreditNoteController::class, 'approve'])->name('credit-notes.approve');
    Route::post('credit-notes/{encodedId}/cancel', [CreditNoteController::class, 'cancel'])->name('credit-notes.cancel');
    Route::post('credit-notes/{encodedId}/apply', [CreditNoteController::class, 'apply'])->name('credit-notes.apply');
    Route::post('credit-notes/{encodedId}/apply-to-invoice', [CreditNoteController::class, 'applyToInvoice'])->name('credit-notes.apply-to-invoice');
    Route::post('credit-notes/{encodedId}/process-refund', [CreditNoteController::class, 'processRefund'])->name('credit-notes.process-refund');
    Route::get('credit-notes/customer/{customerId}/invoices', [CreditNoteController::class, 'getCustomerInvoices'])->name('credit-notes.customer-invoices');
    Route::get('credit-notes/customer/{customerId}/available-invoices', [CreditNoteController::class, 'getAvailableInvoices'])->name('credit-notes.available-invoices');
    Route::get('credit-notes/invoice/{invoiceId}/details', [CreditNoteController::class, 'getInvoiceDetails'])->name('credit-notes.invoice-details');
    Route::get('credit-notes/invoice/{invoiceId}/items', [CreditNoteController::class, 'getInvoiceItems'])->name('credit-notes.invoice-items');
    Route::get('credit-notes/item-details/{id}', [CreditNoteController::class, 'getInventoryItem'])->name('credit-notes.item-details');
    Route::get('credit-notes/statistics', [CreditNoteController::class, 'getStatistics'])->name('credit-notes.statistics');

    // Debug route for credit note testing
    Route::post('credit-notes/test-debug', [CreditNoteController::class, 'testDebug'])->name('credit-notes.test-debug');

    // Cash Sales Routes
    Route::get('cash-sales/customer/{customerId}/cash-deposits', [CashSaleController::class, 'getCustomerCashDeposits'])->name('cash-sales.customer-cash-deposits');
    Route::get('cash-sales/item-details/{id}', [CashSaleController::class, 'getInventoryItem'])->name('cash-sales.item-details');
    Route::get('cash-sales/{encodedId}/print', [CashSaleController::class, 'print'])->name('cash-sales.print');
    Route::resource('cash-sales', CashSaleController::class);

    // POS Sales Routes
    Route::get('pos', [PosSaleController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosSaleController::class, 'store'])->name('pos.store');
    Route::get('pos/list', [PosSaleController::class, 'list'])->name('pos.list');
    Route::get('pos/statistics', [PosSaleController::class, 'statistics'])->name('pos.statistics');
    Route::get('pos/{encodedId}', [PosSaleController::class, 'show'])->name('pos.show');
    Route::get('pos/{encodedId}/edit', [PosSaleController::class, 'edit'])->name('pos.edit');
    Route::put('pos/{encodedId}', [PosSaleController::class, 'update'])->name('pos.update');
    Route::get('pos/{encodedId}/receipt', [PosSaleController::class, 'printReceipt'])->name('pos.receipt');
    Route::delete('pos/{encodedId}/void', [PosSaleController::class, 'void'])->name('pos.void');
    Route::post('pos/item-details', [PosSaleController::class, 'getItemDetails'])->name('pos.item-details');

    // Legacy POS routes (for backward compatibility)
    Route::resource('pos-sales', PosSaleController::class);
    Route::get('pos-sales/item-details/{id}', [PosSaleController::class, 'getInventoryItem'])->name('pos-sales.item-details');
    Route::get('pos-sales/{encodedId}/print', [PosSaleController::class, 'print'])->name('pos-sales.print');
    Route::post('pos-sales/{encodedId}/mark-printed', [PosSaleController::class, 'markReceiptPrinted'])->name('pos-sales.mark-printed');

    // Delivery Routes
    Route::resource('deliveries', DeliveryController::class);
    Route::patch('deliveries/{id}/start-picking', [DeliveryController::class, 'startPicking'])->name('deliveries.start-picking');
    Route::patch('deliveries/{id}/complete-picking', [DeliveryController::class, 'completePicking'])->name('deliveries.complete-picking');
    Route::patch('deliveries/{id}/pick-all', [DeliveryController::class, 'pickAllItems'])->name('deliveries.pick-all');
    Route::patch('deliveries/items/{item}/pick', [DeliveryController::class, 'pickItem'])->name('deliveries.items.pick');
    Route::patch('deliveries/{id}/start-delivery', [DeliveryController::class, 'startDelivery'])->name('deliveries.start-delivery');
    Route::patch('deliveries/{id}/complete-delivery', [DeliveryController::class, 'completeDelivery'])->name('deliveries.complete-delivery');
    Route::patch('deliveries/{id}/pack-all', [DeliveryController::class, 'packAllItems'])->name('deliveries.pack-all');
    Route::patch('deliveries/items/{item}/pack', [DeliveryController::class, 'packItem'])->name('deliveries.items.pack');
    Route::patch('deliveries/{id}/deliver-all', [DeliveryController::class, 'deliverAllItems'])->name('deliveries.deliver-all');
    Route::patch('deliveries/items/{item}/deliver', [DeliveryController::class, 'deliverItem'])->name('deliveries.items.deliver');
    Route::get('deliveries/{id}/generate-note', [DeliveryController::class, 'generateDeliveryNote'])->name('deliveries.generate-note');
    Route::get('deliveries/{id}/note', [DeliveryController::class, 'showDeliveryNote'])->name('deliveries.note');
    Route::get('deliveries/{id}/note/pdf', [DeliveryController::class, 'downloadDeliveryNotePdf'])->name('deliveries.note.pdf');
    Route::get('deliveries/convert-from-order/{orderId}', [DeliveryController::class, 'convertFromOrder'])->name('deliveries.convert-from-order');
    Route::post('deliveries/{id}/convert-to-invoice', [DeliveryController::class, 'convertToInvoice'])->name('deliveries.convert-to-invoice');
});

Route::post('sms/bulk', [App\Http\Controllers\DashboardController::class, 'sendBulkSms'])->name('sms.bulk');
// Payment Voucher Approval Settings
Route::get('/payment-voucher-approval', [SettingsController::class, 'paymentVoucherApprovalSettings'])->name('settings.payment-voucher-approval');
Route::put('/payment-voucher-approval', [SettingsController::class, 'updatePaymentVoucherApprovalSettings'])->name('settings.payment-voucher-approval.update');

// Account Transfer Approval Settings
Route::get('/account-transfer-approval', [SettingsController::class, 'accountTransferApprovalSettings'])->name('settings.account-transfer-approval');
Route::put('/account-transfer-approval', [SettingsController::class, 'updateAccountTransferApprovalSettings'])->name('settings.account-transfer-approval.update');
Route::get('/provision-approval', [SettingsController::class, 'provisionApprovalSettings'])->name('settings.provision-approval');
Route::put('/provision-approval', [SettingsController::class, 'updateProvisionApprovalSettings'])->name('settings.provision-approval.update');

// Journal Entry Approval Settings
Route::get('/journal-entry-approval', [SettingsController::class, 'journalEntryApprovalSettings'])->name('settings.journal-entry-approval');
Route::put('/journal-entry-approval', [SettingsController::class, 'updateJournalEntryApprovalSettings'])->name('settings.journal-entry-approval.update');

// Investment Proposal Approval Settings
Route::get('/investment-proposal-approval', [SettingsController::class, 'investmentProposalApprovalSettings'])->name('settings.investment-proposal-approval');
Route::put('/investment-proposal-approval', [SettingsController::class, 'updateInvestmentProposalApprovalSettings'])->name('settings.investment-proposal-approval.update');

// Period-End Closing Routes
Route::prefix('period-closing')->name('settings.period-closing.')->group(function () {
    Route::get('/', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'index'])->name('index');
    Route::get('/fiscal-years', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'fiscalYears'])->name('fiscal-years');
    Route::get('/fiscal-years/data', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'fiscalYearsData'])->name('fiscal-years.data');
    Route::post('/fiscal-years', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'storeFiscalYear'])->name('fiscal-years.store');
    Route::get('/periods', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'periods'])->name('periods');
    Route::get('/fiscal-years/{fiscalYear}/periods', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'getPeriodsForFiscalYear'])->name('fiscal-years.periods');
    Route::get('/fiscal-years/{fiscalYear}/year-end-wizard', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'yearEndWizard'])->name('fiscal-years.year-end-wizard');
    Route::get('/fiscal-years/{fiscalYear}/period-status', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'getPeriodClosingStatus'])->name('fiscal-years.period-status');
    Route::get('/check-date', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'checkDateLock'])->name('check-date');

    Route::prefix('close-batch')->name('close-batch.')->group(function () {
        Route::get('/create/{period}', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'createCloseBatch'])->name('create');
        Route::post('/store/{period}', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'storeCloseBatch'])->name('store');
        Route::get('/{closeBatch}', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'showCloseBatch'])->name('show');
        Route::get('/{closeBatch}/snapshots/data', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'snapshotsData'])->name('snapshots.data');
        Route::post('/{closeBatch}/adjustments', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'addAdjustment'])->name('adjustments.add');
        Route::delete('/{closeBatch}/adjustments/{closeAdjustment}', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'deleteAdjustment'])->name('adjustments.destroy');
        Route::post('/{closeBatch}/submit-review', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{closeBatch}/approve', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'approve'])->name('approve');
        Route::post('/{closeBatch}/roll-retained-earnings', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'rollRetainedEarnings'])->name('roll-retained-earnings');
    });

    Route::post('/periods/{period}/reopen', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'reopenPeriod'])->name('periods.reopen');
    Route::get('/download-guide', [App\Http\Controllers\PeriodClosing\PeriodClosingController::class, 'downloadGuide'])->name('download-guide');
});

////////////////////////////////////////////// END SALES MANAGEMENT ///////////////////////////////////////////

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/')->with('success', 'You are successfully logout.');
})->middleware('auth');

// Exchange Rate API Routes
Route::prefix('api/exchange-rates')->middleware('throttle.api')->group(function () {
    Route::get('/rate', [App\Http\Controllers\Api\ExchangeRateController::class, 'getRate'])->name('api.exchange-rates.rate');
    Route::get('/convert', [App\Http\Controllers\Api\ExchangeRateController::class, 'convertAmount'])->name('api.exchange-rates.convert');
    Route::get('/history', [App\Http\Controllers\Api\ExchangeRateController::class, 'getHistory'])->name('api.exchange-rates.history');
    Route::get('/currencies', [App\Http\Controllers\Api\ExchangeRateController::class, 'getSupportedCurrencies'])->name('api.exchange-rates.currencies');
    Route::post('/clear-cache', [App\Http\Controllers\Api\ExchangeRateController::class, 'clearCache'])->name('api.exchange-rates.clear-cache');
});

// Currency Reports Routes
Route::prefix('reports/currency')->group(function () {
    Route::get('/', [App\Http\Controllers\Reports\Sales\CurrencyReportController::class, 'index'])->name('reports.currency.index');
    Route::get('/summary', [App\Http\Controllers\Reports\Sales\CurrencyReportController::class, 'summary'])->name('reports.currency.summary');
    Route::get('/comparison', [App\Http\Controllers\Reports\Sales\CurrencyReportController::class, 'comparison'])->name('reports.currency.comparison');
    Route::get('/exchange-rate-analysis', [App\Http\Controllers\Reports\Sales\CurrencyReportController::class, 'exchangeRateAnalysis'])->name('reports.currency.exchange-rate-analysis');
    Route::get('/export-pdf', [App\Http\Controllers\Reports\Sales\CurrencyReportController::class, 'exportPdf'])->name('reports.currency.export-pdf');
});

// Production Management Module Routes
Route::prefix('production')->name('production.')->middleware(['auth'])->group(function () {
    Route::resource('orders', App\Http\Controllers\Sales\SalesOrderController::class);
    Route::resource('batches', App\Http\Controllers\Production\ProductionBatchController::class)->names('batches');
    Route::resource('machines', App\Http\Controllers\Production\ProductionMachineController::class)->names('machines');

    // Work Orders for Sweater Production
    Route::resource('work-orders', App\Http\Controllers\Production\WorkOrderController::class)->names('work-orders');
    Route::post('work-orders/{encodedId}/advance-stage', [App\Http\Controllers\Production\WorkOrderController::class, 'advanceStage'])->name('work-orders.advance-stage');
    Route::post('work-orders/{encodedId}/issue-materials', [App\Http\Controllers\Production\WorkOrderController::class, 'issuesMaterials'])->name('work-orders.issue-materials');
    Route::post('work-orders/{encodedId}/record-production', [App\Http\Controllers\Production\WorkOrderController::class, 'recordProduction'])->name('work-orders.record-production');
    Route::post('work-orders/{encodedId}/quality-check', [App\Http\Controllers\Production\WorkOrderController::class, 'qualityCheck'])->name('work-orders.quality-check');
    Route::post('work-orders/{encodedId}/record-packaging', [App\Http\Controllers\Production\WorkOrderController::class, 'recordPackaging'])->name('work-orders.record-packaging');

    // Finished Goods Packaging (Standalone)
    Route::get('finished-goods-packaging', [App\Http\Controllers\Production\FinishedGoodsPackagingController::class, 'index'])->name('finished-goods-packaging.index');
    Route::post('finished-goods-packaging', [App\Http\Controllers\Production\FinishedGoodsPackagingController::class, 'store'])->name('finished-goods-packaging.store');
    Route::get('finished-goods-packaging/search-items', [App\Http\Controllers\Production\FinishedGoodsPackagingController::class, 'searchItems'])->name('finished-goods-packaging.search-items');

    // Assign order to batch
    Route::get('batches/{encodedId}/assign-order', [App\Http\Controllers\Production\ProductionBatchController::class, 'assignOrderForm'])->name('batches.assign-order');
    Route::post('batches/{encodedId}/assign-order', [App\Http\Controllers\Production\ProductionBatchController::class, 'assignOrder'])->name('batches.assign-order.store');
    // Update assigned order quantity
    Route::post('batches/{batchHashid}/assigned-orders/{orderHashid}/update', [App\Http\Controllers\Production\ProductionBatchController::class, 'updateAssignedOrder']);
    // Delete assigned order
    Route::post('batches/{batchHashid}/assigned-orders/{orderHashid}/delete', [App\Http\Controllers\Production\ProductionBatchController::class, 'deleteAssignedOrder']);
});

// Production Reports (fix for reports.production route used in side menu)
Route::get('/reports/production', function () {
    return redirect()->route('production.batches.index');
})->middleware(['auth'])->name('reports.production');

// Purchases Reports (fix for reports.purchases route used in side menu)
Route::get('/reports/purchases', function () {
    return redirect()->route('purchases.index');
})->middleware(['auth'])->name('reports.purchases');

// Opening Balances
Route::get('/inventory/opening-balances', [OpeningBalanceController::class, 'index'])
    ->middleware(['auth', 'require.branch'])
    ->name('inventory.opening-balances.index');
// Stock Movements
Route::get('/inventory/opening-balances', [OpeningBalanceController::class, 'index'])->middleware(['auth', 'require.branch'])
    ->name('inventory.opening-balances.index');
// Gracefully handle GET requests to import URL by redirecting to index
Route::get('/inventory/opening-balances/import', function () {
    return redirect()->route('inventory.opening-balances.index');
})->middleware(['auth', 'require.branch'])->name('inventory.opening-balances.import.get');
Route::get('/inventory/opening-balances/create', [OpeningBalanceController::class, 'create'])->name('inventory.opening-balances.create');
Route::post('/inventory/opening-balances', [OpeningBalanceController::class, 'store'])->name('inventory.opening-balances.store');
Route::post('/inventory/opening-balances/import', [OpeningBalanceController::class, 'import'])->name('inventory.opening-balances.import');
Route::get('/inventory/opening-balances/template', [OpeningBalanceController::class, 'downloadTemplate'])->name('inventory.opening-balances.template');
// Route::get('/inventory/opening-balances/{movement}', [OpeningBalanceController::class, 'show'])->name('movements.show');
// Route::get('/inventory/opening-balances/{movement}/edit', [OpeningBalanceController::class, 'edit'])->name('movements.edit');
// Route::put('/inventory/opening-balances/{movement}', [OpeningBalanceController::class, 'update'])->name('movements.update');
// Route::delete('/inventory/opening-balances/{movement}', [OpeningBalanceController::class, 'destroy'])->name('movements.destroy');

Route::prefix('production/batches')->group(function () {
    Route::get('{batch}/add-item', [ItemBatchController::class, 'create'])->name('production.batches.add-item');
    Route::post('{batch}/add-item', [ItemBatchController::class, 'store'])->name('production.batches.add-item.store');
    Route::delete('item-batch/{id}/delete', [ItemBatchController::class, 'destroy'])->name('production.batches.item-batch.delete');
});

// API Routes for dynamic data
Route::prefix('api')->middleware(['auth', 'throttle.api'])->group(function () {
    // Password strength API
    Route::post('/password-strength', [\App\Http\Controllers\Api\PasswordStrengthController::class, 'calculateStrength']);
    Route::get('/customers-by-branch/{branchId}', function ($branchId) {
        $customers = \App\Models\Customer::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        return response()->json($customers);
    });

    Route::get('/customers', function (Request $request) {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $request->get('branch_id') ?? session('branch_id') ?? $user->branch_id;

        $query = \App\Models\Customer::where('company_id', $companyId);
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $customers = $query->orderBy('name')
            ->get(['id', 'name', 'customerNo', 'phone', 'email'])
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'customer_no' => $customer->customerNo, // Map customerNo to customer_no for frontend
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ];
            });
        return response()->json($customers);
    });

    Route::get('/suppliers', function (Request $request) {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $request->get('branch_id') ?? session('branch_id') ?? $user->branch_id;

        $query = \App\Models\Supplier::where('company_id', $companyId);
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $suppliers = $query->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
        return response()->json($suppliers);
    });

    Route::get('/salesperson-invoices/{salespersonId}', function ($salespersonId, Request $request) {
        try {
            \Log::info('API Request:', [
                'salespersonId' => $salespersonId,
                'user_id' => auth()->id(),
                'request_params' => $request->all()
            ]);

            $dateFrom = $request->get('date_from', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Get user's assigned branches
            $assignedBranches = auth()->user()->branches()->get();
            $defaultBranchId = $assignedBranches->count() > 1 ? 'all' : ($assignedBranches->first()->id ?? null);
            $branchId = $request->get('branch_id', $defaultBranchId);

            // Parse dates
            $dateFrom = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $dateTo = \Carbon\Carbon::parse($dateTo)->endOfDay();

            \Log::info('Query Parameters:', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'branchId' => $branchId,
                'salespersonId' => $salespersonId
            ]);

            $query = \App\Models\Sales\SalesInvoice::with(['customer'])
                ->where('created_by', $salespersonId)
                ->whereBetween('invoice_date', [$dateFrom, $dateTo])
                ->where('status', '!=', 'cancelled');

            // Apply assigned branch filtering
            $user = auth()->user();
            $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();

            if ($branchId === 'all') {
                if (!empty($assignedBranchIds)) {
                    $query->whereIn('branch_id', $assignedBranchIds);
                } else {
                    $query->whereRaw('1 = 0'); // No assigned branches, return empty
                }
            } else {
                // Ensure the selected branch is in the user's assigned branches
                if (in_array($branchId, $assignedBranchIds)) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0'); // Branch not assigned, return empty
                }
            }

            $invoices = $query->select(['id', 'invoice_number', 'customer_id', 'invoice_date', 'status', 'total_amount', 'paid_amount', 'balance_due', 'branch_id', 'created_by'])
                ->orderBy('invoice_date', 'desc')
                ->get()
                ->map(function ($invoice) {
                    return (object)[
                        'type' => 'invoice',
                        'id' => $invoice->id,
                        'number' => $invoice->invoice_number,
                        'customer_name' => $invoice->customer->name ?? 'N/A',
                        'date' => $invoice->invoice_date,
                        'status' => $invoice->status,
                        'total_amount' => (float)$invoice->total_amount,
                        'paid_amount' => (float)($invoice->paid_amount ?? 0),
                        'balance_due' => (float)($invoice->balance_due ?? 0),
                        'encoded_id' => \Vinkla\Hashids\Facades\Hashids::encode($invoice->id),
                    ];
                });

            // POS Sales by operator/creator
            $posQuery = \App\Models\Sales\PosSale::with(['customer'])
                ->where(function ($q) use ($salespersonId) {
                    $q->where('operator_id', $salespersonId)
                      ->orWhere('created_by', $salespersonId);
                })
                ->whereBetween('sale_date', [$dateFrom, $dateTo]);
            if ($branchId === 'all') {
                if (!empty($assignedBranchIds)) {
                    $posQuery->whereIn('branch_id', $assignedBranchIds);
                } else {
                    $posQuery->whereRaw('1 = 0');
                }
            } else {
                if (in_array($branchId, $assignedBranchIds)) {
                    $posQuery->where('branch_id', $branchId);
                } else {
                    $posQuery->whereRaw('1 = 0');
                }
            }
            $posSales = $posQuery->select(['id','pos_number','customer_id','sale_date','total_amount','branch_id'])
                ->orderBy('sale_date', 'desc')
                ->get()
                ->map(function ($pos) {
                    return (object)[
                        'type' => 'pos',
                        'id' => $pos->id,
                        'number' => $pos->pos_number,
                        'customer_name' => $pos->customer->name ?? 'N/A',
                        'date' => $pos->sale_date,
                        'status' => 'paid',
                        'total_amount' => (float)$pos->total_amount,
                        'paid_amount' => (float)$pos->total_amount,
                        'balance_due' => 0.0,
                        'encoded_id' => \Vinkla\Hashids\Facades\Hashids::encode($pos->id),
                    ];
                });

            // Cash Sales by creator
            $cashQuery = \App\Models\Sales\CashSale::with(['customer'])
                ->where('created_by', $salespersonId)
                ->whereBetween('sale_date', [$dateFrom, $dateTo]);
            if ($branchId === 'all') {
                if (!empty($assignedBranchIds)) {
                    $cashQuery->whereIn('branch_id', $assignedBranchIds);
                } else {
                    $cashQuery->whereRaw('1 = 0');
                }
            } else {
                if (in_array($branchId, $assignedBranchIds)) {
                    $cashQuery->where('branch_id', $branchId);
                } else {
                    $cashQuery->whereRaw('1 = 0');
                }
            }
            $cashSales = $cashQuery->select(['id','sale_number','customer_id','sale_date','total_amount','paid_amount','branch_id'])
                ->orderBy('sale_date', 'desc')
                ->get()
                ->map(function ($cs) {
                    $paid = (float)($cs->paid_amount ?? $cs->total_amount);
                    return (object)[
                        'type' => 'cash_sale',
                        'id' => $cs->id,
                        'number' => $cs->sale_number,
                        'customer_name' => $cs->customer->name ?? 'N/A',
                        'date' => $cs->sale_date,
                        'status' => 'paid',
                        'total_amount' => (float)$cs->total_amount,
                        'paid_amount' => $paid,
                        'balance_due' => max(0.0, (float)$cs->total_amount - $paid),
                        'encoded_id' => \Vinkla\Hashids\Facades\Hashids::encode($cs->id),
                    ];
                });

            \Log::info('Query Results:', [
                'invoices_count' => $invoices->count(),
                'invoices' => $invoices->toArray()
            ]);

            // Merge all documents
            $documents = $invoices->merge($posSales)->merge($cashSales)
                ->sortByDesc('date')
                ->values();

            return response()->json([
                'success' => true,
                'invoices' => $documents
            ]);
        } catch (\Exception $e) {
            \Log::error('API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'salespersonId' => $salespersonId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching invoices: ' . $e->getMessage()
            ], 500);
        }
    });
});

// Hotel & Property Management Routes
Route::prefix('hotel')->name('hotel.')->group(function () {
    Route::get('/management', [App\Http\Controllers\Hotel\HotelManagementController::class, 'index'])->name('management.index');
    Route::get('/property/settings', [App\Http\Controllers\Hotel\PropertySettingsController::class, 'index'])->name('property.settings');
    Route::post('/property/settings', [App\Http\Controllers\Hotel\PropertySettingsController::class, 'update'])->name('property.settings.update');

    // Hotel Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [App\Http\Controllers\Hotel\HotelExpenseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Hotel\HotelExpenseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Hotel\HotelExpenseController::class, 'store'])->name('store');
    });
});

Route::prefix('real-estate')->name('real.estate.')->group(function () {
    Route::get('/', [App\Http\Controllers\Property\RealEstateController::class, 'index'])->name('index');
});

// Property Management Routes
Route::prefix('properties')->name('properties.')->group(function () {
    Route::get('/', [App\Http\Controllers\Hotel\PropertyController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Hotel\PropertyController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Hotel\PropertyController::class, 'store'])->name('store');
    Route::get('/{property}', [App\Http\Controllers\Hotel\PropertyController::class, 'show'])->name('show');
    Route::get('/{property}/edit', [App\Http\Controllers\Hotel\PropertyController::class, 'edit'])->name('edit');
    Route::put('/{property}', [App\Http\Controllers\Hotel\PropertyController::class, 'update'])->name('update');
    Route::delete('/{property}', [App\Http\Controllers\Hotel\PropertyController::class, 'destroy'])->name('destroy');
});

Route::resource('cash_collateral_types', CashCollateralTypeController::class)->middleware('auth');

    ////////////////////////////////////////////// CASHCOLLATERALS MANAGEMENT ///////////////////////////////////////////

Route::middleware(['auth'])->prefix('cash_collaterals')->group(function () {
    Route::get('/', [CashCollateralController::class, 'index'])->name('cash_collaterals.index');
    Route::get('/datatable', [CashCollateralController::class, 'getDataTable'])->name('cash_collaterals.datatable');
    Route::get('/create', [CashCollateralController::class, 'create'])->name('cash_collaterals.create');
    Route::post('/', [CashCollateralController::class, 'store'])->name('cash_collaterals.store');
    Route::get('/{cashcollateral}', [CashCollateralController::class, 'show'])->name('cash_collaterals.show');
    Route::get('/{cashcollateral}/statement-pdf', [CashCollateralController::class, 'exportStatementPdf'])->name('cash_collaterals.statement-pdf');
    Route::get('/{cashcollateral}/edit', [CashCollateralController::class, 'edit'])->name('cash_collaterals.edit');
    Route::put('/{cashcollateral}', [CashCollateralController::class, 'update'])->name('cash_collaterals.update');
    Route::delete('/{cashcollateral}', [CashCollateralController::class, 'destroy'])->name('cash_collaterals.destroy');


    // Direct Receipt and Payment Routes for Cash Collateral
    Route::get('/receipts/{receipt}/edit', [CashCollateralController::class, 'editReceipt'])->name('receipts.edit');
    Route::put('/receipts/{receipt}', [CashCollateralController::class, 'updateReceipt'])->name('receipts.update');
    Route::delete('/receipts/{receipt}', [CashCollateralController::class, 'deleteReceipt'])->name('receipts.destroy');

    Route::get('/payments/{payment}/edit', [CashCollateralController::class, 'editPayment'])->name('payments.edit');
    Route::put('/payments/{payment}', [CashCollateralController::class, 'updatePayment'])->name('payments.update');
    Route::delete('/payments/{payment}', [CashCollateralController::class, 'deletePayment'])->name('payments.destroy');

    // Deposit and Withdrawal routes
    Route::get('/{cashcollateral}/deposit', [CashCollateralController::class, 'deposit'])->name('cash_collaterals.deposit');
    Route::post('/deposit-store', [CashCollateralController::class, 'depositStore'])->name('cash_collaterals.depositStore');
    Route::get('/print-deposit-receipt/{id}', [CashCollateralController::class, 'printDepositReceipt'])->name('cash_collaterals.printDepositReceipt');
    Route::get('/{cashcollateral}/withdraw', [CashCollateralController::class, 'withdraw'])->name('cash_collaterals.withdraw');
    Route::post('/withdraw-store', [CashCollateralController::class, 'withdrawStore'])->name('cash_collaterals.withdrawStore');
    Route::get('/print-withdrawal-receipt/{id}', [CashCollateralController::class, 'printWithdrawalReceipt'])->name('cash_collaterals.printWithdrawalReceipt');

    // Transaction delete routes
    Route::delete('/delete-deposit/{receiptId}', [CashCollateralController::class, 'deleteDeposit'])->name('cash_collaterals.deleteDeposit');
    Route::delete('/delete-withdrawal/{paymentId}', [CashCollateralController::class, 'deleteWithdrawal'])->name('cash_collaterals.deleteWithdrawal');
});

////////////////////////////////////////////// END CASHCOLLATERALS  MANAGEMENT ///////////////////////////////////////////

// Room Management Routes
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [App\Http\Controllers\Hotel\RoomController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Hotel\RoomController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Hotel\RoomController::class, 'store'])->name('store');
    Route::get('/{room}', [App\Http\Controllers\Hotel\RoomController::class, 'show'])->name('show');
    Route::get('/{room}/edit', [App\Http\Controllers\Hotel\RoomController::class, 'edit'])->name('edit');
    Route::put('/{room}', [App\Http\Controllers\Hotel\RoomController::class, 'update'])->name('update');
    Route::delete('/{room}', [App\Http\Controllers\Hotel\RoomController::class, 'destroy'])->name('destroy');
});

// Booking Management Routes
Route::prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', [App\Http\Controllers\Hotel\BookingController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Hotel\BookingController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Hotel\BookingController::class, 'store'])->name('store');

    // Availability utilities MUST come before parameterized {booking} routes
    Route::get('/check-availability', [App\Http\Controllers\Hotel\BookingController::class, 'checkAvailability'])->name('check-availability');
    Route::get('/available-rooms', [App\Http\Controllers\Hotel\BookingController::class, 'availableRooms'])->name('available-rooms');
    Route::get('/{booking}', [App\Http\Controllers\Hotel\BookingController::class, 'show'])->name('show');
    Route::get('/{booking}/edit', [App\Http\Controllers\Hotel\BookingController::class, 'edit'])->name('edit');
    Route::put('/{booking}', [App\Http\Controllers\Hotel\BookingController::class, 'update'])->name('update');
    Route::delete('/{booking}', [App\Http\Controllers\Hotel\BookingController::class, 'destroy'])->name('destroy');

    // Booking actions
    Route::post('/{booking}/check-in', [App\Http\Controllers\Hotel\BookingController::class, 'checkIn'])->name('check-in');
    Route::post('/{booking}/check-out', [App\Http\Controllers\Hotel\BookingController::class, 'checkOut'])->name('check-out');
    Route::post('/{booking}/confirm', [App\Http\Controllers\Hotel\BookingController::class, 'confirm'])->name('confirm');
    Route::post('/{booking}/cancel', [App\Http\Controllers\Hotel\BookingController::class, 'cancel'])->name('cancel');
    Route::post('/{booking}/record-payment', [App\Http\Controllers\Hotel\BookingController::class, 'recordPayment'])->name('record-payment');
    Route::get('/{booking}/export-pdf', [App\Http\Controllers\Hotel\BookingController::class, 'exportPdf'])->name('export-pdf');

    // Booking receipt routes
    Route::get('/receipts/{receipt}/edit', [App\Http\Controllers\Hotel\BookingController::class, 'editReceipt'])->name('receipts.edit');
    Route::get('/receipts/{receipt}/print', [App\Http\Controllers\Hotel\BookingController::class, 'printReceipt'])->name('receipts.print');
    // Fallback show route for receipts if within hotel group
    Route::put('/receipts/{receipt}', [App\Http\Controllers\Hotel\BookingController::class, 'updateReceipt'])->name('receipts.update');
    Route::delete('/receipts/{receipt}', [App\Http\Controllers\Hotel\BookingController::class, 'deleteReceipt'])->name('receipts.destroy');
});

// Guest Management Routes
Route::prefix('guests')->name('guests.')->group(function () {
    Route::get('/', [App\Http\Controllers\Hotel\GuestController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Hotel\GuestController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Hotel\GuestController::class, 'store'])->name('store');
    Route::get('/{guest}', [App\Http\Controllers\Hotel\GuestController::class, 'show'])->name('show');
    Route::get('/{guest}/edit', [App\Http\Controllers\Hotel\GuestController::class, 'edit'])->name('edit');
    Route::put('/{guest}', [App\Http\Controllers\Hotel\GuestController::class, 'update'])->name('update');
    Route::delete('/{guest}', [App\Http\Controllers\Hotel\GuestController::class, 'destroy'])->name('destroy');
});

// Lease Management Routes
Route::prefix('leases')->name('leases.')->group(function () {
    Route::get('/', [App\Http\Controllers\Property\LeaseController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Property\LeaseController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Property\LeaseController::class, 'store'])->name('store');
    Route::get('/{id}', function ($id) {
        return view('property.leases.show', compact('id'));
    })->name('show');
    Route::get('/{id}/edit', function ($id) {
        return view('property.leases.edit', compact('id'));
    })->name('edit');
});

// Tenant Management Routes (for future CRUD operations)
Route::prefix('tenants')->name('tenants.')->group(function () {
    Route::get('/', [App\Http\Controllers\Property\TenantController::class, 'index'])->name('index');
    Route::get('/create', function () {
        return view('property.tenants.create');
    })->name('create');
    Route::get('/{id}', function ($id) {
        return view('property.tenants.show', compact('id'));
    })->name('show');
    Route::get('/{id}/edit', function ($id) {
        return view('property.tenants.edit', compact('id'));
    })->name('edit');
});

// Remove Hotel Reports menu usage (replaced by Hotel Expenses)

// Property Reports Routes (for future implementation)
Route::prefix('property-reports')->name('property.reports.')->group(function () {
    Route::get('/', function () {
        return view('property.reports.index');
    })->name('index');
});

// Imprest Management Routes
Route::prefix('imprest')->name('imprest.')->middleware('auth')->group(function () {
    // Main dashboard
    Route::get('/', [App\Http\Controllers\ImprestController::class, 'index'])->name('index');

    // Imprest Requests (Full CRUD)
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestController::class, 'requests'])->name('index');
        Route::get('/create', [App\Http\Controllers\ImprestController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ImprestController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\ImprestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\ImprestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\ImprestController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\ImprestController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [App\Http\Controllers\ImprestController::class, 'print'])->name('print');
    });

    // Manager Check Actions
    Route::prefix('checked')->name('checked.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestController::class, 'checkedRequests'])->name('index');
        Route::post('/{id}/check', [App\Http\Controllers\ImprestActionController::class, 'check'])->name('check');
    });

    // Finance Approval Actions
    Route::prefix('approved')->name('approved.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestController::class, 'approvedRequests'])->name('index');
        Route::post('/{id}/approve', [App\Http\Controllers\ImprestActionController::class, 'approve'])->name('approve');
    });

    // Disbursement Actions
    Route::prefix('disbursed')->name('disbursed.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestController::class, 'disbursedRequests'])->name('index');
        Route::get('/{id}/disburse', [App\Http\Controllers\ImprestActionController::class, 'showDisburseForm'])->name('disburse.form');
        Route::post('/{id}/disburse', [App\Http\Controllers\ImprestActionController::class, 'disburse'])->name('disburse');
    });

    // Retirement Routes (replaces liquidation)
    Route::prefix('retirement')->name('retirement.')->group(function () {
        Route::get('/', [App\Http\Controllers\RetirementController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\RetirementController::class, 'data'])->name('data');
        Route::get('/create/{imprestRequestId}', [App\Http\Controllers\RetirementController::class, 'create'])->name('create');
        Route::post('/store/{imprestRequestId}', [App\Http\Controllers\RetirementController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\RetirementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\RetirementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\RetirementController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\RetirementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/check', [App\Http\Controllers\RetirementActionController::class, 'check'])->name('check');
        Route::post('/{id}/approve', [App\Http\Controllers\RetirementActionController::class, 'approve'])->name('approve');
        Route::post('/{id}/create-journal', [App\Http\Controllers\RetirementActionController::class, 'createJournal'])->name('create-journal');
    });

    // Closed Imprests
    Route::get('/closed', [App\Http\Controllers\ImprestController::class, 'closedRequests'])->name('closed.index');

    // Close Imprest
    Route::post('/{id}/close', [App\Http\Controllers\ImprestActionController::class, 'close'])->name('close');

    // Multi-level Approval Settings
    Route::prefix('multi-approval-settings')->name('multi-approval-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestApprovalSettingsController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ImprestApprovalSettingsController::class, 'store'])->name('store');
        Route::get('/users-by-branch', [App\Http\Controllers\ImprestApprovalSettingsController::class, 'getUsersByBranch'])->name('users-by-branch');
    });

    // Multi-level Approval Actions
    Route::prefix('multi-approvals')->name('multi-approvals.')->group(function () {
        Route::get('/pending', [App\Http\Controllers\ImprestMultiApprovalController::class, 'pendingApprovals'])->name('pending');
        Route::post('/{approvalId}/approve', [App\Http\Controllers\ImprestMultiApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approvalId}/reject', [App\Http\Controllers\ImprestMultiApprovalController::class, 'reject'])->name('reject');
        Route::get('/{requestId}/history', [App\Http\Controllers\ImprestMultiApprovalController::class, 'approvalHistory'])->name('history');
    });

    // Retirement Multi-level Approval Settings
    Route::prefix('retirement-approval-settings')->name('retirement-approval-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\RetirementApprovalSettingsController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\RetirementApprovalSettingsController::class, 'store'])->name('store');
        Route::get('/users-by-company', [App\Http\Controllers\RetirementApprovalSettingsController::class, 'getUsersByCompany'])->name('users-by-company');
    });

    // Retirement Multi-level Approval Actions
    Route::prefix('retirement-multi-approvals')->name('retirement-multi-approvals.')->group(function () {
        Route::get('/pending', [App\Http\Controllers\RetirementMultiApprovalController::class, 'pending'])->name('pending');
        Route::post('/{approvalId}/approve', [App\Http\Controllers\RetirementMultiApprovalController::class, 'approve'])->name('approve');
        Route::post('/{approvalId}/reject', [App\Http\Controllers\RetirementMultiApprovalController::class, 'reject'])->name('reject');
        Route::get('/{requestId}/history', [App\Http\Controllers\RetirementMultiApprovalController::class, 'approvalHistory'])->name('history');
    });

    // Approval Settings (Full CRUD) - Legacy
    Route::prefix('approval-settings')->name('approval-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\ImprestApprovalSettingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ImprestApprovalSettingController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ImprestApprovalSettingController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\ImprestApprovalSettingController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\ImprestApprovalSettingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\ImprestApprovalSettingController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\ImprestApprovalSettingController::class, 'destroy'])->name('destroy');
    });

    // Imprest System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::post('/', [App\Http\Controllers\ImprestController::class, 'storeSettings'])->name('store');
    });

    // Budget Validation
    Route::post('/validate-budget', [App\Http\Controllers\ImprestController::class, 'validateBudget'])->name('validate-budget');
});

// Store Requisition Management Routes
Route::prefix('store-requisitions')->name('store-requisitions.')->middleware('auth')->group(function () {
    // Main dashboard
    Route::get('/', [App\Http\Controllers\StoreRequisitionController::class, 'index'])->name('index');

    // Store Requisitions (Full CRUD)
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [App\Http\Controllers\StoreRequisitionController::class, 'requisitions'])->name('index');
        Route::get('/create', [App\Http\Controllers\StoreRequisitionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\StoreRequisitionController::class, 'store'])->name('store');
        Route::get('/{storeRequisition}', [App\Http\Controllers\StoreRequisitionController::class, 'show'])->name('show');
        Route::get('/{storeRequisition}/edit', [App\Http\Controllers\StoreRequisitionController::class, 'edit'])->name('edit');
        Route::put('/{storeRequisition}', [App\Http\Controllers\StoreRequisitionController::class, 'update'])->name('update');
        Route::delete('/{storeRequisition}', [App\Http\Controllers\StoreRequisitionController::class, 'destroy'])->name('destroy');
    });

    // Store Requisition Actions
    Route::prefix('actions')->name('actions.')->group(function () {
        Route::post('/{id}/approve', [App\Http\Controllers\StoreRequisitionActionController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [App\Http\Controllers\StoreRequisitionActionController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/return', [App\Http\Controllers\StoreRequisitionActionController::class, 'return'])->name('return');
        Route::post('/{id}/resubmit', [App\Http\Controllers\StoreRequisitionActionController::class, 'resubmit'])->name('resubmit');
        Route::get('/{id}/approval-history', [App\Http\Controllers\StoreRequisitionActionController::class, 'getApprovalHistory'])->name('approval-history');
    });

    // Approval Settings
    Route::prefix('approval-settings')->name('approval-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'store'])->name('store');
        Route::get('/summary', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'getSummary'])->name('summary');
        Route::post('/reset', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'reset'])->name('reset');
        Route::get('/users-by-role', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'getUsersByRole'])->name('users-by-role');
        Route::get('/test-configuration', [App\Http\Controllers\StoreRequisitionApprovalSettingsController::class, 'testConfiguration'])->name('test-configuration');
    });

    // API Routes for AJAX calls
    Route::get('/approved', [App\Http\Controllers\StoreRequisitionController::class, 'getApprovedRequisitions'])->name('approved');
    Route::get('/items/{requisitionId}', [App\Http\Controllers\StoreRequisitionController::class, 'getItems'])->name('items');
});

// Inventory API Routes for Store Issues
Route::prefix('inventory')->name('inventory.')->middleware(['auth', 'throttle.api'])->group(function () {
    Route::get('/locations/user-assigned', [App\Http\Controllers\StoreIssueController::class, 'getUserAssignedLocations'])->name('locations.user-assigned');
    Route::get('/stock/item-location', [App\Http\Controllers\StoreIssueController::class, 'getItemStockAtLocation'])->name('stock.item-location');
});

// Store Issues Management Routes
Route::prefix('store-issues')->name('store-issues.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\StoreIssueController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\StoreIssueController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\StoreIssueController::class, 'store'])->name('store');
    Route::post('/test', [App\Http\Controllers\StoreIssueController::class, 'test'])->name('test');

    // AJAX API Routes (must be before parameterized routes)
    Route::get('/statistics', [App\Http\Controllers\StoreIssueController::class, 'getStatistics'])->name('statistics');

    Route::get('/{storeIssue}', [App\Http\Controllers\StoreIssueController::class, 'show'])->name('show');
    Route::get('/{storeIssue}/edit', [App\Http\Controllers\StoreIssueController::class, 'edit'])->name('edit');
    Route::put('/{storeIssue}', [App\Http\Controllers\StoreIssueController::class, 'update'])->name('update');
    Route::post('/{storeIssue}/cancel', [App\Http\Controllers\StoreIssueController::class, 'cancel'])->name('cancel');
});

// Store Returns Management Routes
Route::prefix('store-returns')->name('store-returns.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\StoreReturnController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\StoreReturnController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\StoreReturnController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\StoreReturnController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\StoreReturnController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\StoreReturnController::class, 'update'])->name('update');
    Route::post('/{id}/process', [App\Http\Controllers\StoreReturnController::class, 'process'])->name('process');
    Route::post('/{id}/cancel', [App\Http\Controllers\StoreReturnController::class, 'cancel'])->name('cancel');
});

// Fallback Assets route to avoid missing route errors from legacy links
Route::get('/asset-management', function () {
    return view('assets.index');
})->name('assets.index')->middleware(['auth', 'company.scope', 'require.branch']);

// Asset settings
Route::prefix('asset-management')->name('assets.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    // Movements / Transfers
    Route::get('/movements', [App\Http\Controllers\Asset\AssetMovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/data', [App\Http\Controllers\Asset\AssetMovementController::class, 'data'])->name('movements.data');
    Route::get('/movements/create', [App\Http\Controllers\Asset\AssetMovementController::class, 'create'])->name('movements.create');
    Route::post('/movements', [App\Http\Controllers\Asset\AssetMovementController::class, 'store'])->name('movements.store');
    // Specific routes must come before the generic {id} route
    Route::post('/movements/{id}/approve', [App\Http\Controllers\Asset\AssetMovementController::class, 'approve'])->name('movements.approve');
    Route::post('/movements/{id}/complete', [App\Http\Controllers\Asset\AssetMovementController::class, 'complete'])->name('movements.complete');
    Route::post('/movements/{id}/reject', [App\Http\Controllers\Asset\AssetMovementController::class, 'reject'])->name('movements.reject');
    Route::get('/movements/{id}', [App\Http\Controllers\Asset\AssetMovementController::class, 'show'])->name('movements.show');

    // Lookups
    Route::get('/movements/lookup/departments', [App\Http\Controllers\Asset\AssetMovementController::class, 'departmentsByBranch'])->name('movements.lookup.departments');
    Route::get('/movements/lookup/users', [App\Http\Controllers\Asset\AssetMovementController::class, 'usersByBranch'])->name('movements.lookup.users');
    Route::get('/movements/lookup/asset-details', [App\Http\Controllers\Asset\AssetMovementController::class, 'assetDetails'])->name('movements.lookup.asset-details');
    Route::get('/settings', [App\Http\Controllers\AssetsController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\AssetsController::class, 'updateSettings'])->name('settings.update');
    // Categories
    Route::get('/categories', [App\Http\Controllers\Asset\AssetCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [App\Http\Controllers\Asset\AssetCategoryController::class, 'data'])->name('categories.data');
    Route::get('/categories/create', [App\Http\Controllers\Asset\AssetCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [App\Http\Controllers\Asset\AssetCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [App\Http\Controllers\Asset\AssetCategoryController::class, 'edit'])->name('categories.edit');
    Route::get('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\Asset\AssetCategoryController::class, 'destroy'])->name('categories.destroy');

    // Registry
    Route::get('/registry', [App\Http\Controllers\Asset\AssetRegistryController::class, 'index'])->name('registry.index');
    Route::get('/registry/data', [App\Http\Controllers\Asset\AssetRegistryController::class, 'data'])->name('registry.data');
    Route::get('/registry/create', [App\Http\Controllers\Asset\AssetRegistryController::class, 'create'])->name('registry.create');
    Route::post('/registry', [App\Http\Controllers\Asset\AssetRegistryController::class, 'store'])->name('registry.store');
    Route::post('/registry/import', [App\Http\Controllers\Asset\AssetRegistryController::class, 'import'])->name('registry.import');
    Route::get('/registry/download-template', [App\Http\Controllers\Asset\AssetRegistryController::class, 'downloadTemplate'])->name('registry.download-template');
    Route::get('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'show'])->name('registry.show');
    Route::get('/registry/{id}/depreciation-history', [App\Http\Controllers\Asset\AssetRegistryController::class, 'depreciationHistory'])->name('registry.depreciation-history');
    Route::get('/registry/{id}/depreciation-history/data', [App\Http\Controllers\Asset\AssetRegistryController::class, 'depreciationHistoryData'])->name('registry.depreciation-history-data');
    Route::get('/registry/{id}/edit', [App\Http\Controllers\Asset\AssetRegistryController::class, 'edit'])->name('registry.edit');
    Route::put('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'update'])->name('registry.update');
    Route::delete('/registry/{id}', [App\Http\Controllers\Asset\AssetRegistryController::class, 'destroy'])->name('registry.destroy');

    // Opening Assets
    Route::get('/openings', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'index'])->name('openings.index');
    Route::get('/openings/data', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'data'])->name('openings.data');
    Route::get('/openings/create', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'create'])->name('openings.create');
    Route::post('/openings', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'store'])->name('openings.store');
    Route::post('/openings/import', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'import'])->name('openings.import');
    Route::get('/openings/download-template', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'downloadTemplate'])->name('openings.download-template');
    Route::get('/openings/{id}', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'show'])->name('openings.show');
    Route::delete('/openings/{id}', [App\Http\Controllers\Asset\OpeningAssetsController::class, 'destroy'])->name('openings.destroy');

    // Depreciation Management
    Route::get('/depreciation', [App\Http\Controllers\Asset\DepreciationController::class, 'index'])->name('depreciation.index');
    Route::post('/depreciation/process', [App\Http\Controllers\Asset\DepreciationController::class, 'process'])->name('depreciation.process');
    Route::get('/depreciation/history', [App\Http\Controllers\Asset\DepreciationController::class, 'history'])->name('depreciation.history');
    Route::get('/depreciation/history/data', [App\Http\Controllers\Asset\DepreciationController::class, 'historyData'])->name('depreciation.history.data');
    Route::get('/depreciation/forecast/{id}', [App\Http\Controllers\Asset\DepreciationController::class, 'forecast'])->name('depreciation.forecast');

    // Revaluation & Impairment Settings
    Route::get('/revaluations/settings', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'index'])->name('revaluations.settings');
    Route::put('/revaluations/settings/category/{id}', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'updateCategory'])->name('revaluations.settings.update-category');
    Route::post('/revaluations/settings/bulk-update', [App\Http\Controllers\Assets\RevaluationSettingsController::class, 'updateBulk'])->name('revaluations.settings.bulk-update');

    // Revaluation & Impairment Management
    Route::prefix('revaluations')->name('revaluations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'update'])->name('update');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'postToGL'])->name('post-gl');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'destroy'])->name('destroy');

        // Batch operations
        Route::get('/batch/{id}', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'showBatch'])->name('batch.show');
        Route::post('/batch/{id}/submit', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'submitBatchForApproval'])->name('batch.submit');
        Route::post('/batch/{id}/approve', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'approveBatch'])->name('batch.approve');
        Route::post('/batch/{id}/reject', [App\Http\Controllers\Assets\AssetRevaluationController::class, 'rejectBatch'])->name('batch.reject');
    });

    Route::prefix('impairments')->name('impairments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'show'])->name('show');
        Route::get('/{id}/create-reversal', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'createReversal'])->name('create-reversal');
        Route::post('/{id}/reversal', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'storeReversal'])->name('store-reversal');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'postToGL'])->name('post-gl');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetImpairmentController::class, 'destroy'])->name('destroy');
    });

    // Asset Disposal Management
    Route::prefix('disposals')->name('disposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Assets\AssetDisposalController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Assets\AssetDisposalController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Assets\AssetDisposalController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Assets\AssetDisposalController::class, 'store'])->name('store');

        // Disposal Reason Codes Management - MUST come before /{id} route
        Route::prefix('reason-codes')->name('reason-codes.')->group(function () {
            Route::get('/', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Assets\DisposalReasonCodeController::class, 'destroy'])->name('destroy');
        });

        // Specific routes must come before the generic {id} route
        Route::get('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Assets\AssetDisposalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'update'])->name('update');
        Route::post('/{id}/submit', [App\Http\Controllers\Assets\AssetDisposalController::class, 'submitForApproval'])->name('submit');
        Route::post('/{id}/approve', [App\Http\Controllers\Assets\AssetDisposalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Assets\AssetDisposalController::class, 'reject'])->name('reject');
        Route::post('/{id}/post-gl', [App\Http\Controllers\Assets\AssetDisposalController::class, 'postToGL'])->name('post-gl');
        Route::post('/{id}/record-receivable', [App\Http\Controllers\Assets\AssetDisposalController::class, 'recordReceivable'])->name('record-receivable');
        Route::delete('/{id}', [App\Http\Controllers\Assets\AssetDisposalController::class, 'destroy'])->name('destroy');
    });

    // Held for Sale (HFS) Management
    Route::prefix('hfs')->name('hfs.')->group(function () {
        // HFS Requests
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'update'])->name('update');
            Route::post('/{id}/submit', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'submitForApproval'])->name('submit');
            Route::post('/{id}/approve', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'reject'])->name('reject');
            Route::post('/{id}/cancel', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/validate', [App\Http\Controllers\Assets\Hfs\HfsRequestController::class, 'validateHfsRequest'])->name('validate');
        });

        // HFS Valuations
        Route::prefix('valuations')->name('valuations.')->group(function () {
            Route::get('/{hfsId}/create', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'create'])->name('create');
            Route::post('/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'store'])->name('store');
            Route::put('/{hfsId}/{valuationId}', [App\Http\Controllers\Assets\Hfs\HfsValuationController::class, 'update'])->name('update');
        });

        // HFS Disposals
        Route::prefix('disposals')->name('disposals.')->group(function () {
            Route::get('/{hfsId}/create', [App\Http\Controllers\Assets\Hfs\HfsDisposalController::class, 'create'])->name('create');
            Route::post('/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsDisposalController::class, 'store'])->name('store');
        });

        // Discontinued Operations
        Route::prefix('discontinued')->name('discontinued.')->group(function () {
            Route::post('/{hfsId}/tag', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'tagAsDiscontinued'])->name('tag');
            Route::put('/{hfsId}/criteria', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'updateCriteria'])->name('update-criteria');
            Route::get('/{hfsId}/check', [App\Http\Controllers\Assets\Hfs\HfsDiscontinuedController::class, 'checkCriteria'])->name('check');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/movement-schedule', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'movementSchedule'])->name('movement-schedule');
            Route::get('/valuation-details', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'valuationDetails'])->name('valuation-details');
            Route::get('/discontinued-ops', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'discontinuedOpsNote'])->name('discontinued-ops');
            Route::get('/overdue', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'overdueReport'])->name('overdue');
            Route::get('/audit-trail', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'auditTrail'])->name('audit-trail');
            Route::get('/audit-trail/{hfsId}', [App\Http\Controllers\Assets\Hfs\HfsReportController::class, 'auditTrail'])->name('audit-trail.detail');
        });
    });

    // Maintenance Management
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Assets\MaintenanceController::class, 'index'])->name('index');
        Route::get('/settings', [App\Http\Controllers\Assets\MaintenanceController::class, 'settings'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\Assets\MaintenanceController::class, 'updateSettings'])->name('settings.update');

        // Maintenance Types
        Route::get('/types', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'index'])->name('types.index');
        Route::get('/types/data', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'index'])->name('types.data');
        Route::get('/types/create', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'create'])->name('types.create');
        Route::post('/types', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'store'])->name('types.store');
        Route::get('/types/{id}/edit', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'edit'])->name('types.edit');
        Route::put('/types/{id}', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'update'])->name('types.update');
        Route::delete('/types/{id}', [App\Http\Controllers\Assets\MaintenanceTypeController::class, 'destroy'])->name('types.destroy');

        // Maintenance Requests
        Route::get('/requests', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/data', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'index'])->name('requests.data');
        Route::get('/requests/create', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{id}/edit', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'update'])->name('requests.update');
        Route::post('/requests/{id}/approve', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{id}/reject', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'reject'])->name('requests.reject');
        Route::delete('/requests/{id}', [App\Http\Controllers\Assets\MaintenanceRequestController::class, 'destroy'])->name('requests.destroy');

        // Work Orders
        Route::get('/work-orders', [App\Http\Controllers\Assets\WorkOrderController::class, 'index'])->name('work-orders.index');
        Route::get('/work-orders/data', [App\Http\Controllers\Assets\WorkOrderController::class, 'index'])->name('work-orders.data');
        Route::get('/work-orders/create', [App\Http\Controllers\Assets\WorkOrderController::class, 'create'])->name('work-orders.create');
        Route::post('/work-orders', [App\Http\Controllers\Assets\WorkOrderController::class, 'store'])->name('work-orders.store');
        Route::get('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'show'])->name('work-orders.show');
        Route::get('/work-orders/{id}/edit', [App\Http\Controllers\Assets\WorkOrderController::class, 'edit'])->name('work-orders.edit');
        Route::put('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'update'])->name('work-orders.update');
        Route::post('/work-orders/{id}/approve', [App\Http\Controllers\Assets\WorkOrderController::class, 'approve'])->name('work-orders.approve');
        Route::get('/work-orders/{id}/execute', [App\Http\Controllers\Assets\WorkOrderController::class, 'execute'])->name('work-orders.execute');
        Route::post('/work-orders/{id}/add-cost', [App\Http\Controllers\Assets\WorkOrderController::class, 'addCost'])->name('work-orders.add-cost');
        Route::post('/work-orders/{id}/complete', [App\Http\Controllers\Assets\WorkOrderController::class, 'complete'])->name('work-orders.complete');
        Route::get('/work-orders/{id}/review', [App\Http\Controllers\Assets\WorkOrderController::class, 'review'])->name('work-orders.review');
        Route::post('/work-orders/{id}/classify', [App\Http\Controllers\Assets\WorkOrderController::class, 'classify'])->name('work-orders.classify');
        Route::delete('/work-orders/{id}', [App\Http\Controllers\Assets\WorkOrderController::class, 'destroy'])->name('work-orders.destroy');
    });

    // Intangible Assets
    Route::prefix('intangible')->name('intangible.')->group(function () {
        Route::get('/', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Intangible\IntangibleAssetController::class, 'store'])->name('store');

        // Intangible cost components
        Route::prefix('assets/{asset}/cost-components')->name('cost-components.')->group(function () {
            Route::get('/', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'store'])->name('store');
            Route::get('/{component}/edit', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'edit'])->name('edit');
            Route::put('/{component}', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'update'])->name('update');
            Route::delete('/{component}', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'destroy'])->name('destroy');
            Route::get('/export', [App\Http\Controllers\Intangible\IntangibleCostComponentController::class, 'export'])->name('export');
        });

        // Intangible amortisation
        Route::get('/amortisation', [App\Http\Controllers\Intangible\IntangibleAmortisationController::class, 'index'])->name('amortisation.index');
        Route::post('/amortisation/process', [App\Http\Controllers\Intangible\IntangibleAmortisationController::class, 'process'])->name('amortisation.process');

        // Intangible impairment
        Route::get('/impairments/create', [App\Http\Controllers\Intangible\IntangibleImpairmentController::class, 'create'])->name('impairments.create');
        Route::post('/impairments', [App\Http\Controllers\Intangible\IntangibleImpairmentController::class, 'store'])->name('impairments.store');

        // Intangible disposal
        Route::get('/disposals/create', [App\Http\Controllers\Intangible\IntangibleDisposalController::class, 'create'])->name('disposals.create');
        Route::post('/disposals', [App\Http\Controllers\Intangible\IntangibleDisposalController::class, 'store'])->name('disposals.store');

        // Intangible categories
        Route::get('/categories', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/data', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'data'])->name('categories.data');
        Route::get('/categories/create', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [App\Http\Controllers\Intangible\IntangibleCategoryController::class, 'store'])->name('categories.store');
    });
});

// Project Management Routes
Route::prefix('projects')->name('projects.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
});

// Investment Management Routes
Route::prefix('investments')->name('investments.')->middleware(['auth', 'company.scope', 'require.branch'])->group(function () {
    Route::get('/', [App\Http\Controllers\Investment\InvestmentController::class, 'index'])->name('index');

    // Investment Proposals
    Route::get('proposals/data', [App\Http\Controllers\Investment\InvestmentProposalController::class, 'data'])->name('proposals.data');
    Route::resource('proposals', App\Http\Controllers\Investment\InvestmentProposalController::class);
    Route::post('proposals/{proposal}/submit', [App\Http\Controllers\Investment\InvestmentProposalController::class, 'submitForApproval'])->name('proposals.submit');
    Route::post('proposals/{proposal}/approve', [App\Http\Controllers\Investment\InvestmentProposalController::class, 'approve'])->name('proposals.approve');
    Route::post('proposals/{proposal}/reject', [App\Http\Controllers\Investment\InvestmentProposalController::class, 'reject'])->name('proposals.reject');
    Route::post('proposals/{proposal}/convert', [App\Http\Controllers\Investment\InvestmentProposalController::class, 'convertToInvestment'])->name('proposals.convert');

    // Investment Master
    Route::get('master/data', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'data'])->name('master.data');
    Route::get('master', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'index'])->name('master.index');
    Route::get('master/{master}', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'show'])->name('master.show');
    Route::get('master/{master}/edit', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'edit'])->name('master.edit');
    Route::put('master/{master}', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'update'])->name('master.update');
    Route::post('master/{master}/recalculate-eir', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'recalculateEir'])->name('master.recalculate-eir');
    Route::post('master/{master}/generate-amortization', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'generateAmortization'])->name('master.generate-amortization');
    Route::get('master/{master}/amortization', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'amortizationSchedule'])->name('master.amortization');
    Route::post('master/{master}/post-accrual', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'postAccrual'])->name('master.post-accrual');
    Route::post('master/{master}/coupon-payment', [App\Http\Controllers\Investment\InvestmentMasterController::class, 'processCouponPayment'])->name('master.coupon-payment');

    // Investment Trades
    Route::get('trades/data', [App\Http\Controllers\Investment\InvestmentTradeController::class, 'data'])->name('trades.data');
    Route::get('trades/investment/{id}/details', [App\Http\Controllers\Investment\InvestmentTradeController::class, 'getInvestmentDetails'])->name('trades.investment.details');
    Route::resource('trades', App\Http\Controllers\Investment\InvestmentTradeController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('trades/{trade}/preview-journal', [App\Http\Controllers\Investment\InvestmentTradeController::class, 'previewJournal'])->name('trades.preview-journal');
    Route::post('trades/{trade}/post-journal', [App\Http\Controllers\Investment\InvestmentTradeController::class, 'postJournal'])->name('trades.post-journal');
    Route::post('trades/{trade}/update-settlement', [App\Http\Controllers\Investment\InvestmentTradeController::class, 'updateSettlement'])->name('trades.update-settlement');

    // Investment Valuations (Phase 4)
    Route::get('valuations/data', [App\Http\Controllers\Investment\InvestmentValuationController::class, 'data'])->name('valuations.data');
    Route::resource('valuations', App\Http\Controllers\Investment\InvestmentValuationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('valuations/{valuation}/approve', [App\Http\Controllers\Investment\InvestmentValuationController::class, 'approve'])->name('valuations.approve');
    Route::get('valuations/{valuation}/preview', [App\Http\Controllers\Investment\InvestmentValuationController::class, 'preview'])->name('valuations.preview');
    Route::post('valuations/{valuation}/post', [App\Http\Controllers\Investment\InvestmentValuationController::class, 'post'])->name('valuations.post');
    Route::post('valuations/market-price', [App\Http\Controllers\Investment\InvestmentValuationController::class, 'storeMarketPrice'])->name('valuations.store-market-price');

    // ECL Calculations (Phase 5)
    Route::prefix('ecl')->name('ecl.')->group(function () {
        Route::get('data', [App\Http\Controllers\Investment\EclCalculationController::class, 'data'])->name('data');
        Route::post('{eclCalc}/post', [App\Http\Controllers\Investment\EclCalculationController::class, 'post'])->name('post');
        Route::resource('/', App\Http\Controllers\Investment\EclCalculationController::class)->parameters(['' => 'eclCalc'])->except(['destroy']);
    });

    // Investment Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Investment\InvestmentReportController::class, 'index'])->name('index');

        // ECL Reports
        Route::prefix('ecl')->name('ecl.')->group(function () {
            Route::get('/summary', [App\Http\Controllers\Investment\InvestmentEclReportController::class, 'summary'])->name('summary');
            Route::get('/detail', [App\Http\Controllers\Investment\InvestmentEclReportController::class, 'detail'])->name('detail');
            Route::get('/trend', [App\Http\Controllers\Investment\InvestmentEclReportController::class, 'trend'])->name('trend');
            Route::get('/export', [App\Http\Controllers\Investment\InvestmentEclReportController::class, 'export'])->name('export');
        });

        // Valuation Reports (Phase 4)
        Route::prefix('valuations')->name('valuations.')->group(function () {
            Route::get('/fair-value-hierarchy', [App\Http\Controllers\Investment\InvestmentValuationReportController::class, 'fairValueHierarchy'])->name('fair-value-hierarchy');
            Route::get('/valuation-history', [App\Http\Controllers\Investment\InvestmentValuationReportController::class, 'valuationHistory'])->name('valuation-history');
        });
    });
});

// User Manuals
Route::prefix('manuals')->name('manuals.')->middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\ManualController::class, 'index'])->name('index');
    Route::get('/store-requisition/download', [App\Http\Controllers\ManualController::class, 'downloadStoreRequisitionManual'])->name('store-requisition.download');
    Route::get('/store-requisition/view', [App\Http\Controllers\ManualController::class, 'viewStoreRequisitionManual'])->name('store-requisition.view');
    Route::post('/generate', [App\Http\Controllers\ManualController::class, 'generateManual'])->name('generate');
});

// School Routes
Route::prefix('school')->name('school.')->middleware(['auth', 'company.scope'])->group(function () {
    // API Routes for AJAX calls
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/classes/search', [App\Http\Controllers\School\ClassesController::class, 'search'])->name('classes.search');
    });

    // Academics & Examinations
    Route::prefix('academics-examinations')->name('academics-examinations.')->group(function () {
        // Custom routes must come BEFORE resource route
        Route::get('/download-marks-sample', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'downloadMarksSample'])->name('download-marks-sample');
        Route::get('/', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'index'])->name('index');
        Route::get('/marks-entry', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'marksEntry'])->name('marks-entry');
        Route::post('/get-students', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getStudents'])->name('get-students');
        Route::post('/get-classes-for-marks-entry', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getClassesForMarksEntry'])->name('get-classes-for-marks-entry');
        Route::get('/get-marks-entry-data', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'getMarksEntryData'])->name('get-marks-entry-data');
        Route::post('/save-marks', [App\Http\Controllers\School\AcademicsExaminationsController::class, 'saveMarks'])->name('save-marks');

        // Removed resource route as all functionality is handled by custom routes above
    });

    // Timetables
    Route::prefix('timetables')->name('timetables.')->group(function () {
        Route::get('/', [App\Http\Controllers\School\TimetableController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\School\TimetableController::class, 'data'])->name('data');
        Route::post('/generate-teacher-timetables', [App\Http\Controllers\School\TimetableController::class, 'generateTeacherTimetables'])->name('generate-teacher-timetables');
        Route::get('/all-teachers/{academicYearId?}', [App\Http\Controllers\School\TimetableController::class, 'showAllTeachers'])->name('show-all-teachers');
        Route::get('/create', [App\Http\Controllers\School\TimetableController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\School\TimetableController::class, 'store'])->name('store');
        Route::get('/{hashId}', [App\Http\Controllers\School\TimetableController::class, 'show'])->name('show');
        Route::get('/{hashId}/print', [App\Http\Controllers\School\TimetableController::class, 'print'])->name('print');
        Route::get('/{hashId}/edit', [App\Http\Controllers\School\TimetableController::class, 'edit'])->name('edit');
        Route::put('/{hashId}', [App\Http\Controllers\School\TimetableController::class, 'update'])->name('update');
        Route::delete('/{hashId}', [App\Http\Controllers\School\TimetableController::class, 'destroy'])->name('destroy');
        Route::get('/{hashId}/duplicate', [App\Http\Controllers\School\TimetableController::class, 'duplicate'])->name('duplicate');
        Route::post('/{hashId}/publish', [App\Http\Controllers\School\TimetableController::class, 'publish'])->name('publish');
        Route::post('/get-streams', [App\Http\Controllers\School\TimetableController::class, 'getStreams'])->name('get-streams');
        Route::post('/get-subjects', [App\Http\Controllers\School\TimetableController::class, 'getSubjects'])->name('get-subjects');
        Route::post('/get-teacher-for-subject', [App\Http\Controllers\School\TimetableController::class, 'getTeacherForSubject'])->name('get-teacher-for-subject');
        Route::post('/check-conflicts', [App\Http\Controllers\School\TimetableController::class, 'checkConflicts'])->name('check-conflicts');

        // Timetable Entries
        Route::prefix('entries')->name('entries.')->group(function () {
            Route::post('/', [App\Http\Controllers\School\TimetableController::class, 'storeEntry'])->name('store');
            Route::get('/{entryId}/edit', [App\Http\Controllers\School\TimetableController::class, 'getEntry'])->name('edit');
            Route::put('/{entryId}', [App\Http\Controllers\School\TimetableController::class, 'updateEntry'])->name('update');
            Route::delete('/{entryId}', [App\Http\Controllers\School\TimetableController::class, 'destroyEntry'])->name('destroy');
        });

        // Bulk Timetable Entries
        Route::get('/{hashId}/bulk-entries', [App\Http\Controllers\School\TimetableController::class, 'bulkEntries'])->name('bulk-entries');
        Route::post('/{hashId}/bulk-entries', [App\Http\Controllers\School\TimetableController::class, 'storeBulkEntries'])->name('bulk-entries.store');

        // Timetable Periods
        Route::prefix('periods')->name('periods.')->group(function () {
            Route::post('/', [App\Http\Controllers\School\TimetableController::class, 'storePeriods'])->name('store');
            Route::post('/copy-monday', [App\Http\Controllers\School\TimetableController::class, 'copyMondayPeriods'])->name('copy-monday');
        });
    });

    // Assignments & Homework
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [App\Http\Controllers\School\AssignmentController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\School\AssignmentController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\School\AssignmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\School\AssignmentController::class, 'store'])->name('store');
        Route::get('/{hashId}', [App\Http\Controllers\School\AssignmentController::class, 'show'])->name('show');
        Route::get('/{hashId}/edit', [App\Http\Controllers\School\AssignmentController::class, 'edit'])->name('edit');
        Route::put('/{hashId}', [App\Http\Controllers\School\AssignmentController::class, 'update'])->name('update');
        Route::delete('/{hashId}', [App\Http\Controllers\School\AssignmentController::class, 'destroy'])->name('destroy');
        Route::post('/get-streams', [App\Http\Controllers\School\AssignmentController::class, 'getStreams'])->name('get-streams');
        Route::get('/{hashId}/submissions', [App\Http\Controllers\School\AssignmentController::class, 'submissions'])->name('submissions');
        Route::post('/{hashId}/submissions', [App\Http\Controllers\School\AssignmentController::class, 'storeSubmissions'])->name('submissions.store');
        Route::get('/{hashId}/submissions/export-sample', [App\Http\Controllers\School\AssignmentController::class, 'exportSubmissionsSample'])->name('submissions.export-sample');
        Route::post('/{hashId}/submissions/import', [App\Http\Controllers\School\AssignmentController::class, 'importSubmissions'])->name('submissions.import');
    });

    // School Digital Library / Learning Portal
    Route::prefix('library')->name('library.')->group(function () {
        Route::get('/', [App\Http\Controllers\School\LibraryController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\School\LibraryController::class, 'data'])->name('data');
        Route::get('/create', [App\Http\Controllers\School\LibraryController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\School\LibraryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\School\LibraryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\School\LibraryController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\School\LibraryController::class, 'destroy'])->name('destroy');
    });

    // School Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/examination-results', [App\Http\Controllers\School\SchoolReportsController::class, 'examinationResults'])->name('examination-results');
        Route::get('/examination-results/pdf', [App\Http\Controllers\School\SchoolReportsController::class, 'exportExaminationResultsPdf'])->name('examination-results.pdf');
        Route::get('/examination-results/excel', [App\Http\Controllers\School\SchoolReportsController::class, 'exportExaminationResultsExcel'])->name('examination-results.excel');
        Route::get('/overall-analysis', [App\Http\Controllers\School\SchoolReportsController::class, 'overallAnalysis'])->name('overall-analysis');
        Route::get('/performance-by-class', [App\Http\Controllers\School\SchoolReportsController::class, 'performanceByClass'])->name('performance-by-class');
        Route::get('/subject-wise-analysis', [App\Http\Controllers\School\SchoolReportsController::class, 'subjectWiseAnalysis'])->name('subject-wise-analysis');
    });
});

// LIPISHA Webhook (no authentication required, uses signature verification)
Route::post('/api/lipisha/webhook', [App\Http\Controllers\Api\LipishaWebhookController::class, 'handle'])
    ->name('api.lipisha.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
