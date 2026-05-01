<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'staff_id',
        'amount',
        'requested_on',
        'approved_on',
        'deduct_month',
        'deduct_year',
        'status',
        'reason',
        'rejection_reason',
        'approved_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:3',
        'requested_on' => 'date',
        'approved_on'  => 'date',
        'deduct_month' => 'integer',
        'deduct_year'  => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getDeductPeriodAttribute(): string
    {
        return str_pad($this->deduct_month, 2, '0', STR_PAD_LEFT) . '/' . $this->deduct_year;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'yellow',
            'approved' => 'blue',
            'rejected' => 'red',
            'deducted' => 'green',
            default    => 'gray',
        };
    }
}
