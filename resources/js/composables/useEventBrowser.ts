import { onBeforeUnmount, reactive, ref } from 'vue';
import type { EventCard, EventFilters } from '@/types/events';

interface BrowseResponse {
    data: EventCard[];
    current_page: number;
    has_more: boolean;
    stats: { ms: number; count: number };
}

function todayIso(): string {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

export function defaultFilters(): EventFilters {
    return { from: todayIso(), to: '', city: '', type: '', status: '' };
}

/**
 * Shared browsing state for the visual pages: filters, paginated fetching from
 * /events/browse, and incremental "load more" for infinite scroll.
 */
export function useEventBrowser() {
    const filters = reactive<EventFilters>(defaultFilters());

    const rows = ref<EventCard[]>([]);
    const page = ref(0);
    const hasMore = ref(true);
    const loading = ref(false);
    const loadedOnce = ref(false);
    const lastMs = ref(0);

    let requestId = 0;
    let debounce: ReturnType<typeof setTimeout> | null = null;

    function buildParams(next: number): string {
        const params = new URLSearchParams({ page: String(next) });
        (Object.keys(filters) as (keyof EventFilters)[]).forEach((key) => {
            const value = filters[key];

            if (value) {
                params.set(key, value);
            }
        });

        return params.toString();
    }

    async function loadMore(): Promise<void> {
        if (loading.value || !hasMore.value) {
            return;
        }

        loading.value = true;
        const current = ++requestId;

        try {
            const response = await fetch(
                `/events/browse?${buildParams(page.value + 1)}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );
            const payload: BrowseResponse = await response.json();

            // Ignore responses superseded by a newer filter change.
            if (current !== requestId) {
                return;
            }

            rows.value.push(...payload.data);
            page.value = payload.current_page;
            hasMore.value = payload.has_more;
            lastMs.value = payload.stats.ms;
            loadedOnce.value = true;
        } finally {
            if (current === requestId) {
                loading.value = false;
            }
        }
    }

    function reset(): void {
        rows.value = [];
        page.value = 0;
        hasMore.value = true;
        loadedOnce.value = false;
        void loadMore();
    }

    /** Re-run the query after a filter change, debounced for text inputs. */
    function applyFilters(): void {
        if (debounce) {
            clearTimeout(debounce);
        }

        debounce = setTimeout(reset, 250);
    }

    function clearFilters(): void {
        Object.assign(filters, defaultFilters());
        reset();
    }

    onBeforeUnmount(() => {
        if (debounce) {
            clearTimeout(debounce);
        }
    });

    return {
        filters,
        rows,
        hasMore,
        loading,
        loadedOnce,
        lastMs,
        loadMore,
        reset,
        applyFilters,
        clearFilters,
    };
}
