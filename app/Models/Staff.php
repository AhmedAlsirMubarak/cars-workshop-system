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
        'status',
        'hired_at',
        'notes',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'hired_at'    => 'date',
    ];

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

    public function getActiveJobCountAttribute(): int
    {
        return $this->jobOrders()->whereIn('status', ['pending', 'in_progress'])->count();
    }
}
