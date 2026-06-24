<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [
                $role->value => Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']),
            ]);

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'Admin@123',
                'phone_number' => '0901000001',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Store Manager',
                'email' => 'manager@example.com',
                'password' => 'Admin@123',
                'phone_number' => '0901000002',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'username' => 'Sales Staff',
                'email' => 'staff@example.com',
                'password' => 'Staff@123',
                'phone_number' => '0902000001',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'username' => 'Order Staff',
                'email' => 'order.staff@example.com',
                'password' => 'Staff@123',
                'phone_number' => '0902000002',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'username' => 'Customer One',
                'email' => 'customer1@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000001',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Customer Two',
                'email' => 'customer2@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000002',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Customer Three',
                'email' => 'customer3@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000003',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'username' => 'Locked Customer',
                'email' => 'locked.customer@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000004',
                'role' => UserRole::CUSTOMER,
                'is_active' => false,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'phone_number' => $userData['phone_number'],
                    'is_active' => $userData['is_active'],
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$userData['role']->value]);
        }
    }
}
