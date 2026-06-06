<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plan: Object
});

const featuresText = ref((props.plan.features || []).join('\n'));

const form = useForm({
    name: props.plan.name,
    price_inr: props.plan.price_inr,
    price_usd: props.plan.price_usd,
    billing_period: props.plan.billing_period,
    max_domains: props.plan.max_domains,
    features: props.plan.features || [],
    is_active: props.plan.is_active,
    is_popular: props.plan.is_popular,
    cta_text: props.plan.cta_text || '',
    description: props.plan.description || '',
});

const submit = () => {
    // Process newlines into features array
    form.features = featuresText.value
        .split('\n')
        .map(f => f.trim())
        .filter(f => f.length > 0);

    form.put(route('admin.plans.update', props.plan.id));
};
</script>

<template>
    <Head :title="'Edit Plan - ' + plan.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('admin.plans.index')" 
                    class="text-gray-400 hover:text-gray-900 hover:bg-slate-100 p-1.5 rounded transition-colors inline-block"
                >
                    <span class="material-symbols-rounded text-sm">arrow_back</span>
                </Link>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Edit Plan: {{ plan.name }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Adjust limits, features, and rates for the {{ plan.name }} plan.</p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-gray-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Plan Name -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Plan Name</label>
                            <input 
                                type="text" 
                                v-model="form.name" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                required
                            />
                            <div v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <!-- Plan Slug -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Plan Slug (Read-only)</label>
                            <input 
                                type="text" 
                                :value="plan.slug" 
                                class="w-full bg-slate-50 border border-gray-200 rounded-lg text-sm text-gray-400 p-3 outline-none cursor-not-allowed"
                                readonly
                            />
                        </div>
                    </div>

                    <!-- Tagline/Description -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Description</label>
                        <input 
                            type="text" 
                            v-model="form.description" 
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                        />
                        <div v-if="form.errors.description" class="text-xs text-red-600 mt-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <!-- Price INR -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Price (INR)</label>
                            <input 
                                type="number" 
                                v-model="form.price_inr" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                required
                                min="0"
                            />
                            <div v-if="form.errors.price_inr" class="text-xs text-red-600 mt-1">{{ form.errors.price_inr }}</div>
                        </div>

                        <!-- Price USD -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Price (USD)</label>
                            <input 
                                type="number" 
                                v-model="form.price_usd" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                required
                                min="0"
                            />
                            <div v-if="form.errors.price_usd" class="text-xs text-red-600 mt-1">{{ form.errors.price_usd }}</div>
                        </div>

                        <!-- Billing Period -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Billing Period</label>
                            <input 
                                type="text" 
                                v-model="form.billing_period" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                required
                            />
                            <div v-if="form.errors.billing_period" class="text-xs text-red-600 mt-1">{{ form.errors.billing_period }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Max Domains -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Max Allowed Domains</label>
                            <input 
                                type="number" 
                                v-model="form.max_domains" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                required
                                min="1"
                            />
                            <div v-if="form.errors.max_domains" class="text-xs text-red-600 mt-1">{{ form.errors.max_domains }}</div>
                        </div>

                        <!-- CTA Button Text -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">CTA Button Text</label>
                            <input 
                                type="text" 
                                v-model="form.cta_text" 
                                class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                            />
                            <div v-if="form.errors.cta_text" class="text-xs text-red-600 mt-1">{{ form.errors.cta_text }}</div>
                        </div>
                    </div>

                    <!-- Features text list -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Plan Features (One per line)</label>
                        <textarea 
                            v-model="featuresText" 
                            rows="6"
                            class="w-full bg-white border border-gray-200 rounded-lg text-sm text-gray-900 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors font-sans"
                            placeholder="Example:&#10;1 Premium License&#10;Automatic Domain Locking&#10;Priority Email Support"
                        ></textarea>
                        <div v-if="form.errors.features" class="text-xs text-red-600 mt-1">{{ form.errors.features }}</div>
                    </div>

                    <!-- Checkboxes (Active & Popular) -->
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                v-model="form.is_active" 
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4.5 w-4.5"
                            />
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Plan Active (Show on pages)</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer sm:ml-8">
                            <input 
                                type="checkbox" 
                                v-model="form.is_popular" 
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4.5 w-4.5"
                            />
                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Highlight as "Popular"</span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-205">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white px-5 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                        >
                            Save Plan
                        </button>
                        <Link 
                            :href="route('admin.plans.index')" 
                            class="border border-gray-200 text-gray-750 hover:bg-slate-50 px-5 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
