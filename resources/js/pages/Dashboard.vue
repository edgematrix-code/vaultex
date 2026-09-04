<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import ActivityRow from '@/components/wallet/ActivityRow.vue';
import AssetRow from '@/components/wallet/AssetRow.vue';
import BalanceCard from '@/components/wallet/BalanceCard.vue';
import QuickActions from '@/components/wallet/QuickActions.vue';
import BalanceHistory from '@/components/charts/BalanceHistory.vue';
import PortfolioDonut from '@/components/charts/PortfolioDonut.vue';
import { getPortfolioChangePct, getPortfolioTotal } from '@/lib/wallet-data';
import type {
    AssetBalance,
    PortfolioPoint,
    SecurityStatus,
    Transaction,
} from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

const props = defineProps<{
    balances: AssetBalance[];
    transactions: Transaction[];
    history: PortfolioPoint[];
    security: SecurityStatus;
}>();

const total = computed(() => getPortfolioTotal(props.balances));
const totalChangePct = computed(() =>
    getPortfolioChangePct(props.balances, total.value),
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <BalanceCard
                :total="total"
                :change-pct="totalChangePct"
                :security="security"
            />
            <QuickActions />
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <PortfolioDonut :balances="balances" :total="total" />
            <BalanceHistory :history="history" />
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div
                class="border-border bg-card rounded-2xl border p-6 lg:col-span-2"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-vault-ink-dim text-sm">Assets</p>
                    <Link
                        href="/wallet"
                        class="text-vault-mint flex items-center gap-1 text-xs font-medium hover:underline"
                    >
                        View wallet
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>
                <div class="divide-border divide-y">
                    <AssetRow
                        v-for="b in balances"
                        :key="b.chain"
                        :balance="b"
                    />
                </div>
            </div>

            <div class="border-border bg-card rounded-2xl border p-6">
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-vault-ink-dim text-sm">Recent activity</p>
                    <Link
                        href="/transactions"
                        class="text-vault-mint flex items-center gap-1 text-xs font-medium hover:underline"
                    >
                        View all
                        <ArrowRight class="size-3.5" />
                    </Link>
                </div>
                <div class="divide-border divide-y">
                    <ActivityRow
                        v-for="t in transactions.slice(0, 5)"
                        :key="t.id"
                        :transaction="t"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
