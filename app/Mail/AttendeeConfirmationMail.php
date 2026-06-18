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

class AttendeeConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Attendee $attendee,
        public Event $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're registered: {$this->event->name()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.attendees.confirmation',
            with: [
                'attendeeName' => $this->attendee->name,
                'eventName' => $this->event->name(),
                'location' => $this->event->location()['label'] ?? 'TBC',
                'startsAt' => $this->event->startsAt(),
                'url' => url("/events/{$this->event->id}"),
            ],
        );
    }
}
