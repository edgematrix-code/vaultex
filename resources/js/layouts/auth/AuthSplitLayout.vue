<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Fingerprint, Layers, ShieldCheck } from '@lucide/vue';
import LightTunnel from '@/components/LightTunnel.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const highlights = [
    {
        icon: ShieldCheck,
        text: 'Non-custodial — you always hold your keys.',
    },
    {
        icon: Layers,
        text: 'BTC, ETH, BNB, TRON, USDT & USDC in one dashboard.',
    },
    {
        icon: Fingerprint,
        text: 'Two-factor confirmation on every withdrawal.',
    },
];
</script>

<template>
    <div class="bg-background grid min-h-svh w-full lg:grid-cols-2">
        <!-- Brand panel (desktop only) -->
        <aside
            class="dark bg-vault-obsidian relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between"
        >
            <LightTunnel
                class="absolute inset-0"
                cable-color="#42D78F"
                pulse-color="#C9F7DF"
                tunnel-color="#0F4A36"
                :speed="0.05"
                :pulse-speed="1.1"
                :pulse-length="0.3"
                :pulse-blend="1"
                :pulse-width="0.9"
                :cable-count="16"
                :thickness="0.5"
                :rim-width="0.22"
                :waviness="0.28"
                :sway="0.4"
                :size="1.2"
                :glow="0.9"
                :fade-near="0.4"
                :fade-far="2.4"
                :brightness="1"
                :opacity="0.7"
                color-variance
                grain
                :grain-intensity="0.05"
                mouse-interaction
                :mouse-strength="0.05"
            />
            <div
                class="pointer-events-none absolute inset-0"
                style="
                    background: radial-gradient(
                        ellipse 80% 90% at 50% 45%,
                        color-mix(
                                in srgb,
                                var(--vault-obsidian) 78%,
                                transparent
                            )
                            0%,
                        transparent 70%
                    );
                "
            ></div>

            <div
                class="relative z-10 flex h-full flex-col justify-between p-10 xl:p-14"
            >
                <Link :href="home()" class="flex w-fit items-center gap-2.5">
                    <img
                        src="/brand/vaultis-mark.png"
                        alt=""
                        class="size-9 object-contain"
                    />
                    <span
                        class="font-display text-foreground text-2xl font-bold tracking-tight"
                    >
                        Vaultis
                    </span>
                </Link>

                <div class="max-w-md">
                    <p
                        class="text-vault-mint text-sm font-semibold tracking-widest uppercase"
                    >
                        Non-custodial · multi-chain
                    </p>
                    <h2
                        class="text-foreground mt-3 text-3xl font-extrabold tracking-tight xl:text-4xl"
                    >
                        Your keys, your vault, your rules.
                    </h2>
                    <ul class="mt-8 space-y-4">
                        <li
                            v-for="item in highlights"
                            :key="item.text"
                            class="flex items-center gap-3 text-sm"
                        >
                            <span
                                class="bg-vault-mint/10 text-vault-mint inline-flex size-8 shrink-0 items-center justify-center rounded-lg"
                            >
                                <component :is="item.icon" class="size-4" />
                            </span>
                            <span class="text-vault-ink-dim">{{
                                item.text
                            }}</span>
                        </li>
                    </ul>
                </div>

                <p class="text-vault-ink-dim text-xs">
                    © {{ new Date().getFullYear() }} Vaultis — Secure Assets
                </p>
            </div>
        </aside>

        <!-- Form panel -->
        <div class="flex min-h-svh flex-col overflow-y-auto">
            <Link
                :href="home()"
                class="flex w-fit items-center gap-2 px-6 pt-6 lg:hidden"
            >
                <img
                    src="/brand/vaultis-mark.png"
                    alt=""
                    class="size-7 object-contain"
                />
                <span class="font-display text-xl font-bold tracking-tight">
                    Vaultis
                </span>
            </Link>

            <div
                class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10"
            >
                <div class="w-full max-w-md">
                    <template v-if="title || description">
                        <h1
                            v-if="title"
                            class="font-display text-2xl font-bold tracking-tight lg:text-3xl"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="text-muted-foreground mt-2 text-sm"
                        >
                            {{ description }}
                        </p>
                    </template>
                    <div class="mt-8">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
