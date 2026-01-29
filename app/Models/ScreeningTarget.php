<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ScreeningTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'kelurahan_user_id',
        'period_type',
        'month',
        'date_from',
        'date_to',
        'target_total',
        'target_suspek',
        'allocation_mode',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kelurahan_user_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ScreeningTargetAllocation::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
