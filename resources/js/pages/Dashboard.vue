<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Check, Copy } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    paddUrl: string;
}>();

const { copy, copied } = useClipboard({ legacy: true });
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

        <Button size="lg" as-child>
            <a :href="paddUrl" target="_blank" rel="noopener noreferrer">
                Start PADD
            </a>
        </Button>

        <div class="grid w-full max-w-xl gap-2">
            <Label for="padd-url">Link for player devices</Label>
            <div class="flex gap-2">
                <Input
                    id="padd-url"
                    :model-value="paddUrl"
                    readonly
                    aria-describedby="padd-url-hint"
                />
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    :aria-label="copied ? 'Link copied' : 'Copy link'"
                    @click="copy(paddUrl)"
                >
                    <Check v-if="copied" />
                    <Copy v-else />
                </Button>
            </div>
            <p id="padd-url-hint" class="text-sm text-muted-foreground">
                The link works without login. Anyone who knows it can see the
                handout shown on the PADD.
            </p>
        </div>
    </div>
</template>
