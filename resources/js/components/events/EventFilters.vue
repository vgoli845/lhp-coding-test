<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { EventFilterOptions, EventFilters } from '@/types/events';

defineProps<{ options: EventFilterOptions }>();

const emit = defineEmits<{ change: []; clear: [] }>();

// Two-way bound so the child can drive the shared filter state directly.
const filters = defineModel<EventFilters>('filters', { required: true });

const titleCase = (value: string) =>
    value.charAt(0).toUpperCase() + value.slice(1).replace(/_/g, ' ');

const hasActiveFilters = computed(
    () =>
        !!(
            filters.value.to ||
            filters.value.city ||
            filters.value.type ||
            filters.value.status
        ),
);

const fieldClass =
    'h-9 rounded-md border border-input bg-background px-3 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] outline-none';
</script>

<template>
    <div
        class="flex flex-wrap items-end gap-3 rounded-xl border bg-card/60 p-4 backdrop-blur"
    >
        <div class="flex flex-col gap-1">
            <label
                class="text-xs font-medium text-muted-foreground"
                for="filter-from"
                >From</label
            >
            <input
                id="filter-from"
                v-model="filters.from"
                type="date"
                :class="fieldClass"
                @change="emit('change')"
            />
        </div>

        <div class="flex flex-col gap-1">
            <label
                class="text-xs font-medium text-muted-foreground"
                for="filter-to"
                >To</label
            >
            <input
                id="filter-to"
                v-model="filters.to"
                type="date"
                :class="fieldClass"
                @change="emit('change')"
            />
        </div>

        <div class="flex flex-col gap-1">
            <label
                class="text-xs font-medium text-muted-foreground"
                for="filter-city"
                >Location</label
            >
            <select
                id="filter-city"
                v-model="filters.city"
                :class="fieldClass"
                @change="emit('change')"
            >
                <option value="">Anywhere</option>
                <option
                    v-for="city in options.cities"
                    :key="city.slug"
                    :value="city.slug"
                >
                    {{ city.label }}
                </option>
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label
                class="text-xs font-medium text-muted-foreground"
                for="filter-type"
                >Category</label
            >
            <select
                id="filter-type"
                v-model="filters.type"
                :class="fieldClass"
                @change="emit('change')"
            >
                <option value="">All categories</option>
                <option v-for="type in options.types" :key="type" :value="type">
                    {{ titleCase(type) }}
                </option>
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label
                class="text-xs font-medium text-muted-foreground"
                for="filter-status"
                >Status</label
            >
            <select
                id="filter-status"
                v-model="filters.status"
                :class="fieldClass"
                @change="emit('change')"
            >
                <option value="">Any status</option>
                <option
                    v-for="status in options.statuses"
                    :key="status"
                    :value="status"
                >
                    {{ titleCase(status) }}
                </option>
            </select>
        </div>

        <Button
            v-if="hasActiveFilters || filters.from"
            variant="ghost"
            size="sm"
            @click="emit('clear')"
        >
            Reset
        </Button>
    </div>
</template>
