<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object,
});

const isInternalMode = ref(false);

const replyForm = useForm({
    message: '',
    is_internal: false,
    status: 'answered',
    attachment: null,
});

const handleFileChange = (e) => {
    replyForm.attachment = e.target.files[0] || null;
};

const submitReply = () => {
    replyForm.is_internal = isInternalMode.value;
    replyForm.post(route('admin.tickets.reply', { ticket: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            const fileInput = document.getElementById('admin-attachment-input');
            if (fileInput) fileInput.value = '';
        },
    });
};

const markResolved = () => {
    router.patch(
        route('admin.tickets.update-status', { ticket: props.ticket.id }),
        { status: 'resolved' },
        { preserveScroll: true }
    );
};

const updatePriority = (newPriority) => {
    router.patch(
        route('admin.tickets.update-priority', { ticket: props.ticket.id }),
        { priority: newPriority },
        { preserveScroll: true }
    );
};

const updateStatus = (newStatus) => {
    router.patch(
        route('admin.tickets.update-status', { ticket: props.ticket.id }),
        { status: newStatus },
        { preserveScroll: true }
    );
};

const categoryLabels = {
    technical: 'Technical Support',
    managed_hosting: 'Managed Hosting',
    billing: 'Billing & Invoices',
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
    <Head :title="`Admin: [#${ticket.ticket_number}] ${ticket.subject}`" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <!-- Breadcrumbs -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('admin.tickets.index')"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors"
                >
                    <span class="material-symbols-rounded text-base">arrow_back</span>
                    Back to All Support Tickets
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        v-if="ticket.status !== 'resolved'"
                        @click="markResolved"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-xs transition-all cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-sm">task_alt</span>
                        Mark as Solved &amp; Resolved
                    </button>
                </div>
            </div>

            <!-- Main Layout Grid: Left Conversation, Right Client Info -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left 2 Cols: Ticket Header, Thread & Reply -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Ticket Header Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                                    #{{ ticket.ticket_number }}
                                </span>
                                <span
                                    :class="statusClasses[ticket.status] || 'bg-gray-100 text-gray-700'"
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                >
                                    {{ ticket.status.replace('_', ' ').toUpperCase() }}
                                </span>
                                <span
                                    :class="priorityClasses[ticket.priority] || 'bg-gray-100 text-gray-700'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border"
                                >
                                    {{ ticket.priority.toUpperCase() }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-400">
                                Opened: {{ new Date(ticket.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                            </div>
                        </div>

                        <h1 class="text-xl font-bold text-gray-900 leading-snug">
                            {{ ticket.subject }}
                        </h1>

                        <div class="text-xs text-gray-500 flex items-center gap-4 pt-3 border-t border-gray-100">
                            <span>Category: <strong>{{ categoryLabels[ticket.category] || ticket.category }}</strong></span>
                            <span v-if="ticket.resolved_at" class="text-purple-700 font-semibold flex items-center gap-1">
                                <span class="material-symbols-rounded text-sm">verified</span>
                                Solved on {{ new Date(ticket.resolved_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </div>

                    <!-- Messages Timeline -->
                    <div class="space-y-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 px-1">
                            Conversation &amp; Notes ({{ ticket.messages?.length || 0 }})
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="msg in ticket.messages"
                                :key="msg.id"
                                class="rounded-2xl p-5 border transition-all"
                                :class="[
                                    msg.is_internal
                                        ? 'bg-amber-50/70 border-amber-200 text-amber-950 shadow-xs'
                                        : (msg.user?.is_admin
                                            ? 'bg-gradient-to-r from-emerald-50/70 via-teal-50/40 to-white border-emerald-200/80 shadow-xs'
                                            : 'bg-white border-gray-200 shadow-xs')
                                ]"
                            >
                                <!-- Author Header -->
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                                            :class="[
                                                msg.is_internal
                                                    ? 'bg-amber-500 text-white'
                                                    : (msg.user?.is_admin
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'bg-slate-200 text-slate-700')
                                            ]"
                                        >
                                            <span v-if="msg.is_internal" class="material-symbols-rounded text-sm">lock</span>
                                            <span v-else-if="msg.user?.is_admin" class="material-symbols-rounded text-sm">verified_user</span>
                                            <span v-else>{{ (msg.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                                {{ msg.user?.name }}
                                                <span
                                                    v-if="msg.is_internal"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900"
                                                >
                                                    <span class="material-symbols-rounded text-xs">lock</span>
                                                    INTERNAL STAFF NOTE (Hidden from client)
                                                </span>
                                                <span
                                                    v-else-if="msg.user?.is_admin"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
                                                >
                                                    Staff Reply
                                                </span>
                                                <span
                                                    v-else
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700"
                                                >
                                                    Client
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-gray-400">
                                                {{ new Date(msg.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="text-sm whitespace-pre-wrap leading-relaxed text-gray-800">
                                    {{ msg.message }}
                                </div>

                                <!-- Attachments -->
                                <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-4 pt-3 border-t border-gray-100/80 space-y-1">
                                    <div class="text-xs font-semibold text-gray-500">Attachments:</div>
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-for="(att, attIdx) in msg.attachments"
                                            :key="attIdx"
                                            :href="att.path"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/80 border border-gray-200 text-xs font-medium text-slate-800 hover:bg-white transition-colors"
                                        >
                                            <span class="material-symbols-rounded text-sm text-gray-500">attachment</span>
                                            <span>{{ att.name }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Reply & Internal Note Box -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-4">
                        
                        <!-- Toggle Mode Tabs -->
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    @click="isInternalMode = false"
                                    :class="[
                                        !isInternalMode
                                            ? 'bg-emerald-50 text-emerald-700 font-bold border-emerald-300'
                                            : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'
                                    ]"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-sm">reply</span>
                                    Reply to Client (Sends Email)
                                </button>

                                <button
                                    type="button"
                                    @click="isInternalMode = true"
                                    :class="[
                                        isInternalMode
                                            ? 'bg-amber-100 text-amber-900 font-bold border-amber-300'
                                            : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200'
                                    ]"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-sm">lock</span>
                                    Internal Admin Note (Private)
                                </button>
                            </div>

                            <div v-if="!isInternalMode" class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Status After:</span>
                                <select
                                    v-model="replyForm.status"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none"
                                >
                                    <option value="answered">Answered</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved (Solved)</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitReply" class="space-y-4">
                            <textarea
                                v-model="replyForm.message"
                                rows="5"
                                :placeholder="isInternalMode ? 'Write private staff notes here (visible only to administrators)...' : 'Write a detailed response to help the client...'"
                                class="w-full border rounded-xl px-4 py-3 text-sm focus:outline-none leading-relaxed"
                                :class="[
                                    isInternalMode
                                        ? 'bg-amber-50/40 border-amber-200 focus:border-amber-400 focus:ring-1 focus:ring-amber-400'
                                        : 'border-gray-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500'
                                ]"
                            ></textarea>
                            <div v-if="replyForm.errors.message" class="text-xs text-rose-500">{{ replyForm.errors.message }}</div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                                <div class="flex-1 max-w-sm">
                                    <input
                                        id="admin-attachment-input"
                                        type="file"
                                        @change="handleFileChange"
                                        class="w-full border border-gray-200 rounded-xl p-1.5 text-xs text-gray-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="replyForm.processing || !replyForm.message.trim()"
                                    class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 rounded-xl text-white font-bold text-xs shadow-xs transition-all cursor-pointer disabled:opacity-50"
                                    :class="isInternalMode ? 'bg-amber-600 hover:bg-amber-500' : 'bg-emerald-600 hover:bg-emerald-500'"
                                >
                                    <span v-if="replyForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                                    <span v-else class="material-symbols-rounded text-sm">
                                        {{ isInternalMode ? 'note_add' : 'send' }}
                                    </span>
                                    <span>
                                        {{ replyForm.processing ? 'Posting...' : (isInternalMode ? 'Save Internal Note' : 'Dispatch Staff Reply') }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Right Col: Client Overview & Quick Actions Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Ticket Controls Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs space-y-4">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Ticket Management</h3>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                            <select
                                :value="ticket.status"
                                @change="updateStatus($event.target.value)"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-emerald-500 cursor-pointer"
                            >
                                <option value="open">Open (Needs Attention)</option>
                                <option value="in_progress">In Progress</option>
                                <option value="answered">Answered (Awaiting Client)</option>
                                <option value="resolved">Resolved (Solved)</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                            <select
                                :value="ticket.priority"
                                @change="updatePriority($event.target.value)"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-emerald-500 cursor-pointer"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <!-- Client Information Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Client Profile</h3>
                            <span class="material-symbols-rounded text-gray-400 text-lg">person</span>
                        </div>

                        <div class="space-y-2.5 text-xs text-gray-600">
                            <div>
                                <div class="font-bold text-sm text-gray-900">{{ ticket.user?.name }}</div>
                                <div class="text-gray-500">{{ ticket.user?.email }}</div>
                            </div>

                            <div v-if="ticket.user?.company_name">
                                <span class="text-gray-400 block">Company:</span>
                                <span class="font-semibold text-gray-800">{{ ticket.user.company_name }}</span>
                            </div>

                            <div v-if="ticket.user?.phone">
                                <span class="text-gray-400 block">Phone:</span>
                                <span class="font-semibold text-gray-800">{{ ticket.user.phone }}</span>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-3 gap-2 text-center">
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Licenses</div>
                                    <div class="text-sm font-extrabold text-gray-900">{{ ticket.user?.licenses_count || 0 }}</div>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Hosting</div>
                                    <div class="text-sm font-extrabold text-gray-900">{{ ticket.user?.hosting_accounts_count || 0 }}</div>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg">
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Invoices</div>
                                    <div class="text-sm font-extrabold text-gray-900">{{ ticket.user?.invoices_count || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
