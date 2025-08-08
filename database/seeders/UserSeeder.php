<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        $users = [
            [
                'email' => 'admin@cardgenerator.com',
                'defaults' => [
                    'name' => 'System Administrator',
                    'password' => Hash::make('admin123'),
                    'email_verified_at' => now(),
                    'role_id' => $adminRole ? $adminRole->id : 1,
                ]
            ],
            [
                'email' => 'john@example.com',
                'defaults' => [
                    'name' => 'John Doe',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole ? $userRole->id : 2,
                ]
            ],
            [
                'email' => 'jane@example.com',
                'defaults' => [
                    'name' => 'Jane Smith',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole ? $userRole->id : 2,
                ]
            ],
            [
                'email' => 'mike@example.com',
                'defaults' => [
                    'name' => 'Mike Johnson',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole ? $userRole->id : 2,
                ]
            ],
            [
                'email' => 'test@test.com',
                'defaults' => [
                    'name' => 'Test User',
                    'password' => Hash::make('test123'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole ? $userRole->id : 2,
                ]
            ]
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData['defaults']
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}
