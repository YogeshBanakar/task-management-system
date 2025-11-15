<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'View All Tasks',
                'slug' => 'view-all-tasks',
                'description' => 'Can view all tasks in the system'
            ],
            [
                'name' => 'Create Task',
                'slug' => 'create-task',
                'description' => 'Can create new tasks'
            ],
            [
                'name' => 'Edit Task',
                'slug' => 'edit-task',
                'description' => 'Can edit tasks'
            ],
            [
                'name' => 'Delete Task',
                'slug' => 'delete-task',
                'description' => 'Can delete tasks'
            ],
            [
                'name' => 'Assign Task',
                'slug' => 'assign-task',
                'description' => 'Can assign tasks to users'
            ],
            [
                'name' => 'View Users',
                'slug' => 'view-users',
                'description' => 'Can view users list'
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'manage-users',
                'description' => 'Can manage users'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
