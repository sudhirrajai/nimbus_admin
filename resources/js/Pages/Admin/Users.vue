<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array
});

// Search & Filter state
const search = ref('');
const statusFilter = ref('');

const filteredUsers = computed(() => {
    return (props.users || []).filter(u => {
        // Status filter
        if (statusFilter.value === 'active' && !u.is_active) return false;
        if (statusFilter.value === 'inactive' && u.is_active) return false;
        if (statusFilter.value === 'admin' && !u.is_admin) return false;

        // Search query
        if (!search.value) return true;
        const q = search.value.toLowerCase();
        const name = (u.name || '').toLowerCase();
        const email = (u.email || '').toLowerCase();
        const code = (u.customer_code || '').toLowerCase();
        const company = (u.company_name || '').toLowerCase();
        const phone = (u.phone || '').toLowerCase();
        const city = (u.city || '').toLowerCase();
        const state = (u.state || '').toLowerCase();
        return name.includes(q) || email.includes(q) || code.includes(q) || company.includes(q) || phone.includes(q) || city.includes(q) || state.includes(q);
    });
});

// Create User Modal State
const showCreateModal = ref(false);
const showCreatePassword = ref(false);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
    company_name: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'India',
    notes: '',
    is_admin: false,
    is_active: true,
});

const generateRandomPasswordForCreate = () => {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    let pass = '';
    for (let i = 0; i < 14; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    createForm.password = pass;
    showCreatePassword.value = true;
};

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    generateRandomPasswordForCreate();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const submitCreateUser = () => {
    createForm.post(route('admin.users.store'), {
        onSuccess: () => {
            closeCreateModal();
        }
    });
};

// Edit User Modal State
const showEditModal = ref(false);
const editingUser = ref(null);
const showEditPassword = ref(false);

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    phone: '',
    company_name: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'India',
    notes: '',
    is_admin: false,
    is_active: true,
});

const generateRandomPasswordForEdit = () => {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    let pass = '';
    for (let i = 0; i < 14; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    editForm.password = pass;
    showEditPassword.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.clearErrors();
    editForm.name = user.name || '';
    editForm.email = user.email || '';
    editForm.password = '';
    editForm.phone = user.phone || '';
    editForm.company_name = user.company_name || '';
    editForm.address = user.address || '';
    editForm.city = user.city || '';
    editForm.state = user.state || '';
    editForm.postal_code = user.postal_code || '';
    editForm.country = user.country || 'India';
    editForm.notes = user.notes || '';
    editForm.is_admin = !!user.is_admin;
    editForm.is_active = user.is_active !== undefined ? !!user.is_active : true;
    showEditPassword.value = false;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingUser.value = null;
    editForm.reset();
};

const submitEditUser = () => {
    if (!editingUser.value) return;
    editForm.put(route('admin.users.update', editingUser.value.uuid || editingUser.value.id), {
        onSuccess: () => {
            closeEditModal();
        }
    });
};

// 1-Click Toggle Active
const toggleActive = (user) => {
    const action = user.is_active ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} ${user.name}'s account?`)) {
        router.post(route('admin.users.toggle-active', user.uuid || user.id), {}, {
            preserveScroll: true
        });
    }
};

// 1-Click Toggle Admin
const toggleAdmin = (user) => {
    if (confirm(`Are you sure you want to toggle admin status for ${user.name}?`)) {
        router.post(route('admin.users.toggle-admin', user.uuid || user.id), {}, {
            preserveScroll: true
        });
    }
};

