<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Switch } from '@/components/ui/switch';

type ServerPreference = {
    event: string;
    email: boolean;
    inApp: boolean;
};

type Preference = ServerPreference & {
    title: string;
    description: string;
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Notification settings', href: '/settings/notifications' },
        ],
    },
});

const props = defineProps<{
    preferences: ServerPreference[];
}>();

const definitions: Record<string, { title: string; description: string }> = {
    deposit_received: {
        title: 'Deposit received',
        description: 'A deposit lands in one of your wallets.',
    },
    withdrawal_sent: {
        title: 'Withdrawal sent',
        description: 'A withdrawal you approved leaves your wallet.',
    },
    new_device_login: {
        title: 'New device login',
        description: 'Your account is signed in from an unrecognized device.',
    },
    price_alerts: {
        title: 'Price movement',
        description: 'An asset in your wallet moves more than 10% in a day.',
    },
};

const preferences = ref<Preference[]>(
    props.preferences.map((preference) => ({
        ...preference,
        ...(definitions[preference.event] ?? {
            title: preference.event,
            description: '',
        }),
    })),
);

let saveTimer: ReturnType<typeof setTimeout> | undefined;

function persist() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        router.patch('/settings/notifications', {
            preferences: preferences.value.map((preference) => ({
                event: preference.event,
                email: preference.email,
                inApp: preference.inApp,
            })),
        });
    }, 400);
}

watch(preferences, persist, { deep: true });
</script>

<template>
    <Head title="Notification settings" />

    <h1 class="sr-only">Notification settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Notification settings"
            description="Choose how Vaultis lets you know when something happens with your wallet."
        />

        <div class="border-border overflow-hidden rounded-2xl border">
            <div
                class="border-border bg-secondary/40 text-vault-ink-dim hidden grid-cols-[1fr_auto_auto] gap-6 border-b px-5 py-2.5 text-xs font-medium sm:grid"
            >
                <span>Event</span>
                <span class="w-12 text-center">Email</span>
                <span class="w-12 text-center">In-app</span>
            </div>
            <div
                v-for="pref in preferences"
                :key="pref.event"
                class="border-border grid grid-cols-[1fr_auto] items-center gap-4 border-b px-5 py-4 last:border-b-0 sm:grid-cols-[1fr_auto_auto]"
            >
                <div>
                    <p class="text-foreground text-sm font-medium">
                        {{ pref.title }}
                    </p>
                    <p class="text-vault-ink-dim text-xs">
                        {{ pref.description }}
                    </p>
                </div>
                <div class="flex w-12 justify-center">
                    <Switch v-model="pref.email" />
                </div>
                <div
                    class="col-start-2 flex w-12 justify-center sm:col-start-3"
                >
                    <Switch v-model="pref.inApp" />
                </div>
            </div>
        </div>
    </div>
</template>
