<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Activity,
    Check,
    Cloud,
    Code2,
    Cpu,
    Database,
    HardDrive,
    Layers,
    Lock,
    Mail,
    Plus,
    Server,
    ShieldCheck,
    Terminal,
    Workflow,
    Zap,
} from 'lucide-vue-next';

const props = defineProps({
    features: Array,
    faqs: Array,
    available_icons: Array,
});

const activeTab = ref('features'); // 'features', 'faqs'

// Icon mapping for live preview
const iconComponentMap = {
    cpu: Cpu,
    terminal: Terminal,
    shield: ShieldCheck,
    zap: Zap,
    database: Database,
    workflow: Workflow,
    server: Server,
    cloud: Cloud,
    lock: Lock,
    code: Code2,
    'hard-drive': HardDrive,
    activity: Activity,
    layers: Layers,
    mail: Mail,
    check: Check,
};

const getIconComponent = (key) => {
    return iconComponentMap[key] || Cpu;
};

// ================= FEATURE MODAL STATE =================
const isFeatureModalOpen = ref(false);
const editingFeature = ref(null);

const featureForm = useForm({
    icon: 'cpu',
    tag: 'PERFORMANCE',
    title: '',
    copy: '',
    sort_order: 1,
    is_active: true,
});

const openCreateFeatureModal = () => {
    editingFeature.value = null;
    featureForm.reset();
    featureForm.clearErrors();
    featureForm.icon = 'cpu';
    featureForm.tag = 'FEATURE';
    featureForm.sort_order = (props.features?.length || 0) + 1;
    featureForm.is_active = true;
    isFeatureModalOpen.value = true;
};

const openEditFeatureModal = (feature) => {
    editingFeature.value = feature;
    featureForm.clearErrors();
    featureForm.icon = feature.icon || 'cpu';
    featureForm.tag = feature.tag || '';
    featureForm.title = feature.title;
    featureForm.copy = feature.copy;
    featureForm.sort_order = feature.sort_order;
    featureForm.is_active = Boolean(feature.is_active);
    isFeatureModalOpen.value = true;
};

const submitFeatureForm = () => {
    if (editingFeature.value) {
        featureForm.put(route('admin.nimbus.features.update', editingFeature.value.id), {
            onSuccess: () => {
                isFeatureModalOpen.value = false;
                editingFeature.value = null;
                featureForm.reset();
            },
        });
    } else {
        featureForm.post(route('admin.nimbus.features.store'), {
            onSuccess: () => {
                isFeatureModalOpen.value = false;
                featureForm.reset();
            },
        });
    }
};

const toggleActiveFeature = (feature) => {
    router.post(route('admin.nimbus.features.toggle-active', feature.id), {}, {
        preserveScroll: true,
    });
};

const deleteFeature = (feature) => {
    if (confirm(`Are you sure you want to delete feature "${feature.title}"?`)) {
        router.delete(route('admin.nimbus.features.destroy', feature.id), {
            preserveScroll: true,
        });
    }
};

// ================= FAQ MODAL STATE =================
const isFaqModalOpen = ref(false);
const editingFaq = ref(null);

const faqForm = useForm({
    question: '',
    answer: '',
    sort_order: 1,
    is_active: true,
});

const openCreateFaqModal = () => {
    editingFaq.value = null;
    faqForm.reset();
    faqForm.clearErrors();
    faqForm.sort_order = (props.faqs?.length || 0) + 1;
    faqForm.is_active = true;
    isFaqModalOpen.value = true;
};

const openEditFaqModal = (faq) => {
    editingFaq.value = faq;
    faqForm.clearErrors();
    faqForm.question = faq.question;
    faqForm.answer = faq.answer;
    faqForm.sort_order = faq.sort_order;
    faqForm.is_active = Boolean(faq.is_active);
    isFaqModalOpen.value = true;
};

const submitFaqForm = () => {
    if (editingFaq.value) {
        faqForm.put(route('admin.nimbus.faqs.update', editingFaq.value.id), {
            onSuccess: () => {
                isFaqModalOpen.value = false;
                editingFaq.value = null;
                faqForm.reset();
            },
        });
    } else {
        faqForm.post(route('admin.nimbus.faqs.store'), {
            onSuccess: () => {
                isFaqModalOpen.value = false;
                faqForm.reset();
            },
        });
    }
};

const toggleActiveFaq = (faq) => {
    router.post(route('admin.nimbus.faqs.toggle-active', faq.id), {}, {
        preserveScroll: true,
    });
};

