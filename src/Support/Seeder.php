<?php

namespace Soda\Blog\Support;

use Illuminate\Database\Seeder as BaseSeeder;
use Soda\Cms\Models\Permission;
use Soda\Cms\Models\Role;

class Seeder extends BaseSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $role_author = Role::create([
            'name'         => 'author',
            'display_name' => 'Author',
            'description'  => 'Authors have access to create, read and edit blog posts.',
        ]);

        $permission_admin_blog = Permission::create([
            'name'         => 'admin-blog',
            'display_name' => 'Admin Blog',
            'description'  => 'Administrate blog settings.',
        ]);

        $permission_manage_blog = Permission::create([
            'name'         => 'manage-blog',
            'display_name' => 'Manage Blog',
            'description'  => 'Create, read and edit blog psots.',
        ]);

        $role_author->attachPermissions([
            $permission_manage_blog,
        ]);

        $adminRole = Role::whereName('admin')->first();

        if($adminRole) {
            $adminRole->attachPermissions([$permission_manage_blog, $permission_admin_blog]);
        }
    }
}
