<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout
        kicker="Step 2 of 2"
        title="Verify your email"
        subtitle="We've sent a verification link to your email address. Please click it to activate your Roook workspace."
    >
        <Head title="Verify Email — Roook Hosting" />

        <div
            class="auth-notice mb-4"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to your email address.
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <button
                class="button button-primary auth-submit cursor-pointer"
                type="submit"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Resending email...</span>
                <template v-else>
                    <span>Resend Verification Email</span>
                    <ArrowRight :size="15" aria-hidden="true" />
                </template>
            </button>
        </form>

        <div class="auth-switch">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="cursor-pointer"
            >
                Log Out
            </Link>
        </div>
    </GuestLayout>
</template>
