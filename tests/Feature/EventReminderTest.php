<?php

use App\Mail\EventReminderMail;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function eventStartingIn(int $seconds, string $status = 'published'): Event
{
    return Event::factory()->for(User::factory())->create([
        'status' => $status,
        'created_time' => Carbon::now()->addSeconds($seconds)->timestamp,
    ]);
}

it('sends a 3-day reminder for events two days out', function () {
    Mail::fake();
    $event = eventStartingIn(2 * 86_400);
    $attendee = Attendee::factory()->create(['event_id' => $event->id]);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminderMail::class, fn ($mail) => $mail->window === '3-day' && $mail->attendee->is($attendee));
    expect($attendee->refresh()->reminder_3d_sent_at)->not->toBeNull();
});

it('sends a 24-hour reminder for events within a day', function () {
    Mail::fake();
    $event = eventStartingIn(12 * 3_600);
    Attendee::factory()->create(['event_id' => $event->id]);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminderMail::class, fn ($mail) => $mail->window === '24-hour');
});

it('does not send reminders twice', function () {
    Mail::fake();
    $event = eventStartingIn(2 * 86_400);
    Attendee::factory()->create(['event_id' => $event->id]);

    $this->artisan('events:send-reminders');
    $this->artisan('events:send-reminders');

    Mail::assertQueued(EventReminderMail::class, 1);
});

it('ignores events outside the reminder windows', function () {
    Mail::fake();
    $event = eventStartingIn(10 * 86_400);
    Attendee::factory()->create(['event_id' => $event->id]);

    $this->artisan('events:send-reminders');

    Mail::assertNothingQueued();
});
