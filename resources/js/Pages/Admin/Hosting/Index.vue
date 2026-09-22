<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    servers: Array,
    accounts: Object,
    requests: Object,
    users: Array,
    managedPlans: Array,
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
    plan_name: 'Starter Cloud',
    status: 'active',
    billing_cycle: 'yearly',
    starts_at: '',
    amount: 3800,
    renewal_price: 4790,
    currency: 'INR',
    renews_at: '',
    auto_invoice: true,
    renewal_invoice_days: 14,
    payment_status: 'paid',
    payment_method: 'Admin Assignment',
    notes: '',
});

const recalculateRenewalDate = () => {
    if (!accountForm.starts_at) return;
    const start = new Date(accountForm.starts_at);
    if (isNaN(start.getTime())) return;

    const cycle = accountForm.billing_cycle || 'yearly';
    const renewal = new Date(start);
    if (cycle === 'monthly') {
        renewal.setMonth(renewal.getMonth() + 1);
    } else if (cycle === 'quarterly') {
        renewal.setMonth(renewal.getMonth() + 3);
    } else if (cycle === 'semi_annual') {
        renewal.setMonth(renewal.getMonth() + 6);
    } else if (cycle === 'biennial') {
        renewal.setFullYear(renewal.getFullYear() + 2);
    } else if (cycle === 'triennial') {
        renewal.setFullYear(renewal.getFullYear() + 3);
    } else {
        renewal.setFullYear(renewal.getFullYear() + 1);
    }
    accountForm.renews_at = renewal.toISOString().substring(0, 10);
};

const onPlanSelectChange = (event) => {
    const slug = event.target.value;
    if (!slug) return;
    const plan = (props.managedPlans || []).find(p => p.slug === slug);
    if (plan) {
        accountForm.plan_name = plan.name;
        accountForm.amount = plan.price_inr;
        accountForm.renewal_price = plan.renewal_price_inr || plan.price_inr;
        accountForm.billing_cycle = plan.billing_period === '/month' ? 'monthly' : 'yearly';
        recalculateRenewalDate();
    }
};

const openNewAccountModal = (prefillUserId = '', prefillDomain = '') => {
    editingAccount.value = null;
    accountForm.reset();
    accountForm.user_id = prefillUserId || (props.users[0]?.id || '');
    accountForm.hosting_server_id = props.servers[0]?.id || '';
    accountForm.domain = prefillDomain;

    const defaultPlan = props.managedPlans && props.managedPlans.length > 0 ? props.managedPlans[0] : null;
    accountForm.plan_name = defaultPlan ? defaultPlan.name : 'Starter Cloud';
    accountForm.status = 'active';
    accountForm.billing_cycle = defaultPlan?.billing_period === '/month' ? 'monthly' : 'yearly';
    accountForm.amount = defaultPlan ? defaultPlan.price_inr : 3800;
    accountForm.renewal_price = defaultPlan ? (defaultPlan.renewal_price_inr || defaultPlan.price_inr) : 4790;
    accountForm.currency = 'INR';

    const today = new Date();
    accountForm.starts_at = today.toISOString().substring(0, 10);
    recalculateRenewalDate();

    accountForm.auto_invoice = true;
    accountForm.renewal_invoice_days = 14;
    accountForm.payment_status = 'paid';
    accountForm.payment_method = 'Admin Assignment';
    accountForm.notes = '';
    showAccountModal.value = true;
};

