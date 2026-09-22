<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    plans: Array,
    available_modules: Object,
});

const activeTab = ref('all'); // 'all', 'self_hosted', 'managed_hosting'

const filteredPlans = computed(() => {
    if (activeTab.value === 'all') return props.plans || [];
    if (activeTab.value === 'self_hosted') {
        return (props.plans || []).filter(p => !p.type || p.type === 'self_hosted');
    }
    return (props.plans || []).filter(p => p.type === 'managed_hosting');
});

const formatPrice = (value, currency) => {
    if (currency === 'INR') {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(value || 0);
    }
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0);
};

// Create Plan Modal State
const showCreateModal = ref(false);
const featuresInput = ref('');

const createForm = useForm({
    name: '',
    type: 'managed_hosting',
    slug: '',
    price_inr: 3800,
    renewal_price_inr: 4790,
    price_usd: 49,
    renewal_price_usd: 59,
    billing_period: '/year',
    max_domains: 5,
    features: [],
    modules: [],
    is_active: true,
    is_popular: false,
    cta_text: 'Get Started',
    description: '',
});

const openCreateModal = (defaultType = 'managed_hosting') => {
    createForm.reset();
    createForm.type = defaultType;
    if (defaultType === 'managed_hosting') {
        createForm.name = 'Cloud VPS Plan';
        createForm.price_inr = 3800;
        createForm.renewal_price_inr = 4790;
        createForm.price_usd = 49;
        createForm.renewal_price_usd = 59;
        createForm.max_domains = 5;
        createForm.cta_text = 'Deploy Cloud';
        featuresInput.value = "1 vCPU & 2GB RAM Cloud Node\n30GB NVMe High-Speed Storage\nFully Managed by VMCORE Team\nFree Auto-Renewing SSL\nAutomated Daily Backups";
    } else {
        createForm.name = 'Nimbus License';
        createForm.price_inr = 499;
        createForm.renewal_price_inr = 499;
        createForm.price_usd = 19;
        createForm.renewal_price_usd = 19;
        createForm.max_domains = 10;
        createForm.cta_text = 'Buy License';
        featuresInput.value = "Self-Hosted on Your Server\n10 Domains Limit\nSSL Automation\nGit Auto-Deploy\nCommunity Support";
    }
    showCreateModal.value = true;
};

