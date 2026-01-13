<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_students';

    protected $fillable = [
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'program_id',
        'enrollment_year',
        'graduation_year',
        'status',
        'admission_level',
        'company_id',
        'branch_id',
        // New fields
        'admission_date',
        'nationality',
        'id_number',
        'permanent_address',
        'current_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'previous_school',
        'qualification',
        'grade_score',
        'completion_year',
        'student_photo'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'enrollment_year' => 'integer',
        'graduation_year' => 'integer',
        'completion_year' => 'integer',
        'password' => 'hashed',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function department()
    {
        return $this->hasOneThrough(Department::class, Program::class, 'id', 'id', 'program_id', 'department_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function parents()
    {
        return $this->belongsToMany(CollegeGuardian::class, 'college_student_parent', 'student_id', 'parent_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    /**
     * Get the course enrollments for the student
     */
    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id');
    }

    /**
     * Get active course enrollments for the student
     */
    public function activeCourseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id')
            ->where('status', 'enrolled');
    }

    /**
     * Get the fee invoices for the student
     */
    public function feeInvoices()
    {
        return $this->hasMany(FeeInvoice::class, 'student_id');
    }

    /**
     * Check if student has outstanding fee balance
     * 
     * @return bool
     */
    public function hasOutstandingFees(): bool
    {
        return $this->feeInvoices()
            ->whereIn('status', ['issued', 'overdue'])
            ->where('total_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->exists();
    }

    /**
     * Get total outstanding fee balance
     * 
     * @return float
     */
    public function getOutstandingBalance(): float
    {
        return $this->feeInvoices()
            ->whereIn('status', ['issued', 'overdue'])
            ->selectRaw('SUM(total_amount - paid_amount) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Check if student is eligible for examinations (has paid fees)
     * 
     * @return bool
     */
    public function canTakeExamination(): bool
    {
        return !$this->hasOutstandingFees();
    }

    /**
     * Get payment status summary for the student
     * 
     * @return array
     */
    public function getPaymentStatus(): array
    {
        $invoices = $this->feeInvoices()
            ->whereIn('status', ['issued', 'overdue', 'paid'])
            ->get();

        $totalAmount = $invoices->sum('total_amount');
        $paidAmount = $invoices->sum('paid_amount');
        $outstandingAmount = $totalAmount - $paidAmount;

        return [
            'total_invoiced' => $totalAmount,
            'total_paid' => $paidAmount,
            'outstanding_balance' => $outstandingAmount,
            'is_cleared' => $outstandingAmount <= 0,
            'payment_percentage' => $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 100,
        ];
    }
}