<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AttendDialog from '@/components/events/AttendDialog.vue';
import EventCard from '@/components/events/EventCard.vue';
import EventFilters from '@/components/events/EventFilters.vue';
import { useEventBrowser } from '@/composables/useEventBrowser';
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
    <Head title="Events Visual 1" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-col gap-1">
            <h1 class="text-2xl font-semibold tracking-tight">
                Discover events
            </h1>
            <p class="text-sm text-muted-foreground">
                Browse what's coming up — filter by date, place or category and
                register your interest.
            </p>
        </header>

        <EventFilters
            :filters="filters"
            :options="filterOptions"
            @change="applyFilters"
            @clear="clearFilters"
        />

        <transition-group
            tag="div"
            class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
        >
            <EventCard
                v-for="event in rows"
                :key="event.id"
                :event="event"
                @attend="openAttend"
            />
        </transition-group>

        <div
            v-if="loadedOnce && rows.length === 0 && !loading"
            class="rounded-xl border border-dashed py-16 text-center"
        >
            <p class="text-sm text-muted-foreground">
                No events match these filters. Try widening your search.
            </p>
        </div>

        <div ref="sentinel" class="h-px"></div>

        <div
            class="flex items-center justify-center py-4 text-sm text-muted-foreground"
        >
            <span v-if="loading">Loading events…</span>
            <span v-else-if="!hasMore && rows.length > 0"
                >You've reached the end.</span
            >
        </div>
    </div>

    <AttendDialog v-model:open="dialogOpen" :event="selectedEvent" />
</template>
