<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    users: Array
});

const toggleAdmin = (user) => {
    if (confirm(`Are you sure you want to toggle admin status for ${user.name}?`)) {
        router.post(route('admin.users.toggle-admin', user.id));
    }
};

const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name} and all of their associated licenses? This action is irreversible.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    Users Management
                </h2>
                <p class="text-xs text-gray-500 mt-1">Review registered platform users and manage administrative access privileges.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Success/Error Alert -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error || $page.props.flash?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error || $page.props.flash?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-750">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p class="text-xs font-medium">{{ $page.props.errors?.error || $page.props.flash?.error }}</p>
                </div>
            </div>

            <!-- Users Table Container -->
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-505">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500">Registered Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500 text-center">Licenses Claimed</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500 text-center">Admin Privileges</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-gray-500 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div class="text-sm text-gray-700">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-xs text-gray-500">
                                    {{ formatDateTime(user.created_at) }}
                                </td>
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-sm">
                                    {{ user.licenses_count }}
                                </td>
                                <td class="px-6 py-4.5 text-center">
                                    <button 
                                        @click="toggleAdmin(user)"
                                        :disabled="user.id === $page.props.auth.user.id"
                                        :class="[
                                            user.is_admin 
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                                : 'bg-slate-50 border-gray-200 text-gray-500 hover:bg-slate-100 hover:text-gray-700',
                                            user.id === $page.props.auth.user.id ? 'opacity-60 cursor-not-allowed' : ''
                                        ]"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-sm transition-all"
                                    >
                                        {{ user.is_admin ? 'Admin' : 'User' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4.5 text-right">
                                    <button 
                                        @click="deleteUser(user)" 
                                        :disabled="user.id === $page.props.auth.user.id"
                                        :class="[
                                            user.id === $page.props.auth.user.id 
                                                ? 'text-gray-300 cursor-not-allowed' 
                                                : 'text-gray-400 hover:text-red-600 hover:bg-red-50'
                                        ]"
                                        class="p-1.5 rounded transition-colors"
                                        title="Delete User"
                                    >
                                        <span class="material-symbols-rounded text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
