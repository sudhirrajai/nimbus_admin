<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    licenses: Array,
    plans: Array,
});

const hasActiveFreeLicense = computed(() => {
    return props.licenses.some(l => l.plan === 'free' && l.status === 'active');
});

const generateFreeLicense = () => {
    if (hasActiveFreeLicense.value) return;
    router.post(route('licenses.free'));
};

const disconnectMachine = (license) => {
    if (confirm('Are you sure you want to disconnect this machine installation? The device using this license will stop working until it registers again or another device claims it.')) {
        router.post(route('licenses.disconnect', { license: license.id }));
    }
};

const revokeLicense = (license) => {
    if (confirm('Are you sure you want to revoke this license? This action cannot be undone, and the devices having this license will stop working immediately.')) {
        router.post(route('licenses.revoke', { license: license.id }));
    }
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
};

const getInstallCommand = (key) => {
    return `curl -sSL ${window.location.origin}/install.sh | sudo bash -s -- --license=${key}`;
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return 'Lifetime';
    return new Date(dateStr).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Nimbus Self-Host Licenses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">terminal</span>
                        Nimbus Self-Host Control Panels
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Deploy and manage Nimbus on your own VPS and bare-metal servers.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="generateFreeLicense" 
                        :disabled="hasActiveFreeLicense"
                        :class="[
                            hasActiveFreeLicense 
                                ? 'bg-slate-100 text-gray-400 cursor-not-allowed border border-gray-200 opacity-60' 
                                : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm'
                        ]"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">check_circle</span>
                        {{ hasActiveFreeLicense ? 'Free License Active' : 'Claim Free License' }}
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=self_hosted'" 
                        class="bg-slate-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">shopping_bag</span>
                        Buy More Licenses
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash messages -->
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

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Licenses</div>
                    <div class="text-2xl font-black text-gray-950 mt-1">{{ licenses.length }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Self-hosted control panel keys</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Active Nodes</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">
                        {{ licenses.filter(l => l.status === 'active').length }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Currently operational</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Server Deployments</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">
                        {{ licenses.filter(l => l.server_ip).length }} Connected
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Hardware nodes claimed</div>
                </div>
            </div>

            <!-- Licenses List -->
            <div v-if="licenses.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div 
                    v-for="license in licenses" 
                    :key="license.id" 
                    class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between"
                >
                    <div class="p-6 space-y-5">
                        <!-- License Header -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center shadow-xs">
                                    <span class="material-symbols-rounded">terminal</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">{{ license.plan }} Plan License</span>
                                    <h4 class="text-sm font-bold text-gray-900 font-mono mt-0.5">{{ license.license_key }}</h4>
                                </div>
                            </div>
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                                :class="[
                                    license.status === 'active' 
                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                        : 'bg-red-50 border-red-200 text-red-700'
                                ]"
                            >
                                {{ license.status }}
                            </span>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-xl">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Connected Server IP</div>
                                <div class="text-xs text-gray-900 font-mono font-bold truncate">{{ license.server_ip || 'Awaiting curl install...' }}</div>
                            </div>
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-xl">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Expires / Term</div>
                                <div class="text-xs text-gray-900 font-semibold truncate">{{ formatDateTime(license.expires_at) }}</div>
                            </div>
                        </div>

                        <!-- 1-Line Installation Command -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">1-Line Installation Command</label>
                                <span class="text-[10px] text-gray-400">Run on Ubuntu 22.04+ or Debian 11+</span>
                            </div>
                            <div class="bg-slate-950 text-slate-200 rounded-xl p-3 flex items-center justify-between gap-3 border border-slate-800">
                                <code class="text-xs text-emerald-300 font-mono block break-all select-all flex-1">
                                    {{ getInstallCommand(license.license_key) }}
                                </code>
                                <button 
                                    @click="copyToClipboard(getInstallCommand(license.license_key))" 
                                    class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors"
                                    title="Copy Command"
                                >
                                    <span class="material-symbols-rounded text-sm">content_copy</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div v-if="license.status === 'active'" class="bg-slate-50 px-6 py-3.5 border-t border-gray-100 flex items-center gap-3">
                        <button 
                            @click="disconnectMachine(license)" 
                            :disabled="!license.machine_id && !license.server_ip"
                            :class="[
                                (!license.machine_id && !license.server_ip) 
                                    ? 'text-gray-400 bg-transparent border-gray-200 cursor-not-allowed opacity-50' 
                                    : 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100'
                            ]"
                            class="flex-1 px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all flex items-center justify-center gap-1.5"
                        >
                            <span class="material-symbols-rounded text-sm">phonelink_off</span>
                            Disconnect IP
                        </button>
                        <button 
                            @click="revokeLicense(license)"
                            class="flex-1 px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider bg-red-50 border border-red-200 text-red-650 hover:bg-red-100 transition-all flex items-center justify-center gap-1.5"
                        >
                            <span class="material-symbols-rounded text-sm">cancel</span>
                            Revoke
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white border border-dashed border-gray-300 rounded-2xl p-14 text-center">
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">terminal</span>
                </div>
                <h3 class="text-sm font-bold text-gray-950">No Self-Hosted Licenses Yet</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Claim your free forever starter license or order a multi-server package to begin self-hosting Nimbus on your servers.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <button 
                        @click="generateFreeLicense" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm"
                    >
                        Claim Free License
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=self_hosted'" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-gray-800 rounded-xl text-xs font-bold"
                    >
                        Browse Licenses
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
