<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    forms: Array,
    submissions: Object, // paginated
    invitations: Array,
    users: Array,
    metrics: Object,
});

// Tabs: 'forms', 'submissions', 'invitations'
const activeTab = ref('forms');

// Search & Filter
const searchQuery = ref('');
const filterFormId = ref('');
const copiedSlug = ref(null);

// Modals State
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const editingFormId = ref(null);

const isAiModalOpen = ref(false);
const aiPrompt = ref('');
const isGeneratingAi = ref(false);
const aiError = ref('');

const isSendModalOpen = ref(false);
const selectedFormForInvite = ref(null);

const isPromoteModalOpen = ref(false);
const selectedSubmissionForPromote = ref(null);

const isViewAnswersModalOpen = ref(false);
const viewingSubmission = ref(null);

// Form Builder Form
const formBuilder = useForm({
    title: '',
    category: 'managed_hosting',
    description: '',
    questions: [],
    is_active: true,
    allow_anonymous: false,
    collect_company: true,
    collect_role: true,
    success_title: 'Thank you for your feedback!',
    success_message: 'Your review and suggestions help us continuously improve our cloud infrastructure.',
});

// Invitation Form
const inviteForm = useForm({
    feedback_form_id: '',
    user_id: '',
    recipient_name: '',
    recipient_email: '',
    personal_note: '',
});

// Promote Testimonial Form
const promoteForm = useForm({
    name: '',
    role: '',
    company: '',
    location: '',
    quote: '',
    rating: 5,
});

// Open Form Modal (Create)
const openCreateForm = () => {
    isEditing.value = false;
    editingFormId.value = null;
    formBuilder.reset();
    formBuilder.clearErrors();
    formBuilder.questions = [
        {
            id: 'q_rating_exp',
            label: 'How would you rate your overall managed hosting experience?',
            type: 'rating',
            required: true,
            placeholder: '',
            options: [],
        },
        {
            id: 'q_uptime',
            label: 'How satisfied are you with server uptime and response speed?',
            type: 'select',
            required: true,
            options: [
                'Exceeded expectations (100% fast & stable)',
                'Good and stable',
                'Average / Acceptable',
                'Needs improvement',
            ],
        },
        {
            id: 'q_best',
            label: 'What do you like best about our service?',
            type: 'text',
            required: false,
            placeholder: 'e.g. Uptime reliability, prompt support...',
            options: [],
        },
    ];
    isFormModalOpen.value = true;
};

// Open Form Modal (Edit)
const openEditForm = (formItem) => {
    isEditing.value = true;
    editingFormId.value = formItem.id;
    formBuilder.clearErrors();
    formBuilder.title = formItem.title;
    formBuilder.category = formItem.category;
    formBuilder.description = formItem.description || '';
    formBuilder.questions = JSON.parse(JSON.stringify(formItem.questions || []));
    formBuilder.is_active = formItem.is_active;
    formBuilder.allow_anonymous = formItem.allow_anonymous;
    formBuilder.collect_company = formItem.collect_company;
    formBuilder.collect_role = formItem.collect_role;
    formBuilder.success_title = formItem.success_title || 'Thank you for your feedback!';
    formBuilder.success_message = formItem.success_message || '';
    isFormModalOpen.value = true;
};

// Save Form (Store / Update)
const saveForm = () => {
    if (isEditing.value) {
        formBuilder.put(route('admin.feedback.update', { feedbackForm: editingFormId.value }), {
            onSuccess: () => {
                isFormModalOpen.value = false;
            },
        });
    } else {
        formBuilder.post(route('admin.feedback.store'), {
            onSuccess: () => {
                isFormModalOpen.value = false;
            },
        });
    }
};

// Toggle Active State
const toggleActive = (formItem) => {
    router.post(route('admin.feedback.toggle-active', { feedbackForm: formItem.id }), {}, {
        preserveScroll: true,
    });
};

// Delete Form
const deleteForm = (formItem) => {
    if (confirm(`Are you sure you want to delete "${formItem.title}" and all its recorded responses?`)) {
        router.delete(route('admin.feedback.destroy', { feedbackForm: formItem.id }), {
            preserveScroll: true,
        });
    }
};

// Question Builder Helpers
const addQuestion = () => {
    const newId = `q_${Date.now()}`;
    formBuilder.questions.push({
        id: newId,
        label: 'New Question',
        type: 'text',
        required: false,
        placeholder: '',
        options: [],
    });
};

const removeQuestion = (idx) => {
    formBuilder.questions.splice(idx, 1);
};

