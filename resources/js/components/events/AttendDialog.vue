<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EventCard } from '@/types/events';

const props = defineProps<{ event: EventCard | null }>();
const open = defineModel<boolean>('open', { default: false });

const page = usePage<{
    auth: { user: { name: string; email: string } | null };
}>();

const form = useForm({ name: '', email: '' });

// Prefill from the signed-in user when the dialog opens.
watch(open, (isOpen) => {
    if (isOpen) {
        form.clearErrors();
        form.name = page.props.auth.user?.name ?? '';
        form.email = page.props.auth.user?.email ?? '';
    }
});

function submit(): void {
    if (!props.event) {
        return;
    }

    form.post(`/events/${props.event.id}/attendees`, {
        preserveScroll: true,
        // Keep the page's loaded events/scroll intact after registering.
        preserveState: true,
        onSuccess: () => {
            form.reset();
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Register your interest</DialogTitle>
                <DialogDescription>
                    {{
                        event
                            ? `Get on the list for “${event.name}”. We'll email you to confirm.`
                            : ''
                    }}
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div class="flex flex-col gap-2">
                    <Label for="attend-name">Name</Label>
                    <Input
                        id="attend-name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="attend-email">Email</Label>
                    <Input
                        id="attend-email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="ghost" @click="open = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting…' : 'Count me in' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
