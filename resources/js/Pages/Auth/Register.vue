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
        kicker="— A THOUGHTFUL START"
        title="Create your account"
        subtitle="Set up your Roook workspace and keep your infrastructure close."
    >
        <Head title="Create Account — Roook Hosting" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Your name
                </label>

                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    placeholder="Morgan Lee"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.name" />
            </div>

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
                    autocomplete="username"
                    class="w-full rounded-xl border border-[#1b2b22] bg-[#0c130f] px-4 py-3 text-sm text-white placeholder-[#3f5247] shadow-inner transition-colors focus:border-[#34d399] focus:outline-none focus:ring-1 focus:ring-[#34d399]"
                />

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-[#c8d6ce] mb-1.5">
                    Password
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

                <p class="text-[11px] text-[#55695e] mt-1.5 leading-normal">
                    Use at least 8 characters. You can update your details any time.
                </p>

                <InputError class="mt-1.5 text-xs text-red-400" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#7fe0a6] hover:bg-[#95f3bd] text-[#06180e] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-150 shadow-sm active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <template v-else>
                        <span>Create account</span>
                        <ArrowRight :size="15" />
                    </template>
                </button>
            </div>

            <div class="text-center pt-3 space-y-2">
                <p class="text-xs text-[#7e9287]">
                    Already have a Roook account? 
                    <Link :href="route('login')" class="font-semibold text-[#7fe0a6] hover:underline ml-1">
                        Sign in
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
