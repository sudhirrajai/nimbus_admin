<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    accounts: Array,
    requests: Array,
    managedPlans: Array,
});

const showRequestModal = ref(false);
const requestForm = useForm({
    domain: '',
    plan_requested: 'Starter Cloud',
    estimated_traffic: 'Under 50,000 visitors/mo',
    notes: '',
});

const submitHostingRequest = () => {
    requestForm.post(route('hosting.request.submit'), {
        onSuccess: () => {
            showRequestModal.value = false;
            requestForm.reset();
        }
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'Active';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Managed Cloud Hosting" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-blue-600">cloud_done</span>
                        Fully Managed Cloud Hosting
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        High-performance cloud servers fully maintained, secured, and backed up by VMCORE engineers.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="showRequestModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2 active:scale-95"
                    >
                        <span class="material-symbols-rounded text-base">add_circle</span>
                        Request New Instance
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=managed_hosting'" 
                        class="bg-slate-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-base">shopping_cart</span>
                        View Cloud Packages
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message -->
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
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Cloud Instances</div>
                    <div class="text-2xl font-black text-gray-950 mt-1">{{ accounts.length }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Active managed production nodes</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">System Uptime SLA</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">99.9%</div>
                    <div class="text-xs text-gray-500 mt-0.5">Proactively monitored 24/7</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending Requests</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">
                        {{ requests.filter(r => r.status === 'pending').length }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Provisioning queue</div>
                </div>
            </div>

            <!-- Active Cloud Accounts Grid -->
            <div v-if="accounts.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div 
                    v-for="account in accounts" 
                    :key="account.id"
                    class="bg-white border-2 border-blue-100 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden"
                >
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20">
                                    <span class="material-symbols-rounded text-2xl">cloud</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ account.plan_name }}</span>
                                    <h4 class="text-base font-bold text-gray-950 font-mono mt-0.5">{{ account.domain }}</h4>
                                </div>
                            </div>
                            <span 
                                :class="{
                                    'bg-emerald-50 text-emerald-700 border-emerald-200': account.status === 'active',
                                    'bg-amber-50 text-amber-700 border-amber-200': account.status === 'suspended',
                                    'bg-rose-50 text-rose-700 border-rose-200': account.status === 'terminated'
                                }"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                            >
                                {{ account.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-xl">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Node IP / Host</div>
                                <div class="text-xs text-gray-900 font-mono font-bold">{{ account.server?.ip_address || 'Dedicated VPS' }}</div>
                            </div>
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-xl">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Renewal Due Date</div>
                                <div class="text-xs text-blue-700 font-semibold">{{ formatDate(account.renews_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 1-Click Login to Nimbus Action -->
                    <div class="pt-5 mt-5 border-t border-gray-100 flex items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            Single sign-on access to your website's control panel.
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold tracking-wide uppercase transition-all shadow-md shadow-blue-600/20 shrink-0"
                        >
                            <span class="material-symbols-rounded text-sm">login</span>
                            1-Click Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white border border-dashed border-gray-300 rounded-2xl p-14 text-center">
                <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">cloud_sync</span>
                </div>
                <h3 class="text-sm font-bold text-gray-950">No Managed Hosting Instances</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Let VMCORE manage your high-speed cloud instances so you never have to configure Linux or manage server crashes.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <button 
                        @click="showRequestModal = true"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm"
                    >
                        Request Cloud Instance
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=managed_hosting'" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-gray-800 rounded-xl text-xs font-bold"
                    >
                        Browse Cloud Packages
                    </Link>
                </div>
            </div>

            <!-- Pending Requests Section -->
            <div v-if="requests.length > 0" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Submitted Hosting Requests</h3>
                <div class="divide-y divide-gray-100">
                    <div 
                        v-for="req in requests" 
                        :key="req.id"
                        class="py-3 flex items-center justify-between gap-4"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-rounded text-gray-400">pending_actions</span>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-gray-900 truncate">
                                    {{ req.domain || 'Cloud VPS' }}
                                </div>
                                <div class="text-[11px] text-gray-500 truncate">
                                    {{ req.plan_requested }} • Submitted {{ new Date(req.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                        <span 
                            :class="{
                                'bg-amber-50 text-amber-700 border-amber-200': req.status === 'pending',
                                'bg-emerald-50 text-emerald-700 border-emerald-200': req.status === 'approved' || req.status === 'fulfilled',
                                'bg-rose-50 text-rose-700 border-rose-200': req.status === 'rejected'
                            }"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
                        >
                            {{ req.status === 'pending' ? 'In Provisioning' : req.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Managed Hosting Modal -->
        <div v-if="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 w-full max-w-lg shadow-2xl animate-fade-in relative text-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">cloud_upload</span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm">Request Managed Cloud Hosting</h3>
                    </div>
                    <button @click="showRequestModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitHostingRequest" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Target Website Domain *</label>
                        <input 
                            type="text" 
                            v-model="requestForm.domain" 
                            placeholder="e.g. clientportal.com or app.mybrand.io" 
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                            required 
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Preferred Cloud Plan</label>
                        <select v-model="requestForm.plan_requested" class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="Starter Cloud">Starter Cloud (1 vCPU, 2GB RAM, 30GB NVMe)</option>
                            <option value="Business Cloud">Business Cloud (2 vCPU, 4GB RAM, 80GB NVMe)</option>
                            <option value="Enterprise Cloud">Enterprise Cloud (4 vCPU, 8GB RAM, 160GB NVMe)</option>
                            <option value="Custom Enterprise Cluster">Custom Enterprise Cluster</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Estimated Monthly Traffic</label>
                        <select v-model="requestForm.estimated_traffic" class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="Under 50,000 visitors/mo">Under 50,000 visitors/mo</option>
                            <option value="50,000 - 250,000 visitors/mo">50,000 - 250,000 visitors/mo</option>
                            <option value="250,000 - 1,000,000 visitors/mo">250,000 - 1,000,000 visitors/mo</option>
                            <option value="Over 1M+ visitors/mo (High Traffic)">Over 1M+ visitors/mo (High Traffic)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Additional Requirements / Notes</label>
                        <textarea 
                            v-model="requestForm.notes" 
                            rows="3" 
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs p-3 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                            placeholder="Need Redis cache, Node.js background workers, custom PHP extensions, etc."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showRequestModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit" :disabled="requestForm.processing" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">
                            {{ requestForm.processing ? 'Submitting...' : 'Submit Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
