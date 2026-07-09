<?php $__env->startComponent('mail::message'); ?>
    # Reset Password

    Hello, <?php echo new \Illuminate\Support\EncodedHtmlString($user->name); ?> (<?php echo new \Illuminate\Support\EncodedHtmlString($user->email); ?>).

    Click the button below to reset your password.

    <?php $__env->startComponent('mail::button', ['url' => $url, 'color' => 'primary']); ?>
        Reset Password
    <?php echo $__env->renderComponent(); ?>

    Regards,<br>
    <?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /var/www/html/resources/views/emails/auth/reset.blade.php ENDPATH**/ ?>