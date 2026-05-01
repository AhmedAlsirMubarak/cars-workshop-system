<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payroll\AttendanceController;
use App\Http\Controllers\Payroll\SalaryAdvanceController;
use App\Http\Controllers\Payroll\PayrollController;

// ============================================================
// Payroll routes — include in routes/web.php inside auth group:
//   require __DIR__ . '/payroll.php';
// ============================================================

Route::middleware(['auth', 'verified', 'role:admin|manager'])
    ->prefix('payroll')
    ->name('payroll.')
    ->group(function () {

        // ── Attendance ────────────────────────────────────────
        Route::get('attendance',  [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'bulkStore'])->name('attendance.store');
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

        // ── Salary Advances ───────────────────────────────────
        Route::get('advances',   [SalaryAdvanceController::class, 'index'])->name('advances.index');
        Route::post('advances',  [SalaryAdvanceController::class, 'store'])->name('advances.store');
        Route::patch('advances/{advance}/approve', [SalaryAdvanceController::class, 'approve'])->name('advances.approve');
        Route::patch('advances/{advance}/reject',  [SalaryAdvanceController::class, 'reject'])->name('advances.reject');
        Route::delete('advances/{advance}',        [SalaryAdvanceController::class, 'destroy'])->name('advances.destroy');

        // ── Payroll ───────────────────────────────────────────
        Route::get('/',           [PayrollController::class, 'index'])->name('index');
        Route::get('report',      [PayrollController::class, 'report'])->name('report');
        Route::get('{payroll}',   [PayrollController::class, 'show'])->name('show');
        Route::put('{payroll}',   [PayrollController::class, 'update'])->name('update');

        Route::post('generate',     [PayrollController::class, 'generate'])->name('generate');
        Route::post('generate-all', [PayrollController::class, 'generateAll'])->name('generate-all');

        Route::patch('{payroll}/approve',   [PayrollController::class, 'approve'])->name('approve');
        Route::patch('{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('mark-paid');
    });
