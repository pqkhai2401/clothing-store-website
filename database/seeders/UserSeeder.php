<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [
                $role->value => Role::firstOrCreate(['name' => $role->value]),
            ]);

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'Admin@123',
                'phone_number' => '0901000001',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'name' => 'Store Manager',
                'email' => 'manager@example.com',
                'password' => 'Admin@123',
                'phone_number' => '0901000002',
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ],
            [
                'name' => 'Sales Staff',
                'email' => 'staff@example.com',
                'password' => 'Staff@123',
                'phone_number' => '0902000001',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'name' => 'Order Staff',
                'email' => 'order.staff@example.com',
                'password' => 'Staff@123',
                'phone_number' => '0902000002',
                'role' => UserRole::STAFF,
                'is_active' => true,
            ],
            [
                'name' => 'Customer One',
                'email' => 'customer1@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000001',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'name' => 'Customer Two',
                'email' => 'customer2@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000002',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'name' => 'Customer Three',
                'email' => 'customer3@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000003',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ],
            [
                'name' => 'Locked Customer',
                'email' => 'locked.customer@example.com',
                'password' => 'Customer@123',
                'phone_number' => '0903000004',
                'role' => UserRole::CUSTOMER,
                'is_active' => false,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'phone_number' => $user['phone_number'],
                    'role_id' => $roles[$user['role']->value]->id,
                    'is_active' => $user['is_active'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
