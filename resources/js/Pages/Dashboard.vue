<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    licenses: {
        type: Array,
        default: () => [],
    },
    hostingAccounts: {
        type: Array,
        default: () => [],
    },
    invoices: {
        type: Array,
        default: () => [],
    },
    hostingRequests: {
        type: Array,
        default: () => [],
    },
});

const activeLicenses = computed(() => props.licenses.filter(l => l.status === 'active'));
const activeHosting = computed(() => props.hostingAccounts.filter(h => h.status === 'active'));
const pendingInvoices = computed(() => props.invoices.filter(i => i.status === 'pending'));

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        My Workspace
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Overview of your active Nimbus licenses, cloud nodes, and billing.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">shopping_cart</span>
                        <span>Order Packages</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message Alerts -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p>{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p>{{ $page.props.errors.error }}</p>
                </div>
            </div>

            <!-- Stats Overview (Matching Original Nimbus Cards) -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Managed Cloud</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">cloud_done</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">{{ activeHosting.length }} Nodes</div>
                    <div class="text-[11px] text-gray-500 mt-1">Fully managed by VMCORE</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Self-Host Licenses</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">terminal</span>
                    </div>
                    <div class="text-2xl font-bold text-emerald-600 mt-2">{{ activeLicenses.length }} Active</div>
                    <div class="text-[11px] text-gray-500 mt-1">Self-hosted control panels</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open Invoices</span>
                        <span class="material-symbols-rounded text-gray-400 text-xl">receipt_long</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">{{ pendingInvoices.length }} Due</div>
                    <div class="text-[11px] text-gray-500 mt-1">Pending payments</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Uptime SLA</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">verified</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">99.9%</div>
                    <div class="text-[11px] text-gray-500 mt-1">High availability nodes</div>
                </div>
            </div>

            <!-- Quick Navigation Hub -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link 
                    :href="route('self-host.index')" 
                    class="bg-white border border-gray-200 hover:border-emerald-500 rounded-lg p-5 shadow-sm hover:shadow transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">terminal</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Nimbus Self-Host</div>
                        <div class="text-[11px] text-gray-500">Keys &amp; curl install command</div>
                    </div>
                </Link>

                <Link 
                    :href="route('hosting.client.index')" 
                    class="bg-white border border-gray-200 hover:border-emerald-500 rounded-lg p-5 shadow-sm hover:shadow transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">cloud_done</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Managed Hosting</div>
                        <div class="text-[11px] text-gray-500">Nodes &amp; 1-click SSO launch</div>
                    </div>
                </Link>

                <Link 
                    :href="route('store.index')" 
                    class="bg-white border border-gray-200 hover:border-emerald-500 rounded-lg p-5 shadow-sm hover:shadow transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">storefront</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Store &amp; Packages</div>
                        <div class="text-[11px] text-gray-500">Order or upgrade services</div>
                    </div>
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    class="bg-white border border-gray-200 hover:border-emerald-500 rounded-lg p-5 shadow-sm hover:shadow transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-slate-50 text-gray-600 border border-gray-200 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">receipt_long</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Invoices &amp; Receipts</div>
                        <div class="text-[11px] text-gray-500">Download billing proofs</div>
                    </div>
                </Link>
            </div>

            <!-- Active Managed Cloud Instances -->
            <div v-if="hostingAccounts.length > 0" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">cloud_done</span>
                        <h3 class="text-sm font-bold text-gray-900">Active Managed Cloud Instances</h3>
                    </div>
                    <Link :href="route('hosting.client.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        View All
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="account in hostingAccounts.slice(0, 4)" 
                        :key="account.id"
                        class="border border-gray-200 rounded-lg p-4 bg-slate-50/50 hover:bg-white hover:border-emerald-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4"
                    >
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">{{ account.plan_name }}</span>
                            <div class="text-sm font-bold text-gray-900 font-mono truncate">{{ account.domain }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Node: {{ account.server?.ip_address || 'Dedicated VPS' }}</div>
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm shrink-0 self-start sm:self-auto"
                        >
                            <span class="material-symbols-rounded text-sm">login</span>
                            1-Click Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Self-Hosted Licenses -->
            <div v-if="licenses.length > 0" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">terminal</span>
                        <h3 class="text-sm font-bold text-gray-900">Self-Hosted Nimbus Licenses</h3>
                    </div>
                    <Link :href="route('self-host.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        View All
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="license in licenses.slice(0, 4)" 
                        :key="license.id"
                        class="border border-gray-200 rounded-lg p-4 bg-slate-50/50 hover:bg-white hover:border-emerald-300 transition-all space-y-2"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">{{ license.plan }} License</span>
                            <span 
                                :class="license.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200'"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            >
                                {{ license.status }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-bold text-gray-900 truncate bg-white p-2 border border-gray-200 rounded">{{ license.license_key }}</div>
                        <div class="text-[11px] text-gray-500">Connected IP: {{ license.server_ip || 'Awaiting installation' }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices Table -->
            <div v-if="invoices.length > 0" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">receipt_long</span>
                        <h3 class="text-sm font-bold text-gray-900">Recent Invoices</h3>
                    </div>
                    <Link :href="route('invoices.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        View All Invoices
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="divide-y divide-gray-100">
                    <div 
                        v-for="inv in invoices" 
                        :key="inv.id" 
                        class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
                    >
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-gray-900">#{{ inv.invoice_number }}</span>
                            <span class="text-gray-500">{{ formatDate(inv.created_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <span class="font-mono font-bold text-gray-900">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                            <span 
                                :class="inv.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            >
                                {{ inv.status }}
                            </span>
                            <Link 
                                :href="route('invoices.show', inv.uuid || inv.id)" 
                                class="p-1 text-gray-400 hover:text-emerald-600 rounded transition-colors"
                                title="View Invoice"
                            >
                                <span class="material-symbols-rounded text-base">visibility</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for new users -->
            <div v-if="licenses.length === 0 && hostingAccounts.length === 0" class="bg-white border border-dashed border-gray-300 rounded-lg p-12 text-center shadow-sm">
                <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">dashboard_customize</span>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Welcome to Your Nimbus Workspace</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    You have not deployed any servers yet. Choose between fully managed cloud hosting or self-hosting on your own VPS.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold uppercase tracking-wide transition-all shadow-sm"
                    >
                        Browse Store Packages
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
