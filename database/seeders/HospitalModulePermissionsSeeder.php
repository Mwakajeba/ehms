<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

/**
 * Safely adds missing Hospital module permissions without re-syncing roles.
 * Run: php artisan db:seed --class=HospitalModulePermissionsSeeder
 */
class HospitalModulePermissionsSeeder extends Seeder
{
    /** @var list<string> */
    private array $hospitalPermissions = [
        'view hospital management',
        'view reception',
        'view cashier',
        'view triage',
        'view doctor',
        'view lab',
        'view ultrasound',
        // Audiology (new module — add these on existing DBs that were seeded before audiology)
        'view audiology',
        'create audiology result',
        'view audiology result',
        'mark audiology result ready',
        'view pharmacy',
        'view dental',
        'create dental procedure',
        'view dental procedure',
        'view rch',
        'view vaccine',
        'view injection',
        'view family planning',
        'view hospital admin',
        'view hospital reports',
    ];

    public function run(): void
    {
        $hospitalGroup = PermissionGroup::where('name', 'hospital')->first();

        foreach ($this->hospitalPermissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['permission_group_id' => $hospitalGroup?->id]
            );

            if ($hospitalGroup && !$permission->permission_group_id) {
                $permission->update(['permission_group_id' => $hospitalGroup->id]);
            }

            $this->command?->info("Permission ensured: {$name}");
        }

        $this->command?->warn('Assign permissions to roles under Settings → Roles & Permissions.');
    }
}
