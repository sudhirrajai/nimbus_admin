<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout
        kicker="Good to have you back"
        title="Sign in to Roook"
        subtitle="Pick up where you left off with your hosting team."
    >
        <Head title="Sign In — Roook Hosting" />

        <div v-if="status" class="auth-notice" role="status">
            {{ status }}
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div class="auth-field">
                <label for="auth-email">Work email</label>
                <input
                    id="auth-email"
                    name="email"
                    type="email"
                    v-model="form.email"
                    autocomplete="email"
                    placeholder="you@yourcompany.com"
                    required
                    autofocus
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div class="auth-field">
                <div class="auth-label-line">
                    <label for="auth-password">Password</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="auth-label-note hover:text-[var(--accent)] transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>
                <input
                    id="auth-password"
                    name="password"
                    type="password"
                    v-model="form.password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    required
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="flex items-center gap-2 pt-1 select-none">
                <input
                    id="auth-remember"
                    type="checkbox"
                    v-model="form.remember"
                    class="rounded border-[var(--edge-strong)] bg-[var(--panel)] text-[var(--accent)] focus:ring-[var(--accent)] cursor-pointer"
                />
                <label for="auth-remember" class="text-xs text-[var(--text-soft)] cursor-pointer">Remember this device</label>
            </div>

            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Signing in...</span>
                <template v-else>
                    <span>Sign in</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>

        <div class="auth-switch">
            New to Roook?
            <Link :href="route('register')" class="ml-1">
                Create an account
            </Link>
        </div>

        <div class="auth-support">
            Need a hand? <a href="mailto:support@roook.host?subject=Roook%20account%20help">Talk to an engineer</a>
        </div>
    </GuestLayout>
</template>
