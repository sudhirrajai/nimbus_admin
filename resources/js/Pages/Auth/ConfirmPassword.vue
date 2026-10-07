<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout
        kicker="Security verification"
        title="Confirm your password"
        subtitle="This is a secure area of Roook. Please confirm your password before continuing."
    >
        <Head title="Confirm Password — Roook Hosting" />

        <form class="auth-form" @submit.prevent="submit">
            <div class="auth-field">
                <label for="auth-password">Password</label>
                <input
                    id="auth-password"
                    type="password"
                    v-model="form.password"
                    placeholder="Enter your current password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Verifying...</span>
                <template v-else>
                    <span>Confirm Access</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>
    </GuestLayout>
</template>
