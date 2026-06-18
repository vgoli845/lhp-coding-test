<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, MapPin, Ticket, Users } from '@lucide/vue';
import { ref } from 'vue';
import AttendDialog from '@/components/events/AttendDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDateTime, formatPrice, relativeToNow } from '@/lib/datetime';
import type { EventDetail } from '@/types/events';

const props = defineProps<{ event: EventDetail }>();

const activeImage = ref(props.event.images[0] ?? null);
const dialogOpen = ref(false);

const statusVariant = (status: string) => {
    switch (status) {
        case 'published':
            return 'default';
        case 'cancelled':
            return 'destructive';
        case 'sold_out':
            return 'secondary';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head :title="event.name" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
        <Link
            href="/events-visual-1"
            class="flex items-center gap-1 text-sm text-primary hover:underline"
        >
            <ArrowLeft class="size-4" /> Back to events
        </Link>

        <div class="flex flex-col gap-3">
            <div
                class="aspect-[16/9] overflow-hidden rounded-2xl border bg-muted"
            >
                <img
                    v-if="activeImage"
                    :src="activeImage"
                    :alt="event.name"
                    class="h-full w-full object-cover"
                />
            </div>
            <div v-if="event.images.length > 1" class="flex gap-2">
                <button
                    v-for="(image, index) in event.images"
                    :key="index"
                    type="button"
                    class="size-16 overflow-hidden rounded-lg border transition-all"
                    :class="
                        activeImage === image
                            ? 'ring-2 ring-primary'
                            : 'opacity-70 hover:opacity-100'
                    "
                    @click="activeImage = image"
                >
                    <img
                        :src="image"
                        :alt="`${event.name} ${index + 1}`"
                        class="h-full w-full object-cover"
                    />
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <Badge
                    :variant="statusVariant(event.status)"
                    class="capitalize"
                >
                    {{ event.status.replace('_', ' ') }}
                </Badge>
                <Badge variant="outline" class="capitalize">{{
                    event.type
                }}</Badge>
            </div>

            <h1 class="text-3xl font-bold tracking-tight">{{ event.name }}</h1>
            <p class="text-muted-foreground">{{ event.description }}</p>

            <div
                class="grid gap-4 rounded-2xl border bg-card p-5 sm:grid-cols-2"
            >
                <div class="flex items-start gap-3">
                    <CalendarDays class="mt-0.5 size-5 text-primary" />
                    <div>
                        <p class="font-medium">
                            {{ formatDateTime(event.starts_at) }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ relativeToNow(event.starts_at) }} · shown in your
                            local time
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <MapPin class="mt-0.5 size-5 text-primary" />
                    <div>
                        <p class="font-medium">
                            {{ event.location ?? 'Location TBC' }}
                        </p>
                        <p
                            v-if="event.venue"
                            class="text-sm text-muted-foreground"
                        >
                            {{ event.venue }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <Ticket class="mt-0.5 size-5 text-primary" />
                    <div>
                        <p class="font-medium">
                            {{
                                formatPrice(
                                    event.price.currency,
                                    event.price.min,
                                )
                            }}
                        </p>
                        <p class="text-sm text-muted-foreground">from</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <Users class="mt-0.5 size-5 text-primary" />
                    <div>
                        <p class="font-medium">
                            {{ event.attendees_count }} attending
                        </p>
                        <p
                            v-if="event.capacity"
                            class="text-sm text-muted-foreground"
                        >
                            Capacity {{ event.capacity.toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="event.tags.length" class="flex flex-wrap gap-2">
                <Badge
                    v-for="tag in event.tags"
                    :key="tag"
                    variant="secondary"
                    >{{ tag }}</Badge
                >
            </div>

            <div>
                <Button
                    size="lg"
                    :disabled="event.status === 'cancelled'"
                    @click="dialogOpen = true"
                >
                    {{
                        event.status === 'cancelled'
                            ? 'Event cancelled'
                            : 'Register interest'
                    }}
                </Button>
            </div>
        </div>
    </div>

    <AttendDialog v-model:open="dialogOpen" :event="event" />
</template>
