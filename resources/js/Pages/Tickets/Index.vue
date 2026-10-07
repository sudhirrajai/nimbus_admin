<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tickets: Object, // paginated
    metrics: Object,
    filters: Object,
    userServices: Object,
});

const isCreateModalOpen = ref(false);

const form = useForm({
    subject: '',
    category: 'technical',
    priority: 'medium',
    message: '',
    attachment: null,
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateModalOpen.value = true;
};

const handleFileChange = (e) => {
    form.attachment = e.target.files[0] || null;
};

const submitTicket = () => {
    form.post(route('tickets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateModalOpen.value = false;
            form.reset();
        },
    });
};

const setStatusFilter = (status) => {
    router.get(route('tickets.index'), { status }, { preserveState: true, preserveScroll: true });
};

const categoryLabels = {
    technical: 'Technical Issue',
    managed_hosting: 'Managed Hosting',
    billing: 'Billing & Invoice',
    license: 'License Key',
    feature_request: 'Feature Request',
    general: 'General Inquiry',
};

const statusClasses = {
    open: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    in_progress: 'bg-blue-50 text-blue-700 border-blue-200',
    answered: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    resolved: 'bg-purple-50 text-purple-700 border-purple-200',
    closed: 'bg-gray-100 text-gray-700 border-gray-200',
};

const priorityClasses = {
    low: 'bg-slate-100 text-slate-700 border-slate-200',
    medium: 'bg-blue-50 text-blue-700 border-blue-200',
    high: 'bg-amber-50 text-amber-700 border-amber-200',
    urgent: 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse',
};
</script>

