@component('mail::message')
# Support Message

**From:** {{ $data['name'] }}
**Email:** {{ $data['email'] }}

## Message:
{{ $data['message'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
