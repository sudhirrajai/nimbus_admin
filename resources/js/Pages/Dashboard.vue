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

const getInstallCommand = (key) => {
    return `curl -sSL ${window.location.origin}/install.sh | sudo bash -s -- --license=${key}`;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900">
                        Welcome back, {{ $page.props.auth.user.name }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Here is a quick snapshot of your active servers, cloud hosting, and billing.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-base">shopping_cart</span>
                        <span>Order Packages</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message Alerts -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p>{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p>{{ $page.props.errors.error }}</p>
                </div>
            </div>

            <!-- 4 Quick Key Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Managed Cloud</span>
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">cloud_done</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-gray-950 mt-2">{{ activeHosting.length }} Nodes</div>
                    <div class="text-xs text-gray-500 mt-1">Fully managed by VMCORE</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Self-Host Licenses</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">terminal</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-emerald-600 mt-2">{{ activeLicenses.length }} Active</div>
                    <div class="text-xs text-gray-500 mt-1">Self-hosted control panels</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Open Invoices</span>
                        <div class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">receipt_long</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-amber-600 mt-2">{{ pendingInvoices.length }} Due</div>
                    <div class="text-xs text-gray-500 mt-1">Pending payments</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Infrastructure SLA</span>
                        <div class="h-8 w-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">verified</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-purple-600 mt-2">99.9%</div>
                    <div class="text-xs text-gray-500 mt-1">High availability uptime</div>
                </div>
            </div>

            <!-- Quick Action Hub (Navigation Shortcuts) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link 
                    :href="route('hosting.client.index')" 
                    class="bg-white border border-gray-200 hover:border-blue-400 rounded-2xl p-4 shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group"
                >
                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">cloud</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Managed Hosting</div>
                        <div class="text-[11px] text-gray-500">Access instances &amp; 1-click SSO</div>
                    </div>
                </Link>

                <Link 
                    :href="route('self-host.index')" 
                    class="bg-white border border-gray-200 hover:border-emerald-400 rounded-2xl p-4 shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group"
                >
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">terminal</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Nimbus Self-Host</div>
                        <div class="text-[11px] text-gray-500">View keys &amp; install commands</div>
                    </div>
                </Link>

                <Link 
                    :href="route('store.index')" 
                    class="bg-white border border-gray-200 hover:border-purple-400 rounded-2xl p-4 shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group"
                >
                    <div class="h-10 w-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">storefront</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Store &amp; Packages</div>
                        <div class="text-[11px] text-gray-500">Order or upgrade cloud nodes</div>
                    </div>
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    class="bg-white border border-gray-200 hover:border-gray-400 rounded-2xl p-4 shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group"
                >
                    <div class="h-10 w-10 rounded-xl bg-slate-100 text-gray-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <span class="material-symbols-rounded text-xl">receipt_long</span>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-900 group-hover:text-gray-950 transition-colors">Invoices &amp; Receipts</div>
                        <div class="text-[11px] text-gray-500">View official payment bills</div>
                    </div>
                </Link>
            </div>

            <!-- Active Managed Cloud Instances (Quick 1-Click Access) -->
            <div v-if="hostingAccounts.length > 0" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-blue-600 text-lg">cloud</span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-600">Active Managed Cloud Instances</h3>
                    </div>
                    <Link :href="route('hosting.client.index')" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
                        View All
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="account in hostingAccounts.slice(0, 4)" 
                        :key="account.id"
                        class="border border-gray-100 rounded-xl p-4 bg-slate-50/50 hover:bg-white hover:border-blue-200 transition-all flex items-center justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ account.plan_name }}</span>
                            <div class="text-sm font-bold text-gray-950 font-mono truncate">{{ account.domain }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Node: {{ account.server?.ip_address || 'Cloud Node' }}</div>
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-xs shrink-0"
                        >
                            <span class="material-symbols-rounded text-sm">login</span>
                            1-Click Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Self-Hosted Licenses (Quick Access) -->
            <div v-if="licenses.length > 0" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">terminal</span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-600">Self-Hosted Nimbus Licenses</h3>
                    </div>
                    <Link :href="route('self-host.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                        View All
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="license in licenses.slice(0, 4)" 
                        :key="license.id"
                        class="border border-gray-100 rounded-xl p-4 bg-slate-50/50 hover:bg-white hover:border-emerald-200 transition-all space-y-2"
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
                        <div class="text-xs font-mono font-bold text-gray-900 truncate">{{ license.license_key }}</div>
                        <div class="text-[11px] text-gray-500">IP: {{ license.server_ip || 'Awaiting connection' }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices Table -->
            <div v-if="invoices.length > 0" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-gray-500 text-lg">receipt_long</span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-600">Recent Invoices</h3>
                    </div>
                    <Link :href="route('invoices.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                        View All Invoices
                        <span class="material-symbols-rounded text-xs">arrow_forward</span>
                    </Link>
                </div>

                <div class="divide-y divide-gray-100">
                    <div 
                        v-for="inv in invoices" 
                        :key="inv.id" 
                        class="py-3 flex items-center justify-between text-xs"
                    >
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-gray-900">#{{ inv.invoice_number }}</span>
                            <span class="text-gray-500">{{ formatDate(inv.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-gray-950">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                            <span 
                                :class="inv.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            >
                                {{ inv.status }}
                            </span>
                            <Link 
                                :href="route('invoices.show', inv.uuid || inv.id)" 
                                class="p-1 text-gray-400 hover:text-gray-900 rounded"
                                title="View Invoice"
                            >
                                <span class="material-symbols-rounded text-base">visibility</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for new users -->
            <div v-if="licenses.length === 0 && hostingAccounts.length === 0" class="bg-white border border-dashed border-gray-300 rounded-2xl p-14 text-center">
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">dashboard_customize</span>
                </div>
                <h3 class="text-sm font-bold text-gray-950">Welcome to Your Nimbus Workspace</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    You have not deployed any servers yet. Choose between fully managed cloud hosting or self-hosting on your own VPS.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm"
                    >
                        Browse Store Packages
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
