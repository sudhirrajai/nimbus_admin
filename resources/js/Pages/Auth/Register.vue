<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Full Name" class="text-gray-750 mb-1.5 ml-1" />

                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    placeholder="John Doe"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-750 mb-1.5 ml-1" />

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="name@company.com"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-gray-750 mb-1.5 ml-1" />

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-gray-750 mb-1.5 ml-1"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-4">
                <PrimaryButton
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <span v-else>Create Account</span>
                </PrimaryButton>
            </div>

            <p class="text-center text-sm text-gray-400">
                Already have an account? 
                <Link :href="route('login')" class="font-semibold text-emerald-500 hover:text-emerald-600 transition-colors underline">
                    Sign in here
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
