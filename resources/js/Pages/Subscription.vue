<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    licenses: Array
});

const getFallbackFeatures = (planSlug) => {
    if (planSlug === 'free') {
        return ['1 Server', '3 Domains Limit', 'SSL Automation', 'File Manager', 'Community Support', 'Basic Monitoring'];
    } else if (planSlug === 'pro') {
        return ['5 Servers Support', '50 Domains Limit', 'Git Auto-Deploy', 'Priority Support', 'Team Access', 'Advanced Security', 'WordPress Manager'];
    } else if (planSlug === 'enterprise') {
        return ['Unlimited Servers', '9999 Domains Limit', 'White Label Support', 'SLA Guarantee', 'Dedicated Manager', 'API Access', 'Custom Integrations'];
    }
    return ['Basic server management features'];
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('License key copied to clipboard!');
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
    <Head title="My Subscription" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    My Subscription & Plan
                </h2>
                <p class="text-xs text-gray-500 mt-1">Review active keys, dynamic capabilities, and premium plan benefits.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Active Subscriptions List -->
            <div v-if="licenses && licenses.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div 
                    v-for="license in licenses" 
                    :key="license.id"
                    class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col justify-between"
                >
                    <!-- Card Header -->
                    <div class="p-6 space-y-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                    <span class="material-symbols-rounded">card_membership</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Active Plan</span>
                                    <h3 class="text-base font-bold text-gray-950 mt-0.5">{{ license.planDetails?.name || license.plan.toUpperCase() }} Plan</h3>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-50 border border-emerald-200 text-emerald-700">
                                {{ license.status }}
                            </span>
                        </div>

                        <!-- License Key Widget -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">License Key</label>
                            <div class="bg-slate-50 border border-gray-200 rounded-lg p-3.5 flex items-center justify-between gap-3">
                                <code class="text-xs text-gray-800 font-mono block break-all select-all leading-normal flex-1">
                                    {{ license.license_key }}
                                </code>
                                <button 
                                    @click="copyToClipboard(license.license_key)" 
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-white hover:bg-slate-50 border border-gray-200 text-gray-650 transition-colors shadow-sm"
                                    title="Copy License Key"
                                >
                                    <span class="material-symbols-rounded text-sm">content_copy</span>
                                </button>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Max Domain Limit</div>
                                <div class="text-sm text-gray-900 font-bold font-mono">
                                    {{ license.planDetails?.max_domains ?? (license.plan === 'free' ? 3 : (license.plan === 'pro' ? 50 : 9999)) }}
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">Linked Domain: {{ license.domain || 'None yet' }}</div>
                            </div>
                            <div class="bg-slate-50 p-4 border border-gray-200 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Expiry Date</div>
                                <div class="text-xs text-gray-900 font-semibold truncate">
                                    {{ license.expires_at ? formatDateTime(license.expires_at) : 'Lifetime (No Expiry)' }}
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">Active since {{ formatDateTime(license.created_at) }}</div>
                            </div>
                        </div>

                        <!-- Dynamic Plan Benefits List -->
                        <div class="border-t border-gray-200 pt-6">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-3.5">Plan Benefits & Features Included</label>
                            <ul class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <li 
                                    v-for="feature in (license.planDetails?.features || getFallbackFeatures(license.plan))" 
                                    :key="feature"
                                    class="flex items-center gap-2 text-xs text-gray-650"
                                >
                                    <span class="material-symbols-rounded text-emerald-500 text-base flex-shrink-0">check_circle</span>
                                    <span class="truncate">{{ feature }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-slate-50/45 px-6 py-4.5 border-t border-gray-200 flex justify-between items-center text-xs">
                        <span class="text-gray-500">Need help or changes?</span>
                        <a 
                            href="mailto:support@vmcore.in" 
                            class="text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                        >
                            <span class="material-symbols-rounded text-sm">mail</span>
                            Contact Billing Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white border border-gray-200 rounded-lg p-12 text-center max-w-xl mx-auto shadow-sm">
                <div class="h-16 w-16 bg-slate-50 border border-gray-200 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-6">
                    <span class="material-symbols-rounded text-3xl">card_membership</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">No Active Plan Found</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                    You do not currently have any active server licensing subscriptions. Browse our pricing tiers to claim a license key.
                </p>
                <div class="mt-8">
                    <Link 
                        :href="route('dashboard') + '#plans-section'"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-3 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm inline-flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm flex-shrink-0">shopping_bag</span>
                        Browse Plans & Pricing
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
