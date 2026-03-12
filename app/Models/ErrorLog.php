<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErrorLog extends Model
{
    use SoftDeletes;

    protected $casts = [
        'request_data' => 'array',
        'request_headers' => 'array',
        'trace_array' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'code',
        'message',
        'exception',
        'level',
        'controller',
        'method',
        'route',
        'url',
        'status_code',
        'user_id',
        'ip_address',
        'user_agent',
        'session_id',
        'request_data',
        'request_headers',
        'file',
        'line',
        'trace',
        'trace_array',
        'fingerprint',
        'occurrences',
        'environment',
        'app_version',
    ];

    // Связь с пользователем (если есть таблица users)
    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
