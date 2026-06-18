<?php

namespace App\Models;

use App\Support\EventImages;
use App\Support\ReverseGeocoder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property int $user_id
 * @property string $type
 * @property string $status
 * @property int|null $created_time
 * @property float|null $latitude
 * @property float|null $longitude
 * @property array<string, mixed>|null $payload
 * @property int|null $attendees_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'created_time' => 'integer',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Attendee, $this>
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * The event's start time. `created_time` is a unix timestamp (UTC) that the
     * dataset uses as the event start.
     */
    public function startsAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_time ?? 0, 'UTC');
    }

    /**
     * The event's end time, taken from the payload schedule when present and
     * otherwise defaulted to two hours after the start.
     */
    public function endsAt(): Carbon
    {
        $end = $this->payload['schedule']['ends_at'] ?? null;

        if (is_numeric($end)) {
            return Carbon::createFromTimestamp((int) $end, 'UTC');
        }

        return $this->startsAt()->copy()->addHours(2);
    }

    public function name(): string
    {
        return (string) ($this->payload['name'] ?? Str::headline($this->type));
    }

    /**
     * @return array{city: string, country: string, label: string}|null
     */
    public function location(): ?array
    {
        return ReverseGeocoder::resolve($this->latitude, $this->longitude);
    }

    /**
     * @return list<string>
     */
    public function images(): array
    {
        return EventImages::for($this->id, $this->type);
    }

    /**
     * Compact representation used by the browsing/list endpoints.
     *
     * @return array<string, mixed>
     */
    public function toCardArray(): array
    {
        $payload = $this->payload ?? [];
        $location = $this->location();

        return [
            'id' => $this->id,
            'name' => $this->name(),
            'description' => (string) ($payload['description'] ?? ''),
            'type' => $this->type,
            'status' => $this->status,
            'starts_at' => $this->startsAt()->toIso8601String(),
            'ends_at' => $this->endsAt()->toIso8601String(),
            'image' => $this->images()[0] ?? null,
            'venue' => $payload['venue']['name'] ?? null,
            'city' => $location['city'] ?? null,
            'country' => $location['country'] ?? null,
            'location' => $location['label'] ?? null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price' => [
                'currency' => $payload['pricing']['currency'] ?? 'USD',
                'min' => isset($payload['pricing']['min_price']) ? (float) $payload['pricing']['min_price'] : null,
            ],
        ];
    }

    /**
     * Full representation used by the event detail page.
     *
     * @return array<string, mixed>
     */
    public function toDetailArray(): array
    {
        $payload = $this->payload ?? [];

        return array_merge($this->toCardArray(), [
            'images' => $this->images(),
            'organizer' => $payload['organizer']['name'] ?? null,
            'capacity' => isset($payload['venue']['capacity']) ? (int) $payload['venue']['capacity'] : null,
            'tags' => $payload['tags'] ?? [],
            'attendees_count' => $this->attendees_count ?? $this->attendees()->count(),
        ]);
    }
}
