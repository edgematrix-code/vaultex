<script setup lang="ts">
import { computed } from 'vue';
import { CHAINS } from '@/lib/wallet-data';
import type { ChainId } from '@/types/wallet';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        chain: ChainId;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { size: 'md' },
);

const chain = computed(() => CHAINS[props.chain]);

const sizeClasses = computed(
    () =>
        ({
            sm: 'size-6 text-[10px] rounded-[7px]',
            md: 'size-9 text-xs rounded-[10px]',
            lg: 'size-12 text-sm rounded-xl',
        })[props.size],
);
</script>

<template>
    <!--
        Rounded-square asset glyph in the chain's identity color, per the
        brand's "digital UI & assets" icon-set direction -- a monogram
        rather than a reproduction of any third-party network logo.
    -->
    <span
        :class="
            cn(
                'tnum inline-flex shrink-0 items-center justify-center font-bold text-white',
                sizeClasses,
            )
        "
        :style="{
            background: `linear-gradient(155deg, ${chain.color}, color-mix(in srgb, ${chain.color} 55%, #06140f))`,
        }"
    >
        {{ chain.symbol.slice(0, 1) }}
    </span>
</template>
