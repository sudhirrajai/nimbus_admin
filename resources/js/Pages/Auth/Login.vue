<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout
        kicker="— WELCOME BACK"
        title="Sign in to your account"
        subtitle="Access your Roook workspace and keep your infrastructure close."
    >
        <Head title="Sign In — Roook Hosting" />

        <div v-if="status" class="mb-5 p-3 rounded-xl bg-[#0e251a] border border-[#1e4630] text-xs font-medium text-[#34d399]">
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

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-[#c8d6ce]">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-[#7e9287] hover:text-[#7fe0a6] transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>

                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="At least 8 characters"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="h-4 w-4 rounded border-[#1b2b22] bg-[#0c130f] text-[#34d399] focus:ring-[#34d399]/20 focus:ring-offset-0"
                    />
                    <span class="text-xs text-[#7e9287]">Remember this device</span>
                </label>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <template v-else>
                        <span>Sign in</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <div class="text-center pt-3 space-y-2">
                <p class="text-xs text-[#7e9287]">
                    Don't have a Roook account? 
                    <Link :href="route('register')" class="font-semibold text-[#7fe0a6] hover:underline ml-1">
                        Create account
                    </Link>
                </p>
                <p class="text-xs text-[#52665b]">
                    Need a hand? 
                    <a href="mailto:support@roook.host" class="font-medium text-[#7fe0a6] hover:underline ml-1">
                        Talk to an engineer
                    </a>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
