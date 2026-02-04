<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaOutbox extends Model
{
    use HasFactory;

    protected $table = 'wa_outbox';

    protected $fillable = [
        'type',
        'to_phone',
        'message',
        'status',
        'scheduled_at',
        'sent_at',
        'attempts',
        'last_error',
        'provider_message_id',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'meta' => 'array',
        'attempts' => 'integer',
    ];

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for messages ready to dispatch
     */
    public function scopeForDispatch($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('scheduled_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Scope for sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return in_array($this->status, ['failed', 'pending']) && $this->attempts < 3;
    }

    /**
     * Mark as sent
     */
    public function markAsSent(string $messageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => $messageId,
            'last_error' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => $this->attempts >= 3 ? 'failed' : 'pending',
            'last_error' => substr($error, 0, 500), // Truncate long errors
        ]);
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