const submitCreatePlan = () => {
    createForm.features = featuresInput.value
        .split('\n')
        .map(f => f.trim())
        .filter(f => f.length > 0);

    createForm.post(route('admin.plans.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};

const toggleActive = (plan) => {
    router.post(route('admin.plans.toggle-active', plan.id), {}, {
        preserveScroll: true,
    });
};

const deletePlan = (plan) => {
    if (confirm(`Are you sure you want to delete plan "${plan.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.plans.destroy', plan.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Manage Plans" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Plans &amp; Service Pricing
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Manage both Self-Hosted Nimbus licenses and Fully Managed Cloud Hosting packages.</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button 
                        @click="openCreateModal('managed_hosting')"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-sm">cloud_sync</span>
                        New Managed Plan
                    </button>
                    <button 
                        @click="openCreateModal('self_hosted')"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-1.5"
                    >
                        <span class="material-symbols-rounded text-sm">key</span>
                        New Self-Host Plan
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Tabs -->
            <div class="border-b border-gray-200 flex items-center gap-2">
                <button 
                    @click="activeTab = 'all'"
                    :class="[
                        activeTab === 'all' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-xs flex items-center gap-2 transition-colors uppercase tracking-wider font-bold"
                >
                    <span class="material-symbols-rounded text-base">view_list</span>
                    All Plans
                    <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-mono">{{ plans?.length || 0 }}</span>
                </button>

                <button 
                    @click="activeTab = 'managed_hosting'"
                    :class="[
                        activeTab === 'managed_hosting' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-xs flex items-center gap-2 transition-colors uppercase tracking-wider font-bold"
                >
                    <span class="material-symbols-rounded text-base text-emerald-600">cloud</span>
                    Fully Managed Cloud
                    <span class="bg-emerald-50 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-mono">
                        {{ plans?.filter(p => p.type === 'managed_hosting').length || 0 }}
                    </span>
                </button>

                <button 
                    @click="activeTab = 'self_hosted'"
                    :class="[
                        activeTab === 'self_hosted' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-xs flex items-center gap-2 transition-colors uppercase tracking-wider font-bold"
                >
                    <span class="material-symbols-rounded text-base text-purple-600">terminal</span>
                    Self-Hosted Nimbus Licenses
                    <span class="bg-purple-50 text-purple-700 text-[10px] px-2 py-0.5 rounded-full font-mono">
                        {{ plans?.filter(p => !p.type || p.type === 'self_hosted').length || 0 }}
                    </span>
                </button>
            </div>

            <!-- Plans Table Container -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan / Offering</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">INR Rates (Term &amp; Renewal)</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">USD Rates</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Max Domains</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="plan in filteredPlans" :key="plan.id" class="hover:bg-slate-50/50 transition-colors">
                                <!-- Plan Identity -->
                                <td class="px-6 py-4.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{ plan.name }}</span>
                                        <span v-if="plan.is_popular" class="inline-flex items-center px-2 py-0.2 rounded-full text-[9px] font-bold uppercase tracking-wider bg-purple-50 border border-purple-200 text-purple-700">
                                            Popular
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ plan.description || 'No description provided' }}</div>
                                    <div class="text-[10px] font-mono text-gray-400 mt-1">slug: {{ plan.slug }}</div>
                                </td>

                                <!-- Category Type Badge -->
                                <td class="px-6 py-4.5">
                                    <span 
                                        :class="plan.type === 'managed_hosting' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-gray-200'"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-2xs"
                                    >
                                        <span class="material-symbols-rounded text-xs">
                                            {{ plan.type === 'managed_hosting' ? 'cloud' : 'terminal' }}
                                        </span>
                                        {{ plan.type === 'managed_hosting' ? 'Managed Cloud' : 'Self-Hosted' }}
                                    </span>
                                </td>

                                <!-- INR Price and Renewal Rate -->
                                <td class="px-6 py-4.5 text-xs font-mono">
                                    <div class="font-bold text-gray-900 text-sm">
                                        {{ formatPrice(plan.price_inr, 'INR') }}<span class="text-[11px] text-gray-400 font-sans font-normal">{{ plan.billing_period }}</span>
                                    </div>
                                    <div class="text-[11px] text-emerald-700 font-medium mt-0.5">
                                        Renews: {{ formatPrice(plan.renewal_price_inr || plan.price_inr, 'INR') }}
                                    </div>
                                </td>

                                <!-- USD Price and Renewal Rate -->
                                <td class="px-6 py-4.5 text-xs font-mono">
                                    <div class="font-semibold text-gray-800">
                                        {{ formatPrice(plan.price_usd, 'USD') }}<span class="text-[11px] text-gray-400 font-sans font-normal">{{ plan.billing_period }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Renews: {{ formatPrice(plan.renewal_price_usd || plan.price_usd, 'USD') }}
                                    </div>
                                </td>

                                <!-- Max Domains -->
                                <td class="px-6 py-4.5 text-center text-sm font-mono font-semibold text-gray-700">
                                    {{ plan.max_domains }}
                                </td>

                                <!-- Status Toggle -->
                                <td class="px-6 py-4.5 text-center">
                                    <button 
                                        @click="toggleActive(plan)"
                                        :title="plan.is_active ? 'Click to deactivate' : 'Click to activate'"
                                        :class="[
                                            plan.is_active 
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' 
                                                : 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100'
                                        ]"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-2xs transition-all"
                                    >
                                        <span class="material-symbols-rounded text-xs">
                                            {{ plan.is_active ? 'check_circle' : 'cancel' }}
                                        </span>
                                        {{ plan.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-6 py-4.5 text-right space-x-1 whitespace-nowrap">
                                    <Link 
                                        :href="route('admin.plans.edit', plan.id)" 
                                        class="text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 p-1.5 rounded-lg transition-colors inline-block"
                                        title="Edit Plan"
                                    >
                                        <span class="material-symbols-rounded text-base">edit</span>
                                    </Link>
                                    <button 
                                        @click="deletePlan(plan)"
                                        class="text-gray-400 hover:text-rose-600 hover:bg-rose-50 p-1.5 rounded-lg transition-colors inline-block"
                                        title="Delete Plan"
                                    >
                                        <span class="material-symbols-rounded text-base">delete</span>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="filteredPlans.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-xs text-gray-400">
                                    No plans configured under this category. Click one of the buttons above to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= CREATE PLAN MODAL ================= -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-200 animate-scale-up">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-rounded text-lg">add_circle</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Create New Plan</h3>
                            <p class="text-[11px] text-gray-500">Configure service offering, term rates, and entitlements.</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitCreatePlan" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Plan Name</label>
                            <input 
                                v-model="createForm.name" 
                                type="text" 
                                required 
                                placeholder="e.g. Business Cloud"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Service Category</label>
                            <select 
                                v-model="createForm.type"
                                class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                required
                            >
                                <option value="managed_hosting">Fully Managed Cloud Hosting</option>
                                <option value="self_hosted">Self-Hosted Nimbus License</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Short Description / Subtitle</label>
                        <input 
                            v-model="createForm.description" 
                            type="text" 
                            placeholder="e.g. High-performance cloud hosting managed entirely by our team"
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" 
                        />
                    </div>

                    <!-- Pricing Grids -->
                    <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 space-y-3">
                        <div class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-emerald-600 text-sm">payments</span>
                            Pricing &amp; Renewal Rates
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Initial Price (INR ₹)</label>
                                <input 
                                    v-model="createForm.price_inr" 
                                    type="number" 
                                    step="1"
                                    min="0"
                                    required
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 font-mono" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Next Renewal Price (INR ₹)</label>
                                <input 
                                    v-model="createForm.renewal_price_inr" 
                                    type="number" 
                                    step="1"
                                    min="0"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 font-mono" 
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Initial Price (USD $)</label>
                                <input 
                                    v-model="createForm.price_usd" 
                                    type="number" 
                                    step="1"
                                    min="0"
                                    required
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 font-mono" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Next Renewal Price (USD $)</label>
                                <input 
                                    v-model="createForm.renewal_price_usd" 
                                    type="number" 
                                    step="1"
                                    min="0"
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 font-mono" 
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Billing Period Display</label>
                                <input 
                                    v-model="createForm.billing_period" 
                                    type="text" 
                                    placeholder="/year or /month"
                                    required
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Max Domains Allowed</label>
                                <input 
                                    v-model="createForm.max_domains" 
                                    type="number" 
                                    min="1"
                                    required
                                    class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 font-mono" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Features text list -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Features (One line per bullet on pricing table)</label>
                        <textarea 
                            v-model="featuresInput" 
                            rows="4" 
                            placeholder="Enter bullet points, one per line..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-sans"
                        ></textarea>
                    </div>

                    <!-- Toggles: Active & Popular -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" v-model="createForm.is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                            <span class="text-xs font-bold text-gray-800">Plan is Active</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" v-model="createForm.is_popular" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                            <span class="text-xs font-bold text-gray-800">Highlight as Popular</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="createForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold uppercase tracking-wider rounded-lg shadow-sm">
                            Create Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
