<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, Pencil, Plus, Trash2 } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { create, destroy, edit, index } from '@/routes/handouts';
import {
    destroy as deactivate,
    update as activate,
} from '@/routes/handouts/activation';
import type { Handout } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Handouts',
                href: index(),
            },
        ],
    },
});

defineProps<{
    handouts: Pick<Handout, 'id' | 'title' | 'is_active'>[];
}>();
</script>

<template>
    <Head title="Handouts" />

    <div class="px-4 py-6">
        <Heading title="Handouts" description="Manage your handouts" />

        <Button as-child>
            <Link :href="create()"><Plus /> New handout</Link>
        </Button>

        <p
            v-if="handouts.length === 0"
            class="mt-6 text-sm text-muted-foreground"
        >
            You have no handouts yet.
        </p>

        <ul v-else class="mt-6 max-w-2xl divide-y rounded-lg border">
            <li
                v-for="handout in handouts"
                :key="handout.id"
                class="flex items-center justify-between gap-4 px-4 py-2"
            >
                <Link
                    :href="edit(handout.id)"
                    class="min-w-0 text-sm font-medium break-words hover:underline"
                >
                    {{ handout.title }}
                </Link>

                <div class="flex shrink-0 items-center gap-1">
                    <Badge v-if="handout.is_active">On PADD</Badge>
                    <Form
                        v-bind="
                            handout.is_active
                                ? deactivate.form(handout.id)
                                : activate.form(handout.id)
                        "
                        :options="{ preserveScroll: true }"
                        v-slot="{ processing }"
                    >
                        <Button
                            type="submit"
                            variant="ghost"
                            size="icon"
                            :disabled="processing"
                            :title="
                                handout.is_active
                                    ? 'Hide from PADD'
                                    : 'Show on PADD'
                            "
                            :aria-label="
                                handout.is_active
                                    ? `Hide ${handout.title} from PADD`
                                    : `Show ${handout.title} on PADD`
                            "
                        >
                            <EyeOff v-if="handout.is_active" />
                            <Eye v-else />
                        </Button>
                    </Form>
                    <Button variant="ghost" size="icon" as-child>
                        <Link
                            :href="edit(handout.id)"
                            :aria-label="`Edit ${handout.title}`"
                        >
                            <Pencil />
                        </Link>
                    </Button>

                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                :aria-label="`Delete ${handout.title}`"
                            >
                                <Trash2 />
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <Form
                                v-bind="destroy.form(handout.id)"
                                :options="{ preserveScroll: true }"
                                class="min-w-0 space-y-6"
                                v-slot="{ processing }"
                            >
                                <DialogHeader class="space-y-3">
                                    <DialogTitle>Delete handout?</DialogTitle>
                                    <DialogDescription class="break-words">
                                        "{{ handout.title }}" will be
                                        permanently deleted.
                                    </DialogDescription>
                                </DialogHeader>

                                <DialogFooter class="gap-2">
                                    <DialogClose as-child>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        :disabled="processing"
                                    >
                                        Delete handout
                                    </Button>
                                </DialogFooter>
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </li>
        </ul>
    </div>
</template>
