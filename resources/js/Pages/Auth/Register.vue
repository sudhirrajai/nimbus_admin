<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create Account — Rook Hosting" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold font-display tracking-tight text-[var(--text)]">
                Create your account
            </h2>
            <p class="text-xs text-[var(--text-muted)] mt-1">
                Get started with managed cloud hosting and server software
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                    Full Name
                </label>

                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    placeholder="Alex Morgan"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                    Work Email
                </label>

                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="name@company.com"
                    required
                    autocomplete="username"
                    class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                    Password
                </label>

                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="At least 8 characters"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                    Confirm Password
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    placeholder="Repeat password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] px-3.5 py-2.5 text-sm text-[var(--text)] placeholder-[var(--text-muted)] shadow-xs transition-all focus:border-[var(--accent)] focus:outline-none focus:ring-1 focus:ring-[var(--accent)]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="button button-primary w-full py-2.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 shadow-sm"
                    :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <template v-else>
                        <span>Create Account</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <p class="text-center text-xs text-[var(--text-muted)] pt-3">
                Already have an account? 
                <Link :href="route('login')" class="font-semibold text-[var(--accent)] hover:underline ml-1">
                    Sign in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
