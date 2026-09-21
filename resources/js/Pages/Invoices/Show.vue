<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    invoice: Object,
    company: Object,
    isAdmin: Boolean,
});

const copied = ref(false);

const copyInvoiceLink = () => {
    navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2500);
};

const triggerPrint = () => {
    window.print();
};

const formatCurrency = (amount, currency = 'INR') => {
    const num = parseFloat(amount) || 0;
    if (currency === 'INR') {
        return '₹' + num.toFixed(2);
    }
    return '$' + num.toFixed(2);
};

const formatOrdinalDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    const day = d.getDate();
    const suffix = ["th", "st", "nd", "rd"][((day % 100 > 10 && day % 100 < 20) || day % 10 > 3) ? 0 : day % 10];
    const month = d.toLocaleString('en-US', { month: 'short' });
    const year = d.getFullYear();
    return `${day}${suffix} ${month} ${year}`;
};

const formatSlashDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
};

// Convert number to words in Indian numbering system
const numberToWords = (num) => {
    const a = [
        '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
        'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
        'seventeen', 'eighteen', 'nineteen'
    ];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    const inWords = (n) => {
        let str = '';
        if (n >= 10000000) {
            str += inWords(Math.floor(n / 10000000)) + ' crore ';
            n %= 10000000;
        }
        if (n >= 100000) {
            str += inWords(Math.floor(n / 100000)) + ' lakh ';
            n %= 100000;
        }
        if (n >= 1000) {
            str += inWords(Math.floor(n / 1000)) + ' thousand ';
            n %= 1000;
        }
        if (n >= 100) {
            str += inWords(Math.floor(n / 100)) + ' hundred ';
            n %= 100;
        }
        if (n > 0) {
            if (n < 20) {
                str += a[n] + ' ';
            } else {
                str += b[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + a[n % 10] : '') + ' ';
            }
        }
        return str.trim();
    };

    const whole = Math.floor(Number(num) || 0);
    const fraction = Math.round(((Number(num) || 0) - whole) * 100);

    let result = inWords(whole);
    if (!result) result = 'zero';

    if (fraction > 0) {
        result += ' and ' + inWords(fraction) + ' paise';
    }

    return result;
};
</script>

