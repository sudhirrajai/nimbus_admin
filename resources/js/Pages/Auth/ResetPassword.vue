<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout
        kicker="Security & access"
        title="Set new password"
        subtitle="Choose a secure password with at least 8 characters for your Roook workspace."
    >
        <Head title="Reset Password — Roook Hosting" />

        <form class="auth-form" @submit.prevent="submit">
            <div class="auth-field">
                <label for="auth-email">Email address</label>
                <input
                    id="auth-email"
                    name="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div class="auth-field">
                <label for="auth-password">New password</label>
                <input
                    id="auth-password"
                    name="password"
                    type="password"
                    v-model="form.password"
                    placeholder="At least 8 characters"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="auth-field">
                <label for="auth-confirm">Confirm new password</label>
                <input
                    id="auth-confirm"
                    name="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    placeholder="Repeat password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password_confirmation" />
            </div>

            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Updating password...</span>
                <template v-else>
                    <span>Reset Password</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>
    </GuestLayout>
</template>
