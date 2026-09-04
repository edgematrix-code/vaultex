<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';
import AlertError from '@/components/AlertError.vue';
import ChainSelect from '@/components/wallet/ChainSelect.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { CHAINS, formatUsd } from '@/lib/wallet-data';
import type { AssetBalance, ChainId } from '@/types/wallet';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Wallet', href: '/wallet' },
            { title: 'Withdraw', href: '/wallet/withdraw' },
        ],
    },
});

const props = defineProps<{
    balances: AssetBalance[];
}>();

const params = new URLSearchParams(
    typeof window !== 'undefined' ? window.location.search : '',
);
const initial = (params.get('chain') as ChainId) || 'eth';

const form = useForm<{
    chain: ChainId;
    destination: string;
    amount: string;
    otp: string;
}>({
    chain: initial in CHAINS ? initial : 'eth',
    destination: '',
    amount: '',
    otp: '',
});

const chain = computed(() => CHAINS[form.chain]);
const available = computed(() =>
    props.balances.find((b) => b.chain === form.chain),
);

const confirmOpen = ref(false);

const networkFee = computed(() =>
    available.value ? available.value.priceUsd * 0.0006 : 0,
);
const amountUsd = computed(
    () => Number(form.amount || 0) * (available.value?.priceUsd ?? 0),
);

const canReview = computed(
    () => form.destination.trim().length > 4 && Number(form.amount) > 0,
);

const fieldErrors = computed(() =>
    Array.from(new Set(Object.values(form.errors).filter(Boolean))),
);

function openConfirm() {
    if (!canReview.value) return;
    form.clearErrors();
    confirmOpen.value = true;
}

function confirmWithdrawal() {
    form.post('/wallet/withdraw', {
        preserveScroll: true,
        onError: () => {
            confirmOpen.value = false;
        },
    });
}

// Re-open the confirmation dialog when the submitted OTP was rejected.
watch(
    () => form.errors.otp,
    (error) => {
        if (error) {
            confirmOpen.value = true;
        }
    },
);
</script>

<template>
    <Head title="Withdraw" />

    <div class="mx-auto flex w-full max-w-xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-foreground text-xl font-semibold tracking-tight">
                Withdraw
            </h1>
            <p class="text-vault-ink-dim mt-1 text-sm">
                Send funds out of your Vaultis wallet to an external address.
            </p>
        </div>

        <AlertError
            v-if="fieldErrors.length > 0"
            :errors="fieldErrors"
            title="Unable to submit withdrawal."
        />

        <div class="border-border bg-card rounded-2xl border p-6">
            <Label class="text-foreground mb-2 block text-sm font-medium"
                >Asset</Label
            >
            <ChainSelect v-model="form.chain" />
        </div>

        <div class="border-border bg-card space-y-4 rounded-2xl border p-6">
            <div>
                <Label for="destination">Destination address</Label>
                <Input
                    id="destination"
                    v-model="form.destination"
                    placeholder="Paste or scan an address"
                    class="mt-1.5 font-mono"
                    :aria-invalid="Boolean(form.errors.destination)"
                />
                <p
                    v-if="form.errors.destination"
                    class="text-vault-rose mt-1.5 text-xs"
                >
                    {{ form.errors.destination }}
                </p>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <Label for="amount">Amount</Label>
                    <button
                        v-if="available"
                        type="button"
                        class="text-vault-mint text-xs hover:underline"
                        @click="form.amount = String(available.balance)"
                    >
                        Max: {{ available.balance }} {{ chain.symbol }}
                    </button>
                </div>
                <div class="relative mt-1.5">
                    <Input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        min="0"
                        step="any"
                        placeholder="0.00"
                        class="pr-16"
                    />
                    <span
                        class="text-vault-ink-dim absolute top-1/2 right-3 -translate-y-1/2 text-sm"
                        >{{ chain.symbol }}</span
                    >
                </div>
                <p
                    v-if="form.errors.amount"
                    class="text-vault-rose mt-1.5 text-xs"
                >
                    {{ form.errors.amount }}
                </p>
                <p
                    v-else-if="form.amount"
                    class="tnum text-vault-ink-dim mt-1.5 text-xs"
                >
                    ≈ {{ formatUsd(amountUsd) }}
                </p>
            </div>

            <div class="bg-secondary/50 space-y-1.5 rounded-xl p-4 text-sm">
                <div class="text-vault-ink-dim flex justify-between">
                    <span>Network fee (estimated)</span>
                    <span class="tnum text-foreground">{{
                        formatUsd(networkFee)
                    }}</span>
                </div>
                <div class="text-vault-ink-dim flex justify-between">
                    <span>Network</span>
                    <span class="text-foreground">{{ chain.network }}</span>
                </div>
            </div>

            <div
                class="border-vault-amber/30 bg-vault-amber/10 text-vault-amber flex items-start gap-2 rounded-xl border p-3 text-xs"
            >
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                Double-check the destination address and network — crypto
                transfers can't be reversed.
            </div>

            <Button
                class="brand-label w-full"
                :disabled="!canReview || form.processing"
                @click="openConfirm"
            >
                Review transfer
            </Button>
        </div>

        <Dialog v-model:open="confirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirm with 2FA</DialogTitle>
                    <DialogDescription>
                        Enter the 6-digit code from your authenticator app to
                        confirm this withdrawal of {{ form.amount }}
                        {{ chain.symbol }}.
                    </DialogDescription>
                </DialogHeader>

                <div class="flex justify-center py-2">
                    <InputOTP
                        v-model="form.otp"
                        :maxlength="6"
                        @complete="confirmWithdrawal"
                    >
                        <InputOTPGroup>
                            <InputOTPSlot
                                v-for="i in 6"
                                :key="i"
                                :index="i - 1"
                            />
                        </InputOTPGroup>
                    </InputOTP>
                </div>
                <p
                    v-if="form.errors.otp"
                    class="text-vault-rose text-center text-xs"
                >
                    {{ form.errors.otp }}
                </p>

                <DialogFooter>
                    <Button variant="outline" @click="confirmOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        :disabled="form.otp.length !== 6 || form.processing"
                        @click="confirmWithdrawal"
                    >
                        {{ form.processing ? 'Sending…' : 'Confirm & send' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
