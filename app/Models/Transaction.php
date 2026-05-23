<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'bank_name',
        'reference',
        'amount',
        'transaction_date',
        'note',
        'internal_reference',
        'raw_payload',
    ];
}
