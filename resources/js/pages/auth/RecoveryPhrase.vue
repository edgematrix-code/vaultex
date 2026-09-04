<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Clipboard, ClipboardCheck, ShieldAlert } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

defineProps<{
    words: string[];
}>();

const copied = ref(false);
const ackForm = useForm({});

const copyPhrase = async () => {
    const source = document.querySelector(
        '[data-phrase-words]',
    ) as HTMLElement | null;
    const phrase = source?.dataset.phraseWords;

    if (!phrase) {
        return;
    }

    try {
        await navigator.clipboard.writeText(phrase);
        copied.value = true;
        window.setTimeout(() => (copied.value = false), 2000);
    } catch {
        // Clipboard unavailable (non-secure context) — the words remain visible.
    }
};

// The server answers with an Inertia location response (409 + X-Inertia-
// Location), which makes the client navigate to the dashboard itself — no
// onSuccess handler needed.
const acknowledge = () => {
    ackForm.post('/register/recovery-phrase/acknowledge');
};
</script>

<template>
    <Head title="Save your recovery phrase" />

    <AuthSplitLayout
        title="Save your recovery phrase"
        description="This is the only time your phrase will be shown. Write it down and keep it somewhere safe — it's the key to your wallet."
    >
        <div class="grid gap-5">
            <div
                :data-phrase-words="words.join(' ')"
                class="border-border bg-muted/50 rounded-xl border p-4"
            >
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <div
                        v-for="(word, index) in words"
                        :key="`${index}-${word}`"
                        class="bg-background border-border flex items-center gap-2 rounded-lg border px-3 py-2"
                    >
                        <span class="text-muted-foreground text-xs font-medium">
                            {{ index + 1 }}
                        </span>
                        <span class="text-foreground text-sm font-semibold">
                            {{ word }}
                        </span>
                    </div>
                </div>
            </div>

            <Button
                type="button"
                variant="outline"
                class="w-full"
                @click="copyPhrase"
            >
                <ClipboardCheck v-if="copied" class="size-4" />
                <Clipboard v-else class="size-4" />
                {{ copied ? 'Copied!' : 'Copy phrase' }}
            </Button>

            <div
                class="flex items-start gap-2.5 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs leading-relaxed text-amber-700 dark:text-amber-400"
            >
                <ShieldAlert class="mt-0.5 size-4 shrink-0" />
                <p>
                    Anyone with this phrase can access your funds. Never share
                    it, and don't store it digitally on a device you don't
                    control. You'll use it to sign in from a new device.
                </p>
            </div>

            <Button
                type="button"
                class="w-full"
                :disabled="ackForm.processing"
                data-test="acknowledge-phrase-button"
                @click="acknowledge"
            >
                <Spinner v-if="ackForm.processing" />
                <Check v-else class="size-4" />
                I've saved my recovery phrase
            </Button>
        </div>
    </AuthSplitLayout>
</template>
