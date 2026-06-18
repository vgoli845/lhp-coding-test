<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  '3-day'|'24-hour'  $window
     */
    public function __construct(
        public Attendee $attendee,
        public Event $event,
        public string $window,
    ) {}

    public function envelope(): Envelope
    {
        $lead = $this->window === '24-hour' ? 'Tomorrow' : 'In 3 days';

        return new Envelope(
            subject: "{$lead}: {$this->event->name()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.attendees.reminder',
            with: [
                'attendeeName' => $this->attendee->name,
                'eventName' => $this->event->name(),
                'location' => $this->event->location()['label'] ?? 'TBC',
                'startsAt' => $this->event->startsAt(),
                'window' => $this->window,
                'url' => url("/events/{$this->event->id}"),
            ],
        );
    }
}
