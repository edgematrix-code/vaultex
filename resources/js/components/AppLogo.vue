<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

withDefaults(
    defineProps<{
        tagline?: boolean;
    }>(),
    {
        tagline: false,
    },
);
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center">
        <AppLogoIcon class="size-8 object-contain" />
    </div>
    <div class="ml-1 grid flex-1 text-left leading-none">
        <span class="font-display truncate text-2xl font-bold tracking-tight">
            Vaultis
        </span>
        <span
            v-if="tagline"
            class="brand-label text-sidebar-foreground/50 mt-0.5 truncate text-[10px] font-semibold"
        >
            Secure Assets
        </span>
    </div>
</template>
