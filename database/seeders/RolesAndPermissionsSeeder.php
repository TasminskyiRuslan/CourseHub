<?php

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

        $student = Role::findOrCreate(UserRole::STUDENT->value);
        $teacher = Role::findOrCreate(UserRole::TEACHER->value);
        $admin = Role::findOrCreate(UserRole::ADMIN->value);
        $superAdmin = Role::findOrCreate(UserRole::SUPER_ADMIN->value);

        $teacher->syncPermissions([
            UserPermission::COURSE_CREATE->value,

            UserPermission::LESSON_CREATE->value,
        ]);
        $admin->syncPermissions([
            UserPermission::COURSE_VIEW_ANY_UNPUBLISHED->value,
            UserPermission::COURSE_DELETE_ANY->value,
            UserPermission::COURSE_UNPUBLISH_ANY->value,

            UserPermission::LESSON_DELETE_ANY->value,

            UserPermission::USER_VIEW_ANY->value,
            UserPermission::USER_DELETE_ANY->value,
            UserPermission::USER_ROLE_EDIT_ANY->value,
            UserPermission::USER_BAN_ANY->value,
            UserPermission::USER_UNBAN_ANY->value,
        ]);
    }
}