<template>
    <Head title="Support Tickets - Roook" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <!-- Top Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                        <span class="material-symbols-rounded text-emerald-600 text-3xl">support_agent</span>
                        Support Tickets
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Submit technical questions, server incident reports, or billing requests directly to Roook engineers.
                    </p>
                </div>
                <div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-sm transition-all hover:scale-[1.02] cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-lg">add_circle</span>
                        Open New Ticket
                    </button>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Total Tickets</span>
                        <span class="material-symbols-rounded text-slate-500 text-xl">confirmation_number</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900">{{ metrics.total }}</div>
                    <div class="text-xs text-gray-500 mt-1">All time support requests</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Open / Pending</span>
                        <span class="material-symbols-rounded text-emerald-600 text-xl">pending_actions</span>
                    </div>
                    <div class="text-3xl font-extrabold text-emerald-600">{{ metrics.open }}</div>
                    <div class="text-xs text-emerald-700 mt-1 font-medium">Currently active with support team</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Resolved</span>
                        <span class="material-symbols-rounded text-purple-600 text-xl">task_alt</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900">{{ metrics.resolved }}</div>
                    <div class="text-xs text-purple-600 mt-1 font-medium">Successfully completed issues</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="border-b border-gray-200 overflow-x-auto pb-1 sm:pb-0">
                <nav class="flex space-x-4 sm:space-x-6 min-w-max" aria-label="Tabs">
                    <button
                        @click="setStatusFilter('all')"
                        :class="[
                            filters.status === 'all'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-1.5 cursor-pointer transition-colors"
                    >
                        All Tickets ({{ metrics.total }})
                    </button>
                    <button
                        @click="setStatusFilter('open')"
                        :class="[
                            filters.status === 'open'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-1.5 cursor-pointer transition-colors"
                    >
                        Open ({{ metrics.open }})
                    </button>
                    <button
                        @click="setStatusFilter('resolved')"
                        :class="[
                            filters.status === 'resolved'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-1.5 cursor-pointer transition-colors"
                    >
                        Resolved ({{ metrics.resolved }})
                    </button>
                </nav>
            </div>

            <!-- Tickets List -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
                <div v-if="tickets.data.length === 0" class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">check_circle</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Support Tickets Found</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                        You don't have any tickets under this filter. If you're experiencing any issues with your cloud servers or panel, open a ticket anytime!
                    </p>
                    <button
                        @click="openCreateModal"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 transition-all cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-lg">add</span>
                        Open Support Ticket
                    </button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3.5">Ticket #</th>
                                <th class="px-6 py-3.5">Subject</th>
                                <th class="px-6 py-3.5">Category</th>
                                <th class="px-6 py-3.5">Priority</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Last Activity</th>
                                <th class="px-6 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            <tr
                                v-for="ticket in tickets.data"
                                :key="ticket.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-emerald-700">
                                    #{{ ticket.ticket_number }}
                                </td>

                                <td class="px-6 py-4 max-w-xs sm:max-w-md">
                                    <Link
                                        :href="route('tickets.show', { ticket: ticket.id })"
                                        class="font-bold text-gray-900 hover:text-emerald-600 transition-colors line-clamp-1 block"
                                    >
                                        {{ ticket.subject }}
                                    </Link>
                                    <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                                        <span>{{ ticket.public_messages_count }} {{ ticket.public_messages_count === 1 ? 'message' : 'messages' }}</span>
                                        <span v-if="ticket.last_replier">&bull; Last by {{ ticket.last_replier.name }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                    {{ categoryLabels[ticket.category] || ticket.category }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="priorityClasses[ticket.priority] || 'bg-gray-100 text-gray-700'"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border"
                                    >
                                        {{ ticket.priority.toUpperCase() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="statusClasses[ticket.status] || 'bg-gray-100 text-gray-700'"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="ticket.status === 'resolved' ? 'bg-purple-500' : 'bg-emerald-500'"></span>
                                        {{ ticket.status.replace('_', ' ').toUpperCase() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ new Date(ticket.last_reply_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <Link
                                        :href="route('tickets.show', { ticket: ticket.id })"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-gray-800 text-xs font-bold transition-colors"
                                    >
                                        View Thread &rarr;
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL: Open New Ticket -->
            <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
                <div class="bg-white rounded-2xl max-w-xl w-full p-5 sm:p-7 my-auto max-h-[90vh] flex flex-col shadow-2xl space-y-4 sm:space-y-5 overflow-hidden">
                    
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-rounded text-xl">confirmation_number</span>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Open Support Ticket</h3>
                                <p class="text-xs text-gray-500">Describe your issue in detail so our engineers can assist rapidly.</p>
                            </div>
                        </div>
                        <button @click="isCreateModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <form @submit.prevent="submitTicket" class="space-y-4 overflow-y-auto">
                        
                        <!-- Subject -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Subject / Issue Title *</label>
                            <input
                                type="text"
                                v-model="form.subject"
                                placeholder="e.g. SSL renewal failure on primary domain / High CPU usage"
                                class="w-full border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            />
                            <div v-if="form.errors.subject" class="text-xs text-rose-500 mt-1">{{ form.errors.subject }}</div>
                        </div>

                        <!-- Category & Priority -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category *</label>
                                <select
                                    v-model="form.category"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500"
                                >
                                    <option value="technical">Technical Support</option>
                                    <option value="managed_hosting">Managed Cloud Hosting</option>
                                    <option value="billing">Billing &amp; Invoices</option>
                                    <option value="license">License Key / Activation</option>
                                    <option value="feature_request">Feature Request</option>
                                    <option value="general">General Inquiry</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Priority *</label>
                                <select
                                    v-model="form.priority"
                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500"
                                >
                                    <option value="low">Low (General question)</option>
                                    <option value="medium">Medium (Standard issue)</option>
                                    <option value="high">High (Service degraded)</option>
                                    <option value="urgent">Urgent (Server downtime)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Problem Description &amp; Details *</label>
                            <textarea
                                v-model="form.message"
                                rows="5"
                                placeholder="Please include steps to reproduce, relevant domain names, error messages, or logs..."
                                class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 leading-relaxed"
                            ></textarea>
                            <div v-if="form.errors.message" class="text-xs text-rose-500 mt-1">{{ form.errors.message }}</div>
                        </div>

                        <!-- File Attachment -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Screenshot / Log Attachment (Optional)</label>
                            <input
                                type="file"
                                @change="handleFileChange"
                                class="w-full border border-gray-200 rounded-xl p-2 text-xs text-gray-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                            />
                            <p class="text-[11px] text-gray-400 mt-1">Allowed: PNG, JPG, PDF, TXT, LOG, ZIP (Max 10MB)</p>
                            <div v-if="form.errors.attachment" class="text-xs text-rose-500 mt-1">{{ form.errors.attachment }}</div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="isCreateModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-100 cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                                <span v-else class="material-symbols-rounded text-sm">send</span>
                                <span>{{ form.processing ? 'Submitting...' : 'Submit Support Ticket' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