<template>
    <Head :title="'Invoice ' + invoice.invoice_number" />

    <AuthenticatedLayout>
        <!-- On-Screen Control Toolbar (Hidden during Print) -->
        <template #header>
            <div class="print:hidden flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Link 
                        :href="isAdmin && $page.props.auth.user.is_admin ? route('admin.invoices.index') : route('invoices.index')"
                        class="p-2 bg-white border border-gray-200 hover:bg-slate-50 text-gray-600 rounded-lg transition-colors flex items-center justify-center shadow-xs"
                        title="Back to Invoices"
                    >
                        <span class="material-symbols-rounded text-lg">arrow_back</span>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold tracking-tight text-gray-900">
                                Invoice <span class="font-mono text-emerald-600">#{{ invoice.invoice_number }}</span>
                            </h2>
                            <span 
                                :class="[
                                    invoice.status === 'paid' 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                        : (invoice.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-100 text-gray-600 border-gray-200')
                                ]"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                            >
                                <span class="material-symbols-rounded text-xs">
                                    {{ invoice.status === 'paid' ? 'check_circle' : (invoice.status === 'pending' ? 'hourglass_top' : 'cancel') }}
                                </span>
                                {{ invoice.status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Nimbus by VMCore Official Invoice</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button 
                        @click="copyInvoiceLink"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold transition-all shadow-xs"
                    >
                        <span class="material-symbols-rounded text-sm">{{ copied ? 'check' : 'link' }}</span>
                        <span>{{ copied ? 'Copied' : 'Copy Link' }}</span>
                    </button>

                    <button 
                        @click="triggerPrint"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-base">print</span>
                        <span>Print / Download</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Preview Wrapper Canvas -->
        <div class="py-4 sm:py-8 -my-8 -mx-6 lg:-mx-8 px-3 sm:px-6 bg-slate-100/70 print:bg-white print:p-0 print:m-0 print:border-none">
            
            <!-- Standard A4 Invoice Sheet Container -->
            <div class="invoice-sheet relative bg-white max-w-[840px] mx-auto rounded-xl shadow-xl border border-gray-200/90 p-8 sm:p-12 overflow-hidden print:max-w-full print:shadow-none print:border-none print:p-0 print:m-0 print:rounded-none">
                
                <!-- MilesWeb-style Diagonal Status Ribbon (Top Right) -->
                <div v-if="invoice.status === 'paid'" class="absolute top-0 right-0 w-36 h-36 overflow-hidden pointer-events-none z-10 print:block">
                    <div class="bg-emerald-500 text-white font-black text-[13px] tracking-widest uppercase py-1.5 text-center shadow-md transform rotate-45 translate-x-9 translate-y-7 w-48">
                        PAID
                    </div>
                </div>
                <div v-else-if="invoice.status === 'pending'" class="absolute top-0 right-0 w-36 h-36 overflow-hidden pointer-events-none z-10 print:block">
                    <div class="bg-amber-500 text-white font-black text-[11px] tracking-widest uppercase py-1.5 text-center shadow-md transform rotate-45 translate-x-9 translate-y-7 w-48">
                        UNPAID
                    </div>
                </div>
                <div v-else class="absolute top-0 right-0 w-36 h-36 overflow-hidden pointer-events-none z-10 print:block">
                    <div class="bg-gray-500 text-white font-black text-[11px] tracking-widest uppercase py-1.5 text-center shadow-md transform rotate-45 translate-x-9 translate-y-7 w-48">
                        CANCELLED
                    </div>
                </div>

                <!-- 1. HEADER SECTION: Brand (Left) and Owner Details (Right) -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6">
                    <!-- Left: Brand Logo & Tagline -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 p-2 shadow-xs text-white">
                                <ApplicationLogo class="h-5 w-5 fill-white" />
                            </div>
                            <span class="text-2xl font-black tracking-tight text-gray-950 font-sans">
                                Nimbus <span class="text-emerald-600 font-bold">by VMCore</span>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium pl-0.5">
                            {{ company?.tagline || 'Your Hosting, Our Responsibility.' }}
                        </p>
                    </div>

                    <!-- Right: Dynamic Owner Company & Bank Details -->
                    <div class="text-left sm:text-right text-xs text-gray-700 leading-relaxed pr-8 sm:pr-14 space-y-3">
                        <div>
                            <div class="font-bold text-gray-950 text-sm">{{ company?.name || 'Nimbus by VMCore' }}</div>
                            <div v-if="company?.address_line1">{{ company.address_line1 }}</div>
                            <div v-if="company?.address_line2">{{ company.address_line2 }}</div>
                            <div v-if="company?.phone">Phone: {{ company.phone }}</div>
                        </div>

                        <!-- Dynamic Bank Transfer Details -->
                        <div v-if="company?.bank_name" class="text-[11px] text-gray-600 space-y-0.5 pt-1">
                            <div>Bank Name: <strong class="text-gray-900">{{ company.bank_name }}</strong></div>
                            <div>Account Name: <strong class="text-gray-900">{{ company.bank_account_name || company.name || 'Nimbus by VMCore' }}</strong></div>
                            <div>Account Number: <strong class="text-gray-900 font-mono">{{ company.bank_account }}</strong></div>
                            <div>IFSC Code: <strong class="text-gray-900 font-mono">{{ company.bank_ifsc }}</strong></div>
                            <div v-if="company?.bank_upi">UPI ID: <strong class="text-gray-900 font-mono">{{ company.bank_upi }}</strong></div>
                        </div>
                    </div>
                </div>

                <!-- 2. INVOICE META & INVOICED TO (Left Aligned, exactly like MilesWeb sample) -->
                <div class="pt-4 pb-6 space-y-6">
                    <!-- Invoice # and Dates -->
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold text-gray-950">
                            Invoice #{{ invoice.invoice_number }}
                        </h2>
                        <div class="text-xs text-gray-700 space-y-0.5">
                            <div>Invoice Date: <span class="text-gray-900 font-medium">{{ formatOrdinalDate(invoice.created_at) }}</span></div>
                            <div>Due Date: <span class="text-gray-900 font-medium">{{ invoice.due_date ? formatOrdinalDate(invoice.due_date) : formatOrdinalDate(invoice.created_at) }}</span></div>
                        </div>
                    </div>

                    <!-- Invoiced To (Dynamic User Details) -->
                    <div class="space-y-1">
                        <div class="text-sm font-bold text-gray-950">Invoiced To</div>
                        <div class="text-xs text-gray-700 leading-relaxed">
                            <div class="font-semibold text-gray-900">{{ invoice.billing_details?.customer_name || invoice.user?.name || 'Valued Customer' }}</div>
                            <div v-if="invoice.user?.company_name" class="text-gray-800">{{ invoice.user.company_name }}</div>
                            <div v-if="invoice.user?.address" class="text-gray-700">{{ invoice.user.address }}</div>
                            <div v-if="invoice.user?.city || invoice.user?.state || invoice.user?.postal_code" class="text-gray-700">
                                {{ [invoice.user?.city, invoice.user?.state, invoice.user?.postal_code].filter(Boolean).join(', ') }}
                            </div>
                            <div>{{ invoice.user?.country || 'India' }}</div>
                            <div class="text-gray-500 pt-0.5">{{ invoice.billing_details?.customer_email || invoice.user?.email }}</div>
                        </div>
                    </div>
                </div>

                <!-- 3. LINE ITEMS TABLE (Columns: Description, Item type, Total) -->
                <div class="pb-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-900 text-xs font-bold border-t border-b border-gray-300">
                                <th class="py-3 px-4 w-[60%]">Description</th>
                                <th class="py-3 px-4 w-[20%] text-left">Item type</th>
                                <th class="py-3 px-4 w-[20%] text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs text-gray-800">
                            <tr>
                                <td class="py-4 px-4 align-top space-y-1">
                                    <div class="font-bold text-gray-950">
                                        {{ invoice.plan_name || 'Managed Cloud Hosting' }}
                                        <span v-if="invoice.billing_details?.domain || invoice.hostingAccount?.domain">
                                            - {{ invoice.billing_details?.domain || invoice.hostingAccount?.domain }}
                                        </span>
                                        <span v-if="invoice.period_start && invoice.period_end" class="font-normal text-gray-600">
                                            ({{ formatSlashDate(invoice.period_start) }} - {{ formatSlashDate(invoice.period_end) }})
                                        </span>
                                    </div>
                                    <div v-if="invoice.billing_details?.server || invoice.hostingAccount?.server?.name" class="text-gray-600 text-[11px]">
                                        Server Location: {{ invoice.billing_details?.server || invoice.hostingAccount?.server?.name }}
                                    </div>
                                    <div class="text-gray-500 text-[11px] leading-relaxed">
                                        {{ invoice.description }}
                                    </div>
                                </td>
                                <td class="py-4 px-4 align-top text-gray-700 font-medium">
                                    {{ invoice.type === 'hosting_plan' ? 'Hosting' : (invoice.type === 'license_plan' ? 'License' : 'Service') }}
                                </td>
                                <td class="py-4 px-4 align-top text-right font-mono font-bold text-gray-950">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 4. TOTALS CALCULATION (Sub Total & Total, matching sample) -->
                <div class="border-t border-gray-200 pt-3 pb-6 flex justify-end">
                    <div class="w-full sm:w-80 space-y-1.5 text-xs text-right">
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="font-bold text-gray-700">Sub Total</span>
                            <span class="font-mono font-bold text-gray-900">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b-2 border-gray-400 text-sm font-black text-gray-950">
                            <span>Total</span>
                            <span class="font-mono">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                    </div>
                </div>

                <!-- 5. TOTAL IN WORDS & TRANSACTIONS SECTION (Page 2 layout in sample) -->
                <div class="space-y-6 pt-2">
                    <!-- Total in words -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs flex flex-col sm:flex-row sm:items-baseline gap-2">
                        <span class="font-bold text-gray-700 whitespace-nowrap">Total Amount (in words):</span>
                        <span class="text-gray-900 font-medium capitalize">
                            Rupees {{ numberToWords(invoice.amount) }} only
                        </span>
                    </div>

                    <!-- Transactions Table -->
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold text-gray-950">Transactions</h3>
                        <table class="w-full text-left border-collapse border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-gray-900 text-xs font-bold border-b border-gray-200">
                                    <th class="py-2.5 px-3">Transaction Date</th>
                                    <th class="py-2.5 px-3">Gateway</th>
                                    <th class="py-2.5 px-3">Transaction ID</th>
                                    <th class="py-2.5 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs text-gray-800 divide-y divide-gray-200">
                                <tr>
                                    <td class="py-2.5 px-3">
                                        {{ invoice.paid_at ? formatOrdinalDate(invoice.paid_at) : formatOrdinalDate(invoice.created_at) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600">
                                        {{ invoice.payment_method || 'Online Payment' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono">
                                        {{ invoice.payment_id || (invoice.status === 'paid' ? 'Completed' : 'Pending') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-semibold">
                                        {{ formatCurrency(invoice.amount, invoice.currency) }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50/70 font-bold">
                                    <td colspan="3" class="py-2.5 px-3 text-right text-gray-700">Balance</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-gray-950">
                                        {{ formatCurrency(invoice.status === 'paid' ? 0 : invoice.amount, invoice.currency) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. CLEAN MINIMAL FOOTER -->
                <div class="mt-12 pt-6 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between text-[11px] text-gray-400 gap-2">
                    <div>
                        PDF Generated on {{ formatOrdinalDate(new Date()) }}
                    </div>
                    <div class="font-medium text-gray-600">
                        Nimbus by VMCore
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Complete suppression of screen and layout chrome */
    aside,
    header,
    footer,
    nav,
    .print\:hidden,
    .print-hide {
        display: none !important;
    }

    /* Reset global page margins and paper geometry */
    @page {
        size: A4 portrait;
        margin: 12mm 15mm 12mm 15mm;
    }

    html, body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-size: 11pt !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Flatten invoice sheet */
    .invoice-sheet {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* High-fidelity color printing */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    table, tr, td, th {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }
}
</style>
