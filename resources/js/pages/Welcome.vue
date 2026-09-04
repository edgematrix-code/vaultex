<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Fingerprint, KeyRound, Layers, Lock, ShieldCheck, Zap } from '@lucide/vue';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import LightTunnel from '@/components/LightTunnel.vue';

const supportedCoins = [
    { symbol: 'BTC', src: '/brand/coins/btc.png' },
    { symbol: 'ETH', src: '/brand/coins/eth.png' },
    { symbol: 'USDT', src: '/brand/coins/usdt.png' },
    { symbol: 'SOL', src: '/brand/coins/sol.png' },
    { symbol: 'BNB', src: '/brand/coins/bnb.png' },
    { symbol: 'USDC', src: '/brand/coins/usdc.png' },
    { symbol: 'TRX', src: '/brand/coins/trx.png' },
];

const features = [
    {
        icon: ShieldCheck,
        title: 'You hold the keys',
        description:
            'Vaultis never custodies your funds. Wallets are provisioned non-custodially, so private keys never touch our servers.',
    },
    {
        icon: Layers,
        title: 'Six chains, one balance',
        description:
            'Bitcoin, Ethereum, BNB Smart Chain, TRON, USDT, and USDC — tracked together with a single portfolio view.',
    },
    {
        icon: Zap,
        title: 'Live market data',
        description:
            'Prices, 24h change, and portfolio value stay current without a heavy background service running behind the scenes.',
    },
    {
        icon: Fingerprint,
        title: 'Verification on every transfer',
        description:
            'Two-factor confirmation is required before any withdrawal leaves your wallet, with a full audit trail of account activity.',
    },
];
</script>

