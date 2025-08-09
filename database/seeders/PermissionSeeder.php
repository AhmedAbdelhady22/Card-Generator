<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'create_cards',
                'slug' => 'create-cards',
                'description' => 'Can create new cards'
            ],
            [
                'name' => 'edit_cards',
                'slug' => 'edit-cards',
                'description' => 'Can edit existing cards'
            ],
            [
                'name' => 'delete_cards',
                'slug' => 'delete-cards',
                'description' => 'Can delete cards'
            ],
            [
                'name' => 'download_pdf',
                'slug' => 'download-pdf',
                'description' => 'Can download PDF versions of cards'
            ],
            [
                'name' => 'view_cards',
                'slug' => 'view-cards',
                'description' => 'Can view cards'
            ],
            [
                'name' => 'manage_users',
                'slug' => 'manage-users',
                'description' => 'Can manage user accounts'
            ],
            [
                'name' => 'manage_permissions',
                'slug' => 'manage-permissions',
                'description' => 'Can manage user permissions'
            ],
            [
                'name' => 'view_admin_panel',
                'slug' => 'view-admin-panel',
                'description' => 'Can access admin panel'
            ],
            [
                'name' => 'view_activity_logs',
                'slug' => 'view-activity-logs',
                'description' => 'Can view activity logs'
            ],
            [
                'name' => 'export_data',
                'slug' => 'export-data',
                'description' => 'Can export data from the system'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
