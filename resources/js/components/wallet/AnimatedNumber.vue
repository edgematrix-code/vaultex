<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

/**
 * Animated balance counter. Respects prefers-reduced-motion (jumps straight
 * to the final value) and only runs the count-up once per value change --
 * a single orchestrated moment, not a scroll-triggered gimmick.
 */
const props = withDefaults(
    defineProps<{
        value: number;
        formatter?: (n: number) => string;
        durationMs?: number;
    }>(),
    { durationMs: 900 },
);

const display = ref(props.value);

const reduceMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function animateTo(target: number) {
    if (reduceMotion) {
        display.value = target;
        return;
    }
    const start = display.value;
    const delta = target - start;
    const startTime = performance.now();

    function step(now: number) {
        const progress = Math.min((now - startTime) / props.durationMs, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        display.value = start + delta * eased;
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

onMounted(() => animateTo(props.value));
watch(
    () => props.value,
    (v) => animateTo(v),
);
</script>

<template>
    <span class="tnum">{{
        formatter ? formatter(display) : display.toFixed(2)
    }}</span>
</template>
