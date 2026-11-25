<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Handout } from '@/types';

defineProps<{
    handout?: Pick<Handout, 'title' | 'body'>;
    errors: Partial<Record<string, string>>;
}>();
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                :default-value="handout?.title"
                required
                maxlength="300"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="body">Text</Label>
            <Textarea
                id="body"
                name="body"
                :default-value="handout?.body"
                required
                maxlength="3000"
                rows="15"
                aria-describedby="body-hint"
            />
            <p id="body-hint" class="text-sm text-muted-foreground">
                Maximum 3000 characters
            </p>
            <InputError :message="errors.body" />
        </div>
    </div>
</template>
