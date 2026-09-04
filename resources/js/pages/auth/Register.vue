<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Mail,
    User,
    X as XIcon,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import PasswordStrengthMeter from '@/components/auth/PasswordStrengthMeter.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

const step = ref(1);
const visited = ref(1);
const totalSteps = 3;

const name = ref('');
const email = ref('');
const password = ref('');
const confirmation = ref('');

const emailValid = computed(() =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()),
);
const nameValid = computed(() => name.value.trim().length > 0);
const passwordValid = computed(() => password.value.length >= 8);
const passwordsMatch = computed(
    () =>
        password.value === confirmation.value && confirmation.value.length > 0,
);

const step1Valid = computed(() => nameValid.value && emailValid.value);
const step2Valid = computed(() => passwordValid.value && passwordsMatch.value);

const lineFill = computed(
    () => 16.6667 + ((visited.value - 1) / (totalSteps - 1)) * 66.6667,
);

const stepTitles = ['Your details', 'Secure your account', 'Review & create'];

function goTo(next: number) {
    step.value = next;
    visited.value = Math.max(visited.value, next);
}

function goNext() {
    if (step.value === 1 && step1Valid.value) {
        goTo(2);
    } else if (step.value === 2 && step2Valid.value) {
        goTo(3);
    }
}

function onEnter() {
    if (step.value < totalSteps) {
        goNext();
        return;
    }
    (
        document.querySelector(
            '[data-test="register-user-button"]',
        ) as HTMLElement | null
    )?.click();
}

// After a failed server-side validation, take the user back to the step
// that contains the offending fields.
function onError(errors: Record<string, string>) {
    if (errors.name || errors.email) {
        goTo(1);
    } else if (errors.password || errors.password_confirmation) {
        goTo(2);
    }
}

// The kit's JSON form submission does not navigate on success. After the
// account is created we send the user to the one-time recovery-phrase reveal
// screen; that screen drops them on the dashboard once acknowledged.
const goToRecoveryPhrase = () => router.visit('/register/recovery-phrase');
</script>

<template>
    <Head title="Register" />

    <AuthSplitLayout
        title="Create your account"
        description="A couple of quick steps to set up your Vaultis wallet"
    >
        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            :on-success="goToRecoveryPhrase"
            :on-error="onError"
            v-slot="{ errors, processing }"
            class="flex flex-col"
        >
            <!-- Step indicator -->
            <div class="mb-8">
                <div class="relative">
                    <div
                        class="bg-muted absolute top-4 right-[16.6667%] left-[16.6667%] h-0.5 rounded-full"
                    />
                    <div
                        class="bg-vault-mint absolute top-4 left-[16.6667%] h-0.5 rounded-full transition-all duration-300"
                        :style="{ width: `${lineFill}%` }"
                    />
                    <ol class="relative grid grid-cols-3">
                        <li
                            v-for="(title, index) in stepTitles"
                            :key="title"
                            class="flex flex-col items-center"
                        >
                            <button
                                type="button"
                                class="text-foreground bg-background border-border ring-vault-mint/40 flex size-8 items-center justify-center rounded-full border text-sm font-semibold transition-all"
                                :class="{
                                    'bg-vault-mint border-vault-mint text-primary-foreground':
                                        step === index + 1,
                                    'border-vault-mint text-vault-mint':
                                        visited > index + 1,
                                    'cursor-pointer': visited >= index + 1,
                                    'cursor-default': visited < index + 1,
                                }"
                                :disabled="visited < index + 1"
                                @click="goTo(index + 1)"
                            >
                                <Check
                                    v-if="visited > index + 1"
                                    class="size-4"
                                />
                                <span v-else>{{ index + 1 }}</span>
                            </button>
                            <span
                                class="text-muted-foreground mt-2 text-xs font-medium"
                                :class="{
                                    'text-foreground font-semibold':
                                        step === index + 1,
                                }"
                            >
                                {{ title }}
                            </span>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Step 1: details -->
            <div
                v-show="step === 1"
                class="grid gap-6"
                @keydown.enter.prevent="onEnter"
            >
                <div class="grid gap-2">
                    <Label for="name">Full name</Label>
                    <div class="relative">
                        <User
                            class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                        />
                        <Input
                            id="name"
                            v-model="name"
                            type="text"
                            name="name"
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            placeholder="Full name"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.name" />
                    <p
                        v-if="!nameValid && name.length > 0"
                        class="text-destructive text-xs"
                    >
                        Please enter your name.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <div class="relative">
                        <Mail
                            class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                        />
                        <Input
                            id="email"
                            v-model="email"
                            type="email"
                            name="email"
                            :tabindex="2"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                    <p
                        v-if="email.length > 0 && !emailValid"
                        class="text-destructive text-xs"
                    >
                        Enter a valid email address.
                    </p>
                </div>

                <Button
                    type="button"
                    class="w-full"
                    :tabindex="3"
                    :disabled="!step1Valid"
                    @click="goNext"
                >
                    Continue
                    <ArrowRight class="size-4" />
                </Button>
            </div>

            <!-- Step 2: security -->
            <div
                v-show="step === 2"
                class="grid gap-6"
                @keydown.enter.prevent="onEnter"
            >
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        v-model="password"
                        name="password"
                        :tabindex="4"
                        autocomplete="new-password"
                        placeholder="Create a password"
                    />
                    <PasswordStrengthMeter :value="password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="confirmation"
                        name="password_confirmation"
                        :tabindex="5"
                        autocomplete="new-password"
                        placeholder="Repeat your password"
                    />
                    <InputError :message="errors.password_confirmation" />
                    <p
                        v-if="confirmation.length > 0 && !passwordsMatch"
                        class="text-destructive flex items-center gap-1.5 text-xs"
                    >
                        <XIcon class="size-3.5" />
                        Passwords don't match yet.
                    </p>
                    <p
                        v-else-if="passwordsMatch"
                        class="text-vault-mint flex items-center gap-1.5 text-xs"
                    >
                        <Check class="size-3.5" />
                        Passwords match.
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        :tabindex="6"
                        @click="goTo(1)"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </Button>
                    <Button
                        type="button"
                        class="flex-1"
                        :tabindex="7"
                        :disabled="!step2Valid"
                        @click="goNext"
                    >
                        Continue
                        <ArrowRight class="size-4" />
                    </Button>
                </div>
            </div>

            <!-- Step 3: review & create -->
            <div
                v-show="step === 3"
                class="grid gap-6"
                @keydown.enter.prevent="onEnter"
            >
                <div class="border-border bg-muted/50 rounded-xl border p-4">
                    <dl class="grid gap-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted-foreground">Name</dt>
                            <dd class="text-foreground font-medium">
                                {{ name }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted-foreground">Email</dt>
                            <dd class="text-foreground font-medium">
                                {{ email }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted-foreground">Password</dt>
                            <dd class="text-foreground font-medium">
                                ••••••••
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted-foreground">Confirmation</dt>
                            <dd
                                class="text-vault-mint flex items-center gap-1.5 font-medium"
                            >
                                <Check class="size-3.5" />
                                Matches
                            </dd>
                        </div>
                    </dl>
                </div>

                <p class="text-muted-foreground text-xs leading-relaxed">
                    When you create your account we'll provision your
                    multi-chain Vaultis wallet (BTC, ETH, BNB, TRON, USDT and
                    USDC) — you keep the keys, we never custody your funds.
                </p>

                <div class="flex items-center justify-between gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        :tabindex="8"
                        @click="goTo(2)"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </Button>
                    <Button
                        type="submit"
                        class="flex-1"
                        :tabindex="9"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" />
                        Create account
                    </Button>
                </div>
            </div>

            <div class="text-muted-foreground mt-8 text-center text-sm">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="10"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthSplitLayout>
</template>
