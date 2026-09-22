<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    licenses: Array,
    hostingAccounts: Array,
    invoices: Array,
});

const activeTab = ref('nimbus_panel'); // 'nimbus_panel' or 'managed_hosting'

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
    alert('License key copied to clipboard!');
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return 'Lifetime';
    return new Date(dateStr).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getFallbackFeatures = (planSlug) => {
    if (planSlug === 'free') {
        return ['1 Server Node', '3 Domains Limit', 'SSL Automation', 'File Manager & Ace Editor', 'Web Terminal', 'Basic Monitoring'];
    } else if (planSlug === 'pro') {
        return ['5 Server Nodes', '50 Domains Limit', 'Git Auto-Deploy', 'Priority Support', 'Team Access', 'WordPress Manager', 'Cron & Supervisor'];
    } else if (planSlug === 'enterprise') {
        return ['Unlimited Servers', '9999 Domains Limit', 'White Label Support', 'SLA Guarantee', 'Dedicated Manager', 'API Access', 'Custom Integrations'];
    }
    return ['Basic server management features'];
};
</script>

<template>
    <Head title="My Subscriptions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        My Subscriptions
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Active plans, renewal rates, and license terms across your Nimbus infrastructure.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">shopping_cart</span>
                        Browse Store Packages
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Navigation Tabs (Original Standard Style) -->
            <div class="flex items-center gap-2 border-b border-gray-200 overflow-x-auto whitespace-nowrap pb-1 sm:pb-0">
                <button 
                    type="button"
                    @click="activeTab = 'nimbus_panel'"
                    :class="[
                        activeTab === 'nimbus_panel' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors cursor-pointer shrink-0"
                >
                    <span class="material-symbols-rounded text-base">terminal</span>
                    Nimbus Panel (Self-Host)
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ licenses?.length || 0 }}</span>
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'managed_hosting'"
                    :class="[
                        activeTab === 'managed_hosting' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors cursor-pointer shrink-0"
                >
                    <span class="material-symbols-rounded text-base">cloud_done</span>
                    Managed Cloud Hosting
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ hostingAccounts?.length || 0 }}</span>
                </button>
            </div>

            <!-- TAB 1: NIMBUS SELF-HOST LICENSES -->
            <div v-if="activeTab === 'nimbus_panel'" class="space-y-6 animate-fade-in">
                <div v-if="licenses && licenses.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div 
                        v-for="license in licenses" 
                        :key="license.id" 
                        class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col justify-between"
                    >
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                        <span class="material-symbols-rounded text-lg">card_membership</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Self-Host Plan</span>
                                        <h3 class="text-base font-bold text-gray-900 mt-0.5">{{ license.planDetails?.name || license.plan.toUpperCase() }} License</h3>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 border border-emerald-200 text-emerald-700">
                                    {{ license.status }}
                                </span>
                            </div>

                            <!-- License Key Box -->
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">License Key</label>
                                <div class="bg-slate-50 border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-3">
                                    <code class="text-xs text-gray-800 font-mono block break-all select-all flex-1">
                                        {{ license.license_key }}
                                    </code>
                                    <button 
                                        @click="copyToClipboard(license.license_key)" 
                                        class="p-1.5 rounded bg-white hover:bg-slate-100 text-gray-600 border border-gray-200 transition-colors cursor-pointer"
                                        title="Copy Key"
                                    >
                                        <span class="material-symbols-rounded text-sm">content_copy</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Meta Grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Connected IP</div>
                                    <div class="text-xs font-mono font-bold text-gray-900 truncate">{{ license.server_ip || 'Awaiting installation' }}</div>
                                </div>
                                <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Expiry Date</div>
                                    <div class="text-xs font-semibold text-gray-900 truncate">{{ formatDateTime(license.expires_at) }}</div>
                                </div>
                            </div>

                            <!-- Features List -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-3">Plan Benefits &amp; Features</label>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
                                    <li v-for="feat in (license.planDetails?.features || getFallbackFeatures(license.plan))" :key="feat" class="flex items-center gap-2">
                                        <span class="material-symbols-rounded text-emerald-500 text-base shrink-0">check_circle</span>
                                        <span class="truncate">{{ feat }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-slate-50/50 px-6 py-4 border-t border-gray-200 flex items-center justify-between text-xs">
                            <Link :href="route('self-host.index')" class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1.5">
                                <span class="material-symbols-rounded text-sm">terminal</span>
                                Manage Deployment
                            </Link>
                            <Link :href="route('store.index') + '?tab=self_hosted'" class="text-gray-500 hover:text-gray-800 font-medium">
                                Upgrade Plan
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-dashed border-gray-300 rounded-lg p-12 text-center shadow-sm">
                    <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">terminal</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">No Self-Hosted Licenses</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        You do not currently have any active self-hosted Nimbus licenses.
                    </p>
                    <Link :href="route('store.index') + '?tab=self_hosted'" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold uppercase tracking-wide transition-all shadow-sm">
                        Browse Self-Host Licenses
                    </Link>
                </div>
            </div>

            <!-- TAB 2: MANAGED CLOUD HOSTING ACCOUNTS -->
            <div v-if="activeTab === 'managed_hosting'" class="space-y-6 animate-fade-in">
                <div v-if="hostingAccounts && hostingAccounts.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div 
                        v-for="account in hostingAccounts" 
                        :key="account.id" 
                        class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col justify-between"
                    >
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                        <span class="material-symbols-rounded text-lg">cloud_done</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Managed Hosting Account</span>
                                        <h3 class="text-base font-bold text-gray-900 font-mono mt-0.5">{{ account.domain }}</h3>
                                    </div>
                                </div>
                                <span 
                                    :class="account.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                                >
                                    {{ account.status }}
                                </span>
                            </div>

                            <!-- Pricing & Renewal Term Box -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Plan &amp; Cycle</div>
                                    <div class="text-sm font-bold text-gray-900">{{ account.plan_name }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">{{ account.human_billing_cycle || '1 Year' }}</div>
                                </div>
                                <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Renewal Rate</div>
                                    <div class="text-sm font-bold font-mono text-emerald-600">
                                        ₹{{ Number(account.renewal_price || account.initial_price || 0).toLocaleString('en-IN') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Renews {{ account.renews_at ? formatDate(account.renews_at) : 'N/A' }}</div>
                                </div>
                            </div>

                            <!-- Node & Server Details -->
                            <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
                                <span>Assigned Node: <strong class="text-gray-800">{{ account.server?.name || account.server?.ip_address || 'Dedicated VPS' }}</strong></span>
                                <span v-if="account.auto_invoice" class="text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">bolt</span>
                                    Auto-Invoicing Active
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-slate-50/50 px-6 py-4 border-t border-gray-200 flex items-center justify-between text-xs">
                            <a 
                                :href="route('hosting.accounts.client-sso', account.id)"
                                target="_blank"
                                class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1.5"
                            >
                                <span class="material-symbols-rounded text-sm">login</span>
                                1-Click Login to Panel
                            </a>
                            <Link 
                                :href="route('invoices.index')"
                                class="text-gray-500 hover:text-gray-700 flex items-center gap-1"
                            >
                                <span class="material-symbols-rounded text-sm">receipt</span>
                                Invoices
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-dashed border-gray-300 rounded-lg p-12 text-center shadow-sm">
                    <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">cloud_done</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">No Managed Hosting Accounts</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        You do not currently have any active managed cloud hosting accounts.
                    </p>
                    <Link :href="route('store.index') + '?tab=managed_hosting'" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold uppercase tracking-wide transition-all shadow-sm">
                        Explore Managed Cloud Packages
                    </Link>
                </div>
            </div>

            <!-- Recent Invoices Snippet -->
            <div v-if="invoices && invoices.length > 0" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">receipt_long</span>
                        <h3 class="text-sm font-bold text-gray-900">Recent Invoices</h3>
                    </div>
                    <Link :href="route('invoices.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1">
                        View All
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
                        <div class="flex items-center gap-4">
                            <span class="font-mono font-bold text-gray-900">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                            <span 
                                :class="inv.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            >
                                {{ inv.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
