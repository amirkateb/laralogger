<?php

namespace Laralogger\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

    protected $guarded = [];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
    ];
}