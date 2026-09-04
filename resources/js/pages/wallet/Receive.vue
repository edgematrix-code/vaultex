<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ChainSelect from '@/components/wallet/ChainSelect.vue';
import QRAddress from '@/components/wallet/QRAddress.vue';
import type { AssetBalance, ChainId } from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Wallet', href: '/wallet' },
            { title: 'Receive', href: '/wallet/receive' },
        ],
    },
});

const props = defineProps<{
    balances: AssetBalance[];
}>();

const params = new URLSearchParams(
    typeof window !== 'undefined' ? window.location.search : '',
);
const initial = (params.get('chain') as ChainId) || 'btc';

const selected = ref<ChainId>(initial);

const address = computed(
    () => props.balances.find((b) => b.chain === selected.value)?.address ?? '',
);
</script>

<template>
    <Head title="Receive" />

    <div class="mx-auto flex w-full max-w-xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-foreground text-xl font-semibold tracking-tight">
                Receive
            </h1>
            <p class="text-vault-ink-dim mt-1 text-sm">
                Share this address, or scan the code, to receive funds.
            </p>
        </div>

        <ChainSelect v-model="selected" />

        <QRAddress :chain="selected" :address="address" />
    </div>
</template>
