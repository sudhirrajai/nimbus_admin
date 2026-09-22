<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    invoices: Object,
    filters: Object,
    stats: Object,
    users: Array,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

const handleFilter = () => {
    router.get(route('admin.invoices.index'), {
        search: search.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

const clearFilter = () => {
    search.value = '';
    statusFilter.value = '';
    handleFilter();
};

// Create Manual Invoice Modal
const showCreateModal = ref(false);
const invoiceForm = useForm({
    user_id: props.users?.[0]?.id || '',
    type: 'hosting_plan',
    plan_name: 'Managed Cloud VPS',
    description: 'Managed Cloud Hosting Service',
    amount: 2999.00,
    currency: 'INR',
    status: 'paid',
    payment_method: 'Admin Assignment',
    payment_id: '',
});

const openCreateModal = () => {
    invoiceForm.reset();
    invoiceForm.user_id = props.users?.[0]?.id || '';
    invoiceForm.type = 'hosting_plan';
    invoiceForm.plan_name = 'Managed Cloud VPS';
    invoiceForm.description = 'Managed Cloud Hosting Service';
    invoiceForm.amount = 2999.00;
    invoiceForm.currency = 'INR';
    invoiceForm.status = 'paid';
    invoiceForm.payment_method = 'Admin Assignment';
    showCreateModal.value = true;
};

const submitInvoiceForm = () => {
    invoiceForm.post(route('admin.invoices.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
        }
    });
};

// Edit / Transaction ID Modal
const showEditModal = ref(false);
const editingInvoice = ref(null);
const editForm = useForm({
    payment_id: '',
    payment_method: '',
    status: 'paid',
    amount: 0,
    paid_at: '',
    description: '',
});

const openEditModal = (inv) => {
    editingInvoice.value = inv;
    editForm.payment_id = inv.payment_id || '';
    editForm.payment_method = inv.payment_method || 'Bank Transfer';
    editForm.status = inv.status || 'paid';
    editForm.amount = Number(inv.amount) || 0;
    editForm.paid_at = inv.paid_at ? new Date(inv.paid_at).toISOString().split('T')[0] : '';
    editForm.description = inv.description || '';
    showEditModal.value = true;
};

const submitEditForm = () => {
    if (!editingInvoice.value) return;
    editForm.put(route('admin.invoices.update', editingInvoice.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editingInvoice.value = null;
        }
    });
};

// Send Branded Invoice Email
const sendingEmailInvoiceId = ref(null);
const sendInvoiceEmail = (inv) => {
    const clientName = inv.user?.name || inv.billing_details?.customer_name || 'the client';
    if (!confirm(`Send branded invoice email for #${inv.invoice_number} to ${clientName}?`)) {
        return;
    }
    sendingEmailInvoiceId.value = inv.id;
    router.post(route('admin.invoices.send-email', inv.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            sendingEmailInvoiceId.value = null;
        }
    });
};

const updateStatus = (invoice, newStatus) => {
    router.patch(route('admin.invoices.update-status', invoice.id), {
        status: newStatus
    }, { preserveScroll: true });
};

