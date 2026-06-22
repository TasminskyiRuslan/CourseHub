@component('mail::message')
    # Verify Email Address

    Please click the button below to confirm your email address.

    @component('mail::button', ['url' => $url, 'color' => 'primary'])
        Confirm Email
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
