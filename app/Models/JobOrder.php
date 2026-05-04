<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class JobOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_number',
        'customer_id',
        'vehicle_id',
        'staff_id',
        'appointment_id',
        'status',
        'priority',
        'complaint',
        'diagnosis',
        'work_performed',
        'recommendations',
        'labour_cost',
        'parts_cost',
        'discount',
        'tax_rate',
        'started_at',
        'completed_at',
        'promised_at',
        'mileage_in',
        'mileage_out',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'promised_at'  => 'datetime',
        'labour_cost'  => 'decimal:2',
        'parts_cost'   => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax_rate'     => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->job_number = self::generateJobNumber();
        });
    }

    public static function generateJobNumber(): string
    {
        $year  = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'WO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function assignedStaff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'job_order_staff');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobOrderItem::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(JobOrderPart::class);
    }

    public function getSubtotalAttribute(): float
    {
        $itemsSum = $this->relationLoaded('items')
            ? $this->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price)
            : (float) ($this->items()->sum(DB::raw('quantity * unit_price')) ?? 0);

        return round((float) $this->labour_cost + (float) $this->parts_cost + $itemsSum, 3);
    }

    public function getTaxAmountAttribute(): float
    {
        return round(($this->subtotal - $this->discount) * ($this->tax_rate / 100), 2);
    }

    public function getTotalAttribute(): float
    {
        return round(($this->subtotal - $this->discount) + $this->tax_amount, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'       => 'yellow',
            'in_progress'   => 'blue',
            'waiting_parts' => 'orange',
            'completed'     => 'green',
            'cancelled'     => 'red',
            default         => 'gray',
        };
    }
}
