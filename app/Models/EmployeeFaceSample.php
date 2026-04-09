<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceSample extends Model
{
    protected $fillable = [
        'employee_id',
        'image_path',
        'is_primary',
        'face_confidence',
        'yaw',
        'pitch',
        'roll',
        'landmarks_json',
        'captured_at',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
        'is_primary' => 'boolean',
        'landmarks_json' => 'array',
        'captured_at' => 'datetime',
        'face_confidence' => 'float',
        'yaw' => 'float',
        'pitch' => 'float',
        'roll' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
