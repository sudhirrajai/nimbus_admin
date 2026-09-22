<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    testimonials: Array,
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingTestimonial = ref(null);

const form = useForm({
    name: '',
    role: '',
    company: '',
    location: '',
    quote: '',
    rating: 5,
    sort_order: 0,
    is_active: true,
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    form.rating = 5;
    form.sort_order = (props.testimonials?.length || 0) + 1;
    form.is_active = true;
    isCreateModalOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.testimonials.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            form.reset();
        },
    });
};

const openEditModal = (item) => {
    editingTestimonial.value = item;
    form.clearErrors();
    form.name = item.name;
    form.role = item.role || '';
    form.company = item.company || '';
    form.location = item.location || '';
    form.quote = item.quote;
    form.rating = item.rating;
    form.sort_order = item.sort_order;
    form.is_active = item.is_active;
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!editingTestimonial.value) return;
    form.put(route('admin.testimonials.update', editingTestimonial.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editingTestimonial.value = null;
            form.reset();
        },
    });
};

const toggleActive = (item) => {
    router.post(route('admin.testimonials.toggle-active', item.id), {}, {
        preserveScroll: true,
    });
};

const deleteTestimonial = (item) => {
    if (confirm(`Are you sure you want to delete testimonial from "${item.name}"?`)) {
        router.delete(route('admin.testimonials.destroy', item.id), {
            preserveScroll: true,
        });
    }
};

const activeCount = computed(() => props.testimonials?.filter(t => t.is_active).length || 0);
const avgRating = computed(() => {
    if (!props.testimonials || props.testimonials.length === 0) return 5.0;
    const sum = props.testimonials.reduce((acc, t) => acc + (t.rating || 5), 0);
    return (sum / props.testimonials.length).toFixed(1);
});
</script>

