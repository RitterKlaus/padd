<script setup lang="ts">
import { ImageIcon, List, Minus, Trash2, Undo2 } from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
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
    imageUrl?: string | null;
    errors: Partial<Record<string, string>>;
}>();

const selectedTheme = ref(props.theme);

const MAX_BODY_LENGTH = 3000;
const body = ref(props.handout?.body ?? '');

const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const imageSelected = ref(false);
const imageRemoved = ref(false);
const imageTooLarge = ref(false);
const hasImage = computed(
    () => imageSelected.value || (!!props.imageUrl && !imageRemoved.value),
);

const onImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;

    imageTooLarge.value = (input.files?.[0]?.size ?? 0) > MAX_IMAGE_SIZE;

    if (imageTooLarge.value) {
        input.value = '';
    }

    imageSelected.value = !!input.files?.length;
};

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

const insertLine = (line: string) => {
    const element = textarea();

    if (!element) {
        return;
    }

    const { selectionEnd } = element;
    const before = body.value.slice(0, selectionEnd);
    const after = body.value.slice(selectionEnd);
    const prefix = before === '' || before.endsWith('\n') ? '' : '\n';
    const suffix = after.startsWith('\n') ? '' : '\n';

    void replaceText(selectionEnd, selectionEnd, `${prefix}${line}${suffix}`);
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
                    @click="insertLine('----')"
                >
                    <Minus /> LCARS bar
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="!hasImage"
                    @click="insertLine('[image]')"
                >
                    <ImageIcon /> Image
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
                A line with "----" becomes an LCARS bar. "[image]" places the
                image, otherwise it is shown below the text.
            </p>
            <InputError :message="errors.body" />
        </div>

        <div class="grid gap-2">
            <Label for="image">Image</Label>
            <template v-if="imageUrl">
                <input
                    type="hidden"
                    name="remove_image"
                    :value="imageRemoved ? 1 : 0"
                />
                <img
                    v-if="!imageRemoved"
                    :src="imageUrl"
                    alt="Current image of this handout"
                    class="max-h-48 max-w-full justify-self-start rounded-lg border"
                />
                <p v-else class="text-sm text-muted-foreground">
                    The image will be deleted when you save.
                </p>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="justify-self-start"
                    @click="imageRemoved = !imageRemoved"
                >
                    <template v-if="imageRemoved">
                        <Undo2 /> Keep image
                    </template>
                    <template v-else><Trash2 /> Delete image</template>
                </Button>
            </template>
            <input
                id="image"
                name="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                aria-describedby="image-hint"
                class="w-full rounded-md border border-input px-3 py-2 text-sm file:mr-3 file:font-medium"
                @change="onImageChange"
            />
            <p id="image-hint" class="text-sm text-muted-foreground">
                JPG, PNG or WebP, maximum 2 MB.
                <template v-if="imageUrl">
                    A new image replaces the current one.
                </template>
            </p>
            <InputError
                :message="
                    imageTooLarge
                        ? 'The image must not be larger than 2 MB.'
                        : errors.image
                "
            />
        </div>
    </div>
</template>
