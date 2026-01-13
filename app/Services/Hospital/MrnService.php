<?php

namespace App\Services\Hospital;

use App\Models\Hospital\MrnSequence;

class MrnService
{
    /**
     * Generate MRN in format: DDMMYY-N
     * Example: 201224-1 (20th Dec 2024, sequence 1)
     */
    public static function generate($companyId, $branchId = null): string
    {
        return MrnSequence::generateNextMrn($companyId, $branchId);
    }
}
