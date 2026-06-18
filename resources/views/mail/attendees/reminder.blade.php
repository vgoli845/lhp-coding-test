<x-mail::message>
@if ($window === '24-hour')
# It's almost time ⏰

**{{ $eventName }}** is happening in about 24 hours.
@else
# Coming up in 3 days 📅

A quick reminder that **{{ $eventName }}** is just 3 days away.
@endif

Hi {{ $attendeeName }},

**When:** {{ $startsAt->format('l, j F Y · H:i') }} (UTC)
**Where:** {{ $location }}

<x-mail::button :url="$url">
View event details
</x-mail::button>

Looking forward to seeing you,
{{ config('app.name') }}
</x-mail::message>
