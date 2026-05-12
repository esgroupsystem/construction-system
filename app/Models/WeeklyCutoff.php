<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyCutoff extends Model
{
    protected $fillable = [
        'cutoff_name',
        'date_from',
        'date_to',
        'status',
        'finalized_at',
    ];
}
