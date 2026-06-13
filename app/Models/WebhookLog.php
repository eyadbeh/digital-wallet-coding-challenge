<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'bank_name',
        'payload',
        'status',
        'error_message',
        'transactions_imported',
        'transactions_duplicated',
    ];
}
