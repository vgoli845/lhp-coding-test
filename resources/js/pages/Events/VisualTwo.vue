<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDays, MapPin } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AttendDialog from '@/components/events/AttendDialog.vue';
import EventFilters from '@/components/events/EventFilters.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useEventBrowser } from '@/composables/useEventBrowser';
import {
    formatDayHeading,
    formatPrice,
    formatTime,
    localDayKey,
    relativeToNow,
} from '@/lib/datetime';
import type {
    EventCard as EventCardType,
    EventFilterOptions,
} from '@/types/events';

defineProps<{ filterOptions: EventFilterOptions }>();

const {
    filters,
    rows,
    hasMore,
    loading,
    loadedOnce,
    loadMore,
    applyFilters,
    clearFilters,
} = useEventBrowser();

// Rows arrive ordered by start time, so grouping by local day preserves order.
const days = computed(() => {
    const groups = new Map<
        string,
        { key: string; heading: string; events: EventCardType[] }
    >();

    for (const event of rows.value) {
        const key = localDayKey(event.starts_at);

        if (!groups.has(key)) {
            groups.set(key, {
                key,
                heading: formatDayHeading(event.starts_at),
                events: [],
            });
        }

        groups.get(key)!.events.push(event);
    }

    return [...groups.values()];
});

const selectedEvent = ref<EventCardType | null>(null);
const dialogOpen = ref(false);

function openAttend(event: EventCardType): void {
    selectedEvent.value = event;
    dialogOpen.value = true;
}

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0]?.isIntersecting) {
                void loadMore();
            }
        },
        { rootMargin: '600px' },
    );

    if (sentinel.value) {
        observer.observe(sentinel.value);
    }

    void loadMore();
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <Head title="Events Visual 2" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight">
                Upcoming agenda
            </h1>
            <p class="text-sm text-muted-foreground">
                A chronological timeline of events, grouped by day.
            </p>
        </header>

        <EventFilters
            :filters="filters"
            :options="filterOptions"
            @change="applyFilters"
            @clear="clearFilters"
        />

        <div
            v-if="loadedOnce && rows.length === 0 && !loading"
            class="rounded-xl border border-dashed py-16 text-center"
        >
            <p class="text-sm text-muted-foreground">
                Nothing scheduled for these filters.
            </p>
        </div>

        <div class="flex flex-col gap-8">
            <section
                v-for="day in days"
                :key="day.key"
                class="flex flex-col gap-3"
            >
                <div
                    class="sticky top-0 z-10 -mx-1 flex items-center gap-3 bg-background/80 px-1 py-2 backdrop-blur"
                >
                    <CalendarDays class="size-5 text-primary" />
                    <h2
                        class="text-sm font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        {{ day.heading }}
                    </h2>
                    <span class="text-xs text-muted-foreground"
                        >· {{ relativeToNow(day.events[0].starts_at) }}</span
                    >
                </div>

                <ol
                    class="relative ml-2 flex flex-col gap-4 border-l border-border pl-6"
                >
                    <li
                        v-for="event in day.events"
                        :key="event.id"
                        class="relative animate-in duration-300 fade-in slide-in-from-bottom-2"
                    >
                        <span
                            class="absolute top-2 -left-[1.95rem] size-3 rounded-full border-2 border-background bg-primary"
                        ></span>

                        <article
                            class="flex gap-4 rounded-xl border bg-card p-3 transition-colors hover:border-primary/40"
                        >
                            <Link
                                :href="`/events/${event.id}`"
                                class="hidden size-20 shrink-0 overflow-hidden rounded-lg sm:block"
                            >
                                <img
                                    v-if="event.image"
                                    :src="event.image"
                                    :alt="event.name"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                />
                            </Link>

                            <div class="flex flex-1 flex-col gap-1">
                                <div
                                    class="flex items-center gap-2 text-sm font-medium text-primary"
                                >
                                    {{ formatTime(event.starts_at) }}
                                    <Badge
                                        variant="outline"
                                        class="capitalize"
                                        >{{ event.type }}</Badge
                                    >
                                </div>
                                <Link
                                    :href="`/events/${event.id}`"
                                    class="line-clamp-1 font-semibold hover:text-primary"
                                >
                                    {{ event.name }}
                                </Link>
                                <div
                                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                                >
                                    <MapPin class="size-4 shrink-0" />
                                    <span class="line-clamp-1">{{
                                        event.location ?? 'Location TBC'
                                    }}</span>
                                    <span class="ml-1"
                                        >·
                                        {{
                                            formatPrice(
                                                event.price.currency,
                                                event.price.min,
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>

                            <Button
                                class="self-center"
                                size="sm"
                                variant="outline"
                                @click="openAttend(event)"
                            >
                                Attend
                            </Button>
                        </article>
                    </li>
                </ol>
            </section>
        </div>

        <div ref="sentinel" class="h-px"></div>

        <div
            class="flex items-center justify-center py-4 text-sm text-muted-foreground"
        >
            <span v-if="loading">Loading events…</span>
            <span v-else-if="!hasMore && rows.length > 0"
                >End of the timeline.</span
            >
        </div>
    </div>

    <AttendDialog v-model:open="dialogOpen" :event="selectedEvent" />
</template>
