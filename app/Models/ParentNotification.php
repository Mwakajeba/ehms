<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentNotification extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Guardian::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Student::class, 'student_id');
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}

