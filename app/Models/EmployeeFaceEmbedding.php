<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceEmbedding extends Model
{
    protected $fillable = [
        'employee_id',
        'image_path',
        'is_primary',
        'model_name',
        'model_version',
        'det_score',
        'quality_score',
        'yaw',
        'pitch',
        'roll',
        'landmarks_json',
        'embedding_json',
        'captured_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'landmarks_json' => 'array',
        'embedding_json' => 'array',
        'captured_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
