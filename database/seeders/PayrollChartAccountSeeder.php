<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hr\PayrollChartAccount;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class PayrollChartAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder populates the external_loan_payable_account_id for all companies
     * using the "Loan Deductions Payable" account (ID 664) as the default.
     */
    public function run(): void
    {
        // Get the Loan Deductions Payable account ID
        $loanDeductionsPayableAccountId = DB::table('chart_accounts')
            ->where('account_code', '2222')
            ->where('account_name', 'Loan Deductions Payable')
            ->value('id');

        if (!$loanDeductionsPayableAccountId) {
            $this->command->warn('Loan Deductions Payable account (2222) not found. Skipping external loan account seeding.');
            return;
        }

        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            // Get or create payroll chart account settings for this company
            $payrollChartAccount = PayrollChartAccount::firstOrCreate(
                ['company_id' => $company->id],
                []
            );

            // Update external_loan_payable_account_id if it's not already set
            if (!$payrollChartAccount->external_loan_payable_account_id) {
                $payrollChartAccount->update([
                    'external_loan_payable_account_id' => $loanDeductionsPayableAccountId
                ]);
                
                $this->command->info("Set external loan payable account for company: {$company->name} (ID: {$company->id})");
            } else {
                $this->command->line("Company {$company->name} already has external loan payable account configured.");
            }
        }

        $this->command->info('Payroll chart account seeding completed successfully!');
    }
}
