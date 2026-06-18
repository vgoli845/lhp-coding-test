<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\CityDirectory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /** Statuses surfaced to the public browsing pages. */
    private const BROWSABLE_STATUSES = ['published', 'sold_out', 'cancelled'];

    /** Half-width (in degrees) of the bounding box used for city filtering. */
    private const CITY_BOX = 0.6;

    public function index(Request $request): Response
    {
        return Inertia::render('Events/Index', [
            'filters' => [
                'status' => $request->status,
                'from' => $request->input('from', '2023-01-01'),
            ],
            'statuses' => ['draft', 'published', 'cancelled', 'sold_out'],
        ]);
    }

    public function visualOne(): Response
    {
        return Inertia::render('Events/VisualOne', [
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    public function visualTwo(): Response
    {
        return Inertia::render('Events/VisualTwo', [
            'filterOptions' => $this->filterOptions(),
        ]);
    }

    /**
     * Filtered, paginated browsing API consumed by both visual pages.
     */
    public function browse(Request $request): JsonResponse
    {
        $start = microtime(true);

        $events = $this->browseQuery($request)
            ->orderBy('created_time')
            ->orderBy('id')
            ->simplePaginate(24)
            ->withQueryString();

        $cards = array_map(fn (Event $event) => $event->toCardArray(), $events->items());

        return response()->json([
            'data' => $cards,
            'current_page' => $events->currentPage(),
            'has_more' => $events->hasMorePages(),
            'stats' => [
                'ms' => (int) round((microtime(true) - $start) * 1000),
                'count' => count($cards),
            ],
        ]);
    }

    public function show(Event $event): Response
    {
        return Inertia::render('Events/Show', [
            'event' => $event->loadCount('attendees')->toDetailArray(),
        ]);
    }

    /**
     * Baseline listing endpoint retained for the original Events/Index table.
     */
    public function data(Request $request): JsonResponse
    {
        $start = microtime(true);

        $events = Event::with('user')
            ->when($request->status, fn (Builder $q, $s) => $q->where('status', $s))
            ->orderByDesc('created_time')
            ->paginate(50)
            ->withQueryString();

        return response()->json([
            'data' => $events->items(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'total' => $events->total(),
            'stats' => [
                'ms' => (int) round((microtime(true) - $start) * 1000),
                'bytes' => strlen((string) json_encode($events->items())),
            ],
        ]);
    }

    /**
     * Build the filtered event query shared by the browse endpoint.
     */
    private function browseQuery(Request $request): Builder
    {
        $statuses = $this->validStatuses($request->string('status')->toString());

        $query = Event::query()->whereIn('status', $statuses);

        // Date range against the (indexed) event start time, as UTC days.
        if ($from = $request->date('from')) {
            $query->where('created_time', '>=', $from->copy()->utc()->startOfDay()->timestamp);
        }

        if ($to = $request->date('to')) {
            $query->where('created_time', '<=', $to->copy()->utc()->endOfDay()->timestamp);
        }

        // Type (event category).
        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        // Location: a bounding box around the selected city anchor.
        if ($city = CityDirectory::find($request->string('city')->toString())) {
            $query->whereBetween('latitude', [$city['lat'] - self::CITY_BOX, $city['lat'] + self::CITY_BOX])
                ->whereBetween('longitude', [$city['lng'] - self::CITY_BOX, $city['lng'] + self::CITY_BOX]);
        }

        return $query;
    }

    /**
     * Resolve the requested status filter to a safe list of statuses.
     *
     * @return list<string>
     */
    private function validStatuses(string $status): array
    {
        if ($status === '' || $status === 'all') {
            return self::BROWSABLE_STATUSES;
        }

        return in_array($status, self::BROWSABLE_STATUSES, true)
            ? [$status]
            : self::BROWSABLE_STATUSES;
    }

    /**
     * Options used to populate the filter controls on the visual pages.
     *
     * @return array<string, mixed>
     */
    private function filterOptions(): array
    {
        return [
            'cities' => array_map(fn (array $c) => [
                'slug' => $c['slug'],
                'label' => "{$c['city']}, {$c['country']}",
            ], CityDirectory::all()),
            'types' => ['concert', 'conference', 'meetup', 'workshop', 'festival', 'sports', 'networking', 'exhibition'],
            'statuses' => self::BROWSABLE_STATUSES,
        ];
    }
}