const moveQuestion = (idx, direction) => {
    const targetIdx = idx + direction;
    if (targetIdx < 0 || targetIdx >= formBuilder.questions.length) return;
    const temp = formBuilder.questions[idx];
    formBuilder.questions[idx] = formBuilder.questions[targetIdx];
    formBuilder.questions[targetIdx] = temp;
};

const addOption = (q) => {
    if (!q.options) q.options = [];
    q.options.push(`Option ${q.options.length + 1}`);
};

const removeOption = (q, optIdx) => {
    q.options.splice(optIdx, 1);
};

// AI Question Generator
const triggerAiGenerate = async () => {
    if (!aiPrompt.value.trim()) return;
    isGeneratingAi.value = true;
    aiError.value = '';

    try {
        const response = await axios.post(route('admin.feedback.generate-ai'), {
            prompt: aiPrompt.value,
            category: formBuilder.category,
        });

        if (response.data && response.data.questions && response.data.questions.length > 0) {
            formBuilder.questions = response.data.questions;
            isAiModalOpen.value = false;
            aiPrompt.value = '';
        } else {
            aiError.value = 'Failed to parse generated questions. Please try refining your prompt.';
        }
    } catch (e) {
        aiError.value = e.response?.data?.message || 'Error communicating with AI service.';
    } finally {
        isGeneratingAi.value = false;
    }
};

const setAiPromptPreset = (presetText) => {
    aiPrompt.value = presetText;
};

// Copy Shareable Public Link
const copyPublicLink = (formItem) => {
    navigator.clipboard.writeText(formItem.public_url);
    copiedSlug.value = formItem.slug;
    setTimeout(() => {
        if (copiedSlug.value === formItem.slug) copiedSlug.value = null;
    }, 2500);
};

// Open Send Email Modal
const openSendModal = (formItem) => {
    selectedFormForInvite.value = formItem;
    inviteForm.reset();
    inviteForm.clearErrors();
    inviteForm.feedback_form_id = formItem.id;
    inviteForm.personal_note = `We'd love to hear how your managed hosting and infrastructure setup is performing. Your thoughts help us serve you better!`;
    isSendModalOpen.value = true;
};

const onUserSelected = () => {
    if (!inviteForm.user_id) return;
    const user = props.users.find(u => u.id === parseInt(inviteForm.user_id));
    if (user) {
        inviteForm.recipient_name = user.name;
        inviteForm.recipient_email = user.email;
    }
};

const sendInvite = () => {
    inviteForm.post(route('admin.feedback.send-invitation', { feedbackForm: selectedFormForInvite.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            isSendModalOpen.value = false;
        },
    });
};

// Promote to Testimonial
const openPromoteModal = (sub) => {
    selectedSubmissionForPromote.value = sub;
    promoteForm.clearErrors();
    promoteForm.name = sub.client_name;
    promoteForm.role = sub.client_role || 'Verified Client';
    promoteForm.company = sub.client_company || '';
    promoteForm.location = '';
    promoteForm.quote = sub.feedback || sub.suggestions || 'Nimbus delivers blazing fast server speeds and rock-solid uptime!';
    promoteForm.rating = sub.rating || 5;
    isPromoteModalOpen.value = true;
};

const submitPromote = () => {
    promoteForm.post(route('admin.feedback.submissions.promote', { submission: selectedSubmissionForPromote.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            isPromoteModalOpen.value = false;
        },
    });
};

// View Answers Modal
const openViewAnswers = (sub) => {
    viewingSubmission.value = sub;
    isViewAnswersModalOpen.value = true;
};

// Map submission answers back to their full question labels and types
const getSubmissionQA = (sub) => {
    if (!sub) return [];

    // 1. Resolve form questions: check sub.form.questions, or find in props.forms
    let questions = sub.form?.questions;
    if (!questions || !Array.isArray(questions) || questions.length === 0) {
        const found = (props.forms || []).find(f => f.id === sub.feedback_form_id);
        if (found?.questions && Array.isArray(found.questions)) {
            questions = found.questions;
        }
    }
    questions = questions || [];

    const answers = sub.answers || {};
    const processedKeys = new Set();
    const result = [];

    // Map by form questions to preserve order and exact labels
    questions.forEach((q, idx) => {
        const qId = q.id || `q_${idx}`;
        processedKeys.add(qId);
        const val = answers[qId] !== undefined ? answers[qId] : null;

        result.push({
            id: qId,
            index: idx + 1,
            label: q.label || `Question #${idx + 1}`,
            type: q.type || 'text',
            value: val,
            hasValue: val !== null && val !== '' && val !== undefined,
        });
    });

    // Handle any extra keys in answers not found in the questions array (e.g. legacy or modified form)
    Object.keys(answers).forEach((k) => {
        if (!processedKeys.has(k)) {
            const val = answers[k];
            let label = k.replace(/^q_/, '').replace(/_/g, ' ');
            if (/^\d+$/.test(label)) {
                label = `Custom Question (#${label.slice(-4)})`;
            } else {
                label = label.charAt(0).toUpperCase() + label.slice(1);
            }

            result.push({
                id: k,
                index: result.length + 1,
                label: label,
                type: typeof val === 'number' && val >= 1 && val <= 5 ? 'rating' : 'text',
                value: val,
                hasValue: val !== null && val !== '' && val !== undefined,
            });
        }
    });

    return result;
};

