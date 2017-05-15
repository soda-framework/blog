<?php

namespace Soda\Blog\Support;

use Soda\Cms\Database\Models\Role;
use Soda\Cms\Database\Models\Permission;
use Illuminate\Database\Seeder as BaseSeeder;

class InstallPermissions extends BaseSeeder
{
    /**
     * Auto generated seed file.
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

        $permission_develop_blog = Permission::create([
            'name'         => 'develop-blog',
            'display_name' => 'Develop Blog',
            'description'  => 'Developer blog settings.',
        ]);

        $permission_admin_blog = Permission::create([
            'name'         => 'admin-blog',
            'display_name' => 'Admin Blog',
            'description'  => 'Administrate blog settings.',
        ]);

        $permission_manage_blog = Permission::create([
            'name'         => 'manage-blog',
            'display_name' => 'Manage Blog',
            'description'  => 'Create, read and edit blog posts.',
        ]);

        $role_author->attachPermissions([
            $permission_manage_blog,
        ]);

        if ($developerRole = Role::whereName('developer')->first()) {
            $developerRole->attachPermissions([$permission_develop_blog, $permission_manage_blog, $permission_admin_blog]);
        }

        if ($adminRole = Role::whereName('admin')->first()) {
            $adminRole->attachPermissions([$permission_manage_blog, $permission_admin_blog]);
        }

        if ($superAdminRole = Role::whereName('super-admin')->first()) {
            $superAdminRole->attachPermissions([$permission_manage_blog, $permission_admin_blog]);
        }
    }
}
