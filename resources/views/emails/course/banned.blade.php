@component('mail::message')
    # Course Banned

    Hello, {{ $user->name }} ({{ $user->email }}).

    Your course **"{{ $course->title }}"** has been banned.

    @component('mail::button', ['url' => $url])
        View Course
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
