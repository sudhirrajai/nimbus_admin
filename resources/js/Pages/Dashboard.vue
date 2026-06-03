<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted } from 'vue';

const generateFreeLicense = () => {
    router.post(route('licenses.free'));
}

onMounted(() => {
    // Load Razorpay script
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.async = true;
    document.body.appendChild(script);
});


defineProps({
    licenses: Array
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
                color: "#6366f1",
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
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white mb-2">
                        My Workspace
                    </h2>
                    <p class="text-slate-400">Manage and deploy your VMCORE licenses.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="generateFreeLicense" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-3 rounded-xl font-bold transition-all hover:shadow-lg hover:shadow-emerald-500/25 flex items-center gap-2 premium-gradient">
                        <span class="material-symbols-rounded">check_circle</span>
                        Claim Free License
                    </button>
                    <button @click="document.getElementById('plans-section').scrollIntoView({ behavior: 'smooth' })" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all hover:shadow-lg hover:shadow-indigo-500/25 flex items-center gap-2 premium-gradient">
                        <span class="material-symbols-rounded">add</span>
                        Purchase New
                    </button>
                </div>
            </div>
        </template>

        <div class="">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="glass-morphism rounded-2xl p-6 border border-white/5">
                        <div class="text-slate-400 text-sm font-medium mb-1">Total Licenses</div>
                        <div class="text-3xl font-bold text-white">{{ licenses.length }}</div>
                    </div>
                    <div class="glass-morphism rounded-2xl p-6 border border-white/5">
                        <div class="text-slate-400 text-sm font-medium mb-1">Active Now</div>
                        <div class="text-3xl font-bold text-emerald-400">{{ licenses.filter(l => l.status === 'active').length }}</div>
                    </div>
                    <div class="glass-morphism rounded-2xl p-6 border border-white/5">
                        <div class="text-slate-400 text-sm font-medium mb-1">Support Plan</div>
                        <div class="text-3xl font-bold text-indigo-400">Premium</div>
                    </div>
                </div>

                <!-- No Licenses State -->
                <div v-if="licenses.length === 0" class="glass-morphism rounded-3xl p-20 text-center border border-white/5">
                    <div class="bg-indigo-500/10 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-8 border border-indigo-500/20">
                        <span class="material-symbols-rounded text-5xl text-indigo-400">receipt_long</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">No Active Licenses</h3>
                    <p class="text-slate-400 mb-10 max-w-sm mx-auto text-lg">Your workspace is empty. Get started by purchasing a license for your server.</p>
                    <Link :href="route('home') + '#pricing'" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:bg-indigo-500 transition-all hover:shadow-lg hover:shadow-indigo-500/25 premium-gradient">
                        Browse Plans
                        <span class="material-symbols-rounded">arrow_forward</span>
                    </Link>
                </div>

                <!-- Licenses Grid -->
                <div v-else>
                    <div class="flex items-center gap-3 mb-6">
                        <h3 class="text-lg font-bold text-white">Active Subscriptions</h3>
                        <div class="h-px flex-1 bg-white/5"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="license in licenses" :key="license.id" class="group relative glass-morphism rounded-[2.5rem] overflow-hidden border border-white/5 hover:border-indigo-500/30 transition-all hover:shadow-2xl hover:shadow-indigo-500/10">
                            <div class="p-10">
                                <div class="flex justify-between items-start mb-10">
                                    <div class="flex items-center gap-4">
                                        <div class="h-14 w-14 rounded-2xl bg-indigo-600/10 flex items-center justify-center border border-indigo-500/20">
                                            <span class="material-symbols-rounded text-3xl text-indigo-400">workspace_premium</span>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-400/80 mb-1 block">{{ license.plan }} Plan</span>
                                            <h4 class="text-xl font-bold text-white tracking-tight">{{ license.license_key }}</h4>
                                        </div>
                                    </div>
                                    <span class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest border" :class="license.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                        {{ license.status }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-6 mb-10">
                                    <div class="p-5 rounded-2xl bg-white/5 border border-white/5">
                                        <div class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Server IP</div>
                                        <div class="text-white font-mono font-medium">{{ license.server_ip || 'Pending...' }}</div>
                                    </div>
                                    <div class="p-5 rounded-2xl bg-white/5 border border-white/5">
                                        <div class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-2">Expiry</div>
                                        <div class="text-white font-medium">{{ license.expires_at || 'Lifetime' }}</div>
                                    </div>
                                </div>

                                <div class="relative group/cmd">
                                    <div class="absolute -top-3 left-6 px-3 py-1 rounded-full bg-slate-900 border border-white/10 text-[10px] font-bold text-slate-500 uppercase tracking-widest z-10">
                                        Deployment Command
                                    </div>
                                    <div class="bg-black/40 rounded-3xl p-6 pt-8 border border-white/5 group-hover/cmd:border-indigo-500/30 transition-colors">
                                        <div class="flex justify-between items-start gap-4">
                                            <code class="text-sm text-indigo-200/90 block break-all font-mono leading-relaxed">
                                                {{ getInstallCommand(license.license_key) }}
                                            </code>
                                            <button @click="copyToClipboard(getInstallCommand(license.license_key))" class="shrink-0 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/20 active:scale-90">
                                                <span class="material-symbols-rounded text-xl">content_copy</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plans Section -->
                <div id="plans-section" class="mt-20 scroll-mt-24">
                    <div class="flex items-center gap-3 mb-10">
                        <h3 class="text-2xl font-bold text-white">Upgrade Your Experience</h3>
                        <div class="h-px flex-1 bg-white/5"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Pro Plan -->
                        <div class="glass-morphism rounded-[3rem] p-10 border border-white/5 flex flex-col relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-indigo-600/10 blur-3xl"></div>
                            
                            <div class="mb-8">
                                <span class="bg-indigo-500/10 text-indigo-400 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full border border-indigo-500/20">Popular Choice</span>
                                <h4 class="text-3xl font-bold text-white mt-6">Pro Plan</h4>
                                <div class="flex items-baseline gap-1 mt-4">
                                    <span class="text-4xl font-bold text-white">₹499</span>
                                    <span class="text-slate-500">/year</span>
                                </div>
                            </div>

                            <ul class="space-y-4 mb-10 flex-1">
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    1 Premium License
                                </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    Automatic Domain Locking
                                </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    Priority Email Support
                                </li>
                            </ul>

                            <button @click="buyPlan('pro')" class="w-full bg-white/5 hover:bg-indigo-600 text-white border border-white/10 hover:border-indigo-500 py-4 rounded-2xl font-bold transition-all">
                                Buy Pro Now
                            </button>
                        </div>

                        <!-- Enterprise Plan -->
                        <div class="glass-morphism rounded-[3rem] p-10 border border-white/10 flex flex-col relative overflow-hidden bg-white/5">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-purple-600/10 blur-3xl"></div>
                            
                            <div class="mb-8">
                                <span class="bg-purple-500/10 text-purple-400 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full border border-purple-500/20">Maximum Power</span>
                                <h4 class="text-3xl font-bold text-white mt-6">Enterprise</h4>
                                <div class="flex items-baseline gap-1 mt-4">
                                    <span class="text-4xl font-bold text-white">₹1,999</span>
                                    <span class="text-slate-500">/year</span>
                                </div>
                            </div>

                            <ul class="space-y-4 mb-10 flex-1">
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    Unlimited Licenses
                                </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    Multi-Server Support
                                    </li>
                                <li class="flex items-center gap-3 text-slate-300">
                                    <span class="material-symbols-rounded text-emerald-400">check_circle</span>
                                    24/7 Dedicated Support
                                </li>
                            </ul>

                            <button @click="buyPlan('enterprise')" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-bold transition-all shadow-xl shadow-indigo-600/20 premium-gradient">
                                Contact Sales / Buy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

