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
            ['name' => 'View Cards', 'slug' => 'cards.view', 'description' => 'View business cards'],
            ['name' => 'Create Cards', 'slug' => 'cards.create', 'description' => 'Create new business cards'],
            ['name' => 'Edit Cards', 'slug' => 'cards.edit', 'description' => 'Edit business cards'],
            ['name' => 'Delete Cards', 'slug' => 'cards.delete', 'description' => 'Delete business cards'],
            
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Delete users'],
            
            ['name' => 'View Activity Logs', 'slug' => 'logs.view', 'description' => 'View activity logs'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'description' => 'Manage user permissions'],
            
            ['name' => 'View All Cards', 'slug' => 'cards.view.all', 'description' => 'View all users cards'],
            ['name' => 'Manage System', 'slug' => 'system.manage', 'description' => 'Full system management'],
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
        $userPermissions = Permission::whereIn('slug', [
            'cards.view',
            'cards.create',
            'cards.edit',
            'cards.delete'
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
