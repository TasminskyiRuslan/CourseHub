@component('mail::message')
    # Course Unbanned

    Hello, {{ $user->name }} ({{ $user->email }}).

    Your course **"{{ $course->title }}"** has been unbanned.

    @component('mail::button', ['url' => $url])
        View Course
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
