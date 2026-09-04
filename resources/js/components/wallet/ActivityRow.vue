<script setup lang="ts">
import { computed } from 'vue';
import { ArrowDownLeft, ArrowLeftRight, ArrowUpRight } from '@lucide/vue';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import TransactionStatusBadge from '@/components/wallet/TransactionStatusBadge.vue';
import {
    CHAINS,
    formatCrypto,
    formatRelativeTime,
    formatUsd,
} from '@/lib/wallet-data';
import type { Transaction } from '@/types/wallet';

const props = defineProps<{ transaction: Transaction }>();

const chain = computed(() => CHAINS[props.transaction.chain]);

const typeMeta = computed(
    () =>
        ({
            deposit: { label: 'Deposit', icon: ArrowDownLeft, sign: '+' },
            withdrawal: { label: 'Withdrawal', icon: ArrowUpRight, sign: '−' },
            internal: { label: 'Transfer', icon: ArrowLeftRight, sign: '' },
        })[props.transaction.type],
);
</script>

<template>
    <div
        class="hover:bg-secondary/60 flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors"
    >
        <ChainGlyph :chain="chain.id" size="sm" />
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5">
                <component
                    :is="typeMeta.icon"
                    class="text-vault-ink-dim size-3.5"
                />
                <p class="text-foreground truncate text-sm font-medium">
                    {{ typeMeta.label }} · {{ chain.symbol }}
                </p>
            </div>
            <p class="text-vault-ink-dim text-xs">
                {{ formatRelativeTime(transaction.createdAt) }}
            </p>
        </div>
        <div class="text-right">
            <p class="tnum text-foreground text-sm font-medium">
                {{ typeMeta.sign }}{{ formatCrypto(transaction.amount) }}
                {{ chain.symbol }}
            </p>
            <p class="tnum text-vault-ink-dim text-xs">
                {{ formatUsd(transaction.usdValue) }}
            </p>
        </div>
        <TransactionStatusBadge
            :status="transaction.status"
            class="hidden sm:inline-flex"
        />
    </div>
</template>
