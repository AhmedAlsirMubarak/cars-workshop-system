<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    CustomerController,
    VehicleController,
    JobOrderController,
    AppointmentController,
    InventoryController,
    InvoiceController,
    StaffController,
    ProfileController,
};

// ── Standard Laravel auth (Breeze Blade) ─────────────────────
require __DIR__ . '/auth.php';

// ── Authenticated routes ──────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('customers', CustomerController::class);

    Route::resource('vehicles', VehicleController::class)->except(['create', 'edit']);
    Route::get('customers/{customer}/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');

    Route::resource('appointments', AppointmentController::class)->except(['show']);
    Route::post('appointments/{appointment}/convert-to-job', [AppointmentController::class, 'convertToJob'])
         ->name('appointments.convert-to-job');

    Route::resource('jobs', JobOrderController::class);
    Route::post('jobs/{job}/parts', [JobOrderController::class, 'addPart'])->name('jobs.add-part');

    Route::get('inventory',            [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/export',     [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('inventory',           [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('inventory/{part}',     [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('inventory/{part}',  [InventoryController::class, 'destroy'])->name('inventory.destroy');

    Route::get('invoices',                    [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}',          [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('jobs/{job}/invoice',         [InvoiceController::class, 'generateFromJob'])->name('invoices.generate-from-job');
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');

    Route::middleware('role:admin|manager')->group(function () {
        Route::resource('staff', StaffController::class);
    });

    // Payroll routes (defined in routes/payroll.php)
    require __DIR__ . '/payroll.php';
});