const deleteInvoice = (invoice) => {
    if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}? This cannot be undone.`)) {
        router.delete(route('admin.invoices.destroy', invoice.id), { preserveScroll: true });
    }
};

const formatCurrency = (amount, currency = 'INR') => {
    const num = parseFloat(amount) || 0;
    if (currency === 'INR') {
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currency;
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Manage Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Invoices & Revenue
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Audit billing receipts, create custom client invoices, and track revenue across plans.</p>
                </div>
                <div>
                    <button 
                        @click="openCreateModal"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">post_add</span>
                        Create Manual Invoice
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p class="text-xs font-medium">{{ $page.props.errors?.error }}</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-rounded text-2xl">payments</span>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Collected Revenue</div>
                        <div class="text-xl font-bold font-mono text-gray-900 mt-0.5">
                            {{ formatCurrency(stats?.total_revenue, 'INR') }}
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <span class="material-symbols-rounded text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Invoices</div>
                        <div class="text-xl font-bold font-mono text-gray-900 mt-0.5">{{ stats?.total_invoices ?? 0 }}</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-rounded text-2xl">verified</span>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Paid Invoices</div>
                        <div class="text-xl font-bold font-mono text-gray-900 mt-0.5">{{ stats?.paid_invoices ?? 0 }}</div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-rounded text-2xl">hourglass_top</span>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pending Invoices</div>
                        <div class="text-xl font-bold font-mono text-gray-900 mt-0.5">{{ stats?.pending_invoices ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center gap-4 justify-between">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-80">
                        <span class="material-symbols-rounded absolute left-3 top-2.5 text-gray-400 text-lg">search</span>
                        <input 
                            type="text" 
                            v-model="search" 
                            @keyup.enter="handleFilter"
                            placeholder="Search by invoice #, user, email..." 
                            class="w-full pl-9 text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>
                    <select 
                        v-model="statusFilter" 
                        @change="handleFilter"
                        class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button 
                        @click="handleFilter" 
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors"
                    >
                        Apply Filters
                    </button>
                    <button 
                        v-if="search || statusFilter"
                        @click="clearFilter" 
                        class="px-3 py-2 text-gray-500 hover:text-gray-700 text-xs font-semibold"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Invoices Table Container -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div v-if="invoices && invoices.data && invoices.data.length > 0" class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Client</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan / Item</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Type</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Transaction ID</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="font-mono text-xs font-bold text-gray-900">{{ inv.invoice_number }}</div>
                                    <div class="text-[11px] text-gray-500">{{ formatDate(inv.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-gray-900">{{ inv.user?.name || inv.billing_details?.customer_name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ inv.user?.email || inv.billing_details?.customer_email }}</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div class="text-xs font-bold text-gray-900">{{ inv.plan_name }}</div>
                                    <div class="text-[11px] text-gray-500 line-clamp-1">{{ inv.description }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-center">
                                    <span 
                                        :class="[
                                            inv.type === 'hosting_plan' 
                                                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                                                : (inv.type === 'license_plan' ? 'bg-purple-50 border-purple-200 text-purple-700' : 'bg-slate-100 border-slate-200 text-slate-700')
                                        ]"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    >
                                        {{ inv.type === 'hosting_plan' ? 'Hosting' : (inv.type === 'license_plan' ? 'License' : 'Custom') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-right font-mono text-xs font-bold text-gray-900">
                                    {{ formatCurrency(inv.amount, inv.currency) }}
                                </td>
                                <td class="px-6 py-4.5">
                                    <div v-if="inv.payment_id" class="flex items-center gap-1.5">
                                        <span class="font-mono text-xs font-semibold text-gray-800 bg-slate-100 px-2 py-0.5 rounded border border-gray-200">{{ inv.payment_id }}</span>
                                        <button 
                                            @click="openEditModal(inv)" 
                                            class="text-gray-400 hover:text-emerald-600 transition-colors p-0.5" 
                                            title="Edit Transaction ID"
                                        >
                                            <span class="material-symbols-rounded text-xs">edit</span>
                                        </button>
                                    </div>
                                    <button 
                                        v-else 
                                        @click="openEditModal(inv)"
                                        class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-dashed border-emerald-300 transition-colors"
                                        title="Add Transaction / Reference ID"
                                    >
                                        <span class="material-symbols-rounded text-xs">add</span>
                                        Add ID
                                    </button>
                                </td>
                                <td class="px-6 py-4.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button 
                                            @click="updateStatus(inv, inv.status === 'paid' ? 'pending' : 'paid')"
                                            :class="[
                                                inv.status === 'paid' 
                                                    ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' 
                                                    : (inv.status === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100' : 'bg-gray-50 border-gray-200 text-gray-500')
                                            ]"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-xs transition-colors"
                                            title="Click to toggle Paid/Pending"
                                        >
                                            <span class="material-symbols-rounded text-xs">
                                                {{ inv.status === 'paid' ? 'check_circle' : (inv.status === 'pending' ? 'schedule' : 'cancel') }}
                                            </span>
                                            {{ inv.status }}
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 text-right whitespace-nowrap space-x-1 sm:space-x-1.5">
                                    <button 
                                        @click="openEditModal(inv)"
                                        class="inline-flex items-center p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                                        title="Edit Invoice & Transaction Details"
                                    >
                                        <span class="material-symbols-rounded text-sm">edit_note</span>
                                    </button>
                                    <button 
                                        @click="sendInvoiceEmail(inv)"
                                        :disabled="sendingEmailInvoiceId === inv.id"
                                        class="inline-flex items-center p-1.5 text-gray-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors disabled:opacity-50"
                                        title="Send Branded Invoice Email to Client"
                                    >
                                        <span v-if="sendingEmailInvoiceId === inv.id" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                                        <span v-else class="material-symbols-rounded text-sm">forward_to_inbox</span>
                                    </button>
                                    <Link 
                                        :href="route('invoices.show', inv.uuid || inv.id)"
                                        class="inline-flex items-center p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                                        title="View / Print Invoice"
                                    >
                                        <span class="material-symbols-rounded text-sm">visibility</span>
                                    </Link>
                                    <button 
                                        @click="deleteInvoice(inv)"
                                        class="inline-flex items-center p-1.5 text-gray-400 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Delete Invoice"
                                    >
                                        <span class="material-symbols-rounded text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <div class="h-14 w-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                        <span class="material-symbols-rounded text-3xl">receipt_long</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Invoices Found</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        No invoices match your current filters. Clear the search or create a manual invoice.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="invoices && invoices.links && invoices.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-slate-50/50">
                    <div class="text-xs text-gray-500">
                        Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} invoices
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in invoices.links" :key="i">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    link.active 
                                        ? 'bg-emerald-600 text-white font-bold' 
                                        : 'bg-white text-gray-700 hover:bg-slate-100 border border-gray-200',
                                    'px-3 py-1 text-xs rounded-md transition-colors'
                                ]"
                                v-html="link.label"
                            />
                            <span 
                                v-else 
                                class="px-3 py-1 text-xs text-gray-400 opacity-50"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Manual Invoice Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-200 animate-scale-up">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">post_add</span>
                        <h3 class="font-bold text-gray-900 text-base">Issue Client Invoice</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitInvoiceForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Target Client User</label>
                        <select v-model="invoiceForm.user_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.email }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Category / Type</label>
                            <select v-model="invoiceForm.type" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="hosting_plan">Managed Hosting</option>
                                <option value="license_plan">License Plan</option>
                                <option value="custom">Custom Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Plan / Title</label>
                            <input type="text" v-model="invoiceForm.plan_name" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Description</label>
                        <textarea v-model="invoiceForm.description" rows="2" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Amount</label>
                            <input type="number" step="0.01" min="0" v-model="invoiceForm.amount" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Currency</label>
                            <select v-model="invoiceForm.currency" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="INR">INR (₹)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                            <select v-model="invoiceForm.status" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Method</label>
                            <input type="text" v-model="invoiceForm.payment_method" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment / Reference ID (Optional)</label>
                        <input type="text" v-model="invoiceForm.payment_id" placeholder="e.g. Bank Ref # or Razorpay ID" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="invoiceForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider shadow-sm flex items-center gap-2">
                            <span v-if="invoiceForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Issue Invoice</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Invoice & Transaction ID Modal -->
        <div v-if="showEditModal && editingInvoice" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-200 animate-scale-up">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">edit_note</span>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Edit Invoice Details</h3>
                            <p class="text-[11px] text-gray-500 font-mono">Invoice #{{ editingInvoice.invoice_number }}</p>
                        </div>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitEditForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            Transaction / Reference ID
                        </label>
                        <input 
                            type="text" 
                            v-model="editForm.payment_id" 
                            placeholder="e.g. Bank Ref #, UTR, or UPI Txn ID"
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono" 
                        />
                        <p class="text-[11px] text-gray-400 mt-1">This transaction ID will be printed on the official invoice receipt and included in client emails.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Status</label>
                            <select v-model="editForm.status" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Method</label>
                            <input 
                                type="text" 
                                v-model="editForm.payment_method" 
                                placeholder="Bank Transfer, UPI, Razorpay..."
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                                required 
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Amount</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                v-model="editForm.amount" 
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono" 
                                required 
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Payment Date</label>
                            <input 
                                type="date" 
                                v-model="editForm.paid_at" 
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Description / Notes</label>
                        <textarea 
                            v-model="editForm.description" 
                            rows="2" 
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="editForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider shadow-sm flex items-center gap-2">
                            <span v-if="editForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
