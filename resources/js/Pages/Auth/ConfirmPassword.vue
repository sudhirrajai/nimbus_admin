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
        kicker="— SECURITY VERIFICATION"
        title="Confirm your password"
        subtitle="This is a secure area of Roook. Please confirm your password before continuing."
    >
        <Head title="Confirm Password — Roook Hosting" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="password" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="Enter your current password"
                    required
                    autocomplete="current-password"
                    autofocus
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />
                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Verifying...</span>
                    <template v-else>
                        <span>Confirm Access</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
