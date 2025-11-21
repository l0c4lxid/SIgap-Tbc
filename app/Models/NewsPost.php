<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\NewsImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class NewsPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'summary',
        'content',
        'status',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function image(): HasOne
    {
        return $this->hasOne(NewsImage::class);
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if ($user->role === UserRole::Pemda) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
