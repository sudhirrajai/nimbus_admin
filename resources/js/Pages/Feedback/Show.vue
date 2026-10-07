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

    <div class="min-h-screen bg-slate-50 text-gray-900 py-10 sm:py-16 px-4 sm:px-6 lg:px-8 selection:bg-emerald-500/20">
        <!-- Subtle Ambient Background Glow -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-100/60 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-40 w-96 h-96 bg-teal-100/50 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-3xl mx-auto">
            <!-- Brand Header -->
            <div class="text-center mb-8 sm:mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold uppercase tracking-wider mb-4 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Roook Hosting
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ form?.title || 'Client Feedback' }}
                </h1>
                <p v-if="form?.description" class="mt-2.5 text-sm sm:text-base text-gray-500 max-w-2xl mx-auto leading-relaxed">
                    {{ form.description }}
                </p>
            </div>

            <!-- State: Form is Closed -->
            <div v-if="isClosed" class="bg-white border border-gray-200 rounded-2xl p-8 sm:p-12 text-center shadow-xs max-w-xl mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-5 text-amber-600">
                    <span class="material-symbols-rounded text-3xl">lock</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Feedback Form Closed</h2>
                <p class="text-gray-500 max-w-md mx-auto mb-6 text-sm">
                    This feedback form is no longer accepting responses. Thank you for your interest and support.
                </p>
                <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider transition-all">
                    &larr; Return to Home
                </a>
            </div>

            <!-- State: Success / Already Submitted -->
            <div v-else-if="isSubmitted" class="bg-white border border-emerald-200 rounded-2xl p-8 sm:p-12 text-center shadow-xs relative overflow-hidden max-w-xl mx-auto">
                <div class="w-20 h-20 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center mx-auto mb-6 text-emerald-600 shadow-sm">
                    <span class="material-symbols-rounded text-4xl">verified</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">
                    {{ form?.success_title || 'Thank you for your feedback!' }}
                </h2>
                <p class="text-gray-600 max-w-lg mx-auto text-sm sm:text-base leading-relaxed mb-6">
                    {{ form?.success_message || 'Your review and suggestions have been shared directly with our engineering and support leads. We truly value your partnership.' }}
                </p>
                
                <div v-if="submissionForm.client_email && !submissionForm.is_anonymous" class="mb-6 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium text-left">
                    <span class="material-symbols-rounded text-emerald-600 text-lg shrink-0">mark_email_read</span>
                    <span>A confirmation email with a full copy of your response has been sent to <strong>{{ submissionForm.client_email }}</strong></span>
                </div>

                <div class="flex items-center justify-center gap-4">
                    <a href="/" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition-all hover:scale-[1.02] uppercase tracking-wider">
                        Visit Nimbus Platform &rarr;
                    </a>
                </div>
            </div>

            <!-- State: Active Feedback Form -->
            <div v-else class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-10 shadow-xs">
                <form @submit.prevent="submitFeedback" class="space-y-8">
                    
                    <!-- Section: Primary Overall Rating -->
                    <div class="text-center py-6 px-4 bg-slate-50/80 border border-gray-200 rounded-2xl">
                        <label class="block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                            Overall Rating &amp; Satisfaction <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-4">How would you score your experience overall?</p>
                        
                        <!-- Stars -->
                        <div class="flex items-center justify-center gap-2 sm:gap-3 my-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="submissionForm.rating = star"
                                @mouseenter="hoveredStar = star"
                                @mouseleave="hoveredStar = 0"
                                class="p-1 focus:outline-none transition-transform hover:scale-115 active:scale-95 cursor-pointer"
                            >
                                <svg
                                    class="w-10 h-10 sm:w-12 sm:h-12 transition-colors duration-150"
                                    :class="[
                                        star <= (hoveredStar || submissionForm.rating)
                                            ? 'text-amber-400 fill-amber-400 filter drop-shadow-[0_2px_6px_rgba(251,191,36,0.35)]'
                                            : 'text-gray-200 fill-gray-200 hover:text-amber-200 hover:fill-amber-200'
                                    ]"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Rating Label Tag -->
                        <div class="h-6 mt-2 flex items-center justify-center">
                            <span class="text-xs sm:text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3.5 py-1 rounded-full shadow-2xs">
                                {{ getRatingLabel }}
                            </span>
                        </div>
                        <div v-if="submissionForm.errors.rating" class="text-xs text-rose-600 mt-2 font-medium">
                            {{ submissionForm.errors.rating }}
                        </div>
                    </div>

                    <!-- Section: Dynamic Form Questions -->
                    <div v-if="form?.questions && form.questions.length > 0" class="space-y-6 pt-2">
                        <div class="border-b border-gray-200 pb-2.5">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-600 text-lg">tune</span>
                                Service Evaluation
                            </h3>
                        </div>

                        <div
                            v-for="(q, index) in form.questions"
                            :key="q.id || index"
                            class="p-4 sm:p-5 rounded-xl bg-slate-50/60 border border-gray-200 space-y-2.5"
                        >
                            <label class="block text-xs sm:text-sm font-bold text-gray-800">
                                {{ q.label }}
                                <span v-if="q.required" class="text-rose-500 ml-1">*</span>
                            </label>

                            <!-- Question Type: Star Rating -->
                            <div v-if="q.type === 'rating'" class="flex items-center gap-2 pt-1">
                                <button
                                    v-for="subStar in 5"
                                    :key="subStar"
                                    type="button"
                                    @click="submissionForm.answers[q.id || `q_${index}`] = subStar"
                                    class="p-1 focus:outline-none transition-transform hover:scale-115 cursor-pointer"
                                >
                                    <svg
                                        class="w-7 h-7"
                                        :class="[
                                            subStar <= (submissionForm.answers[q.id || `q_${index}`] || 0)
                                                ? 'text-amber-400 fill-amber-400'
                                                : 'text-gray-200 fill-gray-200 hover:text-amber-200'
                                        ]"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                </button>
                                <span class="text-xs font-bold text-gray-500 ml-2">
                                    {{ submissionForm.answers[q.id || `q_${index}`] || 0 }} / 5
                                </span>
                            </div>

                            <!-- Question Type: Select Dropdown -->
                            <div v-else-if="q.type === 'select'">
                                <select
                                    v-model="submissionForm.answers[q.id || `q_${index}`]"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
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
                                    class="flex items-center gap-3 p-3 rounded-xl border text-xs sm:text-sm cursor-pointer transition-all"
                                    :class="[
                                        submissionForm.answers[q.id || `q_${index}`] === opt
                                            ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-bold shadow-2xs'
                                            : 'bg-white border-gray-200 text-gray-700 hover:bg-slate-50'
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        :name="`q_radio_${index}`"
                                        :value="opt"
                                        v-model="submissionForm.answers[q.id || `q_${index}`]"
                                        class="text-emerald-600 focus:ring-emerald-500 border-gray-300 h-4 w-4"
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
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>

                            <!-- Question Type: Textarea -->
                            <div v-else>
                                <textarea
                                    v-model="submissionForm.answers[q.id || `q_${index}`]"
                                    rows="3"
                                    :placeholder="q.placeholder || 'Your detailed answer...'"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y"
                                ></textarea>
                            </div>

                            <!-- Field error if any -->
                            <div v-if="submissionForm.errors[`answers.${q.id || `q_${index}`}`]" class="text-xs text-rose-600 font-medium">
                                {{ submissionForm.errors[`answers.${q.id || `q_${index}`}`] }}
                            </div>
                        </div>
                    </div>

                    <!-- Section: Review Quote / Primary Feedback -->
                    <div class="space-y-2">
                        <label class="block text-xs sm:text-sm font-bold text-gray-800">
                            Overall Review &amp; Experience
                        </label>
                        <p class="text-xs text-gray-500">
                            What has been your experience using Nimbus? Feel free to mention speed, uptime, management tools, or support.
                        </p>
                        <textarea
                            v-model="submissionForm.feedback"
                            rows="4"
                            placeholder="Nimbus has provided rock-solid stability for our cloud workloads. The control panel is fast, and technical support was right there when we needed assistance..."
                            class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y leading-relaxed"
                        ></textarea>
                        <div v-if="submissionForm.errors.feedback" class="text-xs text-rose-600">
                            {{ submissionForm.errors.feedback }}
                        </div>
                    </div>

                    <!-- Section: Suggestions & Improvements -->
                    <div class="space-y-2">
                        <label class="block text-xs sm:text-sm font-bold text-gray-800">
                            Suggestions, Features, or Ideas
                        </label>
                        <p class="text-xs text-gray-500">
                            Is there anything we could do to make your experience even better?
                        </p>
                        <textarea
                            v-model="submissionForm.suggestions"
                            rows="3"
                            placeholder="I'd love to see automated staging environments, Telegram alerts for server reboots, and more one-click database tools..."
                            class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 resize-y leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Section: Client Details -->
                    <div class="pt-4 border-t border-gray-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-600 text-lg">person</span>
                                About You
                            </h3>
                            <label v-if="form?.allow_anonymous" class="inline-flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="submissionForm.is_anonymous"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                                />
                                <span>Submit Anonymously</span>
                            </label>
                        </div>

                        <div v-if="!submissionForm.is_anonymous" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Your Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_name"
                                    placeholder="e.g. Alex Morgan"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="submissionForm.errors.client_name" class="text-xs text-rose-600 mt-1">
                                    {{ submissionForm.errors.client_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Email Address <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    v-model="submissionForm.client_email"
                                    placeholder="alex@company.com"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="submissionForm.errors.client_email" class="text-xs text-rose-600 mt-1">
                                    {{ submissionForm.errors.client_email }}
                                </div>
                            </div>

                            <div v-if="form?.collect_company !== false">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Company / Project Name
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_company"
                                    placeholder="e.g. Acme Tech Labs"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>

                            <div v-if="form?.collect_role !== false">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Your Role / Title
                                </label>
                                <input
                                    type="text"
                                    v-model="submissionForm.client_role"
                                    placeholder="e.g. CTO / DevOps Engineer / Founder"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button & Email Copy Notice -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="submissionForm.processing"
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm sm:text-base shadow-sm transition-all hover:scale-[1.005] active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer"
                        >
                            <span v-if="submissionForm.processing" class="material-symbols-rounded animate-spin text-xl">progress_activity</span>
                            <span v-else class="material-symbols-rounded text-xl">send</span>
                            <span>{{ submissionForm.processing ? 'Submitting Your Feedback...' : 'Submit Feedback & Review' }}</span>
                        </button>
                        <p class="text-center text-xs text-gray-500 mt-3 flex items-center justify-center gap-1.5">
                            <span class="material-symbols-rounded text-emerald-600 text-sm">mark_email_read</span>
                            <span>A copy of your response with a thank-you note will be emailed to you automatically.</span>
                        </p>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-gray-400">
                Powered by Nimbus Cloud Infrastructure &bull; Secure Feedback Portal
            </div>
        </div>
    </div>
</template>
