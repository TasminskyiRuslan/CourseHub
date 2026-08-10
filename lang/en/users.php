<?php

declare(strict_types=1);

return [
    'protected' => 'This auth is protected and cannot be modified.',
    'forbidden' => [
        'protected' => 'You cannot perform this action on administrators or super administrators.',
        'update_roles_self' => 'You cannot change your own roles.',
        'update_roles' => 'You do not have permission to update user roles.',
        'delete_self' => 'You cannot delete your own account.',
        'delete' => 'You do not have permission to delete users.',
        'ban_self' => 'You cannot ban or unban your own account.',
        'ban' => 'You do not have permission to ban or unban users.',
    ],
];
