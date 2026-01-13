<?php

namespace App\Models\College;

use App\Models\College\FeeSetting;
use App\Models\College\FeeSettingItem;
use App\Models\College\Program;
use App\Models\College\Student;
use App\Models\FeeGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class FeeInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_fee_invoices';

    protected $fillable = [
        'invoice_number',
        'lipisha_control_number',
        'student_id',
        'program_id',
        'academic_year_id',
        'fee_group_id',
        'period',
        'subtotal',
        'transport_fare',
        'total_amount',
        'paid_amount',
        'due_date',
        'issue_date',
        'status',
        'notes',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'issue_date' => 'date',
        'subtotal' => 'decimal:2',
        'transport_fare' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $appends = ['hashid'];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function feeGroup()
    {
        return $this->belongsTo(FeeGroup::class, 'fee_group_id');
    }

    public function feeInvoiceItems()
    {
        return $this->hasMany(FeeInvoiceItem::class, 'college_fee_invoice_id');
    }

    public function glTransactions()
    {
        return $this->hasMany(\App\Models\GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'college_fee_invoice');
    }

    public function receipts()
    {
        return $this->hasMany(\App\Models\Receipt::class, 'reference')
            ->where('reference_type', 'college_fee_invoice');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class, 'academic_year_id');
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    // Accessors
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    // Static methods
    public static function getFeePeriodOptions()
    {
        return [
            'Semester 1' => 'Semester 1',
            'Semester 2' => 'Semester 2',
            'Full year' => 'Full Year',
        ];
    }

    // Methods
    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }

    public function markAsSent()
    {
        $this->update(['status' => 'issued']);
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
        return 'fee_invoice';
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
}