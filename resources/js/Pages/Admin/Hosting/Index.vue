<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    servers: Array,
    accounts: Object,
    requests: Object,
    users: Array,
});

const activeTab = ref('accounts'); // 'accounts', 'servers', 'requests'

// Server modal state
const showServerModal = ref(false);
const editingServer = ref(null);
const serverForm = useForm({
    name: '',
    ip_address: '',
    panel_url: '',
    api_secret: '',
    is_active: true,
    notes: '',
});

const openNewServerModal = () => {
    editingServer.value = null;
    serverForm.reset();
    serverForm.api_secret = '';
    serverForm.is_active = true;
    showServerModal.value = true;
};

const openEditServerModal = (server) => {
    editingServer.value = server;
    serverForm.name = server.name;
    serverForm.ip_address = server.ip_address;
    serverForm.panel_url = server.panel_url;
    serverForm.api_secret = server.api_secret;
    serverForm.is_active = Boolean(server.is_active);
    serverForm.notes = server.notes || '';
    showServerModal.value = true;
};

const submitServerForm = () => {
    if (editingServer.value) {
        serverForm.put(route('admin.hosting.servers.update', editingServer.value.id), {
            onSuccess: () => { showServerModal.value = false; }
        });
    } else {
        serverForm.post(route('admin.hosting.servers.store'), {
            onSuccess: () => { showServerModal.value = false; }
        });
    }
};

const deleteServer = (server) => {
    if (confirm(`Are you sure you want to delete server "${server.name}"?`)) {
        router.delete(route('admin.hosting.servers.destroy', server.id));
    }
};

// Account modal state
const showAccountModal = ref(false);
const editingAccount = ref(null);
const accountForm = useForm({
    user_id: '',
    hosting_server_id: '',
    domain: '',
    plan_name: 'Managed Cloud VPS',
    status: 'active',
    notes: '',
    amount: 2999,
    currency: 'INR',
    payment_status: 'paid',
    payment_method: 'Admin Assignment',
});

const openNewAccountModal = (prefillUserId = '', prefillDomain = '') => {
    editingAccount.value = null;
    accountForm.reset();
    accountForm.user_id = prefillUserId || (props.users[0]?.id || '');
    accountForm.hosting_server_id = props.servers[0]?.id || '';
    accountForm.domain = prefillDomain;
    accountForm.plan_name = 'Managed Cloud VPS';
    accountForm.status = 'active';
    accountForm.amount = 2999;
    accountForm.currency = 'INR';
    accountForm.payment_status = 'paid';
    accountForm.payment_method = 'Admin Assignment';
    showAccountModal.value = true;
};

const openEditAccountModal = (account) => {
    editingAccount.value = account;
    accountForm.user_id = account.user_id;
    accountForm.hosting_server_id = account.hosting_server_id;
    accountForm.domain = account.domain;
    accountForm.plan_name = account.plan_name;
    accountForm.status = account.status;
    accountForm.notes = account.notes || '';
    showAccountModal.value = true;
};

const submitAccountForm = () => {
    if (editingAccount.value) {
        accountForm.put(route('admin.hosting.accounts.update', editingAccount.value.id), {
            onSuccess: () => { showAccountModal.value = false; }
        });
    } else {
        accountForm.post(route('admin.hosting.accounts.store'), {
            onSuccess: () => { showAccountModal.value = false; }
        });
    }
};

const deleteAccount = (account) => {
    if (confirm(`Are you sure you want to delete hosting account for "${account.domain}"?`)) {
        router.delete(route('admin.hosting.accounts.destroy', account.id));
    }
};

// Request management
const updateRequestStatus = (req, status) => {
    router.patch(route('admin.hosting.requests.update', req.id), { status }, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Managed Hosting" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Managed Hosting & SSO Hub
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Manage dedicated Nimbus server nodes, client hosting accounts, inbound requests, and 1-Click SSO access.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        v-if="activeTab === 'servers'"
                        @click="openNewServerModal"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">dns</span>
                        Add Nimbus Node
                    </button>
                    <button 
                        v-if="activeTab === 'accounts'"
                        @click="openNewAccountModal()"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">person_add</span>
                        Assign Client Account
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-gray-200">
                <button 
                    @click="activeTab = 'accounts'"
                    :class="[
                        activeTab === 'accounts' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors"
                >
                    <span class="material-symbols-rounded text-base">group</span>
                    Client Accounts
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ accounts.total || 0 }}</span>
                </button>

                <button 
                    @click="activeTab = 'servers'"
                    :class="[
                        activeTab === 'servers' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors"
                >
                    <span class="material-symbols-rounded text-base">dns</span>
                    Nimbus Server Nodes
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ servers.length }}</span>
                </button>

                <button 
                    @click="activeTab = 'requests'"
                    :class="[
                        activeTab === 'requests' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors"
                >
                    <span class="material-symbols-rounded text-base">mark_email_unread</span>
                    Inbound Client Requests
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ requests.total || 0 }}</span>
                </button>
            </div>

            <!-- TAB 1: CLIENT ACCOUNTS -->
            <div v-if="activeTab === 'accounts'" class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Client User</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Assigned Domain</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Nimbus Server Node</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan / Tier</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!accounts.data || accounts.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No managed hosting accounts assigned yet. Click "Assign Client Account" to add your first client.
                                    </td>
                                </tr>
                                <tr v-for="account in accounts.data" :key="account.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ account.user?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ account.user?.email || 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                            {{ account.domain }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-gray-800">{{ account.server?.name || 'Unassigned' }}</div>
                                        <div class="text-[11px] font-mono text-gray-400">{{ account.server?.ip_address }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                        {{ account.plan_name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': account.status === 'active',
                                                'bg-amber-50 text-amber-700 border-amber-200': account.status === 'suspended',
                                                'bg-rose-50 text-rose-700 border-rose-200': account.status === 'terminated'
                                            }"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                                        >
                                            {{ account.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a 
                                                :href="route('admin.hosting.accounts.sso', account.id)"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded border border-emerald-200 transition-colors shadow-sm"
                                                title="Log in directly to Nimbus as this client"
                                            >
                                                <span class="material-symbols-rounded text-sm">login</span>
                                                1-Click SSO
                                            </a>
                                            <button 
                                                @click="openEditAccountModal(account)"
                                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-slate-100 rounded transition-colors"
                                                title="Edit Account"
                                            >
                                                <span class="material-symbols-rounded text-base">edit</span>
                                            </button>
                                            <button 
                                                @click="deleteAccount(account)"
                                                class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                                title="Delete Account"
                                            >
                                                <span class="material-symbols-rounded text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: NIMBUS SERVER NODES -->
            <div v-if="activeTab === 'servers'" class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Node Name</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Nimbus Panel URL</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Assigned Accounts</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="servers.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No Nimbus server nodes configured. Click "Add Nimbus Node" to connect your first server.
                                    </td>
                                </tr>
                                <tr v-for="server in servers" :key="server.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ server.name }}</div>
                                        <div class="text-xs text-gray-400 truncate max-w-xs">{{ server.notes || 'Managed Nimbus Instance' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-700">
                                        {{ server.ip_address }}
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-600">
                                        <a :href="server.panel_url" target="_blank" class="hover:underline text-emerald-600">
                                            {{ server.panel_url }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-slate-100 text-slate-700">
                                            {{ server.accounts_count || 0 }} clients
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            :class="server.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                                        >
                                            {{ server.is_active ? 'Active' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a 
                                                :href="route('admin.hosting.servers.sso', server.id)"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded border border-purple-200 transition-colors shadow-sm"
                                                title="1-Click Super Admin Login to this Nimbus Node"
                                            >
                                                <span class="material-symbols-rounded text-sm">admin_panel_settings</span>
                                                Super Admin SSO
                                            </a>
                                            <button 
                                                @click="openEditServerModal(server)"
                                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-slate-100 rounded transition-colors"
                                                title="Edit Server"
                                            >
                                                <span class="material-symbols-rounded text-base">edit</span>
                                            </button>
                                            <button 
                                                @click="deleteServer(server)"
                                                class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                                title="Delete Server"
                                            >
                                                <span class="material-symbols-rounded text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: INBOUND CLIENT REQUESTS -->
            <div v-if="activeTab === 'requests'" class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Requested By</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Target Domain</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan & Traffic</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Client Notes</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!requests.data || requests.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No managed hosting inquiries received yet.
                                    </td>
                                </tr>
                                <tr v-for="req in requests.data" :key="req.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ req.user?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ req.user?.email || 'N/A' }}</div>
                                        <div class="text-[10px] text-gray-400 mt-1">{{ new Date(req.created_at).toLocaleDateString() }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-800">
                                        {{ req.domain || 'Not specified' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-700">
                                        <div class="font-bold">{{ req.plan_requested }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">Traffic: {{ req.estimated_traffic || 'Standard' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate">
                                        {{ req.notes || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span 
                                            :class="{
                                                'bg-amber-50 text-amber-700 border-amber-200': req.status === 'pending',
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': req.status === 'approved' || req.status === 'fulfilled',
                                                'bg-rose-50 text-rose-700 border-rose-200': req.status === 'rejected'
                                            }"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                                        >
                                            {{ req.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                v-if="req.status === 'pending'"
                                                @click="updateRequestStatus(req, 'approved')"
                                                class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded border border-emerald-200"
                                            >
                                                Approve
                                            </button>
                                            <button 
                                                v-if="req.status === 'pending' || req.status === 'approved'"
                                                @click="openNewAccountModal(req.user_id, req.domain)"
                                                class="px-2.5 py-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded shadow-sm"
                                            >
                                                Provision Account
                                            </button>
                                            <button 
                                                v-if="req.status === 'pending'"
                                                @click="updateRequestStatus(req, 'rejected')"
                                                class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded border border-rose-200"
                                            >
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SERVER MODAL -->
        <div v-if="showServerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 border border-gray-200">
                <h3 class="text-base font-bold text-gray-900 mb-4">
                    {{ editingServer ? 'Edit Nimbus Node' : 'Add Nimbus Server Node' }}
                </h3>
                <form @submit.prevent="submitServerForm" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Node Identifier / Name</label>
                        <input type="text" v-model="serverForm.name" placeholder="e.g. Nimbus Cloud Node #1" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Server IP Address</label>
                            <input type="text" v-model="serverForm.ip_address" placeholder="66.116.204.19" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" required />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Status</label>
                            <select v-model="serverForm.is_active" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5">
                                <option :value="true">Active / Online</option>
                                <option :value="false">Maintenance / Offline</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Nimbus Panel URL</label>
                        <input type="url" v-model="serverForm.panel_url" placeholder="https://panel.example.com:8443 or https://66.116.204.19" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" required />
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">API & SSO Secret Key</label>
                        <input type="text" v-model="serverForm.api_secret" placeholder="Leave empty to auto-generate" class="w-full font-mono bg-slate-50 border border-gray-200 rounded-lg text-xs p-2.5" />
                        <p class="text-[11px] text-gray-400 mt-1">This key must match the <code class="font-mono">NIMBUS_SSO_SECRET</code> in the Nimbus server <code class="font-mono">.env</code>.</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Admin Notes</label>
                        <textarea v-model="serverForm.notes" rows="2" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" placeholder="Optional notes..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showServerModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                            {{ editingServer ? 'Update Node' : 'Register Node' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ACCOUNT MODAL -->
        <div v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 border border-gray-200">
                <h3 class="text-base font-bold text-gray-900 mb-4">
                    {{ editingAccount ? 'Edit Client Hosting Account' : 'Assign Client Hosting Account' }}
                </h3>
                <form @submit.prevent="submitAccountForm" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Select Client User</label>
                        <select v-model="accountForm.user_id" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" :disabled="!!editingAccount" required>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Target Nimbus Node</label>
                        <select v-model="accountForm.hosting_server_id" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" required>
                            <option v-for="server in servers" :key="server.id" :value="server.id">
                                {{ server.name }} ({{ server.ip_address }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Assigned Domain</label>
                        <input type="text" v-model="accountForm.domain" placeholder="example.com" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5 font-mono" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Plan / Tier Name</label>
                            <input type="text" v-model="accountForm.plan_name" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" required />
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Account Status</label>
                            <select v-model="accountForm.status" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <!-- Billing & Invoicing for new assignment -->
                    <div v-if="!editingAccount" class="bg-emerald-50/60 border border-emerald-200/80 rounded-lg p-3.5 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-base">receipt_long</span>
                            <span class="text-xs font-bold text-gray-900">Automated Client Invoice Generation</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Invoice Amount</label>
                                <input type="number" step="0.01" min="0" v-model="accountForm.amount" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2" placeholder="2999.00" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Currency</label>
                                <select v-model="accountForm.currency" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2">
                                    <option value="INR">INR (₹)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Payment Status</label>
                                <select v-model="accountForm.payment_status" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2">
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending Payment</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Payment Method</label>
                                <input type="text" v-model="accountForm.payment_method" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2" placeholder="Admin Assignment / Bank Transfer" />
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500">An itemized invoice will be automatically generated and made available to this user upon provisioning.</p>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Notes</label>
                        <textarea v-model="accountForm.notes" rows="2" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" placeholder="Optional notes..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showAccountModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                            {{ editingAccount ? 'Save Changes' : 'Assign Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
