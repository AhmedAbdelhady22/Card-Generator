<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Card permissions
            ['name' => 'view_cards', 'slug' => 'view_cards', 'description' => 'View business cards'],
            ['name' => 'create_cards', 'slug' => 'create_cards', 'description' => 'Create new business cards'],
            ['name' => 'edit_cards', 'slug' => 'edit_cards', 'description' => 'Edit business cards'],
            ['name' => 'delete_cards', 'slug' => 'delete_cards', 'description' => 'Delete business cards'],
            ['name' => 'download_pdf', 'slug' => 'download_pdf', 'description' => 'Download PDF versions of cards'],
            
            // User management permissions
            ['name' => 'manage_users', 'slug' => 'manage_users', 'description' => 'Manage user accounts'],
            ['name' => 'manage_permissions', 'slug' => 'manage_permissions', 'description' => 'Manage user permissions and roles'],
            
            // Admin panel permissions
            ['name' => 'view_admin_panel', 'slug' => 'view_admin_panel', 'description' => 'Access admin panel'],
            ['name' => 'view_activity_logs', 'slug' => 'view_activity_logs', 'description' => 'View system activity logs'],
            ['name' => 'export_data', 'slug' => 'export_data', 'description' => 'Export system data'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator with full access']
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['description' => 'Regular user with limited access']
        );

        // Assign all permissions to admin
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        // Assign basic permissions to user role
        $userPermissions = Permission::whereIn('name', [
            'view_cards',
            'create_cards', 
            'edit_cards',
            'delete_cards',
            'download_pdf'
        ])->pluck('id');
        
        $userRole->permissions()->sync($userPermissions);

        // Create default admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@cardgenerator.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create default regular user
        $regularUser = User::firstOrCreate(
            ['email' => 'user@cardgenerator.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('user123'),
                'role_id' => $userRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Admin user: admin@cardgenerator.com / admin123');
        $this->command->info('Regular user: user@cardgenerator.com / user123');
    }
}
