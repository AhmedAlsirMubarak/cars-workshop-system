<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payroll extends Model
{
    protected $fillable = [
        'staff_id', 'month', 'year',
        'basic_salary', 'bonus', 'bonus_note', 'gross_salary',
        'working_days', 'days_present', 'days_absent', 'days_half', 'hours_worked',
        'absence_deduction', 'advance_deduction', 'other_deduction', 'other_deduction_note', 'total_deductions',
        'net_salary',
        'payment_method', 'bank_amount', 'cash_amount', 'payment_reference',
        'status', 'paid_on', 'notes',
        'prepared_by', 'approved_by',
    ];

    protected $casts = [
        'paid_on'           => 'date',
        'month'             => 'integer',
        'year'              => 'integer',
        'working_days'      => 'integer',
        'days_present'      => 'integer',
        'days_absent'       => 'integer',
        'days_half'         => 'integer',
        'hours_worked'      => 'decimal:2',
        'basic_salary'      => 'decimal:3',
        'bonus'             => 'decimal:3',
        'gross_salary'      => 'decimal:3',
        'absence_deduction' => 'decimal:3',
        'advance_deduction' => 'decimal:3',
        'other_deduction'   => 'decimal:3',
        'total_deductions'  => 'decimal:3',
        'net_salary'        => 'decimal:3',
        'bank_amount'       => 'decimal:3',
        'cash_amount'       => 'decimal:3',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getMonthLabelAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getPeriodAttribute(): string
    {
        return str_pad($this->month, 2, '0', STR_PAD_LEFT) . '/' . $this->year;
    }

    public function getAttendanceRateAttribute(): float
    {
        if ($this->working_days <= 0) return 0;
        return round(($this->days_present + ($this->days_half * 0.5)) / $this->working_days * 100, 1);
    }

    public function recalculate(): void
    {
        $this->gross_salary = round($this->basic_salary + $this->bonus, 3);

        $effectiveAbsent = $this->days_absent + ($this->days_half * 0.5);
        $this->absence_deduction = $this->working_days > 0
            ? round(($this->basic_salary / $this->working_days) * $effectiveAbsent, 3)
            : 0;

        $this->total_deductions = round(
            $this->absence_deduction + $this->advance_deduction + $this->other_deduction, 3
        );

        $this->net_salary = max(0, round($this->gross_salary - $this->total_deductions, 3));

        if ($this->payment_method === 'bank_transfer') {
            $this->bank_amount = $this->net_salary;
            $this->cash_amount = 0;
        } elseif ($this->payment_method === 'cash') {
            $this->bank_amount = 0;
            $this->cash_amount = $this->net_salary;
        }
    }
}
