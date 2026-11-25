<script setup lang="ts">
import { List, Minus } from '@lucide/vue';
import { nextTick, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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

const MAX_BODY_LENGTH = 3000;
const body = ref(props.handout?.body ?? '');

const textarea = () =>
    document.getElementById('body') as HTMLTextAreaElement | null;

const replaceText = async (
    start: number,
    end: number,
    replacement: string,
    selectReplacement = false,
) => {
    const text =
        body.value.slice(0, start) + replacement + body.value.slice(end);

    if (text.length > MAX_BODY_LENGTH) {
        return;
    }

    body.value = text;
    await nextTick();

    const cursor = start + replacement.length;
    const element = textarea();
    element?.focus();
    element?.setSelectionRange(selectReplacement ? start : cursor, cursor);
};

const insertList = () => {
    const element = textarea();

    if (!element) {
        return;
    }

    const { selectionStart, selectionEnd } = element;
    const text = body.value;
    const start = text.slice(0, selectionStart).lastIndexOf('\n') + 1;
    const lastSelected =
        selectionEnd > selectionStart && text[selectionEnd - 1] === '\n'
            ? selectionEnd - 1
            : selectionEnd;
    const lineEnd = text.indexOf('\n', lastSelected);
    const end = lineEnd === -1 ? text.length : lineEnd;
    const lines = text.slice(start, end).split('\n');

    const replacement = lines
        .map((line) =>
            /^\s*-\s/.test(line) || (lines.length > 1 && !line.trim())
                ? line
                : `- ${line}`,
        )
        .join('\n');

    void replaceText(start, end, replacement, lines.length > 1);
};

const insertBar = () => {
    const element = textarea();

    if (!element) {
        return;
    }

    const { selectionEnd } = element;
    const before = body.value.slice(0, selectionEnd);
    const after = body.value.slice(selectionEnd);
    const prefix = before === '' || before.endsWith('\n') ? '' : '\n';
    const suffix = after.startsWith('\n') ? '' : '\n';

    void replaceText(selectionEnd, selectionEnd, `${prefix}----${suffix}`);
};
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
            <div class="flex gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="insertList"
                >
                    <List /> List
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="insertBar"
                >
                    <Minus /> LCARS bar
                </Button>
            </div>
            <Textarea
                id="body"
                v-model="body"
                name="body"
                required
                :maxlength="MAX_BODY_LENGTH"
                rows="15"
                aria-describedby="body-hint"
            />
            <p id="body-hint" class="text-sm text-muted-foreground">
                Maximum 3000 characters. Start a line with "- " for a list item.
                A line with "----" becomes an LCARS bar.
            </p>
            <InputError :message="errors.body" />
        </div>
    </div>
</template>
