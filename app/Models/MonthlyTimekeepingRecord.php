<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyTimekeepingRecord extends Model
{
    protected $fillable = [
        'monthly_cutoff_id',
        'employee_id',
        'attendance_date',
        'computed_ot_minutes',
        'approved_ot_minutes',
        'ot_status',
        'is_finalized',
        'finalized_at',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
    ];
}
