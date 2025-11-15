<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first()->id);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
        $manager->roles()->attach(Role::where('slug', 'manager')->first()->id);

        $employee1 = User::create([
            'name' => 'Employee One',
            'email' => 'employee1@example.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
        $employee1->roles()->attach(Role::where('slug', 'employee')->first()->id);

        $employee2 = User::create([
            'name' => 'Employee Two',
            'email' => 'employee2@example.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
        $employee2->roles()->attach(Role::where('slug', 'employee')->first()->id);
    }
}
