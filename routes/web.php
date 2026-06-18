<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CreditPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarcodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance/scan', [AttendanceController::class, 'scan'])
        ->name('attendance.scan');
    Route::get('/attendance/enroll/{user}', [AttendanceController::class, 'enroll'])
        ->middleware('permission:users.manage')
        ->name('attendance.enroll');
    Route::post('/attendance/enroll/{user}', [AttendanceController::class, 'storeEnrollment'])
        ->middleware('permission:users.manage')
        ->name('attendance.enroll.store');

    Route::resource('categories', CategoryController::class)
        ->middleware('permission:categories.manage');

    Route::resource('products', ProductController::class)
        ->middleware('permission:products.manage');

    Route::resource('suppliers', SupplierController::class)
        ->middleware('permission:purchases.manage');

    Route::resource('customers', CustomerController::class)
        ->middleware('permission:sales.manage');

    Route::resource('purchases', PurchaseController::class)
        ->middleware('permission:purchases.manage');
    Route::get('/purchases/{purchase}/invoice', [PurchaseController::class, 'invoice'])
        ->middleware('permission:purchases.manage')
        ->name('purchases.invoice');

    Route::resource('sales', SaleController::class)
        ->middleware('permission:sales.manage');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])
        ->middleware('permission:sales.manage')
        ->name('sales.invoice');

    Route::resource('credit-payments', CreditPaymentController::class)
        ->only(['index', 'store'])
        ->middleware('permission:credits.manage');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('/reports/export/sales', [ReportController::class, 'exportSalesCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.export.sales');
    Route::get('/reports/export/credit-customers', [ReportController::class, 'exportCreditCustomersCsv'])
        ->middleware('permission:reports.view')
        ->name('reports.export.credit-customers');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
        ->middleware('permission:reports.view')
        ->name('reports.export.excel');

    Route::resource('users', UserController::class)
        ->middleware('permission:users.manage');

    // Barcode generation and scanner
    Route::get('/products/{product}/barcode', [BarcodeController::class, 'image'])
        ->name('products.barcode');

    Route::get('/scanner', [BarcodeController::class, 'scanner'])
        ->name('scanner');

    Route::get('/product-by-barcode/{code}', [BarcodeController::class, 'lookup'])
        ->name('products.lookup');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
