<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'address',
        'organization',
        'notes',
        'initial_password',
        'family_card_number',
        'supervisor_id',
        'pending_supervisor_id',
        'treatment_status',
        'next_follow_up_at',
        'treatment_notes',
    ];

    protected $casts = [
        'next_follow_up_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
