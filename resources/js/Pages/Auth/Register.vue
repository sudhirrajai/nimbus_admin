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
    form.password_confirmation = form.password;
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout
        kicker="A thoughtful start"
        title="Create your account"
        subtitle="Set up your Roook workspace and keep your infrastructure close."
    >
        <Head title="Create Account — Roook Hosting" />

        <form class="auth-form" @submit.prevent="submit">
            <div class="auth-field">
                <label for="auth-name">Your name</label>
                <input
                    id="auth-name"
                    name="name"
                    type="text"
                    v-model="form.name"
                    autocomplete="name"
                    placeholder="Morgan Lee"
                    required
                    autofocus
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.name" />
            </div>

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
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div class="auth-field">
                <div class="auth-label-line">
                    <label for="auth-password">Password</label>
                </div>
                <input
                    id="auth-password"
                    name="password"
                    type="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    placeholder="At least 8 characters"
                    minlength="8"
                    required
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <p class="auth-privacy">Use at least 8 characters. You can update your details any time.</p>

            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Creating account...</span>
                <template v-else>
                    <span>Create account</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>

        <div class="auth-switch">
            Already have a Roook account?
            <Link :href="route('login')" class="ml-1">
                Sign in
            </Link>
        </div>

        <div class="auth-support">
            Need a hand? <a href="mailto:support@roook.host?subject=Roook%20account%20help">Talk to an engineer</a>
        </div>
    </GuestLayout>
</template>
