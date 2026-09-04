<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownToLine, ArrowUpFromLine, Copy, ScanLine } from '@lucide/vue';
import AnimatedNumber from '@/components/wallet/AnimatedNumber.vue';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import {
    formatPct,
    formatUsd,
    getPortfolioChangePct,
    getPortfolioTotal,
    truncateAddress,
} from '@/lib/wallet-data';
import type { AssetBalance } from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Wallet', href: '/wallet' },
        ],
    },
});

const props = defineProps<{
    balances: AssetBalance[];
}>();

const total = computed(() => getPortfolioTotal(props.balances));
const totalChangePct = computed(() =>
    getPortfolioChangePct(props.balances, total.value),
);

function copyAddress(address: string) {
    void navigator.clipboard?.writeText(address);
}
</script>

<template>
    <Head title="Wallet" />

    <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-xl font-semibold tracking-tight"
                >
                    Wallet
                </h1>
                <p class="text-vault-ink-dim mt-1 text-sm">
                    Your non-custodial balances across every supported chain.
                </p>
            </div>
            <div class="flex gap-2">
                <Link
                    href="/wallet/deposit"
                    class="brand-label bg-vault-mint text-primary-foreground inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold whitespace-nowrap"
                >
                    <ArrowDownToLine class="size-4" /> Deposit
                </Link>
                <Link
                    href="/wallet/withdraw"
                    class="brand-label border-border bg-secondary text-secondary-foreground inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold whitespace-nowrap"
                >
                    <ArrowUpFromLine class="size-4" /> Transfer
                </Link>
            </div>
        </div>

        <div
            class="border-border from-vault-forest/80 via-card to-card rounded-2xl border bg-gradient-to-br p-6"
        >
            <p class="text-vault-ink-dim text-sm">Total balance</p>
            <div
                class="text-foreground mt-1 flex items-baseline gap-1 text-3xl font-extrabold tracking-tight"
            >
                <span class="text-vault-ink-dim text-xl font-semibold">$</span>
                <AnimatedNumber
                    :value="total"
                    :formatter="(n) => formatUsd(n).replace('$', '')"
                />
            </div>
            <p
                class="tnum mt-1 text-sm"
                :class="
                    totalChangePct >= 0 ? 'text-vault-mint' : 'text-vault-rose'
                "
            >
                {{ formatPct(totalChangePct) }} today
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="b in balances"
                :key="b.chain"
                class="border-border bg-card flex flex-col gap-4 rounded-2xl border p-5"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <ChainGlyph :chain="b.chain" />
                        <div>
                            <p class="text-foreground text-sm font-medium">
                                {{ b.chain.toUpperCase() }}
                            </p>
                            <button
                                type="button"
                                class="text-vault-ink-dim hover:text-vault-mint flex items-center gap-1 text-xs"
                                @click="copyAddress(b.address)"
                            >
                                {{ truncateAddress(b.address, 4, 4) }}
                                <Copy class="size-3" />
                            </button>
                        </div>
                    </div>
                    <span
                        class="tnum rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="
                            b.change24hPct >= 0
                                ? 'bg-vault-mint/15 text-vault-mint'
                                : 'bg-vault-rose/15 text-vault-rose'
                        "
                    >
                        {{ formatPct(b.change24hPct) }}
                    </span>
                </div>

                <div>
                    <p class="tnum text-foreground text-lg font-semibold">
                        {{ formatUsd(b.usdValue) }}
                    </p>
                    <p class="tnum text-vault-ink-dim text-xs">
                        {{ b.balance }} {{ b.chain.toUpperCase() }}
                    </p>
                </div>

                <div class="mt-auto flex gap-2 pt-1">
                    <Link
                        :href="`/wallet/receive?chain=${b.chain}`"
                        class="border-border text-foreground hover:border-vault-mint/40 flex flex-1 items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-medium"
                    >
                        <ScanLine class="size-3.5" /> Receive
                    </Link>
                    <Link
                        :href="`/wallet/withdraw?chain=${b.chain}`"
                        class="border-border text-foreground hover:border-vault-mint/40 flex flex-1 items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-medium"
                    >
                        <ArrowUpFromLine class="size-3.5" /> Send
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
