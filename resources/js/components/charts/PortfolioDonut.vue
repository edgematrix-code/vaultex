<script setup lang="ts">
import { computed } from 'vue';
import { CHAINS, formatUsd } from '@/lib/wallet-data';
import type { AssetBalance } from '@/types/wallet';

const props = defineProps<{
    balances: AssetBalance[];
    total: number;
}>();

const RADIUS = 62;
const STROKE = 22;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

const segments = computed(() => {
    let offset = 0;
    return [...props.balances]
        .sort((a, b) => b.usdValue - a.usdValue)
        .map((b) => {
            const pct = props.total > 0 ? b.usdValue / props.total : 0;
            const length = pct * CIRCUMFERENCE;
            const seg = {
                chain: CHAINS[b.chain],
                pct,
                dasharray: `${length} ${CIRCUMFERENCE - length}`,
                dashoffset: -offset,
            };
            offset += length;
            return seg;
        });
});
</script>

<template>
    <div
        class="border-border bg-card flex h-full flex-col gap-5 rounded-2xl border p-6"
    >
        <p class="text-vault-ink-dim text-sm">Asset allocation</p>

        <div class="flex flex-1 flex-col items-center gap-6 sm:flex-row">
            <div class="relative shrink-0">
                <svg viewBox="0 0 160 160" class="size-40 -rotate-90">
                    <circle
                        cx="80"
                        cy="80"
                        :r="RADIUS"
                        fill="none"
                        stroke="var(--color-border)"
                        :stroke-width="STROKE"
                    />
                    <circle
                        v-for="seg in segments"
                        :key="seg.chain.id"
                        cx="80"
                        cy="80"
                        :r="RADIUS"
                        fill="none"
                        :stroke="seg.chain.color"
                        :stroke-width="STROKE"
                        stroke-linecap="butt"
                        :stroke-dasharray="seg.dasharray"
                        :stroke-dashoffset="seg.dashoffset"
                        class="transition-[stroke-dasharray] duration-700 ease-out"
                    />
                </svg>
                <div
                    class="absolute inset-0 flex flex-col items-center justify-center"
                >
                    <span class="text-vault-ink-dim text-xs">Total</span>
                    <span class="tnum text-foreground text-sm font-semibold">{{
                        formatUsd(total, { maximumFractionDigits: 0 })
                    }}</span>
                </div>
            </div>

            <ul class="w-full min-w-0 flex-1 space-y-2.5">
                <li
                    v-for="seg in segments"
                    :key="seg.chain.id"
                    class="flex items-center justify-between gap-3 text-sm"
                >
                    <span
                        class="text-foreground flex min-w-0 items-center gap-2"
                    >
                        <span
                            class="size-2.5 shrink-0 rounded-[3px]"
                            :style="{ background: seg.chain.color }"
                        />
                        <span class="truncate">{{ seg.chain.name }}</span>
                    </span>
                    <span class="tnum text-vault-ink-dim shrink-0"
                        >{{ (seg.pct * 100).toFixed(1) }}%</span
                    >
                </li>
            </ul>
        </div>
    </div>
</template>
