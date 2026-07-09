<?php $__env->startComponent('mail::message'); ?>
    # Verify Email Address

    Hello, <?php echo new \Illuminate\Support\EncodedHtmlString($user->name); ?> (<?php echo new \Illuminate\Support\EncodedHtmlString($user->email); ?>).

    Please click the button below to confirm your email address.

    <?php $__env->startComponent('mail::button', ['url' => $url, 'color' => 'primary']); ?>
        Confirm Email
    <?php echo $__env->renderComponent(); ?>

    Regards,<br>
    <?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /var/www/html/resources/views/emails/auth/verify.blade.php ENDPATH**/ ?>