<?php $__env->startComponent('mail::message'); ?>
    # Account Unbanned

    Hello, <?php echo new \Illuminate\Support\EncodedHtmlString($user->name); ?> (<?php echo new \Illuminate\Support\EncodedHtmlString($user->email); ?>).

    Your account has been successfully unbanned. You can now log in and use all the features of our platform.

    <?php $__env->startComponent('mail::button', ['url' => config('app.url')]); ?>
        Go to Dashboard
    <?php echo $__env->renderComponent(); ?>

    Regards,<br>
    <?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /var/www/html/resources/views/emails/user/unban.blade.php ENDPATH**/ ?>