<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'specialization',
        'hourly_rate',
        'basic_salary',
        'bank_name',
        'bank_account_number',
        'iban',
        'payment_method',
        'status',
        'hired_at',
        'notes',
    ];

    protected $casts = [
        'hourly_rate'  => 'decimal:3',
        'basic_salary' => 'decimal:3',
        'hired_at'     => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getActiveJobCountAttribute(): int
    {
        return $this->jobOrders()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();
    }

    /** Daily rate = basic_salary / working_days (default 26) */
    public function dailyRate(int $workingDays = 26): float
    {
        if ($this->basic_salary <= 0 || $workingDays <= 0) {
            return 0;
        }

        return round($this->basic_salary / $workingDays, 3);
    }

    /** Attendance counts for a given month/year */
    public function attendanceSummary(int $month, int $year): array
    {
        $records = $this->attendance()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        return [
            'present'      => $records->where('status', 'present')->count(),
            'absent'       => $records->where('status', 'absent')->count(),
            'half_day'     => $records->where('status', 'half_day')->count(),
            'leave'        => $records->where('status', 'leave')->count(),
            'holiday'      => $records->where('status', 'holiday')->count(),
            'hours_worked' => round($records->sum('hours_worked'), 2),
        ];
    }

    /** Total approved advances to deduct in a given month/year */
    public function pendingAdvanceDeduction(int $month, int $year): float
    {
        return (float) $this->salaryAdvances()
            ->where('status', 'approved')
            ->where('deduct_month', $month)
            ->where('deduct_year', $year)
            ->sum('amount');
    }
}
