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
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                        Managed Cloud Hosting
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        High-performance cloud servers fully maintained, secured, and backed up by VMCORE engineers.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <button 
                        @click="showRequestModal = true"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2 cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-sm">add_circle</span>
                        Request New Instance
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=managed_hosting'" 
                        class="bg-slate-100 hover:bg-slate-200 text-gray-800 border border-gray-200 px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                    >
                        <span class="material-symbols-rounded text-sm">shopping_cart</span>
                        View Cloud Packages
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Message -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p>{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p>{{ $page.props.errors.error }}</p>
                </div>
            </div>

            <!-- Stats Bar (Original Nimbus Style) -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cloud Instances</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">cloud_done</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">{{ accounts.length }}</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">System Uptime SLA</span>
                        <span class="material-symbols-rounded text-emerald-500 text-xl">verified</span>
                    </div>
                    <div class="text-2xl font-bold text-emerald-600 mt-2">99.9%</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Requests</span>
                        <span class="material-symbols-rounded text-gray-400 text-xl">pending_actions</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">
                        {{ requests.filter(r => r.status === 'pending').length }}
                    </div>
                </div>
            </div>

            <!-- Active Cloud Accounts Grid -->
            <div v-if="accounts.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div 
                    v-for="account in accounts" 
                    :key="account.id"
                    class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col justify-between relative"
                >
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                                    <span class="material-symbols-rounded text-lg">cloud</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">{{ account.plan_name }}</span>
                                    <h4 class="text-base font-bold text-gray-900 font-mono mt-0.5">{{ account.domain }}</h4>
                                </div>
                            </div>
                            <span 
                                :class="{
                                    'bg-emerald-50 text-emerald-700 border-emerald-200': account.status === 'active',
                                    'bg-amber-50 text-amber-700 border-amber-200': account.status === 'suspended',
                                    'bg-red-50 text-red-700 border-red-200': account.status === 'terminated'
                                }"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                            >
                                {{ account.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Node IP / Host</div>
                                <div class="text-xs text-gray-900 font-mono font-bold truncate">{{ account.server?.ip_address || 'Dedicated VPS' }}</div>
                            </div>
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Renewal Due Date</div>
                                <div class="text-xs text-emerald-700 font-semibold">{{ formatDate(account.renews_at) }}</div>
                            </div>
                            <div class="bg-slate-50 p-3.5 border border-gray-200 rounded-lg">
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Live Website Health</div>
                                <div v-if="account.uptime_status === 'up'" class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>200 OK</span>
                                    <span v-if="account.uptime_response_time_ms" class="text-[11px] font-mono text-gray-400 font-normal">({{ account.uptime_response_time_ms }}ms)</span>
                                </div>
                                <div v-else-if="account.uptime_status === 'down'" class="flex items-center gap-1.5 text-xs font-bold text-rose-600" :title="account.uptime_last_error">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>Down / Alert</span>
                                </div>
                                <div v-else class="flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span>Monitoring Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 1-Click Login to Nimbus Action -->
                    <div class="pt-5 mt-5 border-t border-gray-200 flex items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            Single sign-on access to your website's control panel.
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm shrink-0"
                        >
                            <span class="material-symbols-rounded text-sm">login</span>
                            1-Click Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white border border-dashed border-gray-300 rounded-lg p-12 text-center shadow-sm">
                <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">cloud_done</span>
                </div>
                <h3 class="text-sm font-bold text-gray-900">No Managed Hosting Instances</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Let VMCORE manage your high-speed cloud instances so you never have to configure Linux or manage server crashes.
                </p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <button 
                        @click="showRequestModal = true"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold uppercase tracking-wide transition-all shadow-sm"
                    >
                        Request Cloud Instance
                    </button>
                    <Link 
                        :href="route('store.index') + '?tab=managed_hosting'" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-gray-800 border border-gray-200 rounded-lg text-xs font-semibold uppercase tracking-wide transition-all"
                    >
                        Browse Packages
                    </Link>
                </div>
            </div>

            <!-- Pending Requests Section -->
            <div v-if="requests.length > 0" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">pending_actions</span>
                        <h3 class="text-sm font-bold text-gray-900">Submitted Hosting Requests</h3>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    <div 
                        v-for="req in requests" 
                        :key="req.id" 
                        class="py-3 flex items-center justify-between gap-4"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="material-symbols-rounded text-gray-400">dns</span>
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
                                'bg-red-50 text-red-700 border-red-200': req.status === 'rejected'
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
        <div v-if="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div class="bg-white border border-gray-200 rounded-xl p-6 w-full max-w-lg shadow-2xl animate-fade-in relative text-gray-900">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600">cloud_upload</span>
                            Request Managed Cloud Hosting
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Let our engineers set up and configure high-performance Nimbus hosting.</p>
                    </div>
                    <button @click="showRequestModal = false" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitHostingRequest" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Target Website Domain *</label>
                        <input 
                            type="text" 
                            v-model="requestForm.domain" 
                            placeholder="e.g. clientportal.com or app.mybrand.io" 
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5 font-mono" 
                            required 
                        />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Preferred Cloud Plan</label>
                        <select v-model="requestForm.plan_requested" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5">
                            <option value="Starter Cloud">Starter Cloud (1 vCPU, 2GB RAM, 30GB NVMe)</option>
                            <option value="Business Cloud">Business Cloud (2 vCPU, 4GB RAM, 80GB NVMe)</option>
                            <option value="Enterprise Cloud">Enterprise Cloud (4 vCPU, 8GB RAM, 160GB NVMe)</option>
                            <option value="Custom Enterprise Cluster">Custom Enterprise Cluster</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Estimated Monthly Traffic</label>
                        <select v-model="requestForm.estimated_traffic" class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5">
                            <option value="Under 50,000 visitors/mo">Under 50,000 visitors/mo</option>
                            <option value="50,000 - 250,000 visitors/mo">50,000 - 250,000 visitors/mo</option>
                            <option value="250,000 - 1,000,000 visitors/mo">250,000 - 1,000,000 visitors/mo</option>
                            <option value="Over 1M+ visitors/mo">Over 1M+ visitors/mo (High Traffic)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Additional Requirements / Notes</label>
                        <textarea 
                            v-model="requestForm.notes" 
                            rows="3" 
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm p-2.5" 
                            placeholder="Need Redis cache, Node.js background workers, custom PHP extensions, etc."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showRequestModal = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                        <button type="submit" :disabled="requestForm.processing" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg shadow-sm">
                            {{ requestForm.processing ? 'Submitting...' : 'Submit Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
