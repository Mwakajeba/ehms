<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Permission as SpatiePermission;

class ExamClassAssignmentPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'exam_class_assignments.view',
            'exam_class_assignments.create',
            'exam_class_assignments.edit',
            'exam_class_assignments.delete',
            'exam_class_assignments.view_any',
            'exam_class_assignments.update',
            'exam_class_assignments.restore',
            'exam_class_assignments.force_delete',
        ];

        // Create permissions in both custom Permission table and Spatie table
        foreach ($permissions as $permissionName) {
            // Create in custom Permission table
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);

            // Also ensure it exists in Spatie permissions table
            try {
                SpatiePermission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
            } catch (\Exception $e) {
                // Permission might already exist, ignore
            }
        }

        $this->command->info('Exam class assignment permissions created successfully.');
    }
}