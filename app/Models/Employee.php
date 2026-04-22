<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'employee_no',
        'full_name',
        'department',
        'position',
        'rate_salary',
        'payroll_type',
        'location',
        'schedule_time_in',
        'schedule_time_out',
        'day_offs',
        'photo_path',
        'is_active',
        'face_registered_at',
        'face_samples_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'face_registered_at' => 'datetime',
        'rate_salary' => 'decimal:2',
        'day_offs' => 'array',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function faceEmbeddings(): HasMany
    {
        return $this->hasMany(EmployeeFaceEmbedding::class)
            ->orderByDesc('is_primary')
            ->latest('captured_at');
    }

    public function primaryFaceEmbedding(): HasOne
    {
        return $this->hasOne(EmployeeFaceEmbedding::class)
            ->where('is_primary', true);
    }
}
