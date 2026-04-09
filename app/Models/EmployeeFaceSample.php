<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'image_path',
        'embedding_path',
        'captured_at',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
