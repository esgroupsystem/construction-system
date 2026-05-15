<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'holiday_date',
        'name',
        'type',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'regular' => 'Regular Holiday',
            'special_non_working' => 'Special Non-Working Day',
            default => 'Holiday',
        };
    }
}
