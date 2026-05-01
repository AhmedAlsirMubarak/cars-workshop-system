<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'staff_id',
        'date',
        'check_in',
        'check_out',
        'hours_worked',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date'         => 'date',
        'hours_worked' => 'decimal:2',
    ];

    // ── Auto-calculate hours_worked before saving ─────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->check_in && $model->check_out) {
                $in  = Carbon::parse($model->check_in);
                $out = Carbon::parse($model->check_out);

                $model->hours_worked = $out->greaterThan($in)
                    ? round($in->diffInMinutes($out) / 60, 2)
                    : 0;
            }

            // Half-day counts as 4 hours if no times given
            if ($model->status === 'half_day' && $model->hours_worked == 0) {
                $model->hours_worked = 4;
            }

            // Absent / holiday = 0 hours
            if (in_array($model->status, ['absent', 'holiday'])) {
                $model->hours_worked = 0;
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'green',
            'absent'   => 'red',
            'half_day' => 'orange',
            'leave'    => 'purple',
            'holiday'  => 'blue',
            default    => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'  => __('app.payroll.present'),
            'absent'   => __('app.payroll.absent'),
            'half_day' => __('app.payroll.half_day'),
            'leave'    => __('app.payroll.leave'),
            'holiday'  => __('app.payroll.holiday'),
            default    => $this->status,
        };
    }
}