<template>
    <Head title="Vaultis — Secure Assets" />

    <div class="bg-vault-obsidian text-foreground dark min-h-screen">
        <header
            class="flex w-full items-center justify-between px-6 py-5 sm:px-10 sm:py-6 lg:px-16"
        >
            <div class="flex items-center gap-2">
                <img
                    src="/brand/vaultis-mark.png"
                    alt="Vaultis"
                    class="size-7 object-contain sm:size-8"
                />
                <span
                    class="font-display text-lg font-bold tracking-tight sm:text-2xl"
                    >Vaultis</span
                >
            </div>

            <nav class="flex items-center gap-1.5 text-xs sm:gap-2 sm:text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="border-border text-foreground hover:border-vault-mint/40 rounded-lg border px-2.5 py-1.5 font-medium whitespace-nowrap sm:px-4 sm:py-2"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-vault-ink-dim hover:text-foreground rounded-lg px-2.5 py-1.5 font-medium whitespace-nowrap sm:px-4 sm:py-2"
                    >
                        Log in
                    </Link>
                    <!-- @chisel-registration -->
                    <Link
                        :href="register()"
                        class="brand-label bg-vault-mint text-primary-foreground rounded-lg px-2.5 py-1.5 font-semibold whitespace-nowrap sm:px-4 sm:py-2"
                    >
                        Get started
                    </Link>
                    <!-- @end-chisel-registration -->
                </template>
            </nav>
        </header>

        <main>
            <section
                class="relative w-full overflow-hidden px-6 pt-16 pb-20 sm:px-10 sm:pt-24 lg:px-16"
            >
                <!-- Animated brand light-tunnel background, tuned to the vault palette -->
                <LightTunnel
                    class="absolute inset-0"
                    cable-color="#42D78F"
                    pulse-color="#C9F7DF"
                    tunnel-color="#0F4A36"
                    :speed="0.06"
                    :pulse-speed="1.2"
                    :pulse-length="0.3"
                    :pulse-blend="1"
                    :pulse-width="0.9"
                    :cable-count="18"
                    :thickness="0.5"
                    :rim-width="0.22"
                    :waviness="0.28"
                    :sway="0.4"
                    :size="1.15"
                    :glow="1.1"
                    :fade-near="0.4"
                    :fade-far="2.4"
                    :brightness="1"
                    color-variance
                    grain
                    :grain-intensity="0.06"
                    :opacity="0.9"
                    mouse-interaction
                    :mouse-strength="0.06"
                />

                <!-- Soft radial veil behind the copy so the headline stays legible -->
                <div
                    class="pointer-events-none absolute inset-0"
                    style="
                        background: radial-gradient(
                            ellipse 70% 75% at 50% 42%,
                            color-mix(
                                    in srgb,
                                    var(--vault-obsidian) 72%,
                                    transparent
                                )
                                0%,
                            transparent 72%
                        );
                    "
                ></div>

                <div class="relative mx-auto max-w-2xl text-center">
                    <span
                        class="brand-label border-vault-mint/30 bg-vault-mint/10 text-vault-mint inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                    >
                        Non-custodial · multi-chain
                    </span>
                    <h1
                        class="text-foreground mt-6 text-4xl font-extrabold tracking-tight sm:text-5xl"
                    >
                        A wallet that keeps your keys where they belong — with
                        you.
                    </h1>
                    <p
                        class="text-vault-ink-dim mx-auto mt-5 max-w-lg text-base"
                    >
                        Vaultis holds your crypto across six chains in one calm,
                        legible dashboard — without ever holding your keys.
                    </p>
                    <div
                        class="mt-8 flex flex-wrap items-center justify-center gap-3"
                    >
                        <Link
                            :href="register()"
                            class="brand-label bg-vault-mint text-primary-foreground inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold whitespace-nowrap"
                        >
                            Create your wallet
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="login()"
                            class="border-border text-foreground hover:border-vault-mint/40 inline-flex items-center gap-2 rounded-xl border px-6 py-3 text-sm font-medium whitespace-nowrap"
                        >
                            Log in
                        </Link>
                    </div>

                    <div
                        class="mt-12 flex flex-wrap items-center justify-center gap-2.5"
                    >
                        <span
                            v-for="coin in supportedCoins"
                            :key="coin.symbol"
                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 backdrop-blur-sm"
                        >
                            <img
                                :src="coin.src"
                                :alt="`${coin.symbol} logo`"
                                class="size-6 rounded-full object-contain"
                                loading="lazy"
                            />
                            <span
                                class="text-vault-ink-dim text-xs font-semibold tracking-wide"
                                >{{ coin.symbol }}</span
                            >
                        </span>
                    </div>
                </div>
            </section>

            <!-- Phone mockup preview, between the hero and the feature cards -->
            <section
                class="relative w-full overflow-hidden px-0 py-10 sm:px-10 sm:py-16 lg:px-16"
            >
                <!-- Soft mint glow behind the mockup -->
                <div
                    class="pointer-events-none absolute top-1/2 left-1/2 h-[420px] w-[720px] -translate-x-1/2 -translate-y-1/2 rounded-full"
                    style="
                        background: radial-gradient(
                            closest-side,
                            color-mix(
                                    in srgb,
                                    var(--vault-mint) 10%,
                                    transparent
                                )
                                0%,
                            transparent 72%
                        );
                    "
                ></div>

                <div class="relative mx-auto max-w-3xl">
                    <img
                        src="/phone-mockup.png"
                        alt="Vaultis wallet app preview across two phones"
                        loading="lazy"
                        class="border-border/60 mx-auto w-full max-w-none rounded-2xl border shadow-2xl shadow-black/60 [mask-image:radial-gradient(ellipse_96%_100%_at_50%_50%,black_88%,transparent_100%)] max-sm:w-[120%] max-sm:-ml-[10%]"
                    />
                </div>
            </section>

            <section class="w-full px-6 pb-24 sm:px-10 lg:px-16">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="border-border bg-card rounded-2xl border p-6"
                    >
                        <div
                            class="bg-vault-mint/10 text-vault-mint inline-flex size-9 items-center justify-center rounded-lg"
                        >
                            <component :is="f.icon" class="size-4.5" />
                        </div>
                        <p class="text-foreground mt-4 text-sm font-semibold">
                            {{ f.title }}
                        </p>
                        <p class="text-vault-ink-dim mt-1.5 text-sm">
                            {{ f.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Security section: why Vaultis is safe, image left + text right -->
            <section class="relative w-full overflow-hidden px-6 py-16 sm:px-10 sm:py-20 lg:px-16">
                <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <!-- Image, left on desktop -->
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute top-1/2 left-1/2 h-[420px] w-[420px] -translate-x-1/2 -translate-y-1/2 rounded-full"
                            style="
                                background: radial-gradient(
                                    closest-side,
                                    color-mix(in srgb, var(--vault-mint) 12%, transparent) 0%,
                                    transparent 72%
                                );
                            "
                        ></div>
                        <div class="relative">
                            <img
                                src="/lady.jpg"
                                alt="A Vaultis user — your keys stay with you"
                                loading="lazy"
                                class="border-border/60 mx-auto w-full max-w-md rounded-2xl border shadow-2xl shadow-black/60 [mask-image:radial-gradient(ellipse_92%_92%_at_50%_50%,black_82%,transparent_100%)] lg:max-w-none"
                            />
                        </div>
                    </div>

                    <!-- Copy, right on desktop -->
                    <div class="text-center lg:text-left">
                        <span
                            class="brand-label border-vault-mint/30 bg-vault-mint/10 text-vault-mint inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                        >
                            <ShieldCheck class="size-3.5" />
                            Security first
                        </span>
                        <h2
                            class="font-display text-foreground mt-5 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Built like a vault.
                            <span class="bg-gradient-to-r from-vault-mint to-vault-mint/40 bg-clip-text text-transparent"
                                >Secured by you.</span
                            >
                        </h2>
                        <p class="text-vault-ink-dim mx-auto mt-4 max-w-lg text-base lg:mx-0">
                            Vaultis is non-custodial by design — your recovery phrase
                            is generated on your device and never stored on our
                            servers. No one can move your funds without your keys.
                        </p>
                        <ul class="mx-auto mt-8 flex max-w-lg flex-col gap-4 text-left lg:mx-0">
                            <li class="flex items-start gap-3">
                                <span
                                    class="bg-vault-mint/10 text-vault-mint mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-lg"
                                >
                                    <KeyRound class="size-4" />
                                </span>
                                <div>
                                    <p class="text-foreground text-sm font-semibold">Keys never leave your device</p>
                                    <p class="text-vault-ink-dim mt-0.5 text-sm">
                                        A 12-word recovery phrase, hashed and held only by you.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span
                                    class="bg-vault-mint/10 text-vault-mint mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-lg"
                                >
                                    <Fingerprint class="size-4" />
                                </span>
                                <div>
                                    <p class="text-foreground text-sm font-semibold">Two-factor on every move</p>
                                    <p class="text-vault-ink-dim mt-0.5 text-sm">
                                        Withdrawals require 2FA confirmation, with a full audit trail.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span
                                    class="bg-vault-mint/10 text-vault-mint mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-lg"
                                >
                                    <Lock class="size-4" />
                                </span>
                                <div>
                                    <p class="text-foreground text-sm font-semibold">Bank-grade encryption</p>
                                    <p class="text-vault-ink-dim mt-0.5 text-sm">
                                        Everything signed and verified, from deposits to webhooks.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <footer
            class="border-border w-full border-t px-6 py-10 sm:px-10 lg:px-16"
        >
            <div
                class="flex flex-col items-center justify-between gap-6 sm:flex-row"
            >
                <div
                    class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:gap-6"
                >
                    <div class="flex items-center gap-2">
                        <img
                            src="/brand/vaultis-mark.png"
                            alt="Vaultis"
                            class="size-7 object-contain"
                        />
                        <span
                            class="font-display text-xl font-bold tracking-tight"
                            >Vaultis</span
                        >
                    </div>
                    <p
                        class="text-vault-ink-dim max-w-xs text-center text-sm sm:text-left"
                    >
                        © {{ new Date().getFullYear() }} Vaultis. Non-custodial
                        — you always hold your keys.
                    </p>
                </div>
                <span class="brand-label text-vault-ink-dim text-xs"
                    >Secure Assets</span
                >
            </div>
        </footer>

        <!-- Giant ghost wordmark below the footer, echoing the classic
             Laravel footer mark -->
        <div class="w-full px-6 pt-4 pb-10 text-center">
            <span
                aria-hidden="true"
                class="font-display bg-gradient-to-b from-white/30 via-white/10 to-transparent bg-clip-text text-6xl leading-none tracking-tight text-transparent select-none sm:text-7xl lg:text-8xl"
                >Vaultis</span
            >
        </div>
    </div>
</template>
