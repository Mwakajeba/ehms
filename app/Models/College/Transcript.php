<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

class Transcript extends Model
{
    protected $fillable = [
        'transcript_number',
        'student_id',
        'program_id',
        'transcript_type',
        'academic_year_id',
        'semester_id',
        'cgpa',
        'total_credits_earned',
        'class_of_award',
        'academic_standing',
        'generated_date',
        'generated_by',
        'file_path',
        'file_hash',
        'verification_code',
        'is_verified',
        'verified_date',
        'verified_by',
        'status',
        'remarks',
        'revocation_reason',
        'revoked_date',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'cgpa' => 'decimal:2',
        'generated_date' => 'date',
        'verified_date' => 'date',
        'revoked_date' => 'date',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(CollegeStudent::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Helper Methods
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function generateVerificationCode(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
    }

    public static function generateTranscriptNumber(int $studentId, string $type): string
    {
        $year = date('Y');
        $prefix = strtoupper(substr($type, 0, 3));
        return "TR-{$year}-{$prefix}-" . str_pad($studentId, 6, '0', STR_PAD_LEFT);
    }
}
