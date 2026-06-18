<x-mail::message>
# You're on the list 🎉

Hi {{ $attendeeName }},

Thanks for registering for **{{ $eventName }}**. We've saved your spot.

**When:** {{ $startsAt->format('l, j F Y · H:i') }} (UTC)
**Where:** {{ $location }}

<x-mail::button :url="$url">
View event details
</x-mail::button>

We'll send you a reminder as the date approaches.

See you there,
{{ config('app.name') }}
</x-mail::message>
