<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { edit, update } from '@/routes/administration';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Administration',
                href: edit(),
            },
        ],
    },
});

defineProps<{
    registrationEnabled: boolean;
    users: Pick<User, 'id' | 'email'>[];
}>();

const processing = ref(false);

const updateRegistration = (enabled: boolean) => {
    router.patch(
        update(),
        { registration_enabled: enabled },
        {
            preserveScroll: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
        },
    );
};
</script>

<template>
    <Head title="Administration" />

    <div class="px-4 py-6">
        <Heading
            title="Administration"
            description="Manage the settings of this application"
        />

        <div
            class="flex max-w-xl items-center justify-between gap-4 rounded-lg border p-4"
        >
            <div class="grid gap-1">
                <Label for="registration-enabled">Allow registration</Label>
                <p
                    id="registration-description"
                    class="text-sm text-muted-foreground"
                >
                    When disabled, the "Register" button is hidden and no new
                    users can sign up.
                </p>
            </div>
            <Switch
                id="registration-enabled"
                aria-describedby="registration-description"
                :model-value="registrationEnabled"
                :disabled="processing"
                @update:model-value="updateRegistration"
            />
        </div>

        <div class="mt-6 max-w-xl overflow-x-auto rounded-lg border">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">
                    Registered users
                </caption>
                <thead class="border-b bg-muted/50 text-muted-foreground">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-medium">
                            User ID
                        </th>
                        <th scope="col" class="px-4 py-2 font-medium">
                            Email address
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-4 py-2">{{ user.id }}</td>
                        <td class="px-4 py-2">{{ user.email }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
