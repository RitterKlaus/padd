<script setup lang="ts">
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { Handout, ThemeOption } from '@/types';

const props = defineProps<{
    handout?: Pick<Handout, 'title' | 'body'>;
    theme: string;
    themes: ThemeOption[];
    errors: Partial<Record<string, string>>;
}>();

const selectedTheme = ref(props.theme);
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
            <Label for="theme">Design</Label>
            <input type="hidden" name="theme" :value="selectedTheme" />
            <Select v-model="selectedTheme">
                <SelectTrigger id="theme" class="w-full">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="option in themes"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.theme" />
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
