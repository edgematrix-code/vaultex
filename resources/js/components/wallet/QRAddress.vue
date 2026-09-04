<script setup lang="ts">
import { ref } from 'vue';
import QRCode from 'qrcode.vue';
import { Check, Copy } from '@lucide/vue';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import { CHAINS } from '@/lib/wallet-data';
import type { ChainId } from '@/types/wallet';

const props = defineProps<{
    chain: ChainId;
    address: string;
}>();

const copied = ref(false);

async function copy() {
    try {
        await navigator.clipboard.writeText(props.address);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1800);
    } catch {
        /* clipboard unavailable — silently ignore */
    }
}
</script>

<template>
    <div
        class="border-border bg-card flex flex-col items-center gap-5 rounded-2xl border p-6 text-center sm:p-8"
    >
        <div class="border-border rounded-2xl border bg-white p-4">
            <QRCode :value="address" :size="176" render-as="svg" :margin="0" />
        </div>

        <div class="flex items-center gap-2">
            <ChainGlyph :chain="chain" size="sm" />
            <span class="text-foreground text-sm font-medium">
                Your {{ CHAINS[chain].name }} deposit address
            </span>
        </div>

        <button
            type="button"
            @click="copy"
            class="group border-border bg-secondary/60 hover:border-vault-mint/40 flex w-full max-w-sm items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left transition-colors"
        >
            <span class="text-foreground min-w-0 truncate font-mono text-sm">{{
                address
            }}</span>
            <span
                class="text-vault-ink-dim group-hover:text-vault-mint shrink-0"
            >
                <Check v-if="copied" class="text-vault-mint size-4" />
                <Copy v-else class="size-4" />
            </span>
        </button>

        <p class="text-vault-ink-dim max-w-sm text-xs">
            Only send {{ CHAINS[chain].symbol }} on the
            {{ CHAINS[chain].network }} network to this address. Sending any
            other asset or network may result in permanent loss.
        </p>
    </div>
</template>