const openEditAccountModal = (account) => {
    editingAccount.value = account;
    accountForm.user_id = account.user_id;
    accountForm.hosting_server_id = account.server_id || account.hosting_server_id;
    accountForm.domain = account.domain;
    accountForm.plan_name = account.plan_name;
    accountForm.status = account.status;
    accountForm.billing_cycle = account.billing_cycle || 'yearly';
    accountForm.amount = account.initial_price || 0;
    accountForm.renewal_price = account.renewal_price || 0;
    accountForm.starts_at = account.starts_at ? account.starts_at.substring(0, 10) : (account.created_at ? account.created_at.substring(0, 10) : '');
    accountForm.renews_at = account.renews_at ? account.renews_at.substring(0, 10) : '';
    accountForm.auto_invoice = account.auto_invoice !== undefined ? Boolean(account.auto_invoice) : true;
    accountForm.renewal_invoice_days = account.renewal_invoice_days || 14;
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

// Renewal Invoice Modal State
const showRenewalModal = ref(false);
const targetAccountForRenewal = ref(null);
const renewalInvoiceForm = useForm({
    amount: 0,
    payment_status: 'pending',
    advance_renewal_date: true,
    transaction_id: '',
});

const openRenewalModal = (account) => {
    targetAccountForRenewal.value = account;
    renewalInvoiceForm.amount = account.renewal_price || account.initial_price || 0;
    renewalInvoiceForm.payment_status = 'pending';
    renewalInvoiceForm.advance_renewal_date = true;
    renewalInvoiceForm.transaction_id = '';
    showRenewalModal.value = true;
};

const submitRenewalInvoice = () => {
    if (!targetAccountForRenewal.value) return;
    renewalInvoiceForm.post(route('admin.hosting.accounts.renewal-invoice', targetAccountForRenewal.value.id), {
        onSuccess: () => {
            showRenewalModal.value = false;
        }
    });
};

const triggerRenewalCheck = () => {
    router.post(route('admin.hosting.renewals.check'), {}, {
        preserveScroll: true,
    });
};

// Request management
const updateRequestStatus = (req, status) => {
    router.patch(route('admin.hosting.requests.update', req.id), { status }, {
        preserveScroll: true
    });
};

// Helper formatters
const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getDaysUntilRenewal = (dateStr) => {
    if (!dateStr) return null;
    const diffTime = new Date(dateStr) - new Date();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

const formatRenewalBadge = (dateStr) => {
    const days = getDaysUntilRenewal(dateStr);
    if (days === null) return { text: 'No Date Set', class: 'bg-slate-100 text-slate-500 border-slate-200' };
    if (days < 0) return { text: `Expired (${Math.abs(days)}d ago)`, class: 'bg-rose-50 text-rose-700 border-rose-200 font-bold' };
    if (days === 0) return { text: 'Due Today', class: 'bg-amber-50 text-amber-700 border-amber-200 font-bold' };
    if (days <= 14) return { text: `Due in ${days} days`, class: 'bg-amber-50 text-amber-700 border-amber-200 font-bold' };
    if (days <= 30) return { text: `Due in ${days} days`, class: 'bg-blue-50 text-blue-700 border-blue-200' };
    return { text: `In ${days} days`, class: 'bg-slate-50 text-slate-700 border-slate-200' };
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
                        Manage dedicated Nimbus server nodes, client hosting accounts, renewal billing cycles, and 1-Click SSO access.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <button 
                        v-if="activeTab === 'servers'"
                        @click="openNewServerModal"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 sm:px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">dns</span>
                        Add Nimbus Node
                    </button>
                    <button 
                        v-if="activeTab === 'accounts'"
                        @click="triggerRenewalCheck"
                        class="bg-white hover:bg-slate-50 text-gray-700 border border-gray-200 px-3 sm:px-3.5 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-2xs flex items-center gap-2"
                        title="Scan accounts and generate renewal invoices for upcoming renewals"
                    >
                        <span class="material-symbols-rounded text-sm text-emerald-600">autorenew</span>
                        Run Renewal Scan
                    </button>
                    <button 
                        v-if="activeTab === 'accounts'"
                        @click="openNewAccountModal()"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 sm:px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">person_add</span>
                        Assign Client Account
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-gray-200 overflow-x-auto pb-1 sm:pb-0">
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
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan & Cycle</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Term & Renewal Rate</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Next Renewal</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!accounts.data || accounts.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
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
                                        <div class="text-[10px] text-gray-400 mt-1">Node: {{ account.server?.name || 'Unassigned' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div class="font-semibold text-gray-800">{{ account.plan_name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ account.human_billing_cycle || '1 Year (Annual)' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono">
                                        <div class="font-bold text-gray-900">
                                            ₹{{ Number(account.initial_price || 0).toLocaleString('en-IN') }} <span class="text-[10px] text-gray-400 font-sans font-normal">(1st Term)</span>
                                        </div>
                                        <div class="text-[11px] text-emerald-700 font-medium mt-0.5">
                                            Renews: ₹{{ Number(account.renewal_price || account.initial_price || 0).toLocaleString('en-IN') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div class="text-[11px] text-gray-600">
                                            Start: <span class="font-medium text-gray-900">{{ formatDate(account.starts_at || account.created_at) }}</span>
                                        </div>
                                        <div v-if="account.renews_at" class="mt-0.5">
                                            <div class="text-[11px] text-gray-600">Due: <span class="font-medium text-gray-900">{{ formatDate(account.renews_at) }}</span></div>
                                            <div class="mt-1 flex items-center gap-1.5">
                                                <span 
                                                    :class="formatRenewalBadge(account.renews_at).class"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] border shadow-2xs"
                                                >
                                                    {{ formatRenewalBadge(account.renews_at).text }}
                                                </span>
                                                <span v-if="account.auto_invoice" class="text-[10px] text-emerald-600 font-bold" title="Auto-invoice enabled before renewal">
                                                    ⚡
                                                </span>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-400 italic text-[11px]">
                                            Not configured
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
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
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a 
                                                :href="route('admin.hosting.accounts.sso', account.id)"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded border border-emerald-200 transition-colors shadow-2xs"
                                                title="Log in directly to Nimbus as this client"
                                            >
                                                <span class="material-symbols-rounded text-sm">login</span>
                                                SSO
                                            </a>
                                            <button 
                                                @click="openRenewalModal(account)"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 text-xs font-semibold rounded border border-gray-200 transition-colors shadow-2xs"
                                                title="Generate Renewal Invoice for next period"
                                            >
                                                <span class="material-symbols-rounded text-sm text-emerald-600">receipt_long</span>
                                                Bill Renewal
                                            </button>
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

            <!-- TAB 2: SERVER NODES -->
            <div v-if="activeTab === 'servers'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="server in servers" 
                        :key="server.id"
                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:border-emerald-500/50 transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-9 w-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                        <span class="material-symbols-rounded">dns</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-sm leading-tight">{{ server.name }}</h3>
                                        <span class="font-mono text-xs text-gray-500">{{ server.ip_address }}</span>
                                    </div>
                                </div>
                                <span 
                                    :class="server.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
                                >
                                    {{ server.is_active ? 'Active' : 'Offline' }}
                                </span>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Nimbus URL</span>
                                    <a :href="server.panel_url" target="_blank" class="font-mono text-emerald-600 hover:underline truncate max-w-[180px]">{{ server.panel_url }}</a>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Assigned Clients</span>
                                    <span class="font-bold text-gray-900">{{ server.accounts_count || 0 }} Accounts</span>
                                </div>
                                <div v-if="server.notes" class="mt-2 p-2 bg-slate-50 rounded text-gray-500 text-[11px]">
                                    {{ server.notes }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <a 
                                :href="route('admin.hosting.servers.sso', server.id)"
                                target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700"
                            >
                                <span class="material-symbols-rounded text-sm">login</span>
                                Super Admin SSO
                            </a>
                            <div class="flex items-center gap-1">
                                <button @click="openEditServerModal(server)" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                    <span class="material-symbols-rounded text-base">edit</span>
                                </button>
                                <button @click="deleteServer(server)" class="p-1 text-rose-400 hover:text-rose-600 rounded">
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: INBOUND REQUESTS -->
            <div v-if="activeTab === 'requests'" class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Client Name & Email</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Requested Plan</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Target Domain</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Traffic & Notes</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="!requests.data || requests.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No managed hosting inquiries submitted yet.
                                    </td>
                                </tr>
                                <tr v-for="req in requests.data" :key="req.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ req.name }}</div>
                                        <div class="text-xs text-gray-500">{{ req.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-semibold text-gray-800">
                                        {{ req.plan_requested }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-emerald-700">
                                        {{ req.domain || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate">
                                        {{ req.requirements || 'None provided' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span 
                                            :class="{
                                                'bg-amber-50 text-amber-700 border-amber-200': req.status === 'pending',
                                                'bg-emerald-50 text-emerald-700 border-emerald-200': req.status === 'approved',
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

        <!-- ACCOUNT MODAL (ASSIGN / EDIT) -->
        <div v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 border border-gray-200 my-8">
                <h3 class="text-base font-bold text-gray-900 mb-4">
                    {{ editingAccount ? 'Edit Client Hosting Account & Renewal' : 'Assign Client Hosting Account' }}
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
                    <!-- Quick Plan Template Selection (Optional) -->
                    <div v-if="managedPlans && managedPlans.length > 0" class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl space-y-1">
                        <label class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                            <span class="material-symbols-rounded text-sm text-emerald-600">tune</span>
                            Load from Managed Plan Template (Optional)
                        </label>
                        <select @change="onPlanSelectChange" class="w-full bg-white border border-emerald-300 rounded-lg text-xs p-2 text-gray-800 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Choose pre-configured plan or customize below --</option>
                            <option v-for="plan in managedPlans" :key="plan.id" :value="plan.slug">
                                {{ plan.name }} — ₹{{ plan.price_inr.toLocaleString('en-IN') }}{{ plan.billing_period }} (Renews: ₹{{ (plan.renewal_price_inr || plan.price_inr).toLocaleString('en-IN') }})
                            </option>
                        </select>
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

                    <!-- Billing Cycle, Term Start Date & Renewal Pricing Configuration -->
                    <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-base">calendar_month</span>
                            <span class="text-xs font-bold text-gray-900">Term Dates & Renewal Pricing (Synchronized to Invoice)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Start Date (Term Start)</label>
                                <input 
                                    type="date" 
                                    v-model="accountForm.starts_at" 
                                    @change="recalculateRenewalDate"
                                    class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Billing Cycle</label>
                                <select 
                                    v-model="accountForm.billing_cycle" 
                                    @change="recalculateRenewalDate"
                                    class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2"
                                >
                                    <option value="yearly">1 Year (Annual)</option>
                                    <option value="monthly">1 Month</option>
                                    <option value="quarterly">3 Months (Quarterly)</option>
                                    <option value="semi_annual">6 Months</option>
                                    <option value="biennial">2 Years</option>
                                    <option value="triennial">3 Years</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Next Renewal Date</label>
                                <input type="date" v-model="accountForm.renews_at" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">
                                    {{ editingAccount ? 'Initial Price (1st Term)' : 'Initial Amount (₹)' }}
                                </label>
                                <input type="number" step="0.01" min="0" v-model="accountForm.amount" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2 font-mono" placeholder="3800.00" />
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">
                                    Next Renewal Price (₹)
                                </label>
                                <input type="number" step="0.01" min="0" v-model="accountForm.renewal_price" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2 font-mono" placeholder="4790.00" />
                            </div>
                        </div>

                        <!-- Auto Invoice Checkbox and Lead Days -->
                        <div class="pt-2 border-t border-gray-200/60 flex items-center justify-between gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="accountForm.auto_invoice" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                <span class="text-xs text-gray-800 font-medium">Auto-generate invoice before renewal</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" min="1" max="90" v-model="accountForm.renewal_invoice_days" class="w-14 bg-white border border-gray-300 rounded text-xs p-1 text-center font-mono" />
                                <span class="text-[11px] text-gray-500">days prior</span>
                            </div>
                        </div>
                    </div>

                    <!-- Initial Invoice Generation settings for new assignment -->
                    <div v-if="!editingAccount" class="bg-emerald-50/60 border border-emerald-200/80 rounded-lg p-3.5 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-base">receipt_long</span>
                            <span class="text-xs font-bold text-gray-900">Initial Invoice Details</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Payment Status</label>
                                <select v-model="accountForm.payment_status" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2">
                                    <option value="paid">Paid Immediately</option>
                                    <option value="pending">Pending Payment</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Payment Method</label>
                                <input type="text" v-model="accountForm.payment_method" class="w-full bg-white border border-gray-300 rounded-lg text-xs p-2" placeholder="Admin Assignment / Bank Transfer" />
                            </div>
                        </div>
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

        <!-- RENEWAL INVOICE MODAL -->
        <div v-if="showRenewalModal && targetAccountForRenewal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200 animate-scale-up">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">receipt_long</span>
                        <h3 class="text-base font-bold text-gray-900">Generate Renewal Invoice</h3>
                    </div>
                    <button @click="showRenewalModal = false" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <div class="mb-4 bg-slate-50 p-3.5 rounded-lg border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Domain:</span>
                        <span class="font-mono font-bold text-gray-900">{{ targetAccountForRenewal.domain }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Client User:</span>
                        <span class="font-semibold text-gray-800">{{ targetAccountForRenewal.user?.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Renewal Period:</span>
                        <span class="text-gray-800">{{ targetAccountForRenewal.human_billing_cycle || '1 Year' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Current Expiry:</span>
                        <span class="font-semibold text-emerald-700">{{ formatDate(targetAccountForRenewal.renews_at) }}</span>
                    </div>
                </div>

                <form @submit.prevent="submitRenewalInvoice" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Renewal Amount (INR)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            v-model="renewalInvoiceForm.amount" 
                            class="w-full bg-white border border-gray-300 rounded-lg text-sm p-2.5 font-mono" 
                            required 
                        />
                        <p class="text-[11px] text-gray-400 mt-1">Pre-filled with configured renewal rate (₹{{ Number(targetAccountForRenewal.renewal_price || targetAccountForRenewal.initial_price || 0).toFixed(2) }}).</p>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Payment Status</label>
                        <select v-model="renewalInvoiceForm.payment_status" class="w-full bg-white border border-gray-300 rounded-lg text-sm p-2.5">
                            <option value="pending">Pending Payment (Issue to Client)</option>
                            <option value="paid">Paid (Mark Received Immediately)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-600 uppercase tracking-wider block mb-1">Transaction / Reference ID (Optional)</label>
                        <input 
                            type="text" 
                            v-model="renewalInvoiceForm.transaction_id" 
                            placeholder="e.g. Bank Ref #, UTR, or UPI Txn ID"
                            class="w-full bg-white border border-gray-300 rounded-lg text-sm p-2.5 font-mono" 
                        />
                    </div>

                    <div v-if="renewalInvoiceForm.payment_status === 'paid'">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-700">
                            <input type="checkbox" v-model="renewalInvoiceForm.advance_renewal_date" class="rounded border-gray-300 text-emerald-600" />
                            <span>Advance account renewal date to the next period</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showRenewalModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="renewalInvoiceForm.processing" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm flex items-center gap-2">
                            <span v-if="renewalInvoiceForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Generate Invoice</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
