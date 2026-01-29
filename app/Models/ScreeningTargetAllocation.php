<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningTargetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_target_id',
        'kader_user_id',
        'allocated_total',
        'allocated_suspek',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(ScreeningTarget::class, 'screening_target_id');
    }

    public function kader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kader_user_id');
    }
}
