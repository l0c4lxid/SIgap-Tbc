<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * @property-read User $patient
 */

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'name',
        'relation',
        'phone',
        'screening_status',
        'notes',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
