<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDownToLine, ArrowUpFromLine, ScanLine } from '@lucide/vue';
</script>

<template>
    <div
        class="border-border bg-card flex flex-col justify-between gap-4 rounded-2xl border p-6"
    >
        <p class="text-vault-ink-dim text-sm">Quick actions</p>

        <div class="grid grid-cols-1 gap-2.5">
            <Link
                href="/wallet/deposit"
                class="brand-label bg-vault-mint text-primary-foreground flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold transition-transform hover:translate-x-0.5"
            >
                Deposit
                <ArrowDownToLine class="size-4" />
            </Link>
            <Link
                href="/wallet/withdraw"
                class="brand-label border-border bg-secondary text-secondary-foreground flex items-center justify-between rounded-xl border px-4 py-3 text-sm font-semibold transition-transform hover:translate-x-0.5"
            >
                Transfer
                <ArrowUpFromLine class="size-4" />
            </Link>
            <Link
                href="/wallet/receive"
                class="brand-label border-border text-foreground flex items-center justify-between rounded-xl border px-4 py-3 text-sm font-semibold transition-transform hover:translate-x-0.5"
            >
                Receive
                <ScanLine class="size-4" />
            </Link>
        </div>
    </div>
</template>
