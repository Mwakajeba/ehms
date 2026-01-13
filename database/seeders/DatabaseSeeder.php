<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CompanySeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
            PermissionGroupsSeeder::class,
            MenuSeeder::class,
            ExamClassAssignmentPermissionsSeeder::class,
            AccountClassSeeder::class,
            MainGroupSeeder::class,
            AccountClassGroupSeeder::class,
            ChartAccountSeeder::class,
            PayrollChartAccountSeeder::class,
            CashFlowCategorySeeder::class,
            EquityCategorySeeder::class,
            CurrencySeeder::class,
            // Initialize system settings with defaults FIRST
            SystemSettingSeeder::class,
            // Run inventory settings AFTER chart accounts exist so defaults can be set
            InventorySettingsSeeder::class,
            HotelSettingsSeeder::class,
            AssetSettingsSeeder::class,
            AssetCategorySeeder::class,
            // Ensure users exist before seeding inventory locations
            UserSeeder::class,
            InventoryLocationSeeder::class,
            LocationUserSeeder::class,
            SupplierSeeder::class,
            BankAccountSeeder::class,
            TransportRevenueAccountSeeder::class,
            TanzaniaPublicHolidaySeeder::class,
            LeaveTypeSeeder::class,
            SalaryComponentSeeder::class,
            // Create default one-year subscriptions for all companies
            DefaultSubscriptionSeeder::class,
            // Hospital Management System
            HospitalDepartmentSeeder::class,
            // TestInventoryDataSeeder::class,
        ]);
    }
}
