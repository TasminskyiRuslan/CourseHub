@component('mail::message')
    # Account Banned

    Hello, {{ $user->name }} ({{ $user->email }}).

    Your account has been banned. Please contact support for more information.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
