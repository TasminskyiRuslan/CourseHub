<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the roles and permissions.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (UserPermission::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $teacher = Role::findOrCreate(UserRole::TEACHER->value);
        $admin = Role::findOrCreate(UserRole::ADMIN->value);
        $superAdmin = Role::findOrCreate(UserRole::SUPER_ADMIN->value);

        $teacher->syncPermissions([
            UserPermission::TEACHER_PANEL_ACCESS->value,
            UserPermission::COURSES_CREATE->value,
            UserPermission::COURSES_UPDATE_OWN->value,
            UserPermission::COURSES_DELETE_OWN->value,
            UserPermission::COURSES_PUBLISH_OWN->value,
            UserPermission::LESSONS_CREATE->value,
            UserPermission::LESSONS_UPDATE_OWN->value,
            UserPermission::LESSONS_DELETE_OWN->value,
        ]);
        $admin->syncPermissions([
            UserPermission::ADMIN_PANEL_ACCESS->value,
            UserPermission::COURSES_DELETE_ALL->value,
            UserPermission::COURSES_BAN_ALL->value,
            UserPermission::LESSONS_DELETE_ALL->value,
            UserPermission::USERS_UPDATE_ROLES_ALL->value,
            UserPermission::USERS_DELETE_ALL->value,
            UserPermission::USERS_BAN_ALL->value,
        ]);
    }
}
