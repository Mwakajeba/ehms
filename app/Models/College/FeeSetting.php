<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class FeeSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_fee_settings';

    protected $fillable = [
        'program_id',
        'fee_period',
        'date_from',
        'date_to',
        'category',
        'amount',
        'includes_transport',
        'description',
        'is_active',
        'company_id',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'amount' => 'decimal:2',
        'includes_transport' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function collegeFeeSettingItems()
    {
        return $this->hasMany(FeeSettingItem::class, 'college_fee_setting_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public static function getFeePeriodOptions()
    {
        return [
            'Semester 1' => 'Semester 1',
            'Semester 2' => 'Semester 2',
            'Full year' => 'Full year',
        ];
    }

    public static function getCategoryOptions()
    {
        return [
            'Regular' => 'Regular',
            'International' => 'International',
            'Special' => 'Special',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public static function findByHashid($hashid)
    {
        $decodedId = Hashids::connection('main')->decode($hashid)[0] ?? null;
        return static::find($decodedId);
    }

    /**
     * Get the route key name.
     */
    public function getRouteKeyName()
    {
        return 'hashid';
    }

    /**
     * Resolve the model from route binding.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::findByHashid($value);
    }
}
