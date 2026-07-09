<?php $__env->startComponent('mail::message'); ?>
    # Course Unbanned

    Hello, <?php echo new \Illuminate\Support\EncodedHtmlString($user->name); ?> (<?php echo new \Illuminate\Support\EncodedHtmlString($user->email); ?>).

    Your course **"<?php echo new \Illuminate\Support\EncodedHtmlString($course->title); ?>"** has been unbanned.

    <?php $__env->startComponent('mail::button', ['url' => $url]); ?>
        View Course
    <?php echo $__env->renderComponent(); ?>

    Regards,<br>
    <?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /var/www/html/resources/views/emails/course/unban.blade.php ENDPATH**/ ?>