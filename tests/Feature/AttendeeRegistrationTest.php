<?php

use App\Mail\AttendeeConfirmationMail;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->event = Event::factory()->for(User::factory())->create(['status' => 'published']);
});

it('registers an attendee and queues a confirmation email', function () {
    $this->post(route('events.attendees.store', $this->event), [
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
    ])->assertRedirect();

    $this->assertDatabaseHas('attendees', [
        'event_id' => $this->event->id,
        'email' => 'grace@example.com',
        'name' => 'Grace Hopper',
    ]);

    Mail::assertQueued(AttendeeConfirmationMail::class, fn ($mail) => $mail->hasTo('grace@example.com'));
});

it('does not register the same email twice for one event', function () {
    Attendee::factory()->create(['event_id' => $this->event->id, 'email' => 'grace@example.com']);

    $this->post(route('events.attendees.store', $this->event), [
        'name' => 'Grace Again',
        'email' => 'grace@example.com',
    ])->assertRedirect();

    expect(Attendee::where('event_id', $this->event->id)->count())->toBe(1);
    Mail::assertNothingQueued();
});

it('validates the registration form', function () {
    $this->post(route('events.attendees.store', $this->event), [
        'name' => '',
        'email' => 'not-an-email',
    ])->assertSessionHasErrors(['name', 'email']);

    Mail::assertNothingQueued();
});
