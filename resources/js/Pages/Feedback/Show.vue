<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    form: Object,
    identifier: String,
    isInvitation: Boolean,
    isAlreadySubmitted: Boolean,
    isClosed: Boolean,
    prefill: Object,
});

const isSubmitted = ref(props.isAlreadySubmitted || false);
const hoveredStar = ref(0);

const ratingLabels = {
    1: 'Poor - Needs significant work',
    2: 'Fair - Below expectations',
    3: 'Good - Meets expectations',
    4: 'Great - Exceeds expectations',
    5: 'Exceptional - Outstanding service!',
};

// Initialize answers map for dynamic questions
const initialAnswers = {};
if (props.form?.questions && Array.isArray(props.form.questions)) {
    props.form.questions.forEach((q, index) => {
        const key = q.id || `q_${index}`;
        initialAnswers[key] = q.type === 'rating' ? 5 : '';
    });
}

const submissionForm = useForm({
    rating: 5,
    client_name: props.prefill?.name || '',
    client_email: props.prefill?.email || '',
    client_company: props.prefill?.company || '',
    client_role: '',
    feedback: '',
    suggestions: '',
    answers: initialAnswers,
    is_anonymous: false,
});

const submitFeedback = () => {
    submissionForm.post(route('feedback.submit', { identifier: props.identifier }), {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitted.value = true;
        },
    });
};

const getRatingLabel = computed(() => {
    const star = hoveredStar.value || submissionForm.rating;
    return ratingLabels[star] || '';
});
</script>

