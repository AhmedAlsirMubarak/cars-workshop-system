<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\SalaryAdvance;
use App\Models\Staff;

class SalaryAdvanceController extends Controller
{
    public function index(Request $request): View
    {
        $advances = SalaryAdvance::with(['staff.user', 'approvedBy'])
            ->when($request->status,   fn ($q, $s)  => $q->where('status', $s))
            ->when($request->staff_id, fn ($q, $id) => $q->where('staff_id', $id))
            ->latest('requested_on')
            ->paginate(20)
            ->withQueryString();

        $staff = Staff::with('user')
            ->where('status', 'active')
            ->get(['id', 'user_id', 'employee_id', 'basic_salary']);

        $summary = [
            'pending_count'  => SalaryAdvance::where('status', 'pending')->count(),
            'pending_amount' => SalaryAdvance::where('status', 'pending')->sum('amount'),
            'this_month'     => SalaryAdvance::where('deduct_month', now()->month)
                ->where('deduct_year', now()->year)
                ->whereIn('status', ['approved', 'deducted'])
                ->sum('amount'),
        ];

        return view('payroll.advances', compact('advances', 'staff', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'     => 'required|exists:staff,id',
            'amount'       => 'required|numeric|min:1',
            'requested_on' => 'required|date|before_or_equal:today',
            'deduct_month' => 'required|integer|min:1|max:12',
            'deduct_year'  => 'required|integer|min:' . now()->year,
            'reason'       => 'required|string|max:500',
        ]);

        SalaryAdvance::create($data);

        return back()->with('success', __('app.payroll.advance_requested'));
    }

    public function approve(Request $request, SalaryAdvance $advance): RedirectResponse
    {
        abort_if($advance->status !== 'pending', 403, 'Only pending advances can be approved.');

        $advance->update([
            'status'      => 'approved',
            'approved_on' => today(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', __('app.payroll.advance_approved'));
    }

    public function reject(Request $request, SalaryAdvance $advance): RedirectResponse
    {
        abort_if($advance->status !== 'pending', 403, 'Only pending advances can be rejected.');

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $advance->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', __('app.payroll.advance_rejected'));
    }

    public function destroy(SalaryAdvance $advance): RedirectResponse
    {
        abort_if($advance->status !== 'pending', 403, 'Only pending advances can be deleted.');

        $advance->delete();

        return back()->with('success', __('app.payroll.advance_deleted'));
    }
}
