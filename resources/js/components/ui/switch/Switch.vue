<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: boolean;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

function toggle() {
    if (props.disabled) return;
    emit('update:modelValue', !props.modelValue);
}
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        @click="toggle"
        :class="
            cn(
                'relative inline-flex h-6 w-10 shrink-0 items-center rounded-full border transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50',
                modelValue
                    ? 'border-transparent bg-vault-mint'
                    : 'border-border bg-muted',
            )
        "
    >
        <span
            :class="
                cn(
                    'pointer-events-none block size-4.5 translate-x-0.5 rounded-full bg-white shadow-sm transition-transform',
                    modelValue && 'translate-x-[18px]',
                )
            "
        />
    </button>
</template>
