<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-300 mb-1.5 ml-1" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="name@company.com"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5 ml-1">
                    <InputLabel for="password" value="Password" class="text-gray-300" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        Forgot?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" class="border-gray-700 bg-gray-900 text-indigo-600 focus:ring-indigo-500/20" />
                <span class="ms-2 text-sm text-gray-400">Keep me logged in</span>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign In</span>
                </PrimaryButton>
            </div>

            <p class="text-center text-sm text-gray-400">
                Don't have an account? 
                <Link :href="route('register')" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                    Create one for free
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
