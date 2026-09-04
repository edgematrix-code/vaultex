<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import ChainSelect from '@/components/wallet/ChainSelect.vue';
import { CHAINS } from '@/lib/wallet-data';
import type { ChainId } from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Wallet', href: '/wallet' },
            { title: 'Deposit', href: '/wallet/deposit' },
        ],
    },
});

const selected = ref<ChainId>('btc');
const chain = computed(() => CHAINS[selected.value]);
</script>

<template>
    <Head title="Deposit" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-foreground text-xl font-semibold tracking-tight">
                Deposit
            </h1>
            <p class="text-vault-ink-dim mt-1 max-w-md text-sm">
                Choose which asset you're sending, then we'll show the correct
                receiving address and network.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
            <div class="border-border bg-card rounded-2xl border p-6">
                <p class="text-foreground mb-4 text-sm font-medium">
                    Select an asset
                </p>
                <ChainSelect v-model="selected" />
            </div>

            <div
                class="border-border bg-secondary/40 flex flex-col justify-between gap-6 rounded-2xl border p-6"
            >
                <div>
                    <p class="text-foreground text-sm font-medium">
                        {{ chain.name }}
                    </p>
                    <p class="text-vault-ink-dim mt-1 text-xs">
                        Deposits confirm automatically — no manual steps once
                        the transaction is broadcast on {{ chain.network }}.
                    </p>
                </div>
                <Link
                    :href="`/wallet/receive?chain=${selected}`"
                    class="brand-label bg-vault-mint text-primary-foreground inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold whitespace-nowrap"
                >
                    Get address
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </div>
    </div>
</template>
