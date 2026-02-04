<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaOtp extends Model
{
    use HasFactory;

    protected $table = 'wa_otps';

    protected $fillable = [
        'user_id',
        'phone',
        'purpose',
        'code_hash',
        'expires_at',
        'used_at',
        'attempts',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the user associated with this OTP
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP has been used
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Check if can verify (not expired, not used, under attempt limit)
     */
    public function canVerify(): bool
    {
        return !$this->isExpired() 
            && !$this->isUsed() 
            && $this->attempts < 5;
    }

    /**
     * Verify OTP code
     */
    public function verify(string $code): bool
    {
        $this->increment('attempts');

        if (!$this->canVerify()) {
            return false;
        }

        // Hash the provided code and compare
        $codeHash = hash('sha256', $code);
        
        if (hash_equals($this->code_hash, $codeHash)) {
            $this->update(['used_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Scope for active OTPs (not expired, not used)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for specific phone and purpose
     */
    public function scopeForPhone($query, string $phone, string $purpose = 'reset_password')
    {
        return $query->where('phone', $phone)
            ->where('purpose', $purpose);
    }

    /**
     * Generate OTP code hash
     */
    public static function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }

    /**
     * Generate random 6-digit OTP
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
