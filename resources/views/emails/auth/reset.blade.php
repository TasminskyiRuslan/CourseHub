@component('mail::message')
    # Reset Password

    Click the button below to reset your password.

    @component('mail::button', ['url' => $url, 'color' => 'primary'])
        Reset Password
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
