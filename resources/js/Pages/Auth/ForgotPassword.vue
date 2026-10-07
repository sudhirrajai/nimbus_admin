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
        kicker="— ACCOUNT RECOVERY"
        title="Reset your password"
        subtitle="Forgot your password? Enter your email address and we will send you a secure password reset link."
    >
        <Head title="Forgot Password — Roook Hosting" />

        <div
            v-if="status"
            class="mb-5 p-3 rounded-xl bg-[#0e251a] border border-[#1e4630] text-xs font-medium text-[#34d399]"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Work email
                </label>

                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="you@yourcompany.com"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Sending link...</span>
                    <template v-else>
                        <span>Send reset link</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <div class="text-center pt-3">
                <Link :href="route('login')" class="text-xs font-semibold text-[#7fe0a6] hover:underline">
                    Back to sign in
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
