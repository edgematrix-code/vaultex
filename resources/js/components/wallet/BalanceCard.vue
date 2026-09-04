<script setup lang="ts">
import { ShieldCheck, ShieldOff, TrendingDown, TrendingUp } from '@lucide/vue';
import AnimatedNumber from '@/components/wallet/AnimatedNumber.vue';
import { formatPct, formatUsd } from '@/lib/wallet-data';
import type { SecurityStatus } from '@/types/wallet';

defineProps<{
    total: number;
    changePct: number;
    security: SecurityStatus;
}>();
</script>

<template>
    <div
        class="border-border from-vault-forest/90 via-card to-card relative col-span-full overflow-hidden rounded-2xl border bg-gradient-to-br p-6 sm:p-8 lg:col-span-2"
    >
        <!-- Convergence motif: thin lines resolving toward the balance figure,
             echoing the brand's "security as convergence" motion principle. -->
        <svg
            class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.16]"
            viewBox="0 0 640 280"
            preserveAspectRatio="none"
            aria-hidden="true"
        >
            <defs>
                <linearGradient id="converge" x1="0" y1="0" x2="1" y2="1">
                    <stop
                        offset="0%"
                        stop-color="var(--vault-mint)"
                        stop-opacity="0"
                    />
                    <stop
                        offset="100%"
                        stop-color="var(--vault-mint)"
                        stop-opacity="0.9"
                    />
                </linearGradient>
            </defs>
            <g stroke="url(#converge)" stroke-width="1" fill="none">
                <path d="M0 20 L 560 40" />
                <path d="M0 60 L 560 60" />
                <path d="M0 100 L 560 80" />
                <path d="M20 -10 L 560 40" />
                <path d="M60 260 L 560 60" />
                <path d="M0 220 L 560 90" />
            </g>
            <circle cx="560" cy="55" r="3" fill="var(--vault-mint)" />
        </svg>

        <div class="relative flex flex-col gap-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-vault-ink-dim text-sm">Portfolio balance</p>
                    <div
                        class="text-foreground mt-2 flex items-baseline gap-1 text-4xl font-extrabold tracking-tight sm:text-5xl"
                    >
                        <span
                            class="text-vault-ink-dim text-2xl font-semibold sm:text-3xl"
                            >$</span
                        >
                        <AnimatedNumber
                            :value="total"
                            :formatter="(n) => formatUsd(n).replace('$', '')"
                        />
                    </div>
                    <div
                        class="mt-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-sm font-medium"
                        :class="
                            changePct >= 0
                                ? 'bg-vault-mint/15 text-vault-mint'
                                : 'bg-vault-rose/15 text-vault-rose'
                        "
                    >
                        <TrendingUp v-if="changePct >= 0" class="size-3.5" />
                        <TrendingDown v-else class="size-3.5" />
                        {{ formatPct(changePct) }}
                        <span class="text-vault-ink-dim font-normal"
                            >today</span
                        >
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    <span
                        class="border-vault-mint/30 bg-vault-mint/10 text-vault-mint inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium"
                    >
                        <ShieldCheck class="size-3.5" />
                        Non-custodial · you hold the keys
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium"
                        :class="
                            security.twoFactorEnabled
                                ? 'border-border bg-secondary text-muted-foreground'
                                : 'border-vault-amber/40 bg-vault-amber/10 text-vault-amber'
                        "
                    >
                        <ShieldCheck
                            v-if="security.twoFactorEnabled"
                            class="size-3.5"
                        />
                        <ShieldOff v-else class="size-3.5" />
                        2FA {{ security.twoFactorEnabled ? 'on' : 'off' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
