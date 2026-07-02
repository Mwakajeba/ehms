<?php

namespace App\Console\Commands;

use App\Models\Hospital\Patient;
use Illuminate\Console\Command;

class NormalizePatientPhoneNumbers extends Command
{
    protected $signature = 'patients:normalize-phones
                            {--dry-run : Show changes without updating the database}
                            {--company= : Limit to a company ID}
                            {--branch= : Limit to a branch ID}';

    protected $description = 'Normalize patient phone numbers to start with 255 (Tanzania format)';

    public function handle(): int
    {
        if (!function_exists('normalize_phone_number')) {
            $this->error('Phone helper is not loaded.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $query = Patient::query()->withTrashed();

        if ($companyId = $this->option('company')) {
            $query->where('company_id', (int) $companyId);
        }

        if ($branchId = $this->option('branch')) {
            $query->where('branch_id', (int) $branchId);
        }

        $patients = $query->get();
        $updated = 0;
        $skipped = 0;

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Processing {$patients->count()} patient record(s)...");

        foreach ($patients as $patient) {
            $changes = [];

            foreach (['phone', 'next_of_kin_phone'] as $field) {
                $original = $patient->{$field};
                if ($original === null || trim((string) $original) === '') {
                    continue;
                }

                $normalized = normalize_phone_number($original);
                if ($normalized !== $original) {
                    $changes[$field] = [$original, $normalized];
                }
            }

            if ($changes === []) {
                $skipped++;
                continue;
            }

            $this->line("Patient #{$patient->id} ({$patient->mrn}):");
            foreach ($changes as $field => [$from, $to]) {
                $this->line("  {$field}: {$from} -> {$to}");
            }

            if (!$dryRun) {
                foreach ($changes as $field => [, $to]) {
                    $patient->{$field} = $to;
                }
                $patient->saveQuietly();
            }

            $updated++;
        }

        $this->newLine();
        $this->info("Updated: {$updated}");
        $this->info("Unchanged: {$skipped}");

        if ($dryRun && $updated > 0) {
            $this->warn('Dry run only — no records were saved. Re-run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}
