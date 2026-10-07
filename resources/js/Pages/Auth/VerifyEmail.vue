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
        kicker="— STEP 2 OF 2"
        title="Verify your email"
        subtitle="We've sent a verification link to your email address. Please click it to activate your Roook workspace."
    >
        <Head title="Verify Email — Roook Hosting" />

        <div
            class="mb-5 p-3 rounded-xl bg-[#0e251a] border border-[#1e4630] text-xs font-medium text-[#34d399]"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to your email address.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Resending email...</span>
                    <template v-else>
                        <span>Resend Verification Email</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <div class="text-center pt-3">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs text-[#7e9287] hover:text-white transition-colors cursor-pointer"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
