<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Role;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->warn('Admin role not found.');
            return;
        }

        $entities = [
            'Dashboard' => [
                'icon' => 'bx bx-home',
                'visibleRoutes' => [
                    ['name' => 'Dashboard', 'route' => 'dashboard'],
                    ['name' => 'Analytics', 'route' => 'analytics.index'],
                ],
                'hiddenRoutes' => [],
            ],
            'Accounting' => [
                'icon' => 'bx bx-calculator',
                'visibleRoutes' => [
                    ['name' => 'Accounting Management', 'route' => 'accounting.index'],
                   
                ],
                'hiddenRoutes' => [
                    'accounting.chart-accounts.create',
                    'accounting.chart-accounts.edit',
                    'accounting.chart-accounts.destroy',
                    'accounting.journals.edit',
                    'accounting.journals.destroy',
                    'accounting.journals.create',
                    'accounting.journals.show',
                    'accounting.fx-rates.create',
                    'accounting.fx-rates.edit',
                    'accounting.fx-rates.import',
                    'accounting.fx-rates.lock',
                    'accounting.fx-rates.unlock',
                    'accounting.fx-rates.process-import',
                    'accounting.fx-rates.download-sample',
                    'accounting.fx-rates.get-rate',
                    'accounting.fx-revaluation.create',
                    'accounting.fx-revaluation.preview',
                    'accounting.fx-revaluation.store',
                    'accounting.fx-revaluation.show',
                    'accounting.fx-revaluation.reverse',
                    'accounting.fx-settings.update',
                    'accounting.accruals-prepayments.create',
                    'accounting.accruals-prepayments.edit',
                    'accounting.accruals-prepayments.destroy',
                    'accounting.accruals-prepayments.show',
                    'accounting.accruals-prepayments.submit',
                    'accounting.accruals-prepayments.approve',
                    'accounting.accruals-prepayments.reject',
                    'accounting.accruals-prepayments.post-journal',
                    'accounting.accruals-prepayments.post-all-pending',
                    'accounting.accruals-prepayments.amortisation-schedule',
                    'accounting.accruals-prepayments.export-pdf',
                    'accounting.accruals-prepayments.export-excel'
                ],
            ],
            'Inventory & Services' => [
                'icon' => 'bx bx-package',
                'visibleRoutes' => [
                    ['name' => 'Inventory Management', 'route' => 'inventory.index'],
                ],
                'hiddenRoutes' => ['inventory.items.index', 'inventory.items.create', 'inventory.items.edit', 'inventory.items.destroy', 'inventory.items.show', 'inventory.categories.index', 'inventory.categories.create', 'inventory.categories.edit', 'inventory.categories.destroy', 'inventory.movements.index', 'inventory.movements.create', 'inventory.movements.edit', 'inventory.movements.destroy'],
            ],

            // 'Cash Deposits' => [
            //     'icon' => 'bx bx-outline',
            //     'visibleRoutes' => [
            //         ['name' => 'Cash Deposit Accounts', 'route' => 'cash_collateral_types.index'],
            //         ['name' => 'Cash Deposits', 'route' => 'cash_collaterals.index'],
            //     ],
            //     'hiddenRoutes' => ['cash_collateral_types.create', 'cash_collateral_types.edit', 'cash_collateral_types.destroy', 'cash_collateral_types.show', 'cash_collaterals.create', 'cash_collaterals.edit', 'cash_collaterals.destroy', 'cash_collaterals.show'],
            // ],

            'Imprests' => [
                'icon' => 'bx bx-money',
                'visibleRoutes' => [
                    ['name' => 'Imprest Management', 'route' => 'imprest.index'],
                ],
                'hiddenRoutes' => [
                    'imprest.requests.index',
                    'imprest.requests.create',
                    'imprest.requests.edit',
                    'imprest.requests.destroy',
                    'imprest.requests.show',
                    'imprest.requests.store',
                    'imprest.requests.update',
                    'imprest.checked.index',
                    'imprest.approved.index',
                    'imprest.disbursed.index',
                    'imprest.closed.index',
                    'imprest.checked.check',
                    'imprest.approved.approve',
                    'imprest.disbursed.disburse',
                    'imprest.close',
                    'imprest.liquidation.create',
                    'imprest.liquidation.store',
                    'imprest.liquidation.show',
                    'imprest.liquidation.verify',
                    'imprest.liquidation.approve',
                ],
            ],

            'Store Requisitions' => [
                'icon' => 'bx bx-package',
                'visibleRoutes' => [
                    ['name' => 'Store Requisition Management', 'route' => 'store-requisitions.index'],
                ],
                'hiddenRoutes' => [
                    'store-requisitions.create',
                    'store-requisitions.edit',
                    'store-requisitions.destroy',
                    'store-requisitions.show',
                    'store-requisitions.store',
                    'store-requisitions.update',
                    'store-requisitions.approve',
                    'store-requisitions.approval-settings.index',
                    'store-requisitions.approval-settings.store',
                    'store-requisitions.approval-settings.reset',
                    'store-requisitions.approval-settings.test-configuration',
                    'store-requisitions.data',
                    'store-requisitions.export',
                    'store-requisitions.print',
                    'store-issues.index',
                    'store-issues.create',
                    'store-issues.edit',
                    'store-issues.destroy',
                    'store-issues.show',
                    'store-issues.store',
                    'store-issues.update',
                    'store-issues.data',
                    'store-issues.export',
                    'store-issues.print',
                    'store-returns.index',
                    'store-returns.create',
                    'store-returns.edit',
                    'store-returns.destroy',
                    'store-returns.show',
                    'store-returns.store',
                    'store-returns.update',
                    'store-returns.data',
                    'store-returns.export',
                    'store-returns.print',
                ],
            ],

            'Sales' => [
                'icon' => 'bx bx-shopping-bag',
                'visibleRoutes' => [
                    ['name' => 'Sales Management', 'route' => 'sales.index'],
                ],
                'hiddenRoutes' => ['sales.proformas.index', 'sales.proformas.create', 'sales.proformas.edit', 'sales.proformas.destroy', 'sales.proformas.show', 'sales.proformas.store', 'sales.proformas.update', 'sales.test-auth'],
            ],
            'Purchases' => [
                'icon' => 'bx bx-shopping-bag',
                'visibleRoutes' => [
                    ['name' => 'Purchases Management', 'route' => 'purchases.index'],
                ],
                'hiddenRoutes' => [
                    'purchases.quotations.index',
                    'purchases.quotations.create',
                    'purchases.quotations.edit',
                    'purchases.quotations.destroy',
                    'purchases.quotations.show'
                ],
            ],

            'HR & Payroll' => [
                'icon' => 'bx bx-user',
                'visibleRoutes' => [
                    ['name' => 'HR & Payroll', 'route' => 'hr-payroll.index'],
                ],
                'hiddenRoutes' => [],
            ],
            
            'Assets Management' => [
                'icon' => 'bx bx-building',
                'visibleRoutes' => [
                    ['name' => 'Assets Management', 'route' => 'assets.index'],
                ],
                'hiddenRoutes' => [],
            ],
            'Loans Payable' => [
                'icon' => 'bx bx-money',
                'visibleRoutes' => [
                    ['name' => 'Loan Management', 'route' => 'loans.index'],
                ],
                'hiddenRoutes' => [
                    'loans.create',
                    'loans.edit',
                    'loans.destroy',
                    'loans.show',
                    'loans.store',
                    'loans.update',
                    'loans.disburse',
                    'loans.payments.create',
                    'loans.payments.store',
                    'loans.schedule.generate',
                    'loans.export.pdf',
                    'loans.export.excel',
                ],
            ],

            'Hospital' => [
                'icon' => 'bx bx-plus-medical',
                'visibleRoutes' => [
                    ['name' => 'Hospital Management', 'route' => 'hospital.index'],
                ],
                'hiddenRoutes' => [
                    // Main module routes
                    'hospital.reception.index',
                    'hospital.cashier.index',
                    'hospital.triage.index',
                    'hospital.doctor.index',
                    'hospital.lab.index',
                    'hospital.ultrasound.index',
                    'hospital.pharmacy.index',
                    'hospital.dental.index',
                    'hospital.rch.index',
                    'hospital.vaccine.index',
                    'hospital.injection.index',
                    'hospital.family-planning.index',
                    'hospital.admin.index',
                    'hospital.reports.index',
                    // Reception routes
                    'hospital.reception.patients.create',
                    'hospital.reception.patients.store',
                    'hospital.reception.patients.show',
                    'hospital.reception.patients.edit',
                    'hospital.reception.patients.update',
                    'hospital.reception.patients.search',
                    'hospital.reception.patients.request-deletion',
                    'hospital.reception.visits.create',
                    'hospital.reception.visits.store',
                    'hospital.reception.visits.show',
                    'hospital.reception.visits.location',
                    'hospital.reception.visits.print-results',
                    // Cashier routes
                    'hospital.cashier.bills.index',
                    'hospital.cashier.bills.show',
                    'hospital.cashier.payments.create',
                    'hospital.cashier.payments.store',
                    'hospital.cashier.clear-bill',
                    // Triage routes
                    'hospital.triage.vitals.create',
                    'hospital.triage.vitals.store',
                    'hospital.triage.vitals.show',
                    'hospital.triage.route-patient',
                    // Doctor routes
                    'hospital.doctor.consultations.create',
                    'hospital.doctor.consultations.store',
                    'hospital.doctor.consultations.show',
                    'hospital.doctor.consultations.edit',
                    'hospital.doctor.consultations.update',
                    // Lab routes
                    'hospital.lab.results.create',
                    'hospital.lab.results.store',
                    'hospital.lab.results.show',
                    'hospital.lab.results.edit',
                    'hospital.lab.results.update',
                    'hospital.lab.results.mark-ready',
                    // Ultrasound routes
                    'hospital.ultrasound.results.create',
                    'hospital.ultrasound.results.store',
                    'hospital.ultrasound.results.show',
                    'hospital.ultrasound.results.edit',
                    'hospital.ultrasound.results.update',
                    'hospital.ultrasound.results.mark-ready',
                    // Pharmacy routes
                    'hospital.pharmacy.dispensations.create',
                    'hospital.pharmacy.dispensations.store',
                    'hospital.pharmacy.dispensations.show',
                    'hospital.pharmacy.dispensations.dispense',
                    // Other department routes
                    'hospital.dental.procedures.create',
                    'hospital.dental.procedures.store',
                    'hospital.dental.procedures.show',
                    'hospital.rch.services.create',
                    'hospital.rch.services.store',
                    'hospital.rch.services.show',
                    'hospital.vaccine.record',
                    'hospital.injection.record',
                    'hospital.family-planning.services.create',
                    'hospital.family-planning.services.store',
                    'hospital.family-planning.services.show',
                    // Admin routes
                    'hospital.admin.departments.index',
                    'hospital.admin.departments.create',
                    'hospital.admin.departments.edit',
                    'hospital.admin.departments.destroy',
                    'hospital.admin.services.index',
                    'hospital.admin.services.create',
                    'hospital.admin.services.edit',
                    'hospital.admin.services.destroy',
                    'hospital.admin.products.index',
                    'hospital.admin.products.create',
                    'hospital.admin.products.edit',
                    'hospital.admin.products.destroy',
                    'hospital.admin.users.index',
                    'hospital.admin.users.assign-roles',
                    'hospital.admin.deletion-requests.index',
                    'hospital.admin.deletion-requests.approve',
                    'hospital.admin.deletion-requests.reject',
                    // Reports routes
                    'hospital.reports.clinical',
                    'hospital.reports.financial',
                    'hospital.reports.operational',
                    'hospital.reports.audit-logs',
                ],
            ],

            'Reports' => [
                'icon' => 'bx bx-file',
                'visibleRoutes' => [
                    ['name' => 'Accounting Reports', 'route' => 'reports.index'],
                    ['name' => 'Inventory Reports', 'route' => 'inventory.reports.index'],
                    ['name' => 'Sales Reports', 'route' => 'sales.reports.index'],
                    ['name' => 'Purchase Reports', 'route' => 'reports.purchases'],
                    ['name' => 'Payroll Reports', 'route' => 'hr.payroll-reports.index'],
                ],
                'hiddenRoutes' => [],
            ],

            'Settings' => [
                'icon' => 'bx bx-cog',
                'visibleRoutes' => [
                    ['name' => 'General Settings', 'route' => 'settings.index'],
                ],
                'hiddenRoutes' => ['settings.company', 'settings.branches', 'settings.user', 'settings.system', 'settings.backup', 'settings.branches.create', 'settings.branches.edit', 'settings.branches.destroy', 'settings.filetypes.index', 'settings.filetypes.create', 'settings.filetypes.edit', 'settings.filetypes.destroy', 'settings.inventory.index', 'settings.inventory.update', 'settings.inventory.locations.index', 'settings.inventory.locations.create', 'settings.inventory.locations.edit', 'settings.inventory.locations.destroy'],
            ],

            // Add Change Branch menu under Dashboard
            'Change Branch' => [
                'icon' => 'bx bx-transfer',
                'visibleRoutes' => [
                    ['name' => 'Change Branch', 'route' => 'change-branch'],
                ],
                'hiddenRoutes' => [],
            ],
        ];

        foreach ($entities as $parentName => $data) {
            $parent = Menu::firstOrCreate([
                'name' => $parentName,
                'route' => null,
                'parent_id' => null,
                'icon' => $data['icon'],
            ]);

            $menuIds = [$parent->id];

            // Only visible menu entries
            foreach ($data['visibleRoutes'] as $child) {
                $childMenu = Menu::firstOrCreate([
                    'name' => $child['name'],
                    'route' => $child['route'],
                    'parent_id' => $parent->id,
                    'icon' => 'bx bx-right-arrow-alt',
                ]);

                $menuIds[] = $childMenu->id;
            }

            // Hidden permission-only routes (not shown in menu)
            // These routes are for permissions only and should not be created as menu entries
            // They are handled by the permission system directly

            $superAdminRole = Role::where('name', 'super-admin')->first();

            $superAdminRole->menus()->syncWithoutDetaching($menuIds);

            $adminRole->menus()->syncWithoutDetaching($menuIds);
        }
    }
}
