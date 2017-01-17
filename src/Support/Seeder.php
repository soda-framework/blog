<?php

namespace Soda\Blog\Support;

use Illuminate\Database\Seeder as BaseSeeder;

class Seeder extends BaseSeeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        $roleModel = app('soda.role.model');
        $permissionModel = app('soda.permission.model');

        $roleAuthor = $roleModel->firstOrCreate([
            'name'         => 'author',
            'display_name' => 'Author',
            'description'  => 'Authors have access to create, read and edit blog posts.',
        ]);

        $permissionDevelopBlog = $permissionModel->firstOrCreate([
            'name'         => 'develop-blog',
            'display_name' => 'Develop Blog',
            'description'  => 'Developer blog settings.',
            'category'     => 'Blog',
        ]);

        $permissionAdminBlog = $permissionModel->firstOrCreate([
            'name'         => 'admin-blog',
            'display_name' => 'Admin Blog',
            'description'  => 'Administrate blog settings.',
            'category'     => 'Blog',
        ]);

        $permissionManageBlog = $permissionModel->firstOrCreate([
            'name'         => 'manage-blog',
            'display_name' => 'Manage Blog',
            'description'  => 'Create, read and edit blog posts.',
            'category'     => 'Blog',
        ]);

        $roleAuthor->attachPermissions([
            $permissionManageBlog,
        ]);

        if ($developerRole = $roleModel->whereName('developer')->first()) {
            $developerRole->attachPermissions([$permissionDevelopBlog]);
        }

        if ($adminRole = $roleModel->whereName('admin')->first()) {
            $adminRole->attachPermissions([$permissionManageBlog, $permissionAdminBlog]);
        }
    }
}
