<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Inventory\Item;
use App\Models\User;

class ProductionTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user's company_id for sample data
        $user = User::first();
        if (!$user || !$user->company_id) {
            $this->command->error('No user with company_id found. Please ensure users are properly set up.');
            return;
        }

        $companyId = $user->company_id;

        // Get a branch ID for the customer
        $branch = \App\Models\Branch::where('company_id', $companyId)->first();
        if (!$branch) {
            $this->command->error('No branch found for company. Please ensure branches are set up.');
            return;
        }
        $branchId = $branch->id;

        // Create sample customers if none exist for this company
        $existingCustomers = Customer::where('company_id', $companyId)->count();
        if ($existingCustomers < 3) {
            // Get the next customer number
            $lastCustomerNo = Customer::max('customerNo') ?? 100000;
            
            $customers = [
                [
                    'customerNo' => $lastCustomerNo + 1,
                    'name' => 'Textile Fashion Ltd',
                    'email' => 'orders@textilefashion.com',
                    'phone' => '+1234567890',
                    'description' => 'Leading textile and fashion retailer',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'status' => 'active',
                ],
                [
                    'customerNo' => $lastCustomerNo + 2,
                    'name' => 'Urban Wear Co',
                    'email' => 'sales@urbanwear.com',
                    'phone' => '+1234567891',
                    'description' => 'Urban and casual wear company',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'status' => 'active',
                ],
                [
                    'customerNo' => $lastCustomerNo + 3,
                    'name' => 'Classic Clothing',
                    'email' => 'info@classicclothing.com',
                    'phone' => '+1234567892',
                    'description' => 'Classic and formal clothing manufacturer',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'status' => 'active',
                ]
            ];

            foreach ($customers as $customerData) {
                Customer::firstOrCreate(
                    ['email' => $customerData['email']],
                    $customerData
                );
            }

            $this->command->info('Sample customers created.');
        }

        // Create sample items (raw materials) if none exist for this company
        $existingItems = Item::where('company_id', $companyId)
            ->where('item_type', 'product')
            ->count();
            
        if ($existingItems < 5) {
            $materials = [
                [
                    'name' => 'Cotton Yarn - White',
                    'sku' => 'YARN-COT-WHT',
                    'description' => '100% Cotton yarn for knitting',
                    'item_type' => 'product',
                    'unit_of_measure' => 'kg',
                    'cost_price' => 25.00,
                    'selling_price' => 35.00,
                    'is_active' => true,
                    'company_id' => $companyId,
                ],
                [
                    'name' => 'Cotton Yarn - Blue',
                    'sku' => 'YARN-COT-BLU',
                    'description' => '100% Cotton yarn for knitting - Blue',
                    'item_type' => 'product',
                    'unit_of_measure' => 'kg',
                    'cost_price' => 25.00,
                    'selling_price' => 35.00,
                    'is_active' => true,
                    'company_id' => $companyId,
                ],
                [
                    'name' => 'Polyester Thread',
                    'sku' => 'THR-POLY',
                    'description' => 'Strong polyester thread for joining',
                    'item_type' => 'product',
                    'unit_of_measure' => 'm',
                    'cost_price' => 0.05,
                    'selling_price' => 0.08,
                    'is_active' => true,
                    'company_id' => $companyId,
                ],
                [
                    'name' => 'Clothing Labels',
                    'sku' => 'LBL-CLO',
                    'description' => 'Brand labels for garments',
                    'item_type' => 'product',
                    'unit_of_measure' => 'piece',
                    'cost_price' => 0.20,
                    'selling_price' => 0.35,
                    'is_active' => true,
                    'company_id' => $companyId,
                ],
                [
                    'name' => 'Packaging Bags',
                    'sku' => 'PKG-BAG',
                    'description' => 'Plastic bags for packaging finished products',
                    'item_type' => 'product',
                    'unit_of_measure' => 'piece',
                    'cost_price' => 0.10,
                    'selling_price' => 0.15,
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            ];

            foreach ($materials as $materialData) {
                Item::firstOrCreate(
                    ['sku' => $materialData['sku']],
                    $materialData
                );
            }

            $this->command->info('Sample materials created.');
        }

        $this->command->info('Production test data seeded successfully!');
        $this->command->info('Customers: ' . Customer::where('company_id', $companyId)->count());
        $this->command->info('Materials: ' . Item::where('company_id', $companyId)->where('item_type', 'product')->count());
    }
}