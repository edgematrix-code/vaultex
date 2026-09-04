<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import { CHAINS, formatCrypto, formatPct, formatUsd } from '@/lib/wallet-data';
import type { AssetBalance } from '@/types/wallet';

const props = defineProps<{
    balance: AssetBalance;
}>();

const chain = computed(() => CHAINS[props.balance.chain]);
</script>

<template>
    <Link
        :href="`/wallet/receive?chain=${balance.chain}`"
        class="hover:bg-secondary/60 grid grid-cols-[auto_1fr_auto] items-center gap-4 rounded-xl px-3 py-3 transition-colors sm:grid-cols-[auto_1.4fr_1fr_auto]"
    >
        <ChainGlyph :chain="chain.id" />

        <div class="min-w-0">
            <p class="text-foreground truncate text-sm font-medium">
                {{ chain.name }}
            </p>
            <p class="text-vault-ink-dim text-xs">{{ chain.network }}</p>
        </div>

        <div class="hidden text-right sm:block">
            <p class="tnum text-foreground text-sm">
                {{ formatCrypto(balance.balance) }} {{ chain.symbol }}
            </p>
            <p
                class="tnum text-xs"
                :class="
                    balance.change24hPct >= 0
                        ? 'text-vault-mint'
                        : 'text-vault-rose'
                "
            >
                {{ formatPct(balance.change24hPct) }}
            </p>
        </div>

        <div class="text-right">
            <p class="tnum text-foreground text-sm font-semibold">
                {{ formatUsd(balance.usdValue) }}
            </p>
            <p class="tnum text-vault-ink-dim text-xs sm:hidden">
                {{ formatCrypto(balance.balance) }} {{ chain.symbol }}
            </p>
        </div>
    </Link>
</template>
