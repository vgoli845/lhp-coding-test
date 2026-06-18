<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Models\Attendee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Email attendees reminders 3 days and 24 hours before their events start';

    /** Statuses that should still receive reminders. */
    private const ACTIVE_STATUSES = ['published', 'sold_out'];

    public function handle(): int
    {
        $now = Carbon::now();

        $sent24h = $this->dispatch(
            window: '24-hour',
            column: 'reminder_24h_sent_at',
            fromTs: (int) $now->timestamp,
            toTs: (int) $now->copy()->addDay()->timestamp,
        );

        $sent3d = $this->dispatch(
            window: '3-day',
            column: 'reminder_3d_sent_at',
            // Strictly more than 24h out so the two windows never overlap.
            fromTs: (int) $now->copy()->addDay()->timestamp,
            toTs: (int) $now->copy()->addDays(3)->timestamp,
        );

        $this->info("Queued {$sent24h} 24-hour and {$sent3d} 3-day reminders.");

        return self::SUCCESS;
    }

    /**
     * @param  '3-day'|'24-hour'  $window
     */
    private function dispatch(string $window, string $column, int $fromTs, int $toTs): int
    {
        $count = 0;

        Attendee::query()
            ->with('event')
            ->whereNull($column)
            ->whereHas('event', function (Builder $query) use ($fromTs, $toTs) {
                $query->whereIn('status', self::ACTIVE_STATUSES)
                    ->where('created_time', '>', $fromTs)
                    ->where('created_time', '<=', $toTs);
            })
            ->chunkById(200, function ($attendees) use ($window, $column, &$count) {
                foreach ($attendees as $attendee) {
                    $event = $attendee->event;

                    if ($event === null) {
                        continue;
                    }

                    Mail::to($attendee->email)->queue(
                        new EventReminderMail($attendee, $event, $window)
                    );

                    $attendee->forceFill([$column => now()])->save();
                    $count++;
                }
            });

        return $count;
    }
}
