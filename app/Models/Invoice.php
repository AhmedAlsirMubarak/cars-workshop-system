<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'job_order_id',
        'customer_id',
        'subtotal',
        'discount',
        'tax_amount',
        'total',
        'amount_paid',
        'status',
        'issued_at',
        'due_at',
        'notes',
    ];

    protected $casts = [
        'issued_at'   => 'date',
        'due_at'      => 'date',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $year  = now()->year;
            $count = self::whereYear('created_at', $year)->count() + 1;
            $model->invoice_number = 'INV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return round($this->total - $this->amount_paid, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at->isPast() && $this->balance_due > 0;
    }
}
