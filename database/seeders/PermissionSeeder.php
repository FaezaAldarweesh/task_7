<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [

            'All roles',
            'View role',
            'Add role',
            'Edit role',
            'Delete role',

            'All Users',
            'View User',
            'Add User',
            'Edit User',
            'soft Delete User',
            'all trashed User',
            'restore user',
            'forceDelete user',
            
            'All tasks',
            'View task',
            'Add task',
            'Edit task',
            'soft Delete task',
            'all trashed task',
            'restore task',
            'forceDelete task',
            'update status',
            'task assign',
            'task reassign',
            'task blocked',
            
            'add comment',
            'update comment',
            'all comment',
            'delete comment',

            'add_Attachment',

        ];

        foreach ($permissions as $permission) {

            Permission::create(['name' => $permission]);
        }
    }
}
