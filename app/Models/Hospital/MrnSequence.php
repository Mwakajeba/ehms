<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MrnSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'sequence',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'date' => 'date',
        'sequence' => 'integer',
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

    /**
     * Generate next MRN sequence for today
     * Format: DDMMYY-N (e.g., 201224-1)
     */
    public static function generateNextMrn($companyId, $branchId = null)
    {
        $today = now();
        $dateKey = $today->format('Y-m-d');

        $sequence = self::firstOrCreate(
            [
                'date' => $dateKey,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ],
            ['sequence' => 0]
        );

        $sequence->increment('sequence');
        $sequence->refresh();

        $day = $today->format('d');
        $month = $today->format('m');
        $year = $today->format('y');
        $number = $sequence->sequence;

        return "{$day}{$month}{$year}-{$number}";
    }
}
