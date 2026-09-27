<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object,
});

const replyForm = useForm({
    message: '',
    attachment: null,
});

const handleFileChange = (e) => {
    replyForm.attachment = e.target.files[0] || null;
};

const submitReply = () => {
    replyForm.post(route('tickets.reply', { ticket: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            const fileInput = document.getElementById('reply-attachment-input');
            if (fileInput) fileInput.value = '';
        },
    });
};

const closeTicket = () => {
    if (confirm('Are you sure your issue is resolved and you want to close this ticket?')) {
        replyForm.post(route('tickets.close', { ticket: props.ticket.id }), {
            preserveScroll: true,
        });
    }
};

const categoryLabels = {
    technical: 'Technical Support',
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
    <Head :title="`[#${ticket.ticket_number}] ${ticket.subject} - Support`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            
            <!-- Breadcrumbs & Navigation -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('tickets.index')"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors"
                >
                    <span class="material-symbols-rounded text-base">arrow_back</span>
                    Back to All Support Tickets
                </Link>

                <div class="flex items-center gap-2">
                    <button
                        v-if="ticket.status !== 'resolved' && ticket.status !== 'closed'"
                        @click="closeTicket"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition-colors cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-sm">task_alt</span>
                        Mark as Resolved
                    </button>
                </div>
            </div>

            <!-- Ticket Overview Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                            #{{ ticket.ticket_number }}
                        </span>
                        <span
                            :class="statusClasses[ticket.status] || 'bg-gray-100 text-gray-700'"
                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="ticket.status === 'resolved' ? 'bg-purple-500' : 'bg-emerald-500'"></span>
                            {{ ticket.status.replace('_', ' ').toUpperCase() }}
                        </span>
                        <span
                            :class="priorityClasses[ticket.priority] || 'bg-gray-100 text-gray-700'"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border"
                        >
                            {{ ticket.priority.toUpperCase() }} PRIORITY
                        </span>
                    </div>

                    <div class="text-xs text-gray-400">
                        Opened on {{ new Date(ticket.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-snug">
                    {{ ticket.subject }}
                </h1>

                <div class="flex items-center gap-4 text-xs text-gray-500 pt-2 border-t border-gray-100">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm text-gray-400">category</span>
                        Category: <strong>{{ categoryLabels[ticket.category] || ticket.category }}</strong>
                    </span>
                    <span v-if="ticket.resolved_at" class="flex items-center gap-1 text-purple-700 font-medium">
                        <span class="material-symbols-rounded text-sm">verified</span>
                        Resolved on {{ new Date(ticket.resolved_at).toLocaleDateString() }}
                    </span>
                </div>
            </div>

            <!-- Messages Timeline -->
            <div class="space-y-4">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500 px-1 flex items-center justify-between">
                    <span>Conversation History ({{ ticket.public_messages?.length || 0 }})</span>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="msg in ticket.public_messages"
                        :key="msg.id"
                        class="rounded-2xl p-5 border transition-all"
                        :class="[
                            msg.user?.is_admin
                                ? 'bg-gradient-to-r from-emerald-50/70 via-teal-50/40 to-white border-emerald-200/80 shadow-xs'
                                : 'bg-white border-gray-200 shadow-xs'
                        ]"
                    >
                        <!-- Author Header -->
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs"
                                    :class="[
                                        msg.user?.is_admin
                                            ? 'bg-emerald-600 text-white shadow-xs'
                                            : 'bg-slate-200 text-slate-700'
                                    ]"
                                >
                                    <span v-if="msg.user?.is_admin" class="material-symbols-rounded text-sm">verified_user</span>
                                    <span v-else>{{ (msg.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                        {{ msg.user?.name }}
                                        <span
                                            v-if="msg.user?.is_admin"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800"
                                        >
                                            Nimbus Engineer
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ new Date(msg.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Body -->
                        <div class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">
                            {{ msg.message }}
                        </div>

                        <!-- Attachments -->
                        <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                            <div class="text-xs font-semibold text-gray-500">Attachments:</div>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    v-for="(att, attIdx) in msg.attachments"
                                    :key="attIdx"
                                    :href="att.path"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-medium text-slate-800 transition-colors"
                                >
                                    <span class="material-symbols-rounded text-sm text-gray-500">attachment</span>
                                    <span>{{ att.name }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Box -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">reply</span>
                        Post a Reply
                    </h3>
                    <span v-if="ticket.status === 'resolved'" class="text-xs text-amber-600 font-medium">
                        Replying will reopen this ticket
                    </span>
                </div>

                <form @submit.prevent="submitReply" class="space-y-4">
                    <textarea
                        v-model="replyForm.message"
                        rows="4"
                        placeholder="Write your reply or additional information here..."
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 leading-relaxed"
                    ></textarea>
                    <div v-if="replyForm.errors.message" class="text-xs text-rose-500">{{ replyForm.errors.message }}</div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                        <div class="flex-1 max-w-sm">
                            <input
                                id="reply-attachment-input"
                                type="file"
                                @change="handleFileChange"
                                class="w-full border border-gray-200 rounded-xl p-1.5 text-xs text-gray-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="replyForm.processing || !replyForm.message.trim()"
                            class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-all cursor-pointer disabled:opacity-50"
                        >
                            <span v-if="replyForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span v-else class="material-symbols-rounded text-sm">send</span>
                            <span>{{ replyForm.processing ? 'Sending...' : 'Send Reply' }}</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
