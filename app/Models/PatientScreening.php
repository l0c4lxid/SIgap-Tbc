<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'kader_id',
        'patient_is_wni',
        'patient_name',
        'patient_nik',
        'patient_phone',
        'patient_address',
        'patient_gender',
        'patient_birth_place',
        'patient_birth_date',
        'patient_age',
        'patient_address_ktp',
        'patient_address_domisili',
        'patient_address_rt',
        'patient_address_rw',
        'patient_address_kelurahan',
        'patient_weight',
        'patient_height',
        'answers',
        'notes',
    ];

    protected $casts = [
        'answers' => 'array',
        'patient_birth_date' => 'date',
        'patient_is_wni' => 'boolean',
    ];

    public function kader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kader_id');
    }
}