<template>
    <Head :title="form?.title ? `${form.title} - Nimbus Feedback` : 'Nimbus Feedback'" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 text-slate-100 py-12 px-4 sm:px-6 lg:px-8 selection:bg-emerald-500/20">
        <!-- Background Glow -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-3xl mx-auto">
            <!-- Brand Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-4 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Nimbus by VMCore
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    {{ form?.title || 'Client Feedback' }}
                </h1>
                <p v-if="form?.description" class="mt-3 text-base text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    {{ form.description }}
                </p>
            </div>

            <!-- State: Form is Closed -->
            <div v-if="isClosed" class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl p-8 sm:p-12 text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5 text-amber-400">
                    <span class="material-symbols-rounded text-3xl">lock</span>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Feedback Form Closed</h2>
                <p class="text-slate-400 max-w-md mx-auto mb-6 text-sm">
                    This feedback form is no longer accepting responses. Thank you for your interest and support.
                </p>
                <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold transition-all">
                    &larr; Return to Home
                </a>
            </div>

            <!-- State: Success / Already Submitted -->
            <div v-else-if="isSubmitted" class="bg-slate-800/80 backdrop-blur-md border border-emerald-500/30 rounded-2xl p-8 sm:p-12 text-center shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="w-20 h-20 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mx-auto mb-6 text-emerald-400 shadow-lg shadow-emerald-500/10">
                    <span class="material-symbols-rounded text-4xl">verified</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">
                    {{ form?.success_title || 'Thank you for your feedback!' }}
                </h2>
                <p class="text-slate-300 max-w-lg mx-auto text-base leading-relaxed mb-8">
                    {{ form?.success_message || 'Your review and suggestions have been shared directly with our engineering and support leads. We truly value your partnership.' }}
                </p>
                <div class="flex items-center justify-center gap-4">
                    <a href="/" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-sm font-bold shadow-lg shadow-emerald-500/20 transition-all hover:scale-105">
                        Visit Nimbus Platform &rarr;
                    </a>
                </div>
            </div>

            <!-- State: Active Feedback Form -->
            <div v-else class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 rounded-2xl p-6 sm:p-10 shadow-2xl">
                <form @submit.prevent="submitFeedback" class="space-y-8">
                    
                    <!-- Section: Primary Overall Rating -->
                    <div class="text-center py-4 bg-slate-900/60 border border-slate-700/40 rounded-xl px-4">
                        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Overall Rating &amp; Satisfaction <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-xs text-slate-400 mb-4">How would you score your experience overall?</p>
                        
                        <!-- Stars -->
                        <div class="flex items-center justify-center gap-2 sm:gap-3 my-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="submissionForm.rating = star"
                                @mouseenter="hoveredStar = star"
                                @mouseleave="hoveredStar = 0"
                                class="p-1.5 focus:outline-none transition-transform hover:scale-110 active:scale-95"
                            >
                                <svg
                                    class="w-9 h-9 sm:w-11 sm:h-11 transition-colors duration-150"
                                    :class="[
                                        star <= (hoveredStar || submissionForm.rating)
                                            ? 'text-amber-400 fill-amber-400 filter drop-shadow-[0_0_8px_rgba(251,191,36,0.5)]'
                                            : 'text-slate-600 fill-slate-700/50'
                                    ]"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Rating Label Tag -->
                        <div class="h-6 mt-1 flex items-center justify-center">
                            <span class="text-xs sm:text-sm font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-0.5 rounded-full">
                                {{ getRatingLabel }}
                            </span>
                        </div>
                        <div v-if="submissionForm.errors.rating" class="text-xs text-rose-400 mt-2 font-medium">
                            {{ submissionForm.errors.rating }}
                        </div>
                    </div>

                    <!-- Section: Dynamic Form Questions -->
                    <div v-if="form?.questions && form.questions.length > 0" class="space-y-6 pt-2">
                        <div class="border-b border-slate-700/50 pb-2">
                            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-400 text-lg">tune</span>
                                Service Evaluation
                            </h3>
                        </div>

                        <div
                            v-for="(q, index) in form.questions"
                            :key="q.id || index"
                            class="p-4 rounded-xl bg-slate-900/40 border border-slate-700/40 space-y-2.5"
                        >
                            <label class="block text-sm font-semibold text-slate-200">
                                {{ q.label }}
                                <span v-if="q.required" class="text-rose-400 ml-1">*</span>
                            </label>

                            <!-- Question Type: Star Rating -->
                            <div v-if="q.type === 'rating'" class="flex items-center gap-2 pt-1">
                                <button
                                    v-for="subStar in 5"
                                    :key="subStar"
                                    type="button"
                                    @click="submissionForm.answers[q.id || `q_${index}`] = subStar"
                                    class="p-1 focus:outline-none transition-transform hover:scale-110"
                                >
                                    <svg
                                        class="w-7 h-7"
                                        :class="[
                                            subStar <= (submissionForm.answers[q.id || `q_${index}`] || 0)
                                                ? 'text-amber-400 fill-amber-400'
                                                : 'text-slate-600 fill-slate-700/40'
                                        ]"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                </button>
                                <span class="text-xs font-semibold text-slate-400 ml-2">
                                    {{ submissionForm.answers[q.id || `q_${index}`] || 0 }} / 5
                                </span>
                            </div>

                            <!-- Question Type: Select Dropdown -->
                            <div v-else-if="q.type === 'select'">
                                <select
                                    v-model="submissionForm.answers[q.id || `q_${index}`]"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                >
                                    <option value="" disabled>Select an option...</option>
                                    <option v-for="(opt, optIdx) in q.options" :key="optIdx" :value="opt">
                                        {{ opt }}
                                    </option>
                                </select>
                            </div>

                            <!-- Question Type: Radio Group -->
                            <div v-else-if="q.type === 'radio'" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                <label
                                    v-for="(opt, optIdx) in q.options"
                                    :key="optIdx"
                                    class="flex items-center gap-3 p-3 rounded-lg border text-xs sm:text-sm cursor-pointer transition-all"
                                    :class="[
                                        submissionForm.answers[q.id || `q_${index}`] === opt
                                            ? 'bg-emerald-500/10 border-emerald-500 text-emerald-300 font-semibold'
                                            : 'bg-slate-800/60 border-slate-700/60 text-slate-300 hover:bg-slate-800'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        :name="`q_radio_${index}`"
                                        :value="opt"
                                        v-model="submissionForm.answers[q.id || `q_${index}`]"
                                        class="text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-700 h-4 w-4"
                                    />
                                    <span>{{ opt }}</span>
                                </label>
                            </div>

                            <!-- Question Type: Text input -->
                            <div v-else-if="q.type === 'text'">
                                <input
                                    type="text"
                                    v-model="submissionForm.answers[q.id || `q_${index}`]"
                                    :placeholder="q.placeholder || 'Your response...'"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>

                            <!-- Question Type: Textarea -->
                            <div v-else>
                                <textarea
                                    v-model="submissionForm.answers[q.id || `q_${index}`]"
                                    rows="3"
                                    :placeholder="q.placeholder || 'Your detailed answer...'"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y"
                                ></textarea>
                            </div>

                            <!-- Field error if any -->
                            <div v-if="submissionForm.errors[`answers.${q.id || `q_${index}`}`]" class="text-xs text-rose-400 font-medium">
                                {{ submissionForm.errors[`answers.${q.id || `q_${index}`}`] }}
                            </div>
                        </div>
                    </div>

                    <!-- Section: Review Quote / Primary Feedback -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-200">
                            Overall Review &amp; Experience
                        </label>
                        <p class="text-xs text-slate-400">
                            What has been your experience using Nimbus? Feel free to mention speed, uptime, management tools, or support.
                        </p>
                        <textarea
                            v-model="submissionForm.feedback"
                            rows="4"
                            placeholder="Nimbus has provided rock-solid stability for our cloud workloads. The control panel is fast, and technical support was right there when we needed assistance..."
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y leading-relaxed"
                        ></textarea>
                        <div v-if="submissionForm.errors.feedback" class="text-xs text-rose-400">
                            {{ submissionForm.errors.feedback }}
                        </div>
                    </div>

                    <!-- Section: Suggestions & Improvements -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-200">
                            Suggestions, Features, or Ideas
                        </label>
                        <p class="text-xs text-slate-400">
                            Is there anything we could do to make your experience even better?
                        </p>
                        <textarea
                            v-model="submissionForm.suggestions"
                            rows="3"
                            placeholder="I'd love to see automated staging environments, Telegram alerts for server reboots, and more one-click database tools..."
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Section: Client Details -->
                    <div class="pt-4 border-t border-slate-700/60 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-400 text-lg">person</span>
                                About You
                            </h3>
                            <label v-if="form?.allow_anonymous" class="inline-flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="submissionForm.is_anonymous"
                                    class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-emerald-500 h-4 w-4"
                                />
                                <span>Submit Anonymously</span>
                            </label>
                        </div>

                        <div v-if="!submissionForm.is_anonymous" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">
                                    Your Full Name <span class="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_name"
                                    placeholder="e.g. Alex Morgan"
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="submissionForm.errors.client_name" class="text-xs text-rose-400 mt-1">
                                    {{ submissionForm.errors.client_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">
                                    Email Address <span class="text-rose-400">*</span>
                                </label>
                                <input
                                    type="email"
                                    v-model="submissionForm.client_email"
                                    placeholder="alex@company.com"
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="submissionForm.errors.client_email" class="text-xs text-rose-400 mt-1">
                                    {{ submissionForm.errors.client_email }}
                                </div>
                            </div>

                            <div v-if="form?.collect_company !== false">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">
                                    Company / Project Name
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_company"
                                    placeholder="e.g. Acme Tech Labs"
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>

                            <div v-if="form?.collect_role !== false">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">
                                    Your Role / Title
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_role"
                                    placeholder="e.g. CTO / DevOps Engineer / Founder"
                                    class="w-full bg-slate-900/80 border border-slate-700 rounded-lg px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="submissionForm.processing"
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-base shadow-xl shadow-emerald-500/20 transition-all hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer"
                        >
                            <span v-if="submissionForm.processing" class="material-symbols-rounded animate-spin text-xl">progress_activity</span>
                            <span v-else class="material-symbols-rounded text-xl">send</span>
                            <span>{{ submissionForm.processing ? 'Submitting Your Feedback...' : 'Submit Feedback & Review' }}</span>
                        </button>
                        <p class="text-center text-xs text-slate-500 mt-3">
                            Thank you for helping us make Nimbus better. We review every single response.
                        </p>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-slate-500">
                Powered by Nimbus Cloud Infrastructure &bull; Secure Feedback Portal
            </div>
        </div>
    </div>
</template>
