<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white mb-2">
                    Profile Settings
                </h2>
                <p class="text-slate-400">Update your account information and security preferences.</p>
            </div>
        </template>

        <div class="">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <div class="glass-morphism rounded-3xl p-8 border border-white/5">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="glass-morphism rounded-3xl p-8 border border-white/5">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="glass-morphism rounded-3xl p-8 border border-white/5 border-red-500/10">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
