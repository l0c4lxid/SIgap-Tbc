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
        'kelurahan_user_id',
        'rw_code',
        'rt_code',
    ];

    protected $casts = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kelurahan_user_id');
    }

    public function getAreaAttribute(): ?string
    {
        $rw = $this->rw_code;
        $rt = $this->rt_code;

        if (! $rw && ! $rt) {
            return null;
        }

        if ($rw && $rt) {
            return "RW {$rw} / RT {$rt}";
        }

        return $rw ? "RW {$rw}" : "RT {$rt}";
    }
}