// Delete Submission
const deleteSubmission = (sub) => {
    if (confirm(`Are you sure you want to delete this feedback submission from ${sub.client_name}?`)) {
        router.delete(route('admin.feedback.submissions.destroy', { submission: sub.id }), {
            preserveScroll: true,
        });
    }
};

// Filtered Submissions
const filteredSubmissions = computed(() => {
    if (!props.submissions?.data) return [];
    return props.submissions.data.filter(s => {
        const matchesForm = !filterFormId.value || s.feedback_form_id === parseInt(filterFormId.value);
        const matchesSearch = !searchQuery.value ||
            s.client_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            s.client_email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            s.client_company?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            s.feedback?.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesForm && matchesSearch;
    });
});
</script>

<template>
    <Head title="Client Feedback &amp; AI Form Builder - Nimbus" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <!-- Top Header & Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
                        <span class="material-symbols-rounded text-emerald-600 text-3xl">rate_review</span>
                        Client Feedback &amp; Reviews
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Collect client reviews, generate AI questionnaire forms, send invitations, and promote feedback to live landing page testimonials.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openCreateForm"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-sm transition-all hover:scale-[1.02] cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-lg">add_circle</span>
                        Create Feedback Form
                    </button>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Total Submissions</span>
                        <span class="material-symbols-rounded text-emerald-600 text-xl">reviews</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900">{{ metrics.total_submissions }}</div>
                    <div class="text-xs text-emerald-600 mt-1 font-medium">Customer responses collected</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Avg Star Rating</span>
                        <span class="material-symbols-rounded text-amber-500 text-xl">star</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900 flex items-center gap-2">
                        {{ metrics.average_rating }}
                        <div class="flex items-center text-amber-400 text-base">
                            <span v-for="i in 5" :key="i" class="material-symbols-rounded text-lg">
                                {{ i <= Math.round(metrics.average_rating) ? 'star' : 'star_border' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Out of 5.0 rating scale</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Active Forms</span>
                        <span class="material-symbols-rounded text-blue-600 text-xl">dynamic_form</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900">{{ metrics.active_forms }}</div>
                    <div class="text-xs text-gray-500 mt-1">Accepting live responses</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-gray-500 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Promoted Testimonials</span>
                        <span class="material-symbols-rounded text-purple-600 text-xl">verified</span>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900">{{ metrics.promoted_testimonials }}</div>
                    <div class="text-xs text-purple-600 mt-1 font-medium">Featured on Landing Page</div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 overflow-x-auto pb-1 sm:pb-0">
                <nav class="flex space-x-4 sm:space-x-8 min-w-max" aria-label="Tabs">
                    <button
                        @click="activeTab = 'forms'"
                        :class="[
                            activeTab === 'forms'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 cursor-pointer transition-colors"
                    >
                        <span class="material-symbols-rounded text-lg">format_list_bulleted</span>
                        Feedback Forms ({{ forms.length }})
                    </button>

                    <button
                        @click="activeTab = 'submissions'"
                        :class="[
                            activeTab === 'submissions'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 cursor-pointer transition-colors"
                    >
                        <span class="material-symbols-rounded text-lg">mark_email_read</span>
                        Client Responses ({{ submissions?.total || 0 }})
                    </button>

                    <button
                        @click="activeTab = 'invitations'"
                        :class="[
                            activeTab === 'invitations'
                                ? 'border-emerald-500 text-emerald-600 font-bold'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 cursor-pointer transition-colors"
                    >
                        <span class="material-symbols-rounded text-lg">outgoing_mail</span>
                        Dispatched Invitations ({{ invitations.length }})
                    </button>
                </nav>
            </div>

            <!-- TAB 1: Feedback Forms List -->
            <div v-if="activeTab === 'forms'" class="space-y-4">
                <div v-if="forms.length === 0" class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-rounded text-2xl">rate_review</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Feedback Forms Created</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                        Create your first feedback form to begin collecting client ratings, managed hosting reviews, and suggestions.
                    </p>
                    <button
                        @click="openCreateForm"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 transition-all cursor-pointer"
                    >
                        <span class="material-symbols-rounded text-lg">add</span>
                        Create Feedback Form
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div
                        v-for="formItem in forms"
                        :key="formItem.id"
                        class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs hover:border-emerald-300 transition-all flex flex-col justify-between"
                    >
                        <div>
                            <!-- Header & Status -->
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-slate-100 text-slate-700 mb-1.5">
                                        {{ formItem.category.replace('_', ' ') }}
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-900 leading-snug">{{ formItem.title }}</h3>
                                </div>
                                <span
                                    :class="[
                                        formItem.is_active
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-rose-50 text-rose-700 border-rose-200'
                                    ]"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                >
                                    {{ formItem.is_active ? 'Active' : 'Closed' }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                                {{ formItem.description || 'No description specified.' }}
                            </p>

                            <!-- Mini Metrics -->
                            <div class="grid grid-cols-3 gap-2 py-3 px-4 bg-slate-50 rounded-xl mb-4 text-center">
                                <div>
                                    <div class="text-xs text-gray-500">Responses</div>
                                    <div class="text-base font-bold text-gray-900">{{ formItem.submissions_count }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Avg Rating</div>
                                    <div class="text-base font-bold text-amber-500 flex items-center justify-center gap-1">
                                        <span class="material-symbols-rounded text-sm">star</span>
                                        {{ formItem.average_rating || '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Questions</div>
                                    <div class="text-base font-bold text-gray-900">{{ formItem.questions?.length || 0 }}</div>
                                </div>
                            </div>

                            <!-- Shareable URL Pill -->
                            <div class="flex items-center gap-2 p-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono text-gray-600 mb-4 truncate">
                                <span class="material-symbols-rounded text-gray-400 text-base flex-shrink-0">link</span>
                                <span class="truncate flex-1">{{ formItem.public_url }}</span>
                                <button
                                    @click="copyPublicLink(formItem)"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 font-sans text-[11px] font-semibold flex-shrink-0 transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-xs">
                                        {{ copiedSlug === formItem.slug ? 'check' : 'content_copy' }}
                                    </span>
                                    {{ copiedSlug === formItem.slug ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <a
                                    :href="formItem.public_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition-colors"
                                >
                                    <span class="material-symbols-rounded text-sm">visibility</span>
                                    Preview
                                </a>
                                <button
                                    @click="openSendModal(formItem)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-sm">send</span>
                                    Send Email
                                </button>
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    @click="toggleActive(formItem)"
                                    :title="formItem.is_active ? 'Close form' : 'Activate form'"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-base">
                                        {{ formItem.is_active ? 'pause_circle' : 'play_circle' }}
                                    </span>
                                </button>
                                <button
                                    @click="openEditForm(formItem)"
                                    title="Edit form & questions"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-base">edit</span>
                                </button>
                                <button
                                    @click="deleteForm(formItem)"
                                    title="Delete form"
                                    class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Submissions & Client Reviews -->
            <div v-else-if="activeTab === 'submissions'" class="space-y-4">
                
                <!-- Filters Bar -->
                <div class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
                    <div class="relative w-full sm:w-80 flex items-center">
                        <span class="material-symbols-rounded pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search client, email, feedback..."
                            class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <select
                            v-model="filterFormId"
                            class="w-full sm:w-auto border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-emerald-500"
                        >
                            <option value="">All Feedback Forms</option>
                            <option v-for="f in forms" :key="f.id" :value="f.id">{{ f.title }}</option>
                        </select>
                    </div>
                </div>

                <!-- Submissions Table -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3.5">Client &amp; Company</th>
                                    <th class="px-6 py-3.5">Rating</th>
                                    <th class="px-6 py-3.5">Feedback / Review Quote</th>
                                    <th class="px-6 py-3.5">Form</th>
                                    <th class="px-6 py-3.5">Date</th>
                                    <th class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-gray-700">
                                <tr v-if="filteredSubmissions.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <span class="material-symbols-rounded text-3xl text-gray-400 block mb-1">sentiment_dissatisfied</span>
                                        No client feedback submissions match your criteria.
                                    </td>
                                </tr>
                                <tr
                                    v-for="sub in filteredSubmissions"
                                    :key="sub.id"
                                    class="hover:bg-slate-50/80 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ sub.client_name }}</div>
                                        <div class="text-xs text-gray-500">{{ sub.client_email }}</div>
                                        <div v-if="sub.client_company || sub.client_role" class="text-xs text-emerald-700 mt-0.5">
                                            {{ [sub.client_role, sub.client_company].filter(Boolean).join(' at ') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center text-amber-400">
                                            <span v-for="s in 5" :key="s" class="material-symbols-rounded text-lg">
                                                {{ s <= sub.rating ? 'star' : 'star_border' }}
                                            </span>
                                            <span class="ml-1 text-xs font-bold text-gray-700">({{ sub.rating }}/5)</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 max-w-sm">
                                        <p class="text-xs sm:text-sm text-gray-800 line-clamp-2 leading-relaxed">
                                            "{{ sub.feedback || sub.suggestions || 'No written comments.' }}"
                                        </p>
                                        <div v-if="sub.is_testimonial" class="mt-1">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                                                <span class="material-symbols-rounded text-xs">verified</span>
                                                Promoted to Testimonials
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ sub.form?.title || 'Form' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ new Date(sub.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- View Answers Button -->
                                            <button
                                                @click="openViewAnswers(sub)"
                                                title="View detailed responses"
                                                class="px-2.5 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 text-gray-700 font-semibold transition-colors cursor-pointer"
                                            >
                                                Details
                                            </button>

                                            <!-- Promote to Testimonial Button -->
                                            <button
                                                v-if="!sub.is_testimonial"
                                                @click="openPromoteModal(sub)"
                                                title="Publish to Landing Page as a verified Testimonial"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-bold transition-all shadow-xs cursor-pointer"
                                            >
                                                <span class="material-symbols-rounded text-sm">reviews</span>
                                                Promote
                                            </button>

                                            <!-- Delete Button -->
                                            <button
                                                @click="deleteSubmission(sub)"
                                                title="Delete submission"
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

            <!-- TAB 3: Dispatched Email Invitations -->
            <div v-else-if="activeTab === 'invitations'" class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3.5">Recipient</th>
                                    <th class="px-6 py-3.5">Feedback Form</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5">Sent Timestamp</th>
                                    <th class="px-6 py-3.5">Completed Timestamp</th>
                                    <th class="px-6 py-3.5 text-right">Direct Token Link</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-gray-700">
                                <tr v-if="invitations.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <span class="material-symbols-rounded text-3xl text-gray-400 block mb-1">outgoing_mail</span>
                                        No client invitations have been dispatched yet.
                                    </td>
                                </tr>
                                <tr
                                    v-for="inv in invitations"
                                    :key="inv.id"
                                    class="hover:bg-slate-50/80 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ inv.recipient_name || 'Client' }}</div>
                                        <div class="text-xs text-gray-500">{{ inv.recipient_email }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-xs font-medium text-gray-800">
                                        {{ inv.form_title }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                inv.status === 'submitted'
                                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                    : 'bg-amber-50 text-amber-700 border-amber-200'
                                            ]"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="inv.status === 'submitted' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                            {{ inv.status === 'submitted' ? 'Completed' : 'Sent / Pending' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ inv.sent_at || '-' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ inv.completed_at || '-' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <button
                                            @click="navigator.clipboard.writeText(inv.invite_url)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-gray-700 font-mono text-[11px] transition-colors cursor-pointer"
                                        >
                                            <span class="material-symbols-rounded text-xs">link</span>
                                            Copy Link
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL 1: Form Builder (Create & Edit) -->
            <div v-if="isFormModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-3xl w-full p-4 sm:p-8 shadow-2xl space-y-5 sm:space-y-6 my-auto sm:my-8 max-h-[90vh] flex flex-col">
                    
                    <!-- Modal Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-4 flex-shrink-0">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ isEditing ? 'Edit Feedback Form' : 'Create Feedback Form' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Customize questionnaire fields, review metrics, and client experience.</p>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <!-- AI Generator Trigger -->
                            <button
                                type="button"
                                @click="isAiModalOpen = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-xs shadow-xs hover:opacity-90 transition-opacity cursor-pointer"
                            >
                                <span class="material-symbols-rounded text-sm">auto_awesome</span>
                                Generate with AI
                            </button>
                            <button @click="isFormModalOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Modal Body -->
                    <div class="space-y-6 overflow-y-auto pr-1 flex-1">
                        
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Form Title *</label>
                                <input
                                    type="text"
                                    v-model="formBuilder.title"
                                    placeholder="e.g. Managed Cloud Hosting Client Review"
                                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="formBuilder.errors.title" class="text-xs text-rose-500 mt-1">{{ formBuilder.errors.title }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category *</label>
                                <select
                                    v-model="formBuilder.category"
                                    class="w-full border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500"
                                >
                                    <option value="managed_hosting">Managed Hosting</option>
                                    <option value="self_hosted">Self-Hosted Panel</option>
                                    <option value="support">Technical Support</option>
                                    <option value="general">General Feedback</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Description / Subtitle</label>
                            <textarea
                                v-model="formBuilder.description"
                                rows="2"
                                placeholder="Explain to your client why their review matters and what this questionnaire covers..."
                                class="w-full border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <!-- Dynamic Questions Builder -->
                        <div class="border border-gray-200 rounded-2xl p-4 sm:p-5 bg-slate-50/50 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                        <span class="material-symbols-rounded text-emerald-600 text-lg">tune</span>
                                        Dynamic Questions ({{ formBuilder.questions.length }})
                                    </h4>
                                    <p class="text-xs text-gray-500">Clients will answer these customized questions on their form.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="addQuestion"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition-colors cursor-pointer"
                                >
                                    <span class="material-symbols-rounded text-sm">add</span>
                                    Add Question
                                </button>
                            </div>

                            <div v-if="formBuilder.questions.length === 0" class="text-center py-6 text-xs text-gray-500">
                                No questions configured yet. Click "Add Question" or "Generate with AI" above!
                            </div>

                            <!-- Question List -->
                            <div class="space-y-3">
                                <div
                                    v-for="(q, qIdx) in formBuilder.questions"
                                    :key="q.id || qIdx"
                                    class="bg-white border border-gray-200 rounded-xl p-4 shadow-xs space-y-3"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1 text-gray-400">
                                            <button
                                                type="button"
                                                @click="moveQuestion(qIdx, -1)"
                                                :disabled="qIdx === 0"
                                                class="hover:text-gray-700 disabled:opacity-30 cursor-pointer"
                                            >
                                                <span class="material-symbols-rounded text-base">arrow_upward</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="moveQuestion(qIdx, 1)"
                                                :disabled="qIdx === formBuilder.questions.length - 1"
                                                class="hover:text-gray-700 disabled:opacity-30 cursor-pointer"
                                            >
                                                <span class="material-symbols-rounded text-base">arrow_downward</span>
                                            </button>
                                            <span class="text-xs font-mono font-bold text-gray-500 ml-1">#{{ qIdx + 1 }}</span>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 font-medium cursor-pointer">
                                                <input type="checkbox" v-model="q.required" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                                <span>Required</span>
                                            </label>
                                            <button
                                                type="button"
                                                @click="removeQuestion(qIdx)"
                                                class="text-rose-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 cursor-pointer"
                                            >
                                                <span class="material-symbols-rounded text-base">delete</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="sm:col-span-2">
                                            <input
                                                type="text"
                                                v-model="q.label"
                                                placeholder="Question text..."
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500"
                                            />
                                        </div>
                                        <div>
                                            <select
                                                v-model="q.type"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500"
                                            >
                                                <option value="rating">Star Rating (1-5)</option>
                                                <option value="text">Short Text</option>
                                                <option value="textarea">Paragraph Textarea</option>
                                                <option value="select">Dropdown Select</option>
                                                <option value="radio">Radio Options</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Options builder for select / radio -->
                                    <div v-if="q.type === 'select' || q.type === 'radio'" class="pt-2 border-t border-gray-100 space-y-2">
                                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider flex items-center justify-between">
                                            <span>Options List</span>
                                            <button
                                                type="button"
                                                @click="addOption(q)"
                                                class="text-emerald-600 hover:underline cursor-pointer"
                                            >
                                                + Add Option
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="flex items-center gap-1.5">
                                                <input
                                                    type="text"
                                                    v-model="q.options[optIdx]"
                                                    class="flex-1 border border-gray-300 rounded-lg px-2.5 py-1 text-xs"
                                                />
                                                <button
                                                    type="button"
                                                    @click="removeOption(q, optIdx)"
                                                    class="text-gray-400 hover:text-rose-500"
                                                >
                                                    <span class="material-symbols-rounded text-sm">remove_circle</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Options & Controls -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" v-model="formBuilder.is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                <span>Form is Active</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" v-model="formBuilder.allow_anonymous" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                <span>Allow Anonymous</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" v-model="formBuilder.collect_company" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                <span>Collect Company</span>
                            </label>
                        </div>

                        <!-- Success Message -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirmation Title</label>
                                <input
                                    type="text"
                                    v-model="formBuilder.success_title"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirmation Message</label>
                                <input
                                    type="text"
                                    v-model="formBuilder.success_message"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                                />
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3 flex-shrink-0">
                        <button
                            type="button"
                            @click="isFormModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-100 transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="saveForm"
                            :disabled="formBuilder.processing"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                        >
                            {{ formBuilder.processing ? 'Saving...' : (isEditing ? 'Update Form' : 'Publish Feedback Form') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL 2: AI Question Generator Dialog -->
            <div v-if="isAiModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                                <span class="material-symbols-rounded text-lg">auto_awesome</span>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Generate Form via AI</h3>
                                <p class="text-xs text-gray-500">Provide a topic or pick from pre-tuned cloud hosting prompts.</p>
                            </div>
                        </div>
                        <button @click="isAiModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <!-- Preset Chips -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Popular Cloud Templates</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                type="button"
                                @click="setAiPromptPreset('Review our managed cloud hosting experience: server uptime, TTFB speed, SSH access, and 24/7 technical support response time')"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                            >
                                🚀 Managed Hosting Review
                            </button>
                            <button
                                type="button"
                                @click="setAiPromptPreset('Feedback on migrating from cPanel to Nimbus Self-Hosted Panel: ease of migration, DNS setup, and feature satisfaction')"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                            >
                                📦 Panel Migration &amp; Setup
                            </button>
                            <button
                                type="button"
                                @click="setAiPromptPreset('Evaluate our emergency support ticket handling, resolution speed, and engineer communication')"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                            >
                                💬 Tech Support Satisfaction
                            </button>
                            <button
                                type="button"
                                @click="setAiPromptPreset('Customer feature wishlist: backup storage, multi-cloud replication, staging domains, and API tools')"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors cursor-pointer"
                            >
                                ✨ Feature Wishlist &amp; NPS
                            </button>
                        </div>
                    </div>

                    <!-- Prompt Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Your Custom AI Prompt</label>
                        <textarea
                            v-model="aiPrompt"
                            rows="3"
                            placeholder="e.g. Create a 5-question feedback form asking about VPS speed, dashboard ease of use, pricing value, and recommendations..."
                            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div v-if="aiError" class="text-xs text-rose-500 font-medium">
                        {{ aiError }}
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isAiModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="triggerAiGenerate"
                            :disabled="isGeneratingAi || !aiPrompt.trim()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                        >
                            <span v-if="isGeneratingAi" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span v-else class="material-symbols-rounded text-sm">auto_awesome</span>
                            <span>{{ isGeneratingAi ? 'Synthesizing Questions...' : 'Generate Questions' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL 3: Send Email Invitation -->
            <div v-if="isSendModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Send Feedback Invitation</h3>
                            <p class="text-xs text-gray-500">Dispatch an email invitation with a direct link.</p>
                        </div>
                        <button @click="isSendModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800">
                        <strong>Form:</strong> {{ selectedFormForInvite?.title }}
                    </div>

                    <!-- User Selector or Custom Email -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pick Existing Client (Optional)</label>
                        <select
                            v-model="inviteForm.user_id"
                            @change="onUserSelected"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500"
                        >
                            <option value="">-- Or enter custom email below --</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.email }}) {{ u.company_name ? ` - ${u.company_name}` : '' }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Recipient Name</label>
                            <input
                                type="text"
                                v-model="inviteForm.recipient_name"
                                placeholder="Client Name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Recipient Email *</label>
                            <input
                                type="email"
                                v-model="inviteForm.recipient_email"
                                placeholder="client@company.com"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            />
                            <div v-if="inviteForm.errors.recipient_email" class="text-xs text-rose-500 mt-1">
                                {{ inviteForm.errors.recipient_email }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Personal Note in Email</label>
                        <textarea
                            v-model="inviteForm.personal_note"
                            rows="2"
                            placeholder="Add a friendly personal message..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isSendModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="sendInvite"
                            :disabled="inviteForm.processing"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                        >
                            <span v-if="inviteForm.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span v-else class="material-symbols-rounded text-sm">send</span>
                            <span>{{ inviteForm.processing ? 'Dispatching...' : 'Dispatch Email' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL 4: Promote to Landing Page Testimonial -->
            <div v-if="isPromoteModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                <span class="material-symbols-rounded text-lg">verified</span>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Promote to Landing Page Testimonial</h3>
                                <p class="text-xs text-gray-500">Feature this review directly on your landing page.</p>
                            </div>
                        </div>
                        <button @click="isPromoteModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Client Name *</label>
                            <input
                                type="text"
                                v-model="promoteForm.name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role / Title</label>
                            <input
                                type="text"
                                v-model="promoteForm.role"
                                placeholder="e.g. Lead DevOps Engineer"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Company</label>
                            <input
                                type="text"
                                v-model="promoteForm.company"
                                placeholder="e.g. TechCorp"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Rating (Stars)</label>
                            <select
                                v-model="promoteForm.rating"
                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm"
                            >
                                <option :value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                                <option :value="4">⭐⭐⭐⭐ (4 Stars)</option>
                                <option :value="3">⭐⭐⭐ (3 Stars)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Testimonial Quote *</label>
                        <textarea
                            v-model="promoteForm.quote"
                            rows="4"
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-sm leading-relaxed"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isPromoteModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-100 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="submitPromote"
                            :disabled="promoteForm.processing"
                            class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                        >
                            <span class="material-symbols-rounded text-sm">publish</span>
                            <span>{{ promoteForm.processing ? 'Publishing...' : 'Publish to Testimonials' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL 5: Detailed Submission Answers Drawer / Modal -->
            <div v-if="isViewAnswersModalOpen && viewingSubmission" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-2xl w-full p-4 sm:p-7 shadow-2xl space-y-4 sm:space-y-5 animate-scale-up my-auto max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Feedback Submission Details</h3>
                            <div class="text-xs text-gray-500 mt-0.5 flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold text-gray-800">{{ viewingSubmission.client_name }}</span>
                                <span class="text-gray-400">({{ viewingSubmission.client_email }})</span>
                                <span v-if="viewingSubmission.client_company" class="text-emerald-700 font-medium">
                                    &bull; {{ viewingSubmission.client_company }}
                                </span>
                            </div>
                        </div>
                        <button @click="isViewAnswersModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <!-- Star Rating Banner -->
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div>
                            <span class="text-xs font-bold text-amber-900 uppercase tracking-wider block">Overall Star Rating</span>
                            <span class="text-[11px] text-amber-700">Client satisfaction score</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center text-amber-400">
                                <span v-for="s in 5" :key="s" class="material-symbols-rounded text-xl">
                                    {{ s <= viewingSubmission.rating ? 'star' : 'star_border' }}
                                </span>
                            </div>
                            <span class="text-sm font-extrabold text-amber-950">{{ viewingSubmission.rating }} / 5</span>
                        </div>
                    </div>

                    <!-- Primary Feedback / Review Quote -->
                    <div v-if="viewingSubmission.feedback" class="space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Client Review / Testimonial Quote</div>
                        <div class="p-3.5 bg-slate-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 leading-relaxed italic">
                            "{{ viewingSubmission.feedback }}"
                        </div>
                    </div>

                    <!-- Suggestions & Ideas -->
                    <div v-if="viewingSubmission.suggestions" class="space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Suggestions &amp; Ideas</div>
                        <div class="p-3.5 bg-emerald-50/40 border border-emerald-200 rounded-xl text-xs sm:text-sm text-emerald-950 leading-relaxed">
                            {{ viewingSubmission.suggestions }}
                        </div>
                    </div>

                    <!-- Answers to Dynamic Questions -->
                    <div class="space-y-3 pt-2">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center justify-between">
                            <span>Questionnaire Answers ({{ getSubmissionQA(viewingSubmission).length }})</span>
                            <span v-if="viewingSubmission.form?.title" class="text-[11px] font-normal text-emerald-700 font-sans">
                                Form: {{ viewingSubmission.form.title }}
                            </span>
                        </div>

                        <div v-if="getSubmissionQA(viewingSubmission).length === 0" class="text-xs text-gray-400 italic p-3 bg-gray-50 rounded-lg">
                            No questionnaire answers were recorded for this submission.
                        </div>

                        <div v-else class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                            <div
                                v-for="item in getSubmissionQA(viewingSubmission)"
                                :key="item.id"
                                class="p-3.5 bg-slate-50 border border-gray-200/90 rounded-xl text-xs space-y-1.5 transition-all"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-bold text-gray-900 leading-snug flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded bg-gray-200 text-gray-600 font-mono text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ item.index }}
                                        </span>
                                        {{ item.label }}
                                    </span>
                                    <span class="text-[10px] uppercase font-semibold text-gray-400 bg-white px-2 py-0.5 rounded border border-gray-200 flex-shrink-0">
                                        {{ item.type }}
                                    </span>
                                </div>

                                <div v-if="item.type === 'rating' && item.hasValue" class="flex items-center gap-2 pt-0.5">
                                    <div class="flex items-center text-amber-400">
                                        <span v-for="s in 5" :key="s" class="material-symbols-rounded text-base">
                                            {{ s <= Number(item.value) ? 'star' : 'star_border' }}
                                        </span>
                                    </div>
                                    <span class="font-bold text-gray-800 text-xs">({{ item.value }} / 5)</span>
                                </div>

                                <div v-else-if="item.hasValue" class="text-gray-800 font-medium text-xs whitespace-pre-wrap bg-white p-2.5 rounded-lg border border-gray-200">
                                    {{ item.value }}
                                </div>

                                <div v-else class="text-gray-400 italic text-[11px] pt-0.5">
                                    (No answer provided by client)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs text-gray-500">
                        <div>Submitted: {{ new Date(viewingSubmission.created_at).toLocaleString() }}</div>
                        <button
                            @click="isViewAnswersModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-semibold text-gray-700 cursor-pointer transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
