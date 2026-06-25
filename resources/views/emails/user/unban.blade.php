@component('mail::message')
    # Account Unbanned

    Hello, {{ $user->name }} ({{ $user->email }}).

    Your account has been successfully unbanned. You can now log in and use all the features of our platform.

    @component('mail::button', ['url' => config('app.url')])
        Go to Dashboard
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
