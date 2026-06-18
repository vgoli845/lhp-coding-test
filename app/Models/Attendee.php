<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $event_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $reminder_3d_sent_at
 * @property Carbon|null $reminder_24h_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event|null $event
 */
class Attendee extends Model
{
    /** @use HasFactory<\Database\Factories\AttendeeFactory> */
    use HasFactory;

    protected $fillable = ['event_id', 'name', 'email'];

    protected $casts = [
        'reminder_3d_sent_at' => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
