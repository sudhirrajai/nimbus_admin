<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail } from 'lucide-vue-next';

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
    <GuestLayout>
        <Head title="Sign In — Rook Hosting" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold font-display tracking-tight text-[var(--text)]">
                Welcome back
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-1">
                Enter your credentials to access your hosting workspace
            </p>
        </div>

        <div v-if="status" class="mb-4 p-3 rounded-lg bg-[var(--green-wash)] border border-[var(--edge)] text-xs font-medium text-[var(--accent)]">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                    Email Address
                </label>

                <div class="relative">
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="name@company.com"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                    />
                </div>

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-[var(--text)]">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-[var(--text-muted)] hover:text-[var(--accent)] transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>

                <div class="relative">
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                    />
                </div>

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="h-4 w-4 rounded border-[var(--edge)] bg-[var(--panel-hi)] text-[var(--accent)] focus:ring-[var(--accent)]/20"
                    />
                    <span class="text-xs text-[var(--text-soft)]">Remember this device</span>
                </label>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="button button-primary w-full py-2.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 shadow-sm"
                    :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Authenticating...</span>
                    <template v-else>
                        <span>Sign In</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <p class="text-center text-xs text-[var(--text-muted)] pt-3">
                Don't have an account yet? 
                <Link :href="route('register')" class="font-semibold text-[var(--accent)] hover:underline ml-1">
                    Create account
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
