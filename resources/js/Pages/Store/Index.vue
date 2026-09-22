<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';

const props = defineProps({
    managedPlans: Array,
    selfHostedPlans: Array,
});

const activeTab = ref('managed_hosting'); // 'managed_hosting' or 'self_hosted'
const selectedCurrency = ref('INR');
const showHostingModal = ref(false);
const selectedPlanForHosting = ref(null);

const hostingForm = useForm({
    domain: '',
    plan_requested: '',
    estimated_traffic: 'Under 50,000 visitors/mo',
    notes: '',
});

onMounted(() => {
    // Check URL parameters for tab
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'self_hosted') {
        activeTab.value = 'self_hosted';
    }

    // Auto-detect timezone currency
    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz && !(tz === 'Asia/Kolkata' || tz.includes('Calcutta') || tz.includes('Kolkata'))) {
            selectedCurrency.value = 'USD';
        }
    } catch (e) {
        console.error('Timezone auto-detection failed:', e);
    }

    // Load Razorpay script
    if (!document.getElementById('razorpay-script')) {
        const script = document.createElement('script');
        script.id = 'razorpay-script';
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);
    }
});

const openHostingRequest = (plan) => {
    selectedPlanForHosting.value = plan;
    hostingForm.plan_requested = plan.name;
    showHostingModal.value = true;
};

const submitHostingRequest = () => {
    hostingForm.post(route('hosting.request.submit'), {
        onSuccess: () => {
            showHostingModal.value = false;
            hostingForm.reset();
        }
    });
};

const buySelfHostPlan = async (plan) => {
    if (plan.price_inr === 0 || plan.slug === 'free') {
        router.post(route('licenses.free'));
        return;
    }

    try {
        const response = await axios.post(route('payment.initiate'), { plan: plan.slug });
        const data = response.data;

        const options = {
            key: data.key_id,
            amount: data.amount,
            currency: "INR",
            name: "Nimbus by VMCore",
            description: `${plan.name} License Purchase`,
            order_id: data.order_id,
            handler: function (response) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = route('payment.verify');
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const params = {
                    _token: csrfToken,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                };

                for (const key in params) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            },
            prefill: {
                name: data.user.name,
                email: data.user.email,
            },
            theme: {
                color: "#10B981",
            },
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    } catch (error) {
        alert('Failed to initiate payment gateway. Please check Razorpay keys in settings.');
        console.error(error);
    }
};

const getFeatures = (plan) => {
    if (!plan || !plan.features) return [];
    if (Array.isArray(plan.features)) return plan.features;
    if (typeof plan.features === 'string') {
        try {
            const parsed = JSON.parse(plan.features);
            if (Array.isArray(parsed)) return parsed;
        } catch (e) {
            return plan.features.split('\n').filter(Boolean);
        }
    }
    return [];
};
</script>

