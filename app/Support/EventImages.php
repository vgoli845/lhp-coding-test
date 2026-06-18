<?php

namespace App\Support;

/**
 * Resolves a deterministic set of local placeholder images for an event.
 *
 * Events in the seeded dataset have no stored imagery, and back-filling image
 * rows for 1.25M events would be wasteful for a demo. Instead we derive a
 * stable gallery from the event's category plus a hash of its id, so each
 * event reliably shows 2–3 images served from /images/events/*. In a real
 * application these URLs would come from an uploads table; the consuming code
 * (model accessor → API → UI) is identical either way.
 */
final class EventImages
{
    /** Categories that have a dedicated themed placeholder. */
    private const CATEGORIES = [
        'concert', 'conference', 'meetup', 'workshop',
        'festival', 'sports', 'networking', 'exhibition',
    ];

    /** Number of generic texture variants available. */
    private const VARIANTS = 4;

    /**
     * @return list<string> Local image URLs (at least two).
     */
    public static function for(string $id, string $type): array
    {
        $category = in_array($type, self::CATEGORIES, true) ? $type : 'meetup';

        // A stable integer derived from the id drives variant selection.
        $hash = (int) hexdec(substr(md5($id), 0, 8));

        $first = ($hash % self::VARIANTS) + 1;
        $second = (($hash >> 3) % self::VARIANTS) + 1;
        if ($second === $first) {
            $second = ($second % self::VARIANTS) + 1;
        }

        return [
            "/images/events/{$category}.svg",
            "/images/events/texture-{$first}.svg",
            "/images/events/texture-{$second}.svg",
        ];
    }
}
