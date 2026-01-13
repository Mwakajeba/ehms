<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinkla\Hashids\Facades\Hashids;

class Student extends Model
{
    protected $fillable = [
        'academic_year_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'email',
        'address',
        'class_id',
        'stream_id',
        'route_id',
        'admission_number',
        'admission_date',
        'boarding_type',
        'has_transport',
        'bus_stop_id',
        'passport_photo',
        'status',
        'status_changed_at',
        'status_notes',
        'discount_type',
        'discount_value',
        'lipisha_customer_id',
        'company_id',
        'branch_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'status_changed_at' => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    public function busStop(): BelongsTo
    {
        return $this->belongsTo(BusStop::class);
    }

    /**
     * Get the company that owns the student.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the student.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardians', 'student_id', 'guardian_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function studentGuardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(\App\Models\FeeInvoice::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class);
    }

    /**
     * Get the exam registrations for this student.
     */
    public function examRegistrations(): HasMany
    {
        return $this->hasMany(\App\Models\SchoolExamRegistration::class, 'student_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'encodedId';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }

    /**
     * Update student status
     */
    public function updateStatus(string $status, string $notes = null): void
    {
        $this->update([
            'status' => $status,
            'status_changed_at' => now(),
            'status_notes' => $notes,
        ]);
    }

    /**
     * Check if student is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the student's full name
     */
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if student has been transferred out
     */
    public function isTransferredOut(): bool
    {
        return $this->status === 'transferred_out';
    }

    /**
     * Get the latest transfer record
     */
    public function latestTransfer()
    {
        return $this->transfers()->latest()->first();
    }

    /**
     * Record a transfer
     */
    public function recordTransfer(array $transferData): StudentTransfer
    {
        return $this->transfers()->create($transferData);
    }

    /**
     * Scope a query to only include students for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include students for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