<template>
    <Head title="Manage Testimonials" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900">
                        Client Testimonials
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Manage reviews, client feedback, and endorsements displayed on the public landing page.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-lg">add_circle</span>
                        <span>Add Testimonial</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Reviews</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">forum</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-gray-950 mt-2">{{ testimonials?.length || 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Endorsements on record</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Published Live</span>
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">visibility</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-blue-600 mt-2">{{ activeCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Visible on landing page</div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Average Rating</span>
                        <div class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">star</span>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-amber-600 mt-2">{{ avgRating }} / 5.0</div>
                    <div class="text-xs text-gray-500 mt-1">Verified satisfaction score</div>
                </div>
            </div>

            <!-- Testimonials Grid -->
            <div v-if="testimonials && testimonials.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="item in testimonials" 
                    :key="item.id"
                    class="bg-white border rounded-2xl p-6 shadow-2xs flex flex-col justify-between transition-all hover:shadow-md relative"
                    :class="item.is_active ? 'border-gray-200' : 'border-dashed border-gray-300 bg-gray-50/50 opacity-75'"
                >
                    <!-- Status Pill & Order -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            <span v-for="star in (item.rating || 5)" :key="star">★</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded">
                                Order: {{ item.sort_order }}
                            </span>
                            <button 
                                type="button"
                                @click="toggleActive(item)"
                                :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                                class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border transition-colors cursor-pointer"
                            >
                                {{ item.is_active ? 'Active' : 'Hidden' }}
                            </button>
                        </div>
                    </div>

                    <!-- Quote Text -->
                    <div class="flex-1 mb-5">
                        <p class="text-xs text-gray-700 leading-relaxed italic">
                            "{{ item.quote }}"
                        </p>
                    </div>

                    <!-- Author Info & Location -->
                    <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-2xs">
                                {{ item.name?.charAt(0) || 'C' }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-950 truncate">{{ item.name }}</div>
                                <div v-if="item.role || item.company" class="text-[11px] text-gray-500 truncate">
                                    {{ [item.role, item.company].filter(Boolean).join(' • ') }}
                                </div>
                                <div v-if="item.location" class="text-[10px] text-emerald-700 font-medium flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-rounded text-xs">location_on</span>
                                    <span>{{ item.location }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5 shrink-0 pl-2">
                            <button 
                                type="button" 
                                @click="openEditModal(item)"
                                class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-slate-100 rounded-lg transition-colors"
                                title="Edit Testimonial"
                            >
                                <span class="material-symbols-rounded text-base">edit</span>
                            </button>
                            <button 
                                type="button" 
                                @click="deleteTestimonial(item)"
                                class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete Testimonial"
                            >
                                <span class="material-symbols-rounded text-base">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center">
                <div class="h-12 w-12 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">forum</span>
                </div>
                <h3 class="text-sm font-bold text-gray-950">No Testimonials Yet</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Add client reviews from TestMe, dmanindia, Maharaj POS, and other valued partners to showcase on your landing page.
                </p>
                <button 
                    type="button" 
                    @click="openCreateModal"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm"
                >
                    <span class="material-symbols-rounded text-base">add</span>
                    <span>Add First Testimonial</span>
                </button>
            </div>
        </div>

        <!-- ======================= CREATE MODAL ======================= -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg overflow-hidden animate-fade-in">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">add_comment</span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm">Add Client Testimonial</h3>
                    </div>
                    <button @click="isCreateModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Client / Brand Name *</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                required
                                placeholder="e.g. TestMe, dmanindia"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                            <div v-if="form.errors.name" class="text-[11px] text-red-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Company / Platform</label>
                            <input 
                                type="text" 
                                v-model="form.company" 
                                placeholder="e.g. Maharaj POS"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Role / Industry</label>
                            <input 
                                type="text" 
                                v-model="form.role" 
                                placeholder="e.g. Managed Cloud Client"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Location</label>
                            <input 
                                type="text" 
                                v-model="form.location" 
                                placeholder="e.g. Mumbai, Vapi Gujarat"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Rating (Stars)</label>
                            <select 
                                v-model.number="form.rating" 
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            >
                                <option :value="5">★★★★★ (5 Stars - Excellent)</option>
                                <option :value="4">★★★★☆ (4 Stars - Great)</option>
                                <option :value="3">★★★☆☆ (3 Stars - Average)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Display Sort Order</label>
                            <input 
                                type="number" 
                                v-model.number="form.sort_order" 
                                min="0"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Testimonial Quote *</label>
                        <textarea 
                            v-model="form.quote" 
                            rows="4" 
                            required
                            placeholder="Describe their feedback, reliability, uptime, support experience..."
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none leading-relaxed"
                        ></textarea>
                        <div v-if="form.errors.quote" class="text-[11px] text-red-600 mt-1">{{ form.errors.quote }}</div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input 
                            type="checkbox" 
                            id="create_is_active" 
                            v-model="form.is_active" 
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                        />
                        <label for="create_is_active" class="text-xs text-gray-700 font-medium">Publish immediately on landing page</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="isCreateModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-xl transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm flex items-center gap-2"
                        >
                            <span v-if="form.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>{{ form.processing ? 'Saving...' : 'Save Testimonial' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ======================= EDIT MODAL ======================= -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg overflow-hidden animate-fade-in">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">edit</span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm">Edit Testimonial</h3>
                    </div>
                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <span class="material-symbols-rounded text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Client / Brand Name *</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                required
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                            <div v-if="form.errors.name" class="text-[11px] text-red-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Company / Platform</label>
                            <input 
                                type="text" 
                                v-model="form.company" 
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Role / Industry</label>
                            <input 
                                type="text" 
                                v-model="form.role" 
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Location</label>
                            <input 
                                type="text" 
                                v-model="form.location" 
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Rating (Stars)</label>
                            <select 
                                v-model.number="form.rating" 
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            >
                                <option :value="5">★★★★★ (5 Stars - Excellent)</option>
                                <option :value="4">★★★★☆ (4 Stars - Great)</option>
                                <option :value="3">★★★☆☆ (3 Stars - Average)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Display Sort Order</label>
                            <input 
                                type="number" 
                                v-model.number="form.sort_order" 
                                min="0"
                                class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Testimonial Quote *</label>
                        <textarea 
                            v-model="form.quote" 
                            rows="4" 
                            required
                            class="w-full bg-white border border-gray-200 rounded-xl text-xs text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none leading-relaxed"
                        ></textarea>
                        <div v-if="form.errors.quote" class="text-[11px] text-red-600 mt-1">{{ form.errors.quote }}</div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input 
                            type="checkbox" 
                            id="edit_is_active" 
                            v-model="form.is_active" 
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                        />
                        <label for="edit_is_active" class="text-xs text-gray-700 font-medium">Publish on landing page</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            @click="isEditModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-slate-100 rounded-xl transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm flex items-center gap-2"
                        >
                            <span v-if="form.processing" class="material-symbols-rounded animate-spin text-sm">progress_activity</span>
                            <span>{{ form.processing ? 'Updating...' : 'Update Testimonial' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
