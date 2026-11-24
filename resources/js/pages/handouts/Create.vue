<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import HandoutFields from '@/components/HandoutFields.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index, store } from '@/routes/handouts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Handouts', href: index() },
            { title: 'New handout', href: create() },
        ],
    },
});
</script>

<template>
    <Head title="New handout" />

    <div class="px-4 py-6">
        <Heading title="New handout" description="Create a new handout" />

        <Form
            v-bind="store.form()"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <HandoutFields :errors="errors" />

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="processing">Save</Button>
                <Button variant="secondary" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
