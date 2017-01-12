<?php

namespace Soda\Blog\Support;

use Illuminate\Database\Seeder as BaseSeeder;
use Soda\Cms\Database\Permissions\Models\Permission;
use Soda\Cms\Database\Roles\Models\Role;

class Seeder extends BaseSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $role_author = Role::firstOrCreate([
            'name'         => 'author',
            'display_name' => 'Author',
            'description'  => 'Authors have access to create, read and edit blog posts.',
        ]);

        $permission_develop_blog = Permission::firstOrCreate([
            'name'         => 'develop-blog',
            'display_name' => 'Develop Blog',
            'description'  => 'Developer blog settings.',
            'category'     => 'Blog',
        ]);

        $permission_admin_blog = Permission::firstOrCreate([
            'name'         => 'admin-blog',
            'display_name' => 'Admin Blog',
            'description'  => 'Administrate blog settings.',
            'category'     => 'Blog',
        ]);

        $permission_manage_blog = Permission::firstOrCreate([
            'name'         => 'manage-blog',
            'display_name' => 'Manage Blog',
            'description'  => 'Create, read and edit blog posts.',
            'category'     => 'Blog',
        ]);

        $role_author->attachPermissions([
            $permission_manage_blog,
        ]);

        $developerRole = Role::whereName('developer')->first();

        if($developerRole) {
            $developerRole->attachPermissions([$permission_develop_blog]);
        }

        $adminRole = Role::whereName('admin')->first();

        if($adminRole) {
            $adminRole->attachPermissions([$permission_manage_blog, $permission_admin_blog]);
        }
    }
}
