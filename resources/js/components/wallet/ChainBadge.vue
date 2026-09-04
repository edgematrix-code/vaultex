<script setup lang="ts">
import { computed } from 'vue';
import { CHAINS } from '@/lib/wallet-data';
import type { ChainId } from '@/types/wallet';
import ChainGlyph from '@/components/wallet/ChainGlyph.vue';

const props = defineProps<{
    chain: ChainId;
}>();

const chain = computed(() => CHAINS[props.chain]);
</script>

<template>
    <span
        class="border-border bg-secondary/60 inline-flex items-center gap-1.5 rounded-full border py-1 pr-2.5 pl-1"
    >
        <ChainGlyph :chain="chain.id" size="sm" />
        <span class="text-foreground text-xs font-medium">{{
            chain.symbol
        }}</span>
    </span>
</template>
