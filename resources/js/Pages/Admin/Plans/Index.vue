<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    plans: Array
});

const formatPrice = (value, currency) => {
    if (currency === 'INR') {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(value);
    }
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Manage Plans" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Plans & Pricing
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Configure user dashboard plans, pricing rates, and domain limits.</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan Details</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Price (INR)</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Price (USD)</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Max Domains</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Highlight</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="plan in plans" :key="plan.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-gray-900">{{ plan.name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ plan.description || 'No description provided' }}</div>
                                    <div class="text-[10px] font-mono text-gray-400 mt-1">slug: {{ plan.slug }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-sm font-medium text-gray-900">
                                    {{ formatPrice(plan.price_inr, 'INR') }}<span class="text-xs text-gray-400">{{ plan.billing_period }}</span>
                                </td>
                                <td class="px-6 py-4.5 text-sm font-medium text-gray-900">
                                    {{ formatPrice(plan.price_usd, 'USD') }}<span class="text-xs text-gray-400">{{ plan.billing_period }}</span>
                                </td>
                                <td class="px-6 py-4.5 text-sm font-mono text-gray-700">
                                    {{ plan.max_domains }}
                                </td>
                                <td class="px-6 py-4.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border" 
                                        :class="[
                                            plan.is_active 
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                                : 'bg-red-50 border-red-200 text-red-700'
                                        ]">
                                        {{ plan.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5">
                                    <span v-if="plan.is_popular" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-purple-50 border-purple-200 text-purple-700">
                                        Popular
                                    </span>
                                    <span v-else class="text-xs text-gray-400 font-medium">-</span>
                                </td>
                                <td class="px-6 py-4.5 text-right">
                                    <Link 
                                        :href="route('admin.plans.edit', plan.id)" 
                                        class="text-gray-400 hover:text-emerald-600 hover:bg-slate-100 p-1.5 rounded transition-colors inline-block"
                                        title="Edit Plan"
                                    >
                                        <span class="material-symbols-rounded text-sm">edit</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
