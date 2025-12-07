<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@wms.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );

        // Warehouse Manager
        User::updateOrCreate(
            ['email' => 'manager@wms.test'],
            [
                'name' => 'Warehouse Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'status' => 'active',
                'is_approved' => true,
            ]
        );

        // Staff Gudang
        User::updateOrCreate(
            ['email' => 'staff@wms.test'],
            [
                'name' => 'Warehouse Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
                'is_approved' => true,
            ]
        );

        // Supplier
        User::updateOrCreate(
            ['email' => 'supplier@wms.test'],
            [
                'name' => 'Default Supplier',
                'password' => Hash::make('password'),
                'role' => 'supplier',
                'status' => 'active',
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => 1,
            ]
        );

    }
}
