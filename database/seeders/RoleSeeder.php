<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'hierarchy_level' => 3,
                'description' => 'Administrator with full access to all features and data'
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'hierarchy_level' => 2,
                'description' => 'Manager who can manage tasks and see employee tasks'
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'hierarchy_level' => 1,
                'description' => 'Employee who can only manage their own tasks'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