// Delete User
const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name} and all of their associated licenses and records? This action is permanent.`)) {
        router.delete(route('admin.users.destroy', user.uuid || user.id), {
            preserveScroll: true
        });
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
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
                    <p class="text-xs text-gray-500 mt-1">Review registered platform users, create new client accounts, manage credentials, and toggle account activation status.</p>
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
            <!-- Flash Message Alerts -->
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

            <!-- Search & Filters Toolbar -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-rounded absolute left-3 top-2.5 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search"
                        type="text"
                        placeholder="Search by name, email, client code, company..."
                        class="w-full pl-10 text-xs rounded-lg border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select 
                        v-model="statusFilter"
                        class="text-xs rounded-lg border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 pl-3 pr-8"
                    >
                        <option value="">All Accounts</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Deactivated Only</option>
                        <option value="admin">Administrators</option>
                    </select>

                    <div class="text-xs text-gray-500 font-medium whitespace-nowrap">
                        Showing {{ filteredUsers.length }} of {{ users?.length || 0 }} users
                    </div>
                </div>
            </div>

            <!-- Users Table Container -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Client Identity</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Role</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Licenses</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Hosting</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Invoices</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in filteredUsers" :key="user.uuid || user.id" class="hover:bg-slate-50/50 transition-colors">
                                <!-- Client Identity (No raw DB ID visible) -->
                                <td class="px-6 py-4.5">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                        <span class="inline-flex font-mono text-[10px] font-bold bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-gray-200">
                                            {{ user.customer_code || ('CUST-' + (user.uuid ? user.uuid.substring(0, 8).toUpperCase() : '001')) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ user.email }}</div>
                                    <div v-if="user.company_name || user.phone || user.city" class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-2">
                                        <span v-if="user.company_name" class="font-medium text-gray-600">{{ user.company_name }}</span>
                                        <span v-if="user.company_name && (user.phone || user.city)">•</span>
                                        <span v-if="user.phone">{{ user.phone }}</span>
                                        <span v-if="user.phone && user.city">•</span>
                                        <span v-if="user.city">{{ user.city }}{{ user.state ? ', ' + user.state : '' }}</span>
                                    </div>
                                </td>

                                <!-- Status Column: Active vs Disabled (1-Click Toggle) -->
                                <td class="px-6 py-4.5 text-center">
                                    <button 
                                        @click="toggleActive(user)"
                                        :disabled="user.id === $page.props.auth.user.id"
                                        :title="user.is_active ? 'Click to deactivate user' : 'Click to activate user'"
                                        :class="[
                                            user.is_active 
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' 
                                                : 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100',
                                            user.id === $page.props.auth.user.id ? 'opacity-60 cursor-not-allowed' : ''
                                        ]"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-xs transition-all"
                                    >
                                        <span class="material-symbols-rounded text-xs">
                                            {{ user.is_active ? 'check_circle' : 'block' }}
                                        </span>
                                        {{ user.is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </td>

                                <!-- Role (Admin Privileges) -->
                                <td class="px-6 py-4.5 text-center">
                                    <button 
                                        @click="toggleAdmin(user)"
                                        :disabled="user.id === $page.props.auth.user.id"
                                        :class="[
                                            user.is_admin 
                                                ? 'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100' 
                                                : 'bg-slate-50 border-gray-200 text-gray-500 hover:bg-slate-100 hover:text-gray-700',
                                            user.id === $page.props.auth.user.id ? 'opacity-60 cursor-not-allowed' : ''
                                        ]"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-xs transition-all"
                                        title="Click to toggle Admin / User role"
                                    >
                                        {{ user.is_admin ? 'Admin' : 'User' }}
                                    </button>
                                </td>

                                <!-- Licenses Count -->
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ user.licenses_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Hosting Accounts Count -->
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ user.hosting_accounts_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Invoices Count -->
                                <td class="px-6 py-4.5 text-center font-semibold text-gray-900 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        {{ user.invoices_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Registered Date -->
                                <td class="px-6 py-4.5 text-xs text-gray-500 whitespace-nowrap">
                                    {{ formatDateTime(user.created_at) }}
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-6 py-4.5 text-right space-x-1.5 whitespace-nowrap">
                                    <!-- Edit User Button -->
                                    <button 
                                        @click="openEditModal(user)"
                                        class="p-1.5 rounded-lg text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors"
                                        title="Edit User Details & Password"
                                    >
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </button>

                                    <!-- Delete User Button -->
                                    <button 
                                        @click="deleteUser(user)" 
                                        :disabled="user.id === $page.props.auth.user.id"
                                        :class="[
                                            user.id === $page.props.auth.user.id 
                                                ? 'text-gray-300 cursor-not-allowed' 
                                                : 'text-gray-400 hover:text-rose-600 hover:bg-rose-50'
                                        ]"
                                        class="p-1.5 rounded-lg transition-colors"
                                        title="Delete User"
                                    >
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-xs text-gray-400">
                                    No users found matching current filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= EDIT USER MODAL ================= -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-200 animate-scale-up">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-rounded text-lg">manage_accounts</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Edit User Profile</h3>
                            <p class="text-[11px] text-gray-500">Update credentials, profile info, and access privileges.</p>
                        </div>
                    </div>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitEditUser" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <!-- Client ID Identifier Badge -->
                    <div class="bg-slate-50 border border-gray-200 rounded-xl p-3 flex items-center justify-between">
                        <span class="text-xs text-gray-500 font-medium">Customer Reference:</span>
                        <span class="font-mono text-xs font-bold text-emerald-700">
                            {{ editingUser?.customer_code || ('CUST-' + (editingUser?.uuid ? editingUser.uuid.substring(0, 8).toUpperCase() : '001')) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                            <input 
                                v-model="editForm.name" 
                                type="text" 
                                required 
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                            <p v-if="editForm.errors.name" class="text-xs text-rose-600 mt-1">{{ editForm.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                            <input 
                                v-model="editForm.email" 
                                type="email" 
                                required 
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                            <p v-if="editForm.errors.email" class="text-xs text-rose-600 mt-1">{{ editForm.errors.email }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input 
                                v-model="editForm.phone" 
                                type="text" 
                                placeholder="+91 98765 43210"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>

                        <!-- Company -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Company / Organization</label>
                            <input 
                                v-model="editForm.company_name" 
                                type="text" 
                                placeholder="Acme Software Ltd."
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                    </div>

                    <!-- Billing & Physical Address -->
                    <div class="border border-gray-200 rounded-xl p-3.5 bg-slate-50/50 space-y-3">
                        <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-sm text-emerald-600">home_pin</span>
                            Billing & Physical Address
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Street Address</label>
                            <input 
                                v-model="editForm.address" 
                                type="text" 
                                placeholder="Plot / Flat No, Street, Society / Area"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">City</label>
                                <input 
                                    v-model="editForm.city" 
                                    type="text" 
                                    placeholder="e.g. Bhavnagar"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">State</label>
                                <input 
                                    v-model="editForm.state" 
                                    type="text" 
                                    placeholder="e.g. Gujarat"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Postal / PIN Code</label>
                                <input 
                                    v-model="editForm.postal_code" 
                                    type="text" 
                                    placeholder="e.g. 364002"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Country</label>
                                <input 
                                    v-model="editForm.country" 
                                    type="text" 
                                    placeholder="e.g. India"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Password Management Box -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-slate-50/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Reset Password</label>
                            <button 
                                type="button" 
                                @click="generateRandomPasswordForEdit" 
                                class="text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-xs">autorenew</span>
                                Generate Random Password
                            </button>
                        </div>
                        <div class="relative">
                            <input 
                                v-model="editForm.password" 
                                :type="showEditPassword ? 'text' : 'password'" 
                                minlength="8"
                                placeholder="Leave blank to keep existing password unchanged"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 pr-10 font-mono" 
                            />
                            <button 
                                type="button"
                                @click="showEditPassword = !showEditPassword"
                                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600"
                            >
                                <span class="material-symbols-rounded text-base">
                                    {{ showEditPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500">Only type here if you wish to overwrite the user's current password.</p>
                        <p v-if="editForm.errors.password" class="text-xs text-rose-600 mt-1">{{ editForm.errors.password }}</p>
                    </div>

                    <!-- Toggles: Account Status & Administrative Role -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <!-- Active / Deactivated Toggle -->
                        <label class="flex items-start gap-3 p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors shadow-2xs">
                            <input 
                                v-model="editForm.is_active" 
                                type="checkbox" 
                                :disabled="editingUser?.id === $page.props.auth.user.id"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 mt-0.5"
                            />
                            <div>
                                <span class="text-xs font-bold text-gray-900 block">Account Enabled</span>
                                <span class="text-[10px] text-gray-500 block leading-tight mt-0.5">
                                    When disabled, user is immediately blocked from logging in.
                                </span>
                            </div>
                        </label>

                        <!-- Admin Privileges Toggle -->
                        <label class="flex items-start gap-3 p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors shadow-2xs">
                            <input 
                                v-model="editForm.is_admin" 
                                type="checkbox" 
                                :disabled="editingUser?.id === $page.props.auth.user.id"
                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4 mt-0.5"
                            />
                            <div>
                                <span class="text-xs font-bold text-gray-900 block">Administrator</span>
                                <span class="text-[10px] text-gray-500 block leading-tight mt-0.5">
                                    Grant full access to servers, licenses, and admin panels.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Internal Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Administrative Notes</label>
                        <textarea 
                            v-model="editForm.notes" 
                            rows="2"
                            placeholder="Optional internal remarks regarding this customer or account..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button 
                            type="button" 
                            @click="closeEditModal"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editForm.processing"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                        >
                            <span v-if="editForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= CREATE USER MODAL ================= -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-200 animate-scale-up">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-rounded text-lg">person_add</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Create New User</h3>
                            <p class="text-[11px] text-gray-500">Register a new client profile with credentials.</p>
                        </div>
                    </div>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitCreateUser" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                            <input 
                                v-model="createForm.name" 
                                type="text" 
                                required 
                                placeholder="e.g. Rahul Sharma"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                            <p v-if="createForm.errors.name" class="text-xs text-rose-600 mt-1">{{ createForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                            <input 
                                v-model="createForm.email" 
                                type="email" 
                                required 
                                placeholder="rahul@example.com"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                            <p v-if="createForm.errors.email" class="text-xs text-rose-600 mt-1">{{ createForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Phone Number</label>
                            <input 
                                v-model="createForm.phone" 
                                type="text" 
                                placeholder="+91 98765 43210"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Company / Organization</label>
                            <input 
                                v-model="createForm.company_name" 
                                type="text" 
                                placeholder="Acme Technologies"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                    </div>

                    <!-- Billing & Physical Address -->
                    <div class="border border-gray-200 rounded-xl p-3.5 bg-slate-50/50 space-y-3">
                        <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-sm text-emerald-600">home_pin</span>
                            Billing & Physical Address
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Street Address</label>
                            <input 
                                v-model="createForm.address" 
                                type="text" 
                                placeholder="Plot / Flat No, Street, Society / Area"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">City</label>
                                <input 
                                    v-model="createForm.city" 
                                    type="text" 
                                    placeholder="e.g. Bhavnagar"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">State</label>
                                <input 
                                    v-model="createForm.state" 
                                    type="text" 
                                    placeholder="e.g. Gujarat"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Postal / PIN Code</label>
                                <input 
                                    v-model="createForm.postal_code" 
                                    type="text" 
                                    placeholder="e.g. 364002"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Country</label>
                                <input 
                                    v-model="createForm.country" 
                                    type="text" 
                                    placeholder="e.g. India"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Password Box -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-slate-50/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Initial Password</label>
                            <button 
                                type="button" 
                                @click="generateRandomPasswordForCreate" 
                                class="text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-xs">autorenew</span>
                                Generate Random Password
                            </button>
                        </div>
                        <div class="relative">
                            <input 
                                v-model="createForm.password" 
                                :type="showCreatePassword ? 'text' : 'password'" 
                                required 
                                minlength="8"
                                placeholder="Min 8 characters"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 pr-10 font-mono" 
                            />
                            <button 
                                type="button"
                                @click="showCreatePassword = !showCreatePassword"
                                class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600"
                            >
                                <span class="material-symbols-rounded text-base">
                                    {{ showCreatePassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="createForm.errors.password" class="text-xs text-rose-600 mt-1">{{ createForm.errors.password }}</p>
                    </div>

                    <!-- Toggles -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <label class="flex items-start gap-3 p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors shadow-2xs">
                            <input 
                                v-model="createForm.is_active" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 mt-0.5"
                            />
                            <div>
                                <span class="text-xs font-bold text-gray-900 block">Account Active</span>
                                <span class="text-[10px] text-gray-500 block leading-tight mt-0.5">Enable immediate platform login.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors shadow-2xs">
                            <input 
                                v-model="createForm.is_admin" 
                                type="checkbox" 
                                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4 mt-0.5"
                            />
                            <div>
                                <span class="text-xs font-bold text-gray-900 block">Administrator</span>
                                <span class="text-[10px] text-gray-500 block leading-tight mt-0.5">Full administrative rights.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Administrative Notes</label>
                        <textarea 
                            v-model="createForm.notes" 
                            rows="2"
                            placeholder="Optional notes..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
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
                            :disabled="createForm.processing"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                        >
                            <span v-if="createForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Create Account</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
