<script setup lang="ts">
import { computed } from 'vue';
import type { TransactionStatus } from '@/types/wallet';

const props = defineProps<{ status: TransactionStatus }>();

const config = computed(
    () =>
        ({
            completed: {
                label: 'Completed',
                class: 'bg-vault-mint/15 text-vault-mint',
            },
            pending: {
                label: 'Pending',
                class: 'bg-vault-amber/15 text-vault-amber',
            },
            failed: {
                label: 'Failed',
                class: 'bg-vault-rose/15 text-vault-rose',
            },
        })[props.status],
);
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
            config.class,
        ]"
    >
        {{ config.label }}
    </span>
</template>
