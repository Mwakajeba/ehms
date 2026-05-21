<?php

use App\Models\Company;
use App\Models\Hospital\HospitalInsuranceType;
use App\Models\Hospital\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('insurance_type_id')
                ->nullable()
                ->after('insurance_number')
                ->constrained('hospital_insurance_types')
                ->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE patients MODIFY insurance_type VARCHAR(100) NULL DEFAULT 'None'");
        }

        $defaultNames = ['None', 'NHIF', 'CHF', 'Jubilee', 'Strategy'];
        $sort = 0;

        foreach (Company::query()->pluck('id') as $companyId) {
            $typeIdsByName = [];

            foreach ($defaultNames as $name) {
                $type = HospitalInsuranceType::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $name,
                    ],
                    [
                        'code' => strtoupper(str_replace(' ', '_', $name)),
                        'is_none' => $name === 'None',
                        'is_active' => true,
                        'sort_order' => $sort++,
                    ]
                );
                $typeIdsByName[$name] = $type->id;
            }

            Patient::query()
                ->where('company_id', $companyId)
                ->whereNull('insurance_type_id')
                ->chunkById(200, function ($patients) use ($typeIdsByName) {
                    foreach ($patients as $patient) {
                        $name = $patient->insurance_type ?: 'None';
                        if (!isset($typeIdsByName[$name])) {
                            $name = 'None';
                        }
                        $patient->update([
                            'insurance_type_id' => $typeIdsByName[$name],
                            'insurance_type' => $name,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insurance_type_id');
        });
    }
};