const deleteFaq = (faq) => {
    if (confirm(`Are you sure you want to delete this FAQ question?`)) {
        router.delete(route('admin.nimbus.faqs.destroy', faq.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Nimbus Product CMS - Roook Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                        <span class="material-symbols-rounded text-emerald-600">terminal</span>
                        Nimbus Product Content Management
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Dynamically manage the 01 / ARCHITECTURE section features, built-in icons, and product FAQs on the Nimbus product page.
                    </p>
                </div>
                <div class="flex items-center gap-2.5">
                    <a
                        :href="route('products.nimbus')"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-2xs"
                    >
                        <span class="material-symbols-rounded text-sm">visibility</span>
                        View Product Page
                    </a>
                    <Link
                        :href="route('admin.plans.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-purple-50 border border-purple-200 rounded-lg text-xs font-semibold text-purple-700 hover:bg-purple-100 transition shadow-2xs"
                    >
                        <span class="material-symbols-rounded text-sm">payments</span>
                        Manage Nimbus Plans
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Tabs Header -->
            <div class="flex items-center gap-2 border-b border-gray-200 bg-white px-6 pt-3 rounded-t-xl shadow-2xs">
                <button
                    type="button"
                    @click="activeTab = 'features'"
                    :class="[
                        activeTab === 'features'
                            ? 'border-emerald-600 text-emerald-600 font-bold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-xs flex items-center gap-2 transition-colors uppercase tracking-wider"
                >
                    <span class="material-symbols-rounded text-base">domain</span>
                    01 / Architecture Features
                    <span class="bg-emerald-50 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-mono">
                        {{ features?.length || 0 }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'faqs'"
                    :class="[
                        activeTab === 'faqs'
                            ? 'border-emerald-600 text-emerald-600 font-bold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                    class="py-3 px-4 border-b-2 text-xs flex items-center gap-2 transition-colors uppercase tracking-wider"
                >
                    <span class="material-symbols-rounded text-base">quiz</span>
                    Product FAQs
                    <span class="bg-emerald-50 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-mono">
                        {{ faqs?.length || 0 }}
                    </span>
                </button>
            </div>

            <!-- ================= FEATURES TAB ================= -->
            <div v-if="activeTab === 'features'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Architecture &amp; Capability Features</h3>
                        <p class="text-xs text-gray-500">Add or edit cards shown in section 01 / ARCHITECTURE with built-in high-performance icons.</p>
                    </div>
                    <button
                        type="button"
                        @click="openCreateFeatureModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition"
                    >
                        <span class="material-symbols-rounded text-sm">add</span>
                        Add Architecture Feature
                    </button>
                </div>

                <!-- Features Table Container -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                    <th class="px-5 py-3.5 w-12 text-center">#</th>
                                    <th class="px-5 py-3.5 w-16 text-center">Icon</th>
                                    <th class="px-5 py-3.5 w-36">Tag</th>
                                    <th class="px-5 py-3.5">Feature Title &amp; Description</th>
                                    <th class="px-5 py-3.5 text-center w-28">Status</th>
                                    <th class="px-5 py-3.5 text-right w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(feature, idx) in features" :key="feature.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4 text-center font-mono text-xs text-gray-400">
                                        {{ String(idx + 1).padStart(2, '0') }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center mx-auto shadow-2xs">
                                            <component :is="getIconComponent(feature.icon)" :size="18" :stroke-width="1.8" />
                                        </div>
                                        <span class="text-[9px] font-mono text-gray-400 block mt-1">{{ feature.icon }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ feature.tag || 'FEATURE' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ feature.title }}</div>
                                        <p class="text-xs text-gray-500 mt-1 max-w-xl line-clamp-2 leading-relaxed">
                                            {{ feature.copy }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="toggleActiveFeature(feature)"
                                            :class="feature.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border transition cursor-pointer"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="feature.is_active ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                            {{ feature.is_active ? 'Active' : 'Hidden' }}
                                        </button>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                @click="openEditFeatureModal(feature)"
                                                class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                title="Edit Feature"
                                            >
                                                <span class="material-symbols-rounded text-lg">edit</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteFeature(feature)"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Delete Feature"
                                            >
                                                <span class="material-symbols-rounded text-lg">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!features?.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-xs">
                                        No architecture features configured yet. Click "Add Architecture Feature" to add your first one.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= FAQS TAB ================= -->
            <div v-if="activeTab === 'faqs'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Product Frequently Asked Questions</h3>
                        <p class="text-xs text-gray-500">Edit or add questions and answers shown in the Nimbus product FAQ accordion.</p>
                    </div>
                    <button
                        type="button"
                        @click="openCreateFaqModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition"
                    >
                        <span class="material-symbols-rounded text-sm">add</span>
                        Add FAQ Question
                    </button>
                </div>

                <!-- FAQs Table Container -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-200 text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                    <th class="px-5 py-3.5 w-12 text-center">#</th>
                                    <th class="px-5 py-3.5">Question &amp; Answer</th>
                                    <th class="px-5 py-3.5 text-center w-28">Status</th>
                                    <th class="px-5 py-3.5 text-right w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(faq, idx) in faqs" :key="faq.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4 text-center font-mono text-xs text-gray-400">
                                        {{ String(idx + 1).padStart(2, '0') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ faq.question }}</div>
                                        <p class="text-xs text-gray-500 mt-1 max-w-2xl leading-relaxed">
                                            {{ faq.answer }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="toggleActiveFaq(faq)"
                                            :class="faq.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200 hover:bg-gray-200'"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border transition cursor-pointer"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="faq.is_active ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                            {{ faq.is_active ? 'Active' : 'Hidden' }}
                                        </button>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                @click="openEditFaqModal(faq)"
                                                class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                title="Edit FAQ"
                                            >
                                                <span class="material-symbols-rounded text-lg">edit</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteFaq(faq)"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Delete FAQ"
                                            >
                                                <span class="material-symbols-rounded text-lg">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!faqs?.length">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-xs">
                                        No FAQs configured yet. Click "Add FAQ Question" to create one.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= CREATE / EDIT FEATURE MODAL ================= -->
        <div v-if="isFeatureModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-200 animate-scale-up">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-rounded text-lg">{{ editingFeature ? 'edit' : 'add_circle' }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">
                                {{ editingFeature ? 'Edit Architecture Feature' : 'Add Architecture Feature' }}
                            </h3>
                            <p class="text-[11px] text-gray-500">Configure feature title, tag, built-in icon, and description.</p>
                        </div>
                    </div>
                    <button @click="isFeatureModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitFeatureForm" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <!-- Icon Selection Grid -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Select Built-in Icon
                        </label>
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 bg-slate-50 p-3 rounded-xl border border-gray-200">
                            <button
                                v-for="item in available_icons"
                                :key="item.key"
                                type="button"
                                @click="featureForm.icon = item.key"
                                :class="[
                                    featureForm.icon === item.key
                                        ? 'bg-emerald-600 text-white ring-2 ring-emerald-500 ring-offset-1 border-transparent shadow-sm'
                                        : 'bg-white text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 border-gray-200'
                                ]"
                                class="flex flex-col items-center justify-center p-2 rounded-lg border text-center transition cursor-pointer"
                                :title="item.label"
                            >
                                <component :is="getIconComponent(item.key)" :size="20" :stroke-width="1.8" />
                                <span class="text-[9px] font-mono mt-1 truncate max-w-full leading-tight">
                                    {{ item.key }}
                                </span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                            <span>Selected Icon:</span>
                            <span class="font-mono font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[11px]">
                                {{ featureForm.icon }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tag / Category (Uppercase)
                            </label>
                            <input
                                v-model="featureForm.tag"
                                type="text"
                                required
                                placeholder="e.g. PERFORMANCE, SECURITY, CI/CD"
                                class="w-full text-xs font-mono uppercase rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Sort Order
                            </label>
                            <input
                                v-model.number="featureForm.sort_order"
                                type="number"
                                min="0"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Feature Title
                        </label>
                        <input
                            v-model="featureForm.title"
                            type="text"
                            required
                            placeholder="e.g. Lightweight Core (<25MB RAM)"
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Description / Copy
                        </label>
                        <textarea
                            v-model="featureForm.copy"
                            rows="3"
                            required
                            placeholder="Enter detailed description of this feature..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-sans"
                        ></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50">
                            <input
                                type="checkbox"
                                v-model="featureForm.is_active"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span class="text-xs font-bold text-gray-800">Display this feature on product page</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="isFeatureModalOpen = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="featureForm.processing"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm disabled:opacity-50"
                        >
                            <span class="material-symbols-rounded text-sm">check</span>
                            {{ editingFeature ? 'Save Changes' : 'Create Feature' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= CREATE / EDIT FAQ MODAL ================= -->
        <div v-if="isFaqModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-200 animate-scale-up">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-rounded text-lg">{{ editingFaq ? 'edit' : 'add_circle' }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">
                                {{ editingFaq ? 'Edit FAQ Question' : 'Add FAQ Question' }}
                            </h3>
                            <p class="text-[11px] text-gray-500">Configure question title, helpful answer, and sort order.</p>
                        </div>
                    </div>
                    <button @click="isFaqModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitFaqForm" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Question
                        </label>
                        <input
                            v-model="faqForm.question"
                            type="text"
                            required
                            placeholder="e.g. What Linux distributions are supported?"
                            class="w-full text-sm rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Answer
                        </label>
                        <textarea
                            v-model="faqForm.answer"
                            rows="4"
                            required
                            placeholder="Write comprehensive, helpful answer for prospective users..."
                            class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-sans"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Sort Order
                            </label>
                            <input
                                v-model.number="faqForm.sort_order"
                                type="number"
                                min="0"
                                class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <div class="flex items-end">
                            <label class="w-full flex items-center gap-2 p-2.5 bg-white border border-gray-200 rounded-xl cursor-pointer hover:bg-slate-50">
                                <input
                                    type="checkbox"
                                    v-model="faqForm.is_active"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />
                                <span class="text-xs font-bold text-gray-800">Is Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="isFaqModalOpen = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="faqForm.processing"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm disabled:opacity-50"
                        >
                            <span class="material-symbols-rounded text-sm">check</span>
                            {{ editingFaq ? 'Save Changes' : 'Create FAQ' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
