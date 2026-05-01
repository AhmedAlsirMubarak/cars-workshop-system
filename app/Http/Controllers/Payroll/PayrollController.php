<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\SalaryAdvance;

class PayrollController extends Controller
{
    // ── Monthly overview ──────────────────────────────────────

    public function index(Request $request): View
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        $payrolls = Payroll::with(['staff.user', 'preparedBy', 'approvedBy'])
            ->where('month', $month)
            ->where('year',  $year)
            ->orderBy('staff_id')
            ->get();

        $allStaff = Staff::with('user')->where('status', 'active')->get();

        // Staff who don't have a payslip yet this month
        $generatedIds = $payrolls->pluck('staff_id')->toArray();
        $missingStaff = $allStaff->whereNotIn('id', $generatedIds)->values();

        $summary = [
            'total_gross'      => $payrolls->sum('gross_salary'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net'        => $payrolls->sum('net_salary'),
            'count_draft'      => $payrolls->where('status', 'draft')->count(),
            'count_approved'   => $payrolls->where('status', 'approved')->count(),
            'count_paid'       => $payrolls->where('status', 'paid')->count(),
        ];

        return view('payroll.index', compact(
            'payrolls', 'allStaff', 'missingStaff', 'summary', 'month', 'year'
        ));
    }

    // ── Generate payslip for one employee ─────────────────────

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'     => 'required|exists:staff,id',
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2020',
            'working_days' => 'required|integer|min:1|max:31',
        ]);

        $staff = Staff::with('user')->findOrFail($data['staff_id']);
        $month = (int) $data['month'];
        $year  = (int) $data['year'];

        // Prevent duplicates
        if (Payroll::where('staff_id', $staff->id)->where('month', $month)->where('year', $year)->exists()) {
            return back()->withErrors(['generate' => __('app.payroll.already_exists')]);
        }

        // Pull attendance summary
        $att = $staff->attendanceSummary($month, $year);

        // Pull approved advances for this month
        $advanceAmount = $staff->pendingAdvanceDeduction($month, $year);

        // Build payslip
        $payroll = new Payroll([
            'staff_id'         => $staff->id,
            'month'            => $month,
            'year'             => $year,
            'basic_salary'     => $staff->basic_salary,
            'bonus'            => 0,
            'working_days'     => $data['working_days'],
            'days_present'     => $att['present'],
            'days_absent'      => $att['absent'],
            'days_half'        => $att['half_day'],
            'hours_worked'     => $att['hours_worked'],
            'advance_deduction' => $advanceAmount,
            'other_deduction'  => 0,
            'payment_method'   => $staff->payment_method === 'both' ? 'split' : $staff->payment_method,
            'prepared_by'      => auth()->id(),
        ]);

        $payroll->recalculate();
        $payroll->save();

        // Mark advances as deducted
        SalaryAdvance::where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->where('deduct_month', $month)
            ->where('deduct_year', $year)
            ->update(['status' => 'deducted']);

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', __('app.payroll.generated', ['name' => $staff->user->name]));
    }

    // ── Bulk generate for all staff ───────────────────────────

    public function generateAll(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2020',
            'working_days' => 'required|integer|min:1|max:31',
        ]);

        $month = (int) $data['month'];
        $year  = (int) $data['year'];
        $count = 0;

        Staff::where('status', 'active')->each(function (Staff $staff) use ($month, $year, $data, &$count) {
            // Skip if payslip already exists
            if (Payroll::where('staff_id', $staff->id)->where('month', $month)->where('year', $year)->exists()) {
                return;
            }

            $att           = $staff->attendanceSummary($month, $year);
            $advanceAmount = $staff->pendingAdvanceDeduction($month, $year);

            $payroll = new Payroll([
                'staff_id'          => $staff->id,
                'month'             => $month,
                'year'              => $year,
                'basic_salary'      => $staff->basic_salary,
                'bonus'             => 0,
                'working_days'      => $data['working_days'],
                'days_present'      => $att['present'],
                'days_absent'       => $att['absent'],
                'days_half'         => $att['half_day'],
                'hours_worked'      => $att['hours_worked'],
                'advance_deduction' => $advanceAmount,
                'other_deduction'   => 0,
                'payment_method'    => $staff->payment_method === 'both' ? 'split' : $staff->payment_method,
                'prepared_by'       => auth()->id(),
            ]);

            $payroll->recalculate();
            $payroll->save();

            SalaryAdvance::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->where('deduct_month', $month)
                ->where('deduct_year', $year)
                ->update(['status' => 'deducted']);

            $count++;
        });

        return back()->with('success', __('app.payroll.bulk_generated', ['count' => $count]));
    }

    // ── View single payslip ───────────────────────────────────

    public function show(Payroll $payroll): View
    {
        $payroll->load([
            'staff.user',
            'preparedBy',
            'approvedBy',
            'staff.attendance' => fn ($q) => $q
                ->whereMonth('date', $payroll->month)
                ->whereYear('date', $payroll->year)
                ->orderBy('date'),
            'staff.salaryAdvances' => fn ($q) => $q
                ->where('deduct_month', $payroll->month)
                ->where('deduct_year', $payroll->year),
        ]);

        return view('payroll.show', compact('payroll'));
    }

    // ── Edit adjustments (bonus / other deductions / split) ───

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        abort_if($payroll->status === 'paid', 403, 'Paid payrolls cannot be edited.');

        $data = $request->validate([
            'bonus'               => 'nullable|numeric|min:0',
            'bonus_note'          => 'nullable|string|max:255',
            'other_deduction'     => 'nullable|numeric|min:0',
            'other_deduction_note' => 'nullable|string|max:255',
            'payment_method'      => 'required|in:bank_transfer,cash,split',
            'bank_amount'         => 'nullable|numeric|min:0',
            'cash_amount'         => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string|max:500',
        ]);

        $payroll->fill($data);
        $payroll->recalculate();

        // For split payments validate the amounts add up correctly
        if ($payroll->payment_method === 'split') {
            $payroll->bank_amount = (float) ($data['bank_amount'] ?? 0);
            $payroll->cash_amount = (float) ($data['cash_amount'] ?? 0);
        }

        $payroll->save();

        return back()->with('success', __('app.payroll.updated'));
    }

    // ── Approve payslip ───────────────────────────────────────

    public function approve(Payroll $payroll): RedirectResponse
    {
        abort_if($payroll->status !== 'draft', 403, 'Only draft payrolls can be approved.');

        $payroll->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', __('app.payroll.approved'));
    }

    // ── Mark as paid ──────────────────────────────────────────

    public function markPaid(Request $request, Payroll $payroll): RedirectResponse
    {
        abort_if($payroll->status !== 'approved', 403, 'Payroll must be approved first.');

        $data = $request->validate([
            'paid_on'           => 'required|date|before_or_equal:today',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $payroll->update([
            ...$data,
            'status' => 'paid',
        ]);

        return back()->with('success', __('app.payroll.paid'));
    }

    // ── Monthly summary report ────────────────────────────────

    public function report(Request $request): View
    {
        $year = (int) ($request->year ?? now()->year);

        $monthlyTotals = collect(range(1, 12))->map(function (int $m) use ($year) {
            $rows = Payroll::where('year', $year)->where('month', $m)->get();

            return [
                'month'            => $m,
                'total_gross'      => round($rows->sum('gross_salary'), 3),
                'total_deductions' => round($rows->sum('total_deductions'), 3),
                'total_net'        => round($rows->sum('net_salary'), 3),
                'total_bank'       => round($rows->sum('bank_amount'), 3),
                'total_cash'       => round($rows->sum('cash_amount'), 3),
                'count_paid'       => $rows->where('status', 'paid')->count(),
                'count_total'      => $rows->count(),
            ];
        });

        $staffTotals = Staff::with('user')
            ->where('status', 'active')
            ->get()
            ->map(function (Staff $s) use ($year) {
                $rows = $s->payrolls()->where('year', $year)->get();
                return [
                    'id'           => $s->id,
                    'name'         => $s->user->name,
                    'employee_id'  => $s->employee_id,
                    'basic_salary' => $s->basic_salary,
                    'annual_gross' => round($rows->sum('gross_salary'), 3),
                    'annual_net'   => round($rows->sum('net_salary'), 3),
                    'months_paid'  => $rows->where('status', 'paid')->count(),
                ];
            });

        $annualTotals = [
            'gross'      => round($monthlyTotals->sum('total_gross'), 3),
            'deductions' => round($monthlyTotals->sum('total_deductions'), 3),
            'net'        => round($monthlyTotals->sum('total_net'), 3),
            'bank'       => round($monthlyTotals->sum('total_bank'), 3),
            'cash'       => round($monthlyTotals->sum('total_cash'), 3),
        ];

        return view('payroll.report', compact(
            'monthlyTotals', 'staffTotals', 'annualTotals', 'year'
        ));
    }
}
