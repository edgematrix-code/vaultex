<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    value?: string;
}>();

const criteria = computed(() => {
    const value = props.value ?? '';

    return {
        length: value.length >= 8,
        mixedCase: /[a-z]/.test(value) && /[A-Z]/.test(value),
        number: /\d/.test(value),
        symbol: /[^A-Za-z0-9]/.test(value),
    };
});

const score = computed(
    () => Object.values(criteria.value).filter(Boolean).length,
);

const empty = computed(() => (props.value ?? '').length === 0);

const labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'] as const;

const meta = computed(() => {
    const map = [
        { label: labels[0], bar: 'bg-red-500', text: 'text-red-500' },
        { label: labels[1], bar: 'bg-red-500', text: 'text-red-500' },
        { label: labels[2], bar: 'bg-amber-500', text: 'text-amber-500' },
        { label: labels[3], bar: 'bg-lime-500', text: 'text-lime-500' },
        { label: labels[4], bar: 'bg-vault-mint', text: 'text-vault-mint' },
    ] as const;

    return map[score.value] ?? map[0];
});

const hints = computed(() => {
    const failed: string[] = [];

    if (!criteria.value.length) {
        failed.push('8+ characters');
    }
    if (!criteria.value.mixedCase) {
        failed.push('upper & lowercase');
    }
    if (!criteria.value.number) {
        failed.push('a number');
    }
    if (!criteria.value.symbol) {
        failed.push('a symbol');
    }

    return failed;
});
</script>

<template>
    <div v-if="!empty" class="space-y-2">
        <div class="flex items-center justify-between">
            <div class="flex flex-1 items-center gap-1.5">
                <div
                    v-for="i in 4"
                    :key="i"
                    class="bg-muted h-1.5 flex-1 rounded-full transition-colors"
                    :class="i <= score ? meta.bar : ''"
                />
            </div>
            <span class="text-xs font-medium" :class="meta.text">
                {{ meta.label }}
            </span>
        </div>
        <p v-if="score < 4" class="text-muted-foreground text-xs">
            Add {{ hints.join(', ') }} for a stronger password.
        </p>
    </div>
</template>
