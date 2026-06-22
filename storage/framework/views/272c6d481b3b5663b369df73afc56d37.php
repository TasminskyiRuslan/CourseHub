<?php $__env->startComponent('mail::message'); ?>
    # Account Suspended

    Your account has been banned. Please contact support for more information.

    Regards,<br>
    <?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /var/www/html/resources/views/emails/user/banned.blade.php ENDPATH**/ ?>