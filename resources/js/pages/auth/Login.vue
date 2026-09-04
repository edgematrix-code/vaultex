<script setup lang="ts">
import { Form, Head, router, useForm } from '@inertiajs/vue3';
import { Mail, ScrollText } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { dashboard, register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

// The kit's JSON form submission does not navigate on success, so we land
// the user on the dashboard explicitly once the session is established.
const goToDashboard = () => router.visit(dashboard());

const showPhrase = ref(false);
const phraseForm = useForm({ phrase: '' });

// The server answers with an Inertia location response (409 + X-Inertia-
// Location), which makes the client navigate to the dashboard itself — no
// onSuccess handler needed.
const signInWithPhrase = () => {
    phraseForm.post('/login/recovery-phrase');
};

const cancelPhrase = () => {
    showPhrase.value = false;
    phraseForm.reset();
    phraseForm.clearErrors();
};
</script>

<template>
    <Head title="Log in" />

    <AuthSplitLayout
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <PasskeyVerify>
            <Button
                v-if="!showPhrase"
                type="button"
                variant="outline"
                class="w-full"
                @click="showPhrase = true"
            >
                <ScrollText class="size-4" />
                Sign in with Recovery phrase
            </Button>

            <div
                v-else
                class="border-border bg-muted/40 rounded-xl border p-4"
            >
                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <Label for="recovery-phrase">Recovery phrase</Label>
                        <button
                            type="button"
                            class="text-muted-foreground hover:text-foreground text-xs underline underline-offset-4"
                            @click="cancelPhrase"
                        >
                            Cancel
                        </button>
                    </div>
                    <textarea
                        id="recovery-phrase"
                        v-model="phraseForm.phrase"
                        name="phrase"
                        rows="3"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        placeholder="Enter your 12-word recovery phrase, separated by spaces"
                        class="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 w-full rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                        @keydown.enter.prevent="signInWithPhrase"
                    />
                    <InputError :message="phraseForm.errors.phrase" />
                    <Button
                        type="button"
                        class="w-full"
                        :disabled="
                            phraseForm.processing ||
                            phraseForm.phrase.trim() === ''
                        "
                        @click="signInWithPhrase"
                    >
                        <Spinner v-if="phraseForm.processing" />
                        Sign in
                    </Button>
                </div>
            </div>
        </PasskeyVerify>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            :on-success="goToDashboard"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <div class="relative">
                        <Mail
                            class="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                        />
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot your password?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <div class="text-muted-foreground text-center text-sm">
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>
    </AuthSplitLayout>
</template>
