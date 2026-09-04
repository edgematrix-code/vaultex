<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Download, Search } from '@lucide/vue';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import TransactionStatusBadge from '@/components/wallet/TransactionStatusBadge.vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import {
    CHAINS,
    formatCrypto,
    formatRelativeTime,
    formatUsd,
    truncateAddress,
} from '@/lib/wallet-data';
import type { Transaction } from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Transactions', href: '/transactions' },
        ],
    },
});

const props = defineProps<{
    transactions: Transaction[];
}>();

const query = ref('');
const typeFilter = ref('all');
const statusFilter = ref('all');

const filtered = computed(() =>
    props.transactions.filter((t) => {
        if (typeFilter.value !== 'all' && t.type !== typeFilter.value)
            return false;
        if (statusFilter.value !== 'all' && t.status !== statusFilter.value)
            return false;
        if (query.value) {
            const q = query.value.toLowerCase();
            return (
                t.txHash.toLowerCase().includes(q) ||
                t.toAddress.toLowerCase().includes(q) ||
                CHAINS[t.chain].name.toLowerCase().includes(q)
            );
        }
        return true;
    }),
);

function exportCsv() {
    const header = [
        'id',
        'date',
        'chain',
        'type',
        'status',
        'amount',
        'usd_value',
        'fee',
        'tx_hash',
    ];
    const rows = filtered.value.map((t) => [
        t.id,
        t.createdAt,
        CHAINS[t.chain].symbol,
        t.type,
        t.status,
        t.amount,
        t.usdValue.toFixed(2),
        t.fee,
        t.txHash,
    ]);
    const csv = [header, ...rows].map((r) => r.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'vaultis-transactions.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Transactions" />

    <div class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1
                    class="text-foreground text-xl font-semibold tracking-tight"
                >
                    Transactions
                </h1>
                <p class="text-vault-ink-dim mt-1 text-sm">
                    Every deposit, withdrawal, and internal transfer.
                </p>
            </div>
            <Button variant="outline" @click="exportCsv">
                <Download class="size-4" />
                Export CSV
            </Button>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <Search
                    class="text-vault-ink-dim pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="query"
                    placeholder="Search by address, chain, or tx hash"
                    class="pl-9"
                />
            </div>
            <Select v-model="typeFilter">
                <SelectTrigger class="w-full sm:w-40"
                    ><SelectValue placeholder="Type"
                /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All types</SelectItem>
                    <SelectItem value="deposit">Deposit</SelectItem>
                    <SelectItem value="withdrawal">Withdrawal</SelectItem>
                    <SelectItem value="internal">Transfer</SelectItem>
                </SelectContent>
            </Select>
            <Select v-model="statusFilter">
                <SelectTrigger class="w-full sm:w-40"
                    ><SelectValue placeholder="Status"
                /></SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All statuses</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="failed">Failed</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="border-border bg-card overflow-hidden rounded-2xl border">
            <div
                class="border-border text-vault-ink-dim hidden grid-cols-[auto_1fr_1fr_1fr_auto_auto] gap-4 border-b px-5 py-3 text-xs font-medium sm:grid"
            >
                <span>Asset</span>
                <span>Type</span>
                <span>Destination</span>
                <span>Date</span>
                <span class="text-right">Amount</span>
                <span class="text-right">Status</span>
            </div>

            <Link
                v-for="t in filtered"
                :key="t.id"
                :href="`/transactions/${t.id}`"
                class="border-border hover:bg-secondary/50 grid grid-cols-2 items-center gap-3 border-b px-5 py-4 transition-colors last:border-b-0 sm:grid-cols-[auto_1fr_1fr_1fr_auto_auto] sm:gap-4"
            >
                <div class="flex items-center gap-2.5">
                    <ChainGlyph :chain="t.chain" size="sm" />
                    <span class="text-foreground text-sm font-medium">{{
                        CHAINS[t.chain].symbol
                    }}</span>
                </div>
                <span
                    class="text-foreground hidden text-sm capitalize sm:block"
                    >{{ t.type }}</span
                >
                <span
                    class="text-vault-ink-dim hidden truncate font-mono text-xs sm:block"
                    >{{ truncateAddress(t.toAddress) }}</span
                >
                <span class="text-vault-ink-dim hidden text-xs sm:block">{{
                    formatRelativeTime(t.createdAt)
                }}</span>
                <div class="text-right">
                    <p class="tnum text-foreground text-sm font-medium">
                        {{ formatCrypto(t.amount) }}
                        {{ CHAINS[t.chain].symbol }}
                    </p>
                    <p class="tnum text-vault-ink-dim text-xs">
                        {{ formatUsd(t.usdValue) }}
                    </p>
                </div>
                <div class="flex justify-end">
                    <TransactionStatusBadge :status="t.status" />
                </div>
            </Link>

            <p
                v-if="filtered.length === 0"
                class="text-vault-ink-dim px-5 py-10 text-center text-sm"
            >
                No transactions match your filters.
            </p>
        </div>
    </div>
</template>
