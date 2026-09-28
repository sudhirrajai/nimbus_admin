<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    tickets: Object, // paginated
    metrics: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const priority = ref(props.filters.priority || 'all');
const category = ref(props.filters.category || 'all');

let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.tickets.index'),
        {
            search: search.value,
            status: status.value,
            priority: priority.value,
            category: category.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

const onFilterChange = () => {
    applyFilters();
};

const updateStatusQuick = (ticket, newStatus) => {
    router.patch(
        route('admin.tickets.update-status', { ticket: ticket.id }),
        { status: newStatus },
        { preserveScroll: true }
    );
};

const deleteTicket = (ticket) => {
    if (confirm(`Are you sure you want to permanently delete Ticket #${ticket.ticket_number}?`)) {
        router.delete(route('admin.tickets.destroy', { ticket: ticket.id }), {
            preserveScroll: true,
        });
    }
};

const categoryLabels = {
    technical: 'Technical',
    managed_hosting: 'Managed Hosting',
    billing: 'Billing',
    license: 'License',
    feature_request: 'Feature Request',
    general: 'General',
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
    <Head title="Support Tickets Management - Admin Nimbus" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <!-- Top Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                        <span class="material-symbols-rounded text-emerald-600 text-3xl">support_agent</span>
                        Support Tickets Management
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Respond to client inquiries, investigate server issues, manage priority tickets, and mark solutions as resolved.
                    </p>
                </div>
            </div>

            <!-- Metrics Row -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Total</span>
                        <span class="material-symbols-rounded text-slate-400 text-xl">confirmation_number</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ metrics.total }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total requests filed</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Open</span>
                        <span class="material-symbols-rounded text-emerald-600 text-xl">mark_email_unread</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600">{{ metrics.open }}</div>
                    <div class="text-xs text-emerald-700 mt-1 font-medium">Awaiting staff review</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">In Progress</span>
                        <span class="material-symbols-rounded text-blue-600 text-xl">sync</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-blue-600">{{ metrics.in_progress }}</div>
                    <div class="text-xs text-blue-600 mt-1">Under investigation</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Answered</span>
                        <span class="material-symbols-rounded text-indigo-600 text-xl">reply_all</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600">{{ metrics.answered }}</div>
                    <div class="text-xs text-indigo-600 mt-1">Awaiting client response</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Resolved</span>
                        <span class="material-symbols-rounded text-purple-600 text-xl">task_alt</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-extrabold text-purple-600">{{ metrics.resolved }}</div>
                    <div class="text-xs text-purple-600 mt-1 font-medium">Solved tickets</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white border border-gray-200 rounded-2xl p-3 sm:p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="relative w-full md:w-80">
                    <span class="material-symbols-rounded absolute left-3 top-2.5 text-gray-400 text-lg">search</span>
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search ticket #, subject, client, email..."
                        class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 w-full md:w-auto">
                    <select
                        v-model="status"
                        @change="onFilterChange"
                        class="border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500"
                    >
                        <option value="all">All Statuses</option>
                        <option value="active">Active (Open/Prog/Ans)</option>
                        <option value="open">Open Only</option>
                        <option value="in_progress">In Progress</option>
                        <option value="answered">Answered</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>

                    <select
                        v-model="priority"
                        @change="onFilterChange"
                        class="border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500"
                    >
                        <option value="all">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>

                    <select
                        v-model="category"
                        @change="onFilterChange"
                        class="border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:outline-none focus:border-emerald-500"
                    >
                        <option value="all">All Categories</option>
                        <option value="technical">Technical</option>
                        <option value="managed_hosting">Managed Hosting</option>
                        <option value="billing">Billing</option>
                        <option value="license">License</option>
                        <option value="feature_request">Feature Request</option>
                        <option value="general">General</option>
                    </select>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
                <div v-if="tickets.data.length === 0" class="p-12 text-center text-gray-500">
                    <span class="material-symbols-rounded text-3xl text-gray-400 block mb-1">sentiment_satisfied</span>
                    No tickets found matching current filters.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3.5">Ticket #</th>
                                <th class="px-6 py-3.5">Client</th>
                                <th class="px-6 py-3.5">Subject</th>
                                <th class="px-6 py-3.5">Category</th>
                                <th class="px-6 py-3.5">Priority</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Last Activity</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            <tr
                                v-for="ticket in tickets.data"
                                :key="ticket.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-emerald-700">
                                    <Link :href="route('admin.tickets.show', { ticket: ticket.id })" class="hover:underline">
                                        #{{ ticket.ticket_number }}
                                    </Link>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ ticket.user?.name || 'User' }}</div>
                                    <div class="text-xs text-gray-500">{{ ticket.user?.email }}</div>
                                    <div v-if="ticket.user?.company_name" class="text-[11px] text-gray-400">
                                        {{ ticket.user.company_name }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 max-w-xs sm:max-w-sm">
                                    <Link
                                        :href="route('admin.tickets.show', { ticket: ticket.id })"
                                        class="font-bold text-gray-900 hover:text-emerald-600 transition-colors line-clamp-1 block"
                                    >
                                        {{ ticket.subject }}
                                    </Link>
                                    <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                        <span>{{ ticket.messages_count }} messages</span>
                                        <span v-if="ticket.last_replier">&bull; Last: {{ ticket.last_replier.name }}</span>
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
                                    <select
                                        :value="ticket.status"
                                        @change="updateStatusQuick(ticket, $event.target.value)"
                                        :class="statusClasses[ticket.status] || 'bg-gray-100 text-gray-700'"
                                        class="border rounded-full text-xs font-bold px-2 py-0.5 focus:outline-none cursor-pointer"
                                    >
                                        <option value="open">OPEN</option>
                                        <option value="in_progress">IN PROGRESS</option>
                                        <option value="answered">ANSWERED</option>
                                        <option value="resolved">RESOLVED</option>
                                        <option value="closed">CLOSED</option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ new Date(ticket.last_reply_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.tickets.show', { ticket: ticket.id })"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition-colors"
                                        >
                                            Manage &rarr;
                                        </Link>
                                        <button
                                            @click="deleteTicket(ticket)"
                                            title="Delete ticket"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                        >
                                            <span class="material-symbols-rounded text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
