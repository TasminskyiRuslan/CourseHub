@component('mail::message')
    # Reset Password

    Hello, {{ $user->name }} ({{ $user->email }}).

    Click the button below to reset your password.

    @component('mail::button', ['url' => $url, 'color' => 'primary'])
        Reset Password
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
