<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends Model
{
    protected $fillable = [
        'student_id',
        'transfer_type',
        'academic_year_id',
        'status',
        'previous_school',
        'new_school',
        'transfer_date',
        'reason',
        'academic_records',
        'transfer_certificate_number',
        'outstanding_fees',
        'notes',
        'processed_by',
        'transfer_certificate',
        'academic_report'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'outstanding_fees' => 'decimal:2',
    ];

    /**
     * Get the transfer number attribute.
     */
    public function getTransferNumberAttribute()
    {
        return 'TRF-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the student name attribute.
     */
    public function getStudentNameAttribute()
    {
        return $this->student ? $this->student->first_name . ' ' . $this->student->last_name : 'N/A';
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return \Vinkla\Hashids\Facades\Hashids::encode($this->getKey());
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
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($value);
        $id = $decoded[0] ?? null;

        \Log::info('Resolving route binding', [
            'value' => $value,
            'decoded' => $decoded,
            'id' => $id
        ]);

        if (!$id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        $model = $this->where('id', $id)->first();
        if (!$model) {
            \Log::info('Model not found for ID: ' . $id);
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        return $model;
    }

    /**
     * Static method to resolve route binding.
     */
    public static function resolveRouteBindingStatic($value, $field = null)
    {
        $instance = new static();
        return $instance->resolveRouteBinding($value, $field);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'processed_by');
    }

    /**
     * Scope for transfer in records
     */
    public function scopeTransferIn($query)
    {
        return $query->where('transfer_type', 'transfer_in');
    }

    /**
     * Scope for transfer out records
     */
    public function scopeTransferOut($query)
    {
        return $query->where('transfer_type', 'transfer_out');
    }

    /**
     * Scope for re-admission records
     */
    public function scopeReAdmission($query)
    {
        return $query->where('transfer_type', 're_admission');
    }
}
