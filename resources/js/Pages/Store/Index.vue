<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';

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
    <Head title="Order Packages & Store" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">storefront</span>
                        Order Packages &amp; Store
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Select and configure Fully Managed Cloud Hosting packages or Self-Hosted Nimbus licenses.
                    </p>
                </div>

                <!-- Currency Switcher -->
                <div class="flex items-center gap-2">
                    <div class="inline-flex bg-slate-100 p-1 rounded-xl border border-gray-200">
                        <button 
                            type="button"
                            @click="selectedCurrency = 'INR'" 
                            :class="[selectedCurrency === 'INR' ? 'bg-white text-emerald-600 font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900']"
                            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                        >
                            India (₹ INR)
                        </button>
                        <button 
                            type="button"
                            @click="selectedCurrency = 'USD'" 
                            :class="[selectedCurrency === 'USD' ? 'bg-white text-emerald-600 font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900']"
                            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer"
                        >
                            Global ($ USD)
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Dual Service Category Tabs -->
            <div class="flex justify-center">
                <div class="inline-flex p-1.5 bg-slate-100 rounded-2xl border border-gray-200 shadow-2xs gap-1.5">
                    <button 
                        type="button"
                        @click="activeTab = 'managed_hosting'"
                        :class="activeTab === 'managed_hosting' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-base text-blue-600">cloud</span>
                        Fully Managed Cloud Hosting ({{ managedPlans?.length || 0 }})
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'self_hosted'"
                        :class="activeTab === 'self_hosted' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-base text-emerald-600">terminal</span>
                        Self-Hosted Nimbus Licenses ({{ selfHostedPlans?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- TAB 1: MANAGED CLOUD HOSTING PACKAGES -->
            <div v-if="activeTab === 'managed_hosting'" class="space-y-6 animate-fade-in">
                <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-rounded text-xl">support_agent</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-950">Zero DevOps Hassle &bull; Managed by Nimbus Engineers</div>
                            <div class="text-xs text-gray-600 mt-0.5">High-speed NVMe nodes, 24/7 security monitoring, automated backups, and 99.9% uptime SLA included with every package.</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="plan in managedPlans" 
                        :key="plan.id"
                        class="bg-white border-2 rounded-2xl p-7 flex flex-col justify-between relative shadow-2xs hover:shadow-md transition-all"
                        :class="plan.is_popular ? 'border-blue-500 shadow-blue-500/10' : 'border-gray-200'"
                    >
                        <div v-if="plan.is_popular" class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                            Most Popular
                        </div>

                        <div class="space-y-5">
                            <div>
                                <h3 class="text-lg font-black text-gray-950">{{ plan.name }}</h3>
                                <p v-if="plan.description" class="text-xs text-gray-500 mt-1 min-h-[32px] leading-relaxed">{{ plan.description }}</p>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-black text-gray-950">
                                        {{ selectedCurrency === 'INR' ? '₹' + Number(plan.price_inr).toLocaleString('en-IN') : '$' + plan.price_usd }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-medium">{{ plan.billing_period }}</span>
                                </div>
                                <div v-if="plan.renewal_price_inr" class="text-[11px] text-blue-700 font-mono">
                                    Renews at: {{ selectedCurrency === 'INR' ? '₹' + Number(plan.renewal_price_inr).toLocaleString('en-IN') : '$' + plan.renewal_price_usd }}{{ plan.billing_period }}
                                </div>
                            </div>

                            <ul class="space-y-2.5 border-t border-gray-100 pt-5 text-xs text-gray-700">
                                <li v-for="feat in getFeatures(plan)" :key="feat" class="flex items-center gap-2">
                                    <span class="text-blue-600 font-bold">✓</span>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="pt-6">
                            <button 
                                type="button"
                                @click="openHostingRequest(plan)"
                                :class="plan.is_popular ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 hover:bg-black text-white'"
                                class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all cursor-pointer active:scale-98"
                            >
                                {{ plan.cta_text || 'Deploy ' + plan.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: SELF-HOSTED NIMBUS LICENSES -->
            <div v-if="activeTab === 'self_hosted'" class="space-y-6 animate-fade-in">
                <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="h-10 w-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-rounded text-xl">terminal</span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-950">Bring Your Own Server &bull; 100% Data Sovereignty</div>
                            <div class="text-xs text-gray-600 mt-0.5">Run a single curl command on any Ubuntu/Debian server and unlock an elite server management panel with zero lock-in.</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="plan in selfHostedPlans" 
                        :key="plan.id"
                        class="bg-white border-2 rounded-2xl p-7 flex flex-col justify-between relative shadow-2xs hover:shadow-md transition-all"
                        :class="plan.is_popular ? 'border-emerald-500 shadow-emerald-500/10' : 'border-gray-200'"
                    >
                        <div v-if="plan.is_popular" class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                            Most Popular
                        </div>

                        <div class="space-y-5">
                            <div>
                                <h3 class="text-lg font-black text-gray-950">{{ plan.name }}</h3>
                                <p v-if="plan.description" class="text-xs text-gray-500 mt-1 min-h-[32px] leading-relaxed">{{ plan.description }}</p>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-black text-gray-950">
                                        {{ selectedCurrency === 'INR' ? (plan.price_inr === 0 ? '₹0' : '₹' + Number(plan.price_inr).toLocaleString('en-IN')) : (plan.price_usd === 0 ? '$0' : '$' + plan.price_usd) }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-medium">{{ plan.billing_period }}</span>
                                </div>
                                <div v-if="plan.renewal_price_inr && plan.renewal_price_inr !== plan.price_inr" class="text-[11px] text-emerald-700 font-mono">
                                    Renews at: {{ selectedCurrency === 'INR' ? '₹' + Number(plan.renewal_price_inr).toLocaleString('en-IN') : '$' + plan.renewal_price_usd }}{{ plan.billing_period }}
                                </div>
                            </div>

                            <ul class="space-y-2.5 border-t border-gray-100 pt-5 text-xs text-gray-700">
                                <li v-for="feat in getFeatures(plan)" :key="feat" class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="pt-6">
                            <button 
                                type="button"
                                @click="buySelfHostPlan(plan)"
                                :class="plan.is_popular ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-900 hover:bg-black text-white'"
                                class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all cursor-pointer active:scale-98"
                            >
                                {{ plan.cta_text || (plan.price_inr > 0 ? 'Buy License Now' : 'Claim Free License') }}
                            </button>
                            <div v-if="selectedCurrency === 'USD' && plan.price_inr > 0" class="text-[10px] text-gray-400 text-center mt-2">
                                Billed in INR (₹{{ plan.price_inr }}) via Razorpay
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Managed Hosting Modal -->
        <div v-if="showHostingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 w-full max-w-lg shadow-2xl animate-fade-in relative text-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">cloud_upload</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Deploy {{ selectedPlanForHosting?.name || 'Cloud Hosting' }}</h3>
                            <span class="text-[11px] text-gray-500">Submit your target domain to begin server provisioning</span>
                        </div>
                    </div>
                    <button @click="showHostingModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitHostingRequest" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Target Website Domain *</label>
                        <input 
                            type="text" 
                            v-model="hostingForm.domain" 
                            placeholder="e.g. clientportal.com or app.mybrand.io" 
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                            required 
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Estimated Traffic</label>
                        <select v-model="hostingForm.estimated_traffic" class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="Under 50,000 visitors/mo">Under 50,000 visitors/mo</option>
                            <option value="50,000 - 250,000 visitors/mo">50,000 - 250,000 visitors/mo</option>
                            <option value="250,000 - 1,000,000 visitors/mo">250,000 - 1,000,000 visitors/mo</option>
                            <option value="Over 1M+ visitors/mo">Over 1M+ visitors/mo (High Traffic)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Custom Notes / Software Requirements</label>
                        <textarea 
                            v-model="hostingForm.notes" 
                            rows="3" 
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                            placeholder="Need Redis cache, Node.js background workers, custom PHP extensions, etc."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showHostingModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit" :disabled="hostingForm.processing" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">
                            {{ hostingForm.processing ? 'Submitting...' : 'Submit Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
