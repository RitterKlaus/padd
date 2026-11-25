<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import HandoutFields from '@/components/HandoutFields.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { edit, index, update } from '@/routes/handouts';
import type { Handout, ThemeOption } from '@/types';

defineOptions({
    layout: (props: { handout: Handout }) => ({
        breadcrumbs: [
            { title: 'Handouts', href: index() },
            { title: 'Edit handout', href: edit(props.handout.id) },
        ],
    }),
});

defineProps<{
    handout: Pick<Handout, 'id' | 'title' | 'body' | 'theme'>;
    imageUrl: string | null;
    themes: ThemeOption[];
}>();
</script>

<template>
    <Head title="Edit handout" />

    <div class="px-4 py-6">
        <Heading
            title="Edit handout"
            description="Change the title, design, text and image of this handout"
        />

        <Form
            v-bind="update.form(handout.id)"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <HandoutFields
                :handout="handout"
                :image-url="imageUrl"
                :theme="handout.theme"
                :themes="themes"
                :errors="errors"
            />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Save</Button>
                <Button variant="secondary" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
