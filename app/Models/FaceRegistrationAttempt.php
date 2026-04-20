<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceRegistrationAttempt extends Model
{
    protected $fillable = [
        'employee_id',
        'status',
        'message',
        'frames_received',
        'accepted_frames',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
