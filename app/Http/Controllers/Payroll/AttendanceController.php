<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Attendance;
use App\Models\Staff;

class AttendanceController extends Controller
{
    /**
     * Show daily attendance entry + monthly summary tab.
     */
    public function index(Request $request): View
    {
        $date  = $request->date  ?? today()->toDateString();
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        // All active staff
        $allStaff = Staff::with('user')
            ->where('status', 'active')
            ->orderBy('employee_id')
            ->get();

        // Existing attendance records for selected date, keyed by staff_id
        $dailyRecords = Attendance::where('date', $date)
            ->get()
            ->keyBy('staff_id');

        // Monthly summary: per-staff counts for the selected month
        $monthlySummary = $allStaff->map(function (Staff $s) use ($month, $year) {
            $summary = $s->attendanceSummary($month, $year);
            return [
                'id'           => $s->id,
                'name'         => $s->user->name,
                'employee_id'  => $s->employee_id,
                'basic_salary' => $s->basic_salary,
                ...$summary,
                // Effective working days = present + half*0.5
                'effective_days' => round($summary['present'] + ($summary['half_day'] * 0.5), 1),
            ];
        });

        return view('payroll.attendance', [
            'allStaff'       => $allStaff,
            'dailyRecords'   => $dailyRecords,
            'monthlySummary' => $monthlySummary,
            'date'           => $date,
            'month'          => $month,
            'year'           => $year,
        ]);
    }

    /**
     * Save attendance for all staff in one bulk POST.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'date'                 => 'required|date|before_or_equal:today',
            'records'              => 'required|array|min:1',
            'records.*.staff_id'   => 'required|exists:staff,id',
            'records.*.status'     => 'required|in:present,absent,half_day,leave,holiday',
            'records.*.check_in'   => 'nullable|date_format:H:i',
            'records.*.check_out'  => 'nullable|date_format:H:i|after:records.*.check_in',
            'records.*.notes'      => 'nullable|string|max:255',
        ]);

        foreach ($request->records as $record) {
            Attendance::updateOrCreate(
                [
                    'staff_id' => $record['staff_id'],
                    'date'     => $request->date,
                ],
                [
                    'status'      => $record['status'],
                    'check_in'    => $record['check_in']  ?? null,
                    'check_out'   => $record['check_out'] ?? null,
                    'notes'       => $record['notes']     ?? null,
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', __('app.payroll.attendance_saved'));
    }

    /**
     * Delete a single attendance record.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return back()->with('success', __('app.payroll.attendance_deleted'));
    }
}
