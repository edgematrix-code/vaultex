<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!--
        Vaultis primary mark: interlocking shield + "V" checkmark with a
        hexagonal apex node, per brand guidelines section 01. Shipped as the
        approved raster asset rather than redrawn, so the gradient facets
        match brand exactly.
    -->
    <img
        src="/brand/vaultis-mark.png"
        alt="Vaultis"
        :class="className"
        v-bind="$attrs"
    />
</template>
