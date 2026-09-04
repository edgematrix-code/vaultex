<script setup lang="ts">
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';
import { CHAIN_LIST } from '@/lib/wallet-data';
import type { ChainId } from '@/types/wallet';

defineProps<{
    modelValue: ChainId;
}>();

defineEmits<{
    (e: 'update:modelValue', value: ChainId): void;
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        <button
            v-for="c in CHAIN_LIST"
            :key="c.id"
            type="button"
            @click="$emit('update:modelValue', c.id)"
            :class="[
                'flex items-center gap-2.5 rounded-xl border px-3 py-2.5 text-left transition-colors',
                modelValue === c.id
                    ? 'border-vault-mint/50 bg-vault-mint/10'
                    : 'border-border hover:border-vault-mint/30 hover:bg-secondary/60',
            ]"
        >
            <ChainGlyph :chain="c.id" size="sm" />
            <span class="min-w-0">
                <span
                    class="text-foreground block truncate text-sm font-medium"
                    >{{ c.symbol }}</span
                >
                <span class="text-vault-ink-dim block truncate text-[11px]">{{
                    c.network
                }}</span>
            </span>
        </button>
    </div>
</template>
