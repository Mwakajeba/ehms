<?php

namespace App\Models\Hospital;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitDepartment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'visit_id',
        'department_id',
        'status',
        'waiting_started_at',
        'service_started_at',
        'service_ended_at',
        'waiting_time_seconds',
        'service_time_seconds',
        'served_by',
        'notes',
        'sequence',
    ];

    protected $casts = [
        'waiting_started_at' => 'datetime',
        'service_started_at' => 'datetime',
        'service_ended_at' => 'datetime',
        'waiting_time_seconds' => 'integer',
        'service_time_seconds' => 'integer',
        'sequence' => 'integer',
    ];

    // Relationships
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function department()
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
    }

    public function servedBy()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    // Helper methods for time calculation
    public function calculateWaitingTime()
    {
        if ($this->waiting_started_at && $this->service_started_at) {
            $this->waiting_time_seconds = $this->waiting_started_at->diffInSeconds($this->service_started_at);
            $this->save();
        }
        return $this->waiting_time_seconds;
    }

    public function calculateServiceTime()
    {
        if ($this->service_started_at && $this->service_ended_at) {
            $this->service_time_seconds = $this->service_started_at->diffInSeconds($this->service_ended_at);
            $this->save();
        }
        return $this->service_time_seconds;
    }

    public function getWaitingTimeFormattedAttribute()
    {
        $hours = floor($this->waiting_time_seconds / 3600);
        $minutes = floor(($this->waiting_time_seconds % 3600) / 60);
        $seconds = $this->waiting_time_seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getServiceTimeFormattedAttribute()
    {
        $hours = floor($this->service_time_seconds / 3600);
        $minutes = floor(($this->service_time_seconds % 3600) / 60);
        $seconds = $this->service_time_seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
