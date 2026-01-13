<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'description',
        'sort_order',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent attachable model (Assignment or AssignmentSubmission).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the company that owns the attachment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the attachment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get file URL.
     */
    public function getUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }
        // Use url() helper for full URL (includes domain) - better for mobile apps
        $filePath = ltrim($this->file_path, '/');
        $filePath = preg_replace('#^storage/#', '', $filePath);
        return url('storage/' . $filePath);
    }

    /**
     * Get file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
