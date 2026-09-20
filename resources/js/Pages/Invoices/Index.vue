<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object,
});

const formatCurrency = (amount, currency = 'INR') => {
    const num = parseFloat(amount) || 0;
    if (currency === 'INR') {
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currency;
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="My Invoices & Receipts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Invoices & Billing Receipts
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">
                        View and download tax invoices and transaction receipts for your Nimbus licenses and managed hosting plans.
                    </p>
                </div>
                <div>
                    <Link 
                        :href="route('subscription')" 
                        class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3.5 py-2 rounded-lg transition-colors"
                    >
                        <span class="material-symbols-rounded text-sm">card_membership</span>
                        View Active Plans
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Invoices Table Container -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div v-if="invoices && invoices.data && invoices.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-gray-500">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Type</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="font-mono text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        <span class="material-symbols-rounded text-emerald-600 text-base">receipt</span>
                                        {{ inv.invoice_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 text-xs text-gray-500">
                                    {{ formatDate(inv.created_at) }}
                                </td>
                                <td class="px-6 py-4.5">
                                    <div class="text-xs font-bold text-gray-900">{{ inv.plan_name || 'Nimbus Service' }}</div>
                                    <div class="text-[11px] text-gray-500 line-clamp-1">{{ inv.description }}</div>
                                </td>
                                <td class="px-6 py-4.5 text-center">
                                    <span 
                                        :class="[
                                            inv.type === 'hosting_plan' 
                                                ? 'bg-blue-50 border-blue-200 text-blue-700' 
                                                : 'bg-purple-50 border-purple-200 text-purple-700'
                                        ]"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-xs"
                                    >
                                        {{ inv.type === 'hosting_plan' ? 'Managed Hosting' : (inv.type === 'license_plan' ? 'License Plan' : 'Custom') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-right font-mono text-xs font-bold text-gray-900">
                                    {{ formatCurrency(inv.amount, inv.currency) }}
                                </td>
                                <td class="px-6 py-4.5 text-center">
                                    <span 
                                        :class="[
                                            inv.status === 'paid' 
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                                : (inv.status === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-gray-50 border-gray-200 text-gray-500')
                                        ]"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-xs"
                                    >
                                        <span class="material-symbols-rounded text-xs">
                                            {{ inv.status === 'paid' ? 'check_circle' : (inv.status === 'pending' ? 'schedule' : 'cancel') }}
                                        </span>
                                        {{ inv.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-right">
                                    <Link 
                                        :href="route('invoices.show', inv.uuid || inv.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 rounded-lg text-xs font-semibold transition-colors border border-gray-200 shadow-2xs"
                                    >
                                        <span class="material-symbols-rounded text-sm">visibility</span>
                                        View & Print
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <div class="h-14 w-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                        <span class="material-symbols-rounded text-3xl">receipt_long</span>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No Invoices Found</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        You don't have any generated invoices or billing receipts yet. Invoices will automatically appear here once you purchase a license or are provisioned a managed hosting plan.
                    </p>
                    <div class="mt-6">
                        <Link 
                            :href="route('dashboard') + '#plans-section'"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-wider shadow-sm transition-all"
                        >
                            <span class="material-symbols-rounded text-sm">shopping_cart</span>
                            Explore Plans
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="invoices && invoices.links && invoices.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-slate-50/50">
                    <div class="text-xs text-gray-500">
                        Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} invoices
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in invoices.links" :key="i">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    link.active 
                                        ? 'bg-emerald-600 text-white font-bold' 
                                        : 'bg-white text-gray-700 hover:bg-slate-100 border border-gray-200',
                                    'px-3 py-1 text-xs rounded-md transition-colors'
                                ]"
                                v-html="link.label"
                            />
                            <span 
                                v-else 
                                class="px-3 py-1 text-xs text-gray-400 opacity-50"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
