<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Super Admin Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the system's root user. This name will be
    | assigned to the super admin account during the database seeding
    | process if the user does not already exist.
    |
    */
    'name' => env('SUPER_ADMIN_NAME', 'SuperAdmin'),

    /*
    |--------------------------------------------------------------------------
    | Super Admin Email Address
    |--------------------------------------------------------------------------
    |
    | This is the email address that will be used to identify the unique
    | super admin account. This email is also used by the seeder to
    | find or create the root administrator record.
    |
    */
    'email' => env('SUPER_ADMIN_EMAIL', 'super.admin@coursehub.com'),

    /*
    |--------------------------------------------------------------------------
    | Super Admin Password
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default password for the super admin.
    | For security reasons, it is highly recommended to change this
    | value in your .env file before deploying to production.
    |
    */
    'password' => env('SUPER_ADMIN_PASSWORD', 'secret'),
];
