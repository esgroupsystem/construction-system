<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_no',
        'full_name',
        'department',
        'position',
        'photo_path',
        'is_active',
        'face_registered_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'face_registered_at' => 'datetime',
    ];

    public function faceSamples(): HasMany
    {
        return $this->hasMany(EmployeeFaceSample::class)
            ->orderByDesc('is_primary')
            ->latest('captured_at');
    }

    public function primaryFaceSample(): HasOne
    {
        return $this->hasOne(EmployeeFaceSample::class)
            ->where('is_primary', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
