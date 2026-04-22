<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'time_in',
        'time_out',
        'time_in_method',
        'time_out_method',
        'time_in_confidence',
        'time_out_confidence',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'time_in_confidence' => 'decimal:4',
            'time_out_confidence' => 'decimal:4',
        ];
    }

    protected $appends = [
        'late_minutes',
        'undertime_minutes',
        'worked_minutes',
        'late_text',
        'undertime_text',
        'worked_text',
        'status_text',
        'schedule_text',
        'attendance_summary_text',
        'attendance_summary_class',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getLateMinutesAttribute(): int
    {
        if (! $this->time_in || ! $this->employee || ! $this->employee->schedule_time_in || ! $this->attendance_date) {
            return 0;
        }

        $scheduledIn = $this->buildScheduledDateTime($this->employee->schedule_time_in);
        $actualIn = Carbon::parse($this->time_in);

        if (! $scheduledIn) {
            return 0;
        }

        return $actualIn->greaterThan($scheduledIn)
            ? $scheduledIn->diffInMinutes($actualIn)
            : 0;
    }

    public function getUndertimeMinutesAttribute(): int
    {
        if (! $this->time_out || ! $this->employee || ! $this->employee->schedule_time_out || ! $this->attendance_date) {
            return 0;
        }

        $scheduledOut = $this->buildScheduledDateTime($this->employee->schedule_time_out);
        $actualOut = Carbon::parse($this->time_out);

        if (! $scheduledOut) {
            return 0;
        }

        return $actualOut->lessThan($scheduledOut)
            ? $actualOut->diffInMinutes($scheduledOut)
            : 0;
    }

    public function getWorkedMinutesAttribute(): int
    {
        if (! $this->time_in || ! $this->time_out) {
            return 0;
        }

        $timeIn = Carbon::parse($this->time_in);
        $timeOut = Carbon::parse($this->time_out);

        if ($timeOut->lessThanOrEqualTo($timeIn)) {
            return 0;
        }

        return $timeIn->diffInMinutes($timeOut);
    }

    public function getLateTextAttribute(): string
    {
        return $this->late_minutes > 0
            ? $this->formatMinutes($this->late_minutes)
            : 'On time';
    }

    public function getUndertimeTextAttribute(): string
    {
        return $this->undertime_minutes > 0
            ? $this->formatMinutes($this->undertime_minutes)
            : 'Complete';
    }

    public function getWorkedTextAttribute(): string
    {
        if (! $this->time_in || ! $this->time_out) {
            return 'No Time Out';
        }

        return $this->formatMinutes($this->worked_minutes);
    }

    public function getStatusTextAttribute(): string
    {
        if (! $this->time_in && ! $this->time_out) {
            return 'Absent';
        }

        if ($this->time_in && ! $this->time_out) {
            return 'No Time Out';
        }

        if ($this->late_minutes > 0 && $this->undertime_minutes > 0) {
            return 'Late / Undertime';
        }

        if ($this->late_minutes > 0) {
            return 'Late';
        }

        if ($this->undertime_minutes > 0) {
            return 'Undertime';
        }

        return 'On Time';
    }

    public function getScheduleTextAttribute(): string
    {
        if (! $this->employee || ! $this->employee->schedule_time_in || ! $this->employee->schedule_time_out) {
            return 'No schedule';
        }

        $timeIn = Carbon::createFromFormat('H:i:s', substr((string) $this->employee->schedule_time_in, 0, 8));
        $timeOut = Carbon::createFromFormat('H:i:s', substr((string) $this->employee->schedule_time_out, 0, 8));

        return $timeIn->format('h:i A').' - '.$timeOut->format('h:i A');
    }

    public function getAttendanceSummaryTextAttribute(): string
    {
        if (! $this->time_in && ! $this->time_out) {
            return 'Absent';
        }

        if ($this->time_in && ! $this->time_out) {
            return 'No Time Out';
        }

        if ($this->late_minutes > 0 && $this->undertime_minutes > 0) {
            return 'Late '.$this->formatMinutes($this->late_minutes).
                ' / Undertime '.$this->formatMinutes($this->undertime_minutes);
        }

        if ($this->late_minutes > 0) {
            return 'Late '.$this->formatMinutes($this->late_minutes);
        }

        if ($this->undertime_minutes > 0) {
            return 'Undertime '.$this->formatMinutes($this->undertime_minutes);
        }

        return 'On Time';
    }

    public function getAttendanceSummaryClassAttribute(): string
    {
        if (! $this->time_in && ! $this->time_out) {
            return 'secondary';
        }

        if ($this->time_in && ! $this->time_out) {
            return 'secondary';
        }

        if ($this->late_minutes > 0 && $this->undertime_minutes > 0) {
            return 'danger';
        }

        if ($this->late_minutes > 0) {
            return 'danger';
        }

        if ($this->undertime_minutes > 0) {
            return 'warning';
        }

        return 'success';
    }

    private function buildScheduledDateTime($scheduleValue): ?Carbon
    {
        if (! $scheduleValue || ! $this->attendance_date) {
            return null;
        }

        $date = Carbon::parse($this->attendance_date)->format('Y-m-d');
        $time = substr((string) $scheduleValue, 0, 8);

        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$time);
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours} hr {$mins} min";
        }

        if ($hours > 0) {
            return "{$hours} hr";
        }

        return "{$mins} min";
    }
}
