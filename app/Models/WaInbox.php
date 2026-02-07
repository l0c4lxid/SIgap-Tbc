<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaInbox extends Model
{
    use HasFactory;

    protected $table = 'wa_inbox';

    protected $fillable = [
        'wa_message_id',
        'from_phone',
        'push_name',
        'message',
        'media_path',
        'media_type',
        'received_at',
        'is_group',
        'raw_data',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_group' => 'boolean',
        'raw_data' => 'array',
    ];
}
