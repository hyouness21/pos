<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DealerPurchaseController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

// Dev-only instant login (local environment only)
if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        auth()->loginUsingId(1);
        return redirect('/reports');
    });
}

Route::middleware(['auth'])->group(function () {

    // Dashboard (redirect to reports)
    Route::get('/dashboard', fn () => redirect()->route('reports.index'))->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Items
    Route::get('/storehouse', [ItemController::class, 'storehouse'])->name('storehouse');
    Route::patch('/items/{item}/stock', [ItemController::class, 'updateStock'])->name('items.stock');
    Route::resource('items', ItemController::class)->except(['show']);

    // Customers — best must come before {customer} to avoid routing clash
    Route::get('/customers/best', [CustomerController::class, 'best'])->name('customers.best');
    Route::resource('customers', CustomerController::class);

    // Invoices
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/refunds/create', [RefundController::class, 'create'])->name('invoices.refunds.create');
    Route::post('/invoices/{invoice}/refunds', [RefundController::class, 'store'])->name('invoices.refunds.store');
    Route::resource('invoices', InvoiceController::class);

    // Dealers
    Route::resource('dealers', DealerController::class);

    // Dealer Purchases (nested under dealer for create/store, standalone for show/destroy)
    Route::get('/dealers/{dealer}/purchases/create', [DealerPurchaseController::class, 'create'])->name('dealer-purchases.create');
    Route::post('/dealers/{dealer}/purchases', [DealerPurchaseController::class, 'store'])->name('dealer-purchases.store');
    Route::get('/dealer-purchases/{dealerPurchase}', [DealerPurchaseController::class, 'show'])->name('dealer-purchases.show');
    Route::get('/dealer-purchases/{dealerPurchase}/edit', [DealerPurchaseController::class, 'edit'])->name('dealer-purchases.edit');
    Route::put('/dealer-purchases/{dealerPurchase}', [DealerPurchaseController::class, 'update'])->name('dealer-purchases.update');
    Route::delete('/dealer-purchases/{dealerPurchase}', [DealerPurchaseController::class, 'destroy'])->name('dealer-purchases.destroy');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/pending', [ReportController::class, 'pending'])->name('reports.pending');
    Route::get('/reports/best-seller', [ReportController::class, 'bestSeller'])->name('reports.best-seller');
    Route::get('/reports/earnings/daily', [ReportController::class, 'earningsDaily'])->name('reports.earnings-daily');
    Route::get('/reports/earnings/monthly', [ReportController::class, 'earningsMonthly'])->name('reports.earnings-monthly');
});

require __DIR__.'/auth.php';
