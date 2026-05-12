<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyCutoff extends Model
{
    protected $fillable = [
        'cutoff_name',
        'date_from',
        'date_to',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];
}
