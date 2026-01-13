<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionMachine;

class ProductionMachineSeeder extends Seeder
{
    public function run()
    {
        $machines = [
            // Knitting Machines
            [
                'machine_name' => 'Knitting Machine KM-001',
                'location' => 'Knitting Section A',
                'status' => 'used',
                'production_stage' => 'KNITTING',
                'gauge' => '12GG',
                'purchased_date' => '2023-01-15',
            ],
            [
                'machine_name' => 'Knitting Machine KM-002',
                'location' => 'Knitting Section A',
                'status' => 'used',
                'production_stage' => 'KNITTING',
                'gauge' => '14GG',
                'purchased_date' => '2023-03-20',
            ],
            [
                'machine_name' => 'Knitting Machine KM-003',
                'location' => 'Knitting Section B',
                'status' => 'used',
                'production_stage' => 'KNITTING',
                'gauge' => '12GG',
                'purchased_date' => '2022-11-10',
            ],
            
            // Cutting Machines
            [
                'machine_name' => 'Cutting Table CT-001',
                'location' => 'Cutting Section',
                'status' => 'used',
                'production_stage' => 'CUTTING',
                'gauge' => null,
                'purchased_date' => '2023-02-01',
            ],
            [
                'machine_name' => 'Electric Scissors ES-001',
                'location' => 'Cutting Section',
                'status' => 'new',
                'production_stage' => 'CUTTING',
                'gauge' => null,
                'purchased_date' => '2023-04-15',
            ],
            
            // Joining/Stitching Machines
            [
                'machine_name' => 'Overlock Machine OL-001',
                'location' => 'Stitching Section A',
                'status' => 'used',
                'production_stage' => 'JOINING',
                'gauge' => null,
                'purchased_date' => '2023-01-25',
            ],
            [
                'machine_name' => 'Overlock Machine OL-002',
                'location' => 'Stitching Section A',
                'status' => 'used',
                'production_stage' => 'JOINING',
                'gauge' => null,
                'purchased_date' => '2023-02-10',
            ],
            [
                'machine_name' => 'Chain Stitch Machine CS-001',
                'location' => 'Stitching Section B',
                'status' => 'used',
                'production_stage' => 'JOINING',
                'gauge' => null,
                'purchased_date' => '2023-03-05',
            ],
            
            // Embroidery Machines
            [
                'machine_name' => 'Embroidery Machine EM-001',
                'location' => 'Embroidery Section',
                'status' => 'new',
                'production_stage' => 'EMBROIDERY',
                'gauge' => null,
                'purchased_date' => '2023-05-20',
            ],
            [
                'machine_name' => 'Embroidery Machine EM-002',
                'location' => 'Embroidery Section',
                'status' => 'new',
                'production_stage' => 'EMBROIDERY',
                'gauge' => null,
                'purchased_date' => '2023-06-15',
            ],
            
            // Ironing/Finishing Equipment
            [
                'machine_name' => 'Steam Press SP-001',
                'location' => 'Finishing Section',
                'status' => 'used',
                'production_stage' => 'IRONING_FINISHING',
                'gauge' => null,
                'purchased_date' => '2023-02-20',
            ],
            [
                'machine_name' => 'Industrial Iron II-001',
                'location' => 'Finishing Section',
                'status' => 'used',
                'production_stage' => 'IRONING_FINISHING',
                'gauge' => null,
                'purchased_date' => '2023-03-10',
            ],
            
            // Packaging Equipment
            [
                'machine_name' => 'Packaging Station PS-001',
                'location' => 'Packaging Section',
                'status' => 'used',
                'production_stage' => 'PACKAGING',
                'gauge' => null,
                'purchased_date' => '2023-04-01',
            ],
            [
                'machine_name' => 'Heat Sealer HS-001',
                'location' => 'Packaging Section',
                'status' => 'new',
                'production_stage' => 'PACKAGING',
                'gauge' => null,
                'purchased_date' => '2023-04-20',
            ],
        ];

        foreach ($machines as $machine) {
            ProductionMachine::create($machine);
        }

        $this->command->info('Production machines seeded successfully for sweater production workflow!');
    }
}