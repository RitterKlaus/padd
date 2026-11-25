<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    handoutCount: number;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col items-start gap-4 p-4">
        <Card class="w-full max-w-xs">
            <CardHeader>
                <CardTitle>Handouts</CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-3xl font-semibold">{{ handoutCount }}</p>
            </CardContent>
        </Card>

        <Button type="button" size="lg">Start PADD</Button>
    </div>
</template>
