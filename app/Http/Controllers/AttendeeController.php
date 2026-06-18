<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendeeRequest;
use App\Mail\AttendeeConfirmationMail;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AttendeeController extends Controller
{
    /**
     * Register interest/attendance for an event and email a confirmation.
     */
    public function store(StoreAttendeeRequest $request, Event $event): RedirectResponse
    {
        /** @var array{name: string, email: string} $data */
        $data = $request->validated();

        $attendee = Attendee::firstOrCreate(
            ['event_id' => $event->id, 'email' => $data['email']],
            ['name' => $data['name']],
        );

        // Only confirm the first time someone registers for this event.
        if ($attendee->wasRecentlyCreated) {
            Mail::to($attendee->email)->queue(new AttendeeConfirmationMail($attendee, $event));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => "You're on the list — check your inbox for confirmation.",
            ]);
        } else {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => "You're already registered for this event.",
            ]);
        }

        return back();
    }
}
