<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalProduct extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'unit',
        'price',
        'stock_quantity',
        'min_stock_level',
        'nhif_eligible',
        'chf_eligible',
        'jubilee_eligible',
        'strategy_eligible',
        'is_active',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'nhif_eligible' => 'boolean',
        'chf_eligible' => 'boolean',
        'jubilee_eligible' => 'boolean',
        'strategy_eligible' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInStock($query)
    {
        return $query->whereColumn('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }
}
