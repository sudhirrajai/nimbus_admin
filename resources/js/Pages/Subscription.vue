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
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">card_membership</span>
                        My Subscriptions &amp; Services
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Review active plans, renewal rates, and license terms across your Nimbus infrastructure.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-base">shopping_cart</span>
                        Browse Store Packages
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Tab Selector: Nimbus Self-Host vs Managed Cloud Hosting -->
            <div class="flex justify-center sm:justify-start">
                <div class="inline-flex p-1.5 bg-slate-100 rounded-2xl border border-gray-200 shadow-2xs gap-1.5">
                    <button 
                        type="button"
                        @click="activeTab = 'nimbus_panel'"
                        :class="activeTab === 'nimbus_panel' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-base text-emerald-600">terminal</span>
                        Nimbus Self-Host Licenses ({{ licenses?.length || 0 }})
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'managed_hosting'"
                        :class="activeTab === 'managed_hosting' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-base text-blue-600">cloud</span>
                        Managed Cloud Hosting ({{ hostingAccounts?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- TAB 1: NIMBUS SELF-HOST LICENSES -->
            <div v-if="activeTab === 'nimbus_panel'" class="space-y-6 animate-fade-in">
                <div v-if="licenses && licenses.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div 
                        v-for="license in licenses" 
                        :key="license.id"
                        class="bg-white border border-gray-200 rounded-2xl shadow-2xs overflow-hidden flex flex-col justify-between"
                    >
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                        <span class="material-symbols-rounded">card_membership</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Self-Host Plan</span>
                                        <h3 class="text-base font-bold text-gray-950 mt-0.5">{{ license.planDetails?.name || license.plan.toUpperCase() }} License</h3>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 border border-emerald-200 text-emerald-700">
                                    {{ license.status }}
                                </span>
                            </div>

                            <!-- License Key Box -->
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">License Key</label>
                                <div class="bg-slate-50 border border-gray-200 rounded-xl p-3 flex items-center justify-between gap-3">
                                    <code class="text-xs text-gray-800 font-mono block break-all select-all flex-1">
                                        {{ license.license_key }}
                                    </code>
                                    <button 
                                        @click="copyToClipboard(license.license_key)" 
                                        class="p-1.5 rounded-lg bg-white hover:bg-slate-100 text-gray-600 border border-gray-200 transition-colors"
                                        title="Copy Key"
                                    >
                                        <span class="material-symbols-rounded text-sm">content_copy</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Meta Grid -->
                            <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-gray-200 rounded-xl p-4">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Connected IP</div>
                                    <div class="text-xs font-mono font-bold text-gray-900 mt-0.5 truncate">{{ license.server_ip || 'Pending install...' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Expires</div>
                                    <div class="text-xs font-semibold text-gray-900 mt-0.5 truncate">{{ formatDateTime(license.expires_at) }}</div>
                                </div>
                            </div>

                            <!-- Features List -->
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Included Capabilities</div>
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-700">
                                    <div v-for="feat in (license.planDetails?.features || getFallbackFeatures(license.plan))" :key="feat" class="flex items-center gap-1.5">
                                        <span class="text-emerald-600 font-bold text-xs">✓</span>
                                        <span class="truncate">{{ feat }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-slate-50 px-6 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                            <Link :href="route('self-host.index')" class="text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1.5">
                                <span class="material-symbols-rounded text-sm">terminal</span>
                                Manage Deployment
                            </Link>
                            <Link :href="route('store.index') + '?tab=self_hosted'" class="text-gray-500 hover:text-gray-800 font-medium">
                                Upgrade Plan
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center">
                    <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">terminal</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-950">No Self-Hosted Licenses</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        You do not currently have any active self-hosted Nimbus licenses.
                    </p>
                    <Link :href="route('store.index') + '?tab=self_hosted'" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm">
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
                        class="bg-white border-2 border-blue-100 rounded-2xl shadow-2xs overflow-hidden flex flex-col justify-between"
                    >
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                                        <span class="material-symbols-rounded">cloud_sync</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Managed Hosting Account</span>
                                        <h3 class="text-base font-bold text-gray-950">{{ account.domain }}</h3>
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
                            <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-gray-200 rounded-xl p-4">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Plan & Term</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ account.plan_name }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">{{ account.human_billing_cycle || '1 Year' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Next Renewal Rate</div>
                                    <div class="text-sm font-bold font-mono text-emerald-700 mt-0.5">
                                        ₹{{ Number(account.renewal_price || account.initial_price || 0).toLocaleString('en-IN') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Renews {{ account.renews_at ? formatDate(account.renews_at) : 'N/A' }}</div>
                                </div>
                            </div>

                            <!-- Node & Server Details -->
                            <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
                                <span>Assigned Node: <strong class="text-gray-700">{{ account.server?.name || 'Managed Server' }}</strong></span>
                                <span v-if="account.auto_invoice" class="text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="material-symbols-rounded text-sm">bolt</span>
                                    Auto-Invoicing Active
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-slate-50 px-6 py-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                            <a 
                                :href="route('hosting.accounts.client-sso', account.id)"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1.5"
                            >
                                <span class="material-symbols-rounded text-sm">login</span>
                                1-Click SSO to Nimbus Panel
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

                <div v-else class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center">
                    <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">cloud_sync</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-950">No Managed Hosting Accounts</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        You do not currently have any active managed cloud hosting accounts.
                    </p>
                    <Link :href="route('store.index') + '?tab=managed_hosting'" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm">
                        Explore Managed Cloud Packages
                    </Link>
                </div>
            </div>

            <!-- Recent Invoices Snippet -->
            <div v-if="invoices && invoices.length > 0" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Recent Invoices</h3>
                    <Link :href="route('invoices.index')" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
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
                            <span class="font-mono font-bold text-gray-950">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
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