<template>
    <Head title="Store & Packages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        Store &amp; Packages
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Choose between Fully Managed Cloud Hosting or Self-Hosted Nimbus licenses.
                    </p>
                </div>

                <!-- Currency Switcher matching original UI -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-gray-200">
                        <button 
                            type="button"
                            @click="selectedCurrency = 'INR'"
                            :class="selectedCurrency === 'INR' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 text-xs rounded transition-all cursor-pointer"
                        >
                            INR (₹)
                        </button>
                        <button 
                            type="button"
                            @click="selectedCurrency = 'USD'"
                            :class="selectedCurrency === 'USD' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 text-xs rounded transition-all cursor-pointer"
                        >
                            USD ($)
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Navigation Tabs (Standard Theme Style) -->
            <div class="flex items-center gap-2 border-b border-gray-200">
                <button 
                    type="button"
                    @click="activeTab = 'managed_hosting'"
                    :class="[
                        activeTab === 'managed_hosting' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors cursor-pointer"
                >
                    <span class="material-symbols-rounded text-base">cloud_done</span>
                    Fully Managed Cloud Hosting
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ managedPlans?.length || 0 }}</span>
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'self_hosted'"
                    :class="[
                        activeTab === 'self_hosted' 
                            ? 'border-emerald-500 text-emerald-600 font-semibold' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-sm flex items-center gap-2 transition-colors cursor-pointer"
                >
                    <span class="material-symbols-rounded text-base">terminal</span>
                    Nimbus Self-Host Licenses
                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-mono">{{ selfHostedPlans?.length || 0 }}</span>
                </button>
            </div>

            <!-- TAB 1: MANAGED CLOUD HOSTING PACKAGES -->
            <div v-if="activeTab === 'managed_hosting'" class="space-y-6 animate-fade-in">
                <!-- Info Banner -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-emerald-600 text-2xl">support_agent</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900">Zero Maintenance &bull; Fully Managed by Nimbus Engineers</div>
                            <div class="text-xs text-gray-600 mt-0.5">High-speed NVMe nodes, 24/7 security monitoring, automated backups, and 99.9% uptime SLA included.</div>
                        </div>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="plan in managedPlans" 
                        :key="plan.id"
                        class="bg-white border rounded-lg p-8 flex flex-col justify-between relative shadow-sm"
                        :class="[plan.is_popular ? 'border-emerald-500 ring-1 ring-emerald-500 shadow-emerald-500/5' : 'border-gray-200']"
                    >
                        <div class="space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">{{ plan.name }}</h4>
                                    <p v-if="plan.description" class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ plan.description }}</p>
                                </div>
                                <span v-if="plan.is_popular" class="bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border border-emerald-200">
                                    Popular
                                </span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-gray-900">
                                        {{ selectedCurrency === 'INR' ? '₹' + Number(plan.price_inr).toLocaleString('en-IN') : '$' + plan.price_usd }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ plan.billing_period }}</span>
                                </div>
                                <div v-if="plan.renewal_price_inr" class="text-xs text-gray-500 font-mono">
                                    Renews at: {{ selectedCurrency === 'INR' ? '₹' + Number(plan.renewal_price_inr).toLocaleString('en-IN') : '$' + plan.renewal_price_usd }} {{ plan.billing_period }}
                                </div>
                            </div>

                            <ul class="space-y-3.5 border-t border-gray-200 pt-6">
                                <li v-for="feat in getFeatures(plan)" :key="feat" class="flex items-center gap-2.5 text-xs text-gray-600">
                                    <span class="material-symbols-rounded text-emerald-500 text-sm">check_circle</span>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <button 
                                type="button"
                                @click="openHostingRequest(plan)"
                                class="w-full text-xs font-semibold py-3 rounded-lg mt-8 transition-colors shadow-sm cursor-pointer"
                                :class="[
                                    plan.is_popular 
                                        ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-500/10' 
                                        : 'bg-slate-100 hover:bg-slate-200 text-gray-800 border border-gray-200'
                                ]"
                            >
                                {{ plan.cta_text || 'Deploy ' + plan.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: SELF-HOSTED NIMBUS LICENSES -->
            <div v-if="activeTab === 'self_hosted'" class="space-y-6 animate-fade-in">
                <!-- Info Banner -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-emerald-600 text-2xl">terminal</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900">Bring Your Own Server &bull; 100% Data Sovereignty</div>
                            <div class="text-xs text-gray-600 mt-0.5">Run a single curl command on any Ubuntu/Debian server and unlock an elite server management panel.</div>
                        </div>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="plan in selfHostedPlans" 
                        :key="plan.id"
                        class="bg-white border rounded-lg p-8 flex flex-col justify-between relative shadow-sm"
                        :class="[plan.is_popular ? 'border-emerald-500 ring-1 ring-emerald-500 shadow-emerald-500/5' : 'border-gray-200']"
                    >
                        <div class="space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">{{ plan.name }}</h4>
                                    <p v-if="plan.description" class="text-xs text-gray-500 mt-1 min-h-[32px]">{{ plan.description }}</p>
                                </div>
                                <span v-if="plan.is_popular" class="bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border border-emerald-200">
                                    Popular
                                </span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-gray-900">
                                        {{ selectedCurrency === 'INR' ? (plan.price_inr === 0 ? '₹0' : '₹' + Number(plan.price_inr).toLocaleString('en-IN')) : (plan.price_usd === 0 ? '$0' : '$' + plan.price_usd) }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ plan.billing_period }}</span>
                                </div>
                                <div v-if="plan.renewal_price_inr && plan.renewal_price_inr !== plan.price_inr" class="text-xs text-gray-500 font-mono">
                                    Renews at: {{ selectedCurrency === 'INR' ? '₹' + Number(plan.renewal_price_inr).toLocaleString('en-IN') : '$' + plan.renewal_price_usd }} {{ plan.billing_period }}
                                </div>
                            </div>

                            <ul class="space-y-3.5 border-t border-gray-200 pt-6">
                                <li v-for="feat in getFeatures(plan)" :key="feat" class="flex items-center gap-2.5 text-xs text-gray-600">
                                    <span class="material-symbols-rounded text-emerald-500 text-sm">check_circle</span>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <button 
                                type="button"
                                @click="buySelfHostPlan(plan)"
                                class="w-full text-xs font-semibold py-3 rounded-lg mt-8 transition-colors shadow-sm cursor-pointer"
                                :class="[
                                    plan.is_popular 
                                        ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-500/10' 
                                        : 'bg-slate-100 hover:bg-slate-200 text-gray-800 border border-gray-200'
                                ]"
                            >
                                {{ plan.cta_text || (plan.price_inr > 0 ? 'Buy ' + plan.name + ' Now' : 'Claim Free License') }}
                            </button>
                            <div v-if="selectedCurrency === 'USD' && plan.price_inr > 0" class="text-[10px] text-gray-400 text-center mt-2">
                                Processed as ₹{{ plan.price_inr }} via Razorpay
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Requirements / Self-Host Callout Banner -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-1.5 text-center md:text-left">
                    <div class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        <span class="material-symbols-rounded text-xs">tune</span>
                        Custom Architecture &amp; Enterprise
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                        Want to self host your server or got custom requirements?
                    </h3>
                    <p class="text-xs text-gray-500 max-w-2xl">
                        Looking for dedicated bare-metal clusters, custom enterprise SLA, migration assistance, or private self-hosted setups? Our engineering team is ready to configure it for you.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a 
                        href="mailto:support@vmcore.in?subject=Custom%20Hosting%20%2F%20Self-Host%20Requirements" 
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-xs font-semibold uppercase tracking-wider shadow-sm transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-sm">mail</span>
                        <span>Contact Us Now</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Request Managed Hosting Modal -->
        <div v-if="showHostingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div class="bg-white border border-gray-200 rounded-xl p-6 w-full max-w-lg shadow-2xl animate-fade-in relative text-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600">cloud_upload</span>
                            Request {{ selectedPlanForHosting?.name || 'Managed Cloud Hosting' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Let our engineers set up and configure high-performance Nimbus hosting.</p>
                    </div>
                    <button @click="showHostingModal = false" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitHostingRequest" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Target Website Domain *</label>
                        <input 
                            type="text" 
                            v-model="hostingForm.domain" 
                            placeholder="e.g. clientportal.com or app.mybrand.io" 
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5 font-mono" 
                            required 
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Estimated Monthly Traffic</label>
                        <select v-model="hostingForm.estimated_traffic" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5">
                            <option value="Under 50,000 visitors/mo">Under 50,000 visitors/mo</option>
                            <option value="50,000 - 250,000 visitors/mo">50,000 - 250,000 visitors/mo</option>
                            <option value="250,000 - 1,000,000 visitors/mo">250,000 - 1,000,000 visitors/mo</option>
                            <option value="Over 1M+ visitors/mo">Over 1M+ visitors/mo (High Traffic)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Additional Requirements / Notes</label>
                        <textarea 
                            v-model="hostingForm.notes" 
                            rows="3" 
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" 
                            placeholder="Need Redis cache, Node.js background workers, custom PHP extensions, etc."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showHostingModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="hostingForm.processing" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm">
                            {{ hostingForm.processing ? 'Submitting...' : 'Submit Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
