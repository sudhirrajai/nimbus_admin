<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
    isAdmin: Boolean,
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
        month: 'long',
        day: 'numeric',
    });
};

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Invoice ' + invoice.invoice_number" />

    <AuthenticatedLayout>
        <template #header>
            <div class="print:hidden flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Link 
                        :href="isAdmin && $page.props.auth.user.is_admin ? route('admin.invoices.index') : route('invoices.index')"
                        class="p-2 bg-white border border-gray-200 hover:bg-slate-50 text-gray-600 rounded-lg transition-colors"
                        title="Back to Invoices"
                    >
                        <span class="material-symbols-rounded text-lg">arrow_back</span>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                            Invoice <span class="font-mono text-emerald-600">{{ invoice.invoice_number }}</span>
                        </h2>
                        <p class="text-xs text-gray-500">Official billing receipt and tax documentation.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button 
                        @click="triggerPrint"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-all shadow-sm"
                    >
                        <span class="material-symbols-rounded text-sm">print</span>
                        Print / Download PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto my-4 print:my-0 print:max-w-full">
            <!-- Printable Invoice Paper -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 sm:p-12 print:border-none print:shadow-none print:p-0">
                
                <!-- Invoice Header -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-8 border-b border-gray-200">
                    <!-- Brand & Company Info -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 p-2 shadow-sm">
                                <ApplicationLogo class="h-6 w-6 fill-white" />
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-950 tracking-tight">Nimbus <span class="text-xs font-semibold text-gray-500">by VMCore</span></h1>
                                <p class="text-[11px] text-gray-400">High Performance Cloud & Server Management</p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 space-y-0.5">
                            <p>VMCore Technologies</p>
                            <p>support@vmcore.in • https://nimbus.vmcore.in</p>
                        </div>
                    </div>

                    <!-- Invoice Meta -->
                    <div class="sm:text-right space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-widest text-emerald-600">TAX INVOICE</div>
                        <div class="text-2xl font-black font-mono text-gray-900">{{ invoice.invoice_number }}</div>
                        <div class="text-xs text-gray-500">
                            Issued: <span class="font-medium text-gray-700">{{ formatDate(invoice.created_at) }}</span>
                        </div>
                        <div v-if="invoice.paid_at" class="text-xs text-gray-500">
                            Paid on: <span class="font-medium text-gray-700">{{ formatDate(invoice.paid_at) }}</span>
                        </div>
                        <div class="pt-2">
                            <span 
                                :class="[
                                    invoice.status === 'paid' 
                                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                                        : (invoice.status === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-gray-100 border-gray-200 text-gray-600')
                                ]"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border"
                            >
                                <span class="material-symbols-rounded text-sm">
                                    {{ invoice.status === 'paid' ? 'verified' : (invoice.status === 'pending' ? 'hourglass_top' : 'cancel') }}
                                </span>
                                {{ invoice.status.toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Parties Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-8 border-b border-gray-200">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To</div>
                        <div class="text-base font-bold text-gray-900">{{ invoice.billing_details?.customer_name || invoice.user?.name || 'Customer' }}</div>
                        <div class="text-xs text-gray-600 mt-0.5">{{ invoice.billing_details?.customer_email || invoice.user?.email }}</div>
                        <div v-if="invoice.billing_details?.domain" class="text-xs font-mono text-emerald-700 mt-1">
                            Domain: {{ invoice.billing_details.domain }}
                        </div>
                        <div v-if="invoice.billing_details?.server" class="text-xs text-gray-500 mt-0.5">
                            Node: {{ invoice.billing_details.server }}
                        </div>
                    </div>

                    <div class="sm:text-right">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Details</div>
                        <div class="text-xs text-gray-700">
                            Method: <span class="font-semibold text-gray-900">{{ invoice.payment_method || 'Online Payment' }}</span>
                        </div>
                        <div v-if="invoice.payment_id" class="text-xs text-gray-700 font-mono mt-0.5">
                            Transaction ID: <span class="font-medium text-gray-900">{{ invoice.payment_id }}</span>
                        </div>
                        <div v-if="invoice.license" class="text-xs text-gray-700 font-mono mt-0.5">
                            License: <span class="font-medium text-gray-900">{{ invoice.license.license_key }}</span>
                        </div>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="py-8">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200 text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                <th class="pb-3">Description & Service Item</th>
                                <th class="pb-3 text-center">Category</th>
                                <th class="pb-3 text-right">Price</th>
                                <th class="pb-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ invoice.plan_name || 'Nimbus Cloud Service' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ invoice.description }}</div>
                                    <div v-if="invoice.billing_details?.notes" class="text-xs text-gray-400 mt-1 italic">
                                        Note: {{ invoice.billing_details.notes }}
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-slate-100 text-slate-700">
                                        {{ invoice.type === 'hosting_plan' ? 'Managed Hosting' : (invoice.type === 'license_plan' ? 'License Plan' : 'Service') }}
                                    </span>
                                </td>
                                <td class="py-4 text-right font-mono text-xs font-semibold text-gray-800">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                                <td class="py-4 text-right font-mono text-sm font-bold text-gray-900">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Totals -->
                <div class="border-t-2 border-gray-200 pt-6 flex justify-end">
                    <div class="w-full sm:w-72 space-y-3">
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-mono font-medium">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Taxes / GST</span>
                            <span class="font-mono text-gray-400">Included (0.00)</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between text-base font-bold text-gray-950">
                            <span>Total Due / Paid</span>
                            <span class="font-mono text-emerald-600">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Terms -->
                <div class="mt-12 pt-8 border-t border-gray-100 text-center text-xs text-gray-400 space-y-1">
                    <p class="font-medium text-gray-600">Thank you for choosing Nimbus by VMCore.</p>
                    <p>This is a computer-generated invoice and requires no physical signature.</p>
                    <p>Questions? Reach out to support at <a href="mailto:support@vmcore.in" class="text-emerald-600 underline">support@vmcore.in</a>.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Hide layout chrome during print */
    aside, header, nav, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        background-color: white !important;
        color: black !important;
    }
}
</style>
