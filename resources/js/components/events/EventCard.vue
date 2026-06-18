<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarDays, MapPin, Ticket } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDateTime, formatPrice, relativeToNow } from '@/lib/datetime';
import type { EventCard } from '@/types/events';

defineProps<{ event: EventCard }>();
const emit = defineEmits<{ attend: [event: EventCard] }>();

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
    <article
        class="group flex flex-col overflow-hidden rounded-xl border bg-card shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
    >
        <Link
            :href="`/events/${event.id}`"
            class="relative block aspect-[16/10] overflow-hidden"
        >
            <img
                v-if="event.image"
                :src="event.image"
                :alt="event.name"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div class="absolute top-3 left-3 flex gap-2">
                <Badge
                    :variant="statusVariant(event.status)"
                    class="capitalize"
                >
                    {{ event.status.replace('_', ' ') }}
                </Badge>
                <Badge
                    variant="outline"
                    class="bg-background/80 capitalize backdrop-blur"
                    >{{ event.type }}</Badge
                >
            </div>
        </Link>

        <div class="flex flex-1 flex-col gap-3 p-4">
            <div class="flex flex-col gap-1">
                <Link
                    :href="`/events/${event.id}`"
                    class="line-clamp-1 text-base font-semibold hover:text-primary"
                >
                    {{ event.name }}
                </Link>
                <p class="line-clamp-2 text-sm text-muted-foreground">
                    {{ event.description }}
                </p>
            </div>

            <div class="mt-auto flex flex-col gap-1.5 text-sm">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <CalendarDays class="size-4 shrink-0" />
                    <span>{{ formatDateTime(event.starts_at) }}</span>
                    <span class="text-xs text-primary"
                        >· {{ relativeToNow(event.starts_at) }}</span
                    >
                </div>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="size-4 shrink-0" />
                    <span class="line-clamp-1">{{
                        event.location ?? 'Location TBC'
                    }}</span>
                </div>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <Ticket class="size-4 shrink-0" />
                    <span>{{
                        formatPrice(event.price.currency, event.price.min)
                    }}</span>
                </div>
            </div>

            <Button class="mt-1" size="sm" @click="emit('attend', event)"
                >Register interest</Button
            >
        </div>
    </article>
</template>
