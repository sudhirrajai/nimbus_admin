<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout
        kicker="Account recovery"
        title="Reset your password"
        subtitle="Forgot your password? Enter your email address and we will send you a secure password reset link."
    >
        <Head title="Forgot Password — Roook Hosting" />

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
                    placeholder="you@yourcompany.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Sending link...</span>
                <template v-else>
                    <span>Send reset link</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>

        <div class="auth-switch">
            <Link :href="route('login')">
                Back to sign in
            </Link>
        </div>
    </GuestLayout>
</template>
