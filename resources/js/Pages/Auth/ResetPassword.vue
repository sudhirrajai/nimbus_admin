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
        kicker="— SECURITY & ACCESS"
        title="Set new password"
        subtitle="Choose a secure password with at least 8 characters for your Roook workspace."
    >
        <Head title="Reset Password — Roook Hosting" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Email address
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />
                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    New password
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="At least 8 characters"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />
                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Confirm new password
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    placeholder="Repeat password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />
                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Updating password...</span>
                    <template v-else>
                        <span>Reset Password</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
