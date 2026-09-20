<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    users: Array
});

// Create User Modal State
const showCreateModal = ref(false);
const showPassword = ref(false);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    is_admin: false,
});

const generateRandomPassword = () => {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    let pass = '';
    for (let i = 0; i < 14; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    userForm.password = pass;
    showPassword.value = true;
};

const openCreateModal = () => {
    userForm.reset();
    userForm.clearErrors();
    generateRandomPassword();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    userForm.reset();
};

const submitCreateUser = () => {
    userForm.post(route('admin.users.store'), {
        onSuccess: () => {
            closeCreateModal();
        }
    });
};

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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Users Management
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Review registered platform users, create new client accounts, and manage administrative privileges.</p>
                </div>
                <div>
                    <button 
                        @click="openCreateModal"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">person_add</span>
                        Create User
                    </button>
                </div>
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
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">User Details</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Licenses</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Hosting</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Invoices</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Admin Privileges</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-xs text-gray-500">
                                    {{ formatDateTime(user.created_at) }}
                                </td>
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ user.licenses_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ user.hosting_accounts_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        {{ user.invoices_count ?? 0 }}
                                    </span>
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

        <!-- Create User Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-200 animate-scale-up">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">person_add</span>
                        <h3 class="font-bold text-gray-900 text-base">Create New User</h3>
                    </div>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitCreateUser" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                        <input 
                            v-model="userForm.name" 
                            type="text" 
                            required 
                            placeholder="e.g. Rahul Sharma"
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                        />
                        <p v-if="userForm.errors.name" class="text-xs text-red-600 mt-1">{{ userForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input 
                            v-model="userForm.email" 
                            type="email" 
                            required 
                            placeholder="rahul@example.com"
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                        />
                        <p v-if="userForm.errors.email" class="text-xs text-red-600 mt-1">{{ userForm.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Password</label>
                            <button 
                                type="button" 
                                @click="generateRandomPassword" 
                                class="text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-xs">autorenew</span>
                                Generate Strong Password
                            </button>
                        </div>
                        <div class="relative">
                            <input 
                                v-model="userForm.password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                minlength="8"
                                placeholder="Min 8 characters"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 pr-10 font-mono" 
                            />
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600"
                            >
                                <span class="material-symbols-rounded text-base">
                                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="userForm.errors.password" class="text-xs text-red-600 mt-1">{{ userForm.errors.password }}</p>
                    </div>

                    <!-- Admin Privilege Checkbox -->
                    <div class="pt-2">
                        <label class="flex items-center gap-3 p-3 bg-slate-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-slate-100 transition-colors">
                            <input 
                                v-model="userForm.is_admin" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                            />
                            <div>
                                <span class="text-xs font-bold text-gray-800 block">Grant Administrative Privileges</span>
                                <span class="text-[11px] text-gray-500 block">User will have access to server nodes, all client accounts, licenses, and global settings.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button 
                            type="button" 
                            @click="closeCreateModal"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="userForm.processing"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                        >
                            <span v-if="userForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Create Account</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
