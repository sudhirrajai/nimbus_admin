<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, computed } from 'vue';

const props = defineProps({
    licenses: Array
});

const hasActiveFreeLicense = computed(() => {
    return props.licenses.some(l => l.plan === 'free' && l.status === 'active');
});

const generateFreeLicense = () => {
    if (hasActiveFreeLicense.value) return;
    router.post(route('licenses.free'));
}

const disconnectMachine = (license) => {
    if (confirm('Are you sure you want to disconnect this machine installation? The device using this license will stop working until it registers again or another device claims it.')) {
        router.post(route('licenses.disconnect', { license: license.id }));
    }
}

const revokeLicense = (license) => {
    if (confirm('Are you sure you want to revoke this license? This action cannot be undone, and the devices having this license will stop working immediately. You will not be able to use this key again.')) {
        router.post(route('licenses.revoke', { license: license.id }));
    }
}

onMounted(() => {
    // Load Razorpay script
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.async = true;
    document.body.appendChild(script);
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
}

const getInstallCommand = (key) => {
    return `curl -sSL https://vmcore.in/install.sh | bash -s -- --license=${key}`;
}

const buyPlan = async (plan) => {
    try {
        const response = await axios.post(route('payment.initiate'), { plan });
        const data = response.data;

        const options = {
            key: data.key_id,
            amount: data.amount,
            currency: "INR",
            name: "VMCORE Central",
            description: `${plan.toUpperCase()} Plan Subscription`,
            order_id: data.order_id,
            handler: function (response) {
                // Post to verify
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = route('payment.verify');
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
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
                color: "#111827",
            },
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    } catch (error) {
        alert('Failed to initiate payment. Please check your configuration.');
        console.error(error);
    }
}

</script>

<template>
    <Head title="My Licenses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">
                        My Workspace
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">Manage and deploy your VMCORE licenses.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="generateFreeLicense" 
                        :disabled="hasActiveFreeLicense"
                        :class="[
                            hasActiveFreeLicense 
                                ? 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700 opacity-60' 
                                : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm'
                        ]"
                        class="px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">check_circle</span>
                        {{ hasActiveFreeLicense ? 'Free License Claimed' : 'Claim Free License' }}
                    </button>
                    <button 
                        @click="document.getElementById('plans-section').scrollIntoView({ behavior: 'smooth' })" 
                        class="bg-gray-200 hover:bg-white text-gray-900 px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">add</span>
                        Purchase New
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Success/Error Alert -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error || $page.props.flash?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error || $page.props.flash?.error" class="flex items-center gap-3 p-4 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p class="text-xs font-medium">{{ $page.props.errors?.error || $page.props.flash?.error }}</p>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Licenses</span>
                        <span class="material-symbols-rounded text-gray-500 text-xl">receipt_long</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2">{{ licenses.length }}</div>
                </div>
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Now</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">sensors</span>
                    </div>
                    <div class="text-2xl font-bold text-emerald-400 mt-2">
                        {{ licenses.filter(l => l.status === 'active').length }}
                    </div>
                </div>
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Support Plan</span>
                        <span class="material-symbols-rounded text-emerald-400 text-xl">verified_user</span>
                    </div>
                    <div class="text-2xl font-bold text-emerald-400 mt-2">Premium</div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="licenses.length === 0" class="bg-gray-800 border border-gray-700 rounded-lg p-16 text-center shadow-sm">
                <div class="bg-gray-700 w-16 h-16 rounded-lg flex items-center justify-center mx-auto mb-6 border border-gray-600">
                    <span class="material-symbols-rounded text-3xl text-gray-400">receipt_long</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">No Active Licenses</h3>
                <p class="text-sm text-gray-400 mb-8 max-w-sm mx-auto">Your workspace is empty. Get started by purchasing a license for your server.</p>
                <Link :href="route('home') + '#pricing'" class="inline-flex items-center gap-2 bg-gray-200 text-gray-900 px-6 py-3 rounded-lg text-xs font-semibold tracking-wide uppercase hover:bg-white transition-all shadow-sm">
                    Browse Plans
                    <span class="material-symbols-rounded text-sm">arrow_forward</span>
                </Link>
            </div>

            <!-- Licenses Grid -->
            <div v-else class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Active Subscriptions</h3>
                    <div class="h-px flex-1 bg-gray-700 ml-4"></div>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div 
                        v-for="license in licenses" 
                        :key="license.id" 
                        class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden shadow-sm hover:border-gray-600 transition-all flex flex-col justify-between"
                    >
                        <div class="p-6 space-y-6">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-gray-700/20 flex items-center justify-center border border-gray-600/20 text-gray-300">
                                        <span class="material-symbols-rounded">workspace_premium</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ license.plan }} Plan</span>
                                        <h4 class="text-sm font-bold text-white font-mono mt-0.5">{{ license.license_key }}</h4>
                                    </div>
                                </div>
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider ring-1 ring-inset"
                                    :class="[
                                        license.status === 'active' 
                                            ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20' 
                                            : 'bg-rose-500/10 text-rose-400 ring-rose-500/20'
                                    ]"
                                >
                                    {{ license.status }}
                                </span>
                            </div>

                            <!-- Detail Widgets -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-900 p-4 border border-gray-700 rounded-lg">
                                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Server IP</div>
                                    <div class="text-xs text-white font-mono font-medium truncate">{{ license.server_ip || 'Pending...' }}</div>
                                </div>
                                <div class="bg-gray-900 p-4 border border-gray-700 rounded-lg">
                                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Expiry</div>
                                    <div class="text-xs text-white font-medium truncate">{{ license.expires_at || 'Lifetime' }}</div>
                                </div>
                            </div>

                            <!-- Deployment Command -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Deployment Command</label>
                                <div class="bg-gray-900 border border-gray-700 rounded-lg p-3 flex items-center justify-between gap-3">
                                    <code class="text-xs text-gray-300 font-mono block break-all select-all leading-normal flex-1">
                                        {{ getInstallCommand(license.license_key) }}
                                    </code>
                                    <button 
                                        @click="copyToClipboard(getInstallCommand(license.license_key))" 
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors"
                                        title="Copy Command"
                                    >
                                        <span class="material-symbols-rounded text-sm">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div v-if="license.status === 'active'" class="bg-gray-900/40 px-6 py-4 border-t border-gray-700 flex items-center gap-3">
                            <button 
                                @click="disconnectMachine(license)" 
                                :disabled="!license.machine_id && !license.server_ip"
                                :class="[
                                    (!license.machine_id && !license.server_ip) 
                                        ? 'text-gray-600 bg-transparent border-gray-900 cursor-not-allowed' 
                                        : 'text-amber-500 bg-amber-500/5 border-amber-500/20 hover:bg-amber-500/10 hover:text-amber-400'
                                ]"
                                class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center justify-center gap-1.5"
                            >
                                <span class="material-symbols-rounded text-base">phonelink_off</span>
                                Disconnect IP
                            </button>
                            <button 
                                @click="revokeLicense(license)"
                                class="flex-1 px-3 py-2 rounded-lg text-xs font-semibold bg-rose-500/5 border border-rose-500/20 text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all flex items-center justify-center gap-1.5"
                            >
                                <span class="material-symbols-rounded text-base">cancel</span>
                                Revoke License
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade pricing widget section -->
            <div id="plans-section" class="scroll-mt-24 pt-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Upgrade License Plans</h3>
                    <div class="h-px flex-1 bg-gray-700 ml-4"></div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Pro Card -->
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-8 flex flex-col justify-between relative shadow-sm">
                        <div class="space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-white">Pro Plan</h4>
                                    <p class="text-xs text-gray-400 mt-1">Excellent choice for growing platforms.</p>
                                </div>
                                <span class="bg-gray-700/50 text-gray-300 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border border-gray-600">Popular</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-white">₹499</span>
                                <span class="text-xs text-gray-500">/year</span>
                            </div>
                            <ul class="space-y-3.5 border-t border-gray-700/80 pt-6">
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    1 Premium License
                                </li>
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    Automatic Domain Locking
                                </li>
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    Priority Email Support
                                </li>
                            </ul>
                        </div>
                        <button 
                            @click="buyPlan('pro')" 
                            class="w-full bg-gray-755 hover:bg-gray-700 text-white text-xs font-semibold py-3 rounded-lg mt-8 transition-colors border border-gray-600"
                        >
                            Buy Pro Now
                        </button>
                    </div>

                    <!-- Enterprise Card -->
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-8 flex flex-col justify-between relative shadow-sm">
                        <div class="space-y-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-white">Enterprise</h4>
                                    <p class="text-xs text-gray-400 mt-1">Ideal for large scale networks.</p>
                                </div>
                                <span class="bg-gray-700/50 text-gray-300 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border border-gray-600">Max Performance</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-bold text-white">₹1,999</span>
                                <span class="text-xs text-gray-500">/year</span>
                            </div>
                            <ul class="space-y-3.5 border-t border-gray-700/80 pt-6">
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    Unlimited Licenses
                                </li>
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    Multi-Server Support
                                </li>
                                <li class="flex items-center gap-2.5 text-xs text-gray-300">
                                    <span class="material-symbols-rounded text-emerald-400 text-sm">check_circle</span>
                                    24/7 Dedicated Support
                                </li>
                            </ul>
                        </div>
                        <button 
                            @click="buyPlan('enterprise')" 
                            class="w-full bg-gray-200 hover:bg-white text-gray-900 text-xs font-semibold py-3 rounded-lg mt-8 transition-colors shadow-sm"
                        >
                            Buy Enterprise Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
