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
            <div class="print:hidden flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <Link 
                        :href="isAdmin && $page.props.auth?.user?.is_admin ? route('admin.invoices.index') : route('invoices.index')"
                        class="p-2 bg-white border border-gray-200 hover:bg-slate-50 text-gray-600 rounded-lg transition-colors flex items-center justify-center shadow-xs shrink-0"
                        title="Back to Invoices"
                    >
                        <span class="material-symbols-rounded text-lg">arrow_back</span>
                    </Link>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-lg sm:text-xl font-bold tracking-tight text-gray-900 truncate">
                                Invoice <span class="font-mono text-emerald-600">#{{ invoice.invoice_number }}</span>
                            </h2>
                            <span 
                                :class="[
                                    invoice.status === 'paid' 
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                        : (invoice.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-100 text-gray-600 border-gray-200')
                                ]"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border shrink-0"
                            >
                                <span class="material-symbols-rounded text-xs">
                                    {{ invoice.status === 'paid' ? 'check_circle' : (invoice.status === 'pending' ? 'hourglass_top' : 'cancel') }}
                                </span>
                                {{ invoice.status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Nimbus by VMCore Official Invoice</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <button 
                        @click="copyInvoiceLink"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-50 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold transition-all shadow-xs"
                    >
                        <span class="material-symbols-rounded text-sm">{{ copied ? 'check' : 'link' }}</span>
                        <span>{{ copied ? 'Copied' : 'Copy Link' }}</span>
                    </button>

                    <button 
                        @click="triggerPrint"
                        type="button"
                        class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-base">print</span>
                        <span>Print / Download</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Preview Wrapper Canvas -->
        <div class="invoice-wrapper py-4 sm:py-8 -my-8 -mx-4 sm:-mx-6 lg:-mx-8 px-2 sm:px-6 bg-slate-100/70 print:bg-white print:p-0 print:m-0 print:border-none">
            
            <!-- Standard A4 Invoice Sheet Container -->
            <div class="invoice-sheet relative bg-white w-full max-w-[880px] mx-auto rounded-2xl shadow-xl border border-gray-200/90 p-4 sm:p-8 md:p-12 lg:p-14 overflow-hidden print:w-full print:max-w-full print:shadow-none print:border-none print:p-0 print:m-0 print:rounded-none">

                <!-- 1. HEADER SECTION: Brand (Left) and Owner Details (Right) -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-8 border-b-2 border-gray-100">
                    <!-- Left: Brand Logo & Tagline -->
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 p-2 shadow-xs text-white">
                                <ApplicationLogo class="h-6 w-6 fill-white" />
                            </div>
                            <div>
                                <span class="text-2xl font-black tracking-tight text-gray-950 font-sans block leading-none">
                                    Nimbus <span class="text-emerald-600 font-bold">by VMCore</span>
                                </span>
                                <span class="text-[10px] text-gray-400 font-semibold tracking-wider uppercase block mt-1">Cloud Server Infrastructure</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 font-medium pl-0.5">
                            {{ company?.tagline || 'Your Hosting, Our Responsibility.' }}
                        </p>
                    </div>

                    <!-- Right: Dynamic Owner Company Details -->
                    <div class="text-left sm:text-right text-xs text-gray-700 leading-relaxed space-y-1">
                        <div class="font-black text-gray-950 text-sm">{{ company?.name || 'Nimbus by VMCore' }}</div>
                        <div v-if="company?.address_line1">{{ company.address_line1 }}</div>
                        <div v-if="company?.address_line2">{{ company.address_line2 }}</div>
                        <div v-if="company?.phone" class="font-medium text-gray-800">Phone: {{ company.phone }}</div>
                        <div v-if="company?.email" class="text-gray-500">{{ company.email }}</div>
                    </div>
                </div>

                <!-- 2. INVOICE META & INVOICED TO (Clean 2-Column Balanced Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-gray-200">
                    <!-- Left: Invoiced To (Dynamic User Details) -->
                    <div class="space-y-1.5">
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Invoiced To</div>
                        <div class="text-base font-bold text-gray-950">{{ invoice.billing_details?.customer_name || invoice.user?.name || 'Valued Customer' }}</div>
                        <div v-if="invoice.user?.company_name" class="text-xs font-semibold text-gray-800">{{ invoice.user.company_name }}</div>
                        <div v-if="invoice.user?.address" class="text-xs text-gray-600">{{ invoice.user.address }}</div>
                        <div v-if="invoice.user?.city || invoice.user?.state || invoice.user?.postal_code" class="text-xs text-gray-600">
                            {{ [invoice.user?.city, invoice.user?.state, invoice.user?.postal_code].filter(Boolean).join(', ') }}
                        </div>
                        <div class="text-xs text-gray-600">{{ invoice.user?.country || 'India' }}</div>
                        <div class="text-xs text-emerald-700 font-medium pt-0.5">{{ invoice.billing_details?.customer_email || invoice.user?.email }}</div>
                        <div v-if="invoice.user?.phone" class="text-xs text-gray-500">{{ invoice.user.phone }}</div>
                    </div>

                    <!-- Right: Invoice Meta & Clean Status Badge -->
                    <div class="sm:text-right space-y-3 flex flex-col sm:items-end justify-between">
                        <div class="space-y-1">
                            <h2 class="text-xl sm:text-2xl font-black text-gray-950 font-mono tracking-tight break-all sm:break-normal">
                                Invoice #{{ invoice.invoice_number }}
                            </h2>
                            <div class="text-xs text-gray-700 space-y-1">
                                <div><span class="text-gray-400 font-medium">Invoice Date:</span> <span class="font-semibold text-gray-900">{{ formatOrdinalDate(invoice.created_at) }}</span></div>
                                <div><span class="text-gray-400 font-medium">Due Date:</span> <span class="font-semibold text-gray-900">{{ invoice.due_date ? formatOrdinalDate(invoice.due_date) : formatOrdinalDate(invoice.created_at) }}</span></div>
                            </div>
                        </div>

                        <!-- Non-overlapping Status Stamp -->
                        <div>
                            <span 
                                :class="[
                                    invoice.status === 'paid' 
                                        ? 'border-emerald-600 text-emerald-700 bg-emerald-50' 
                                        : (invoice.status === 'pending' ? 'border-amber-500 text-amber-700 bg-amber-50' : 'border-gray-400 text-gray-600 bg-gray-50')
                                ]"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-black tracking-widest uppercase border-2 shadow-2xs"
                            >
                                <span class="material-symbols-rounded text-sm">
                                    {{ invoice.status === 'paid' ? 'check_circle' : (invoice.status === 'pending' ? 'schedule' : 'cancel') }}
                                </span>
                                <span>{{ invoice.status === 'paid' ? 'PAID' : (invoice.status === 'pending' ? 'UNPAID' : invoice.status) }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. LINE ITEMS TABLE (Columns: Description, Item type, Total) -->
                <div class="py-6 overflow-x-auto -mx-2 px-2 sm:mx-0 sm:px-0">
                    <table class="w-full min-w-[480px] sm:min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-900 text-xs font-bold uppercase tracking-wider border-t-2 border-b-2 border-gray-300">
                                <th class="py-3 px-3 sm:px-4 w-[60%]">Description</th>
                                <th class="py-3 px-3 sm:px-4 w-[20%] text-left">Item Type</th>
                                <th class="py-3 px-3 sm:px-4 w-[20%] text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs text-gray-800">
                            <tr>
                                <td class="py-4 sm:py-5 px-3 sm:px-4 align-top space-y-1.5">
                                    <div class="text-sm font-bold text-gray-950">
                                        {{ invoice.plan_name || 'Managed Cloud Hosting' }}
                                        <span v-if="invoice.billing_details?.domain || invoice.hostingAccount?.domain">
                                            - {{ invoice.billing_details?.domain || invoice.hostingAccount?.domain }}
                                        </span>
                                        <span v-if="invoice.period_start && invoice.period_end" class="font-normal text-gray-600 text-xs ml-1">
                                            ({{ formatSlashDate(invoice.period_start) }} - {{ formatSlashDate(invoice.period_end) }})
                                        </span>
                                    </div>
                                    <div v-if="invoice.billing_details?.server || invoice.hostingAccount?.server?.name" class="text-gray-600 text-xs flex items-center gap-1">
                                        <span class="text-gray-400">Server Location:</span>
                                        <span class="font-medium text-gray-700">{{ invoice.billing_details?.server || invoice.hostingAccount?.server?.name }}</span>
                                    </div>
                                    <div class="text-gray-500 text-xs leading-relaxed pt-0.5">
                                        {{ invoice.description }}
                                    </div>
                                </td>
                                <td class="py-4 sm:py-5 px-3 sm:px-4 align-top text-gray-700 font-semibold text-xs">
                                    {{ invoice.type === 'hosting_plan' ? 'Hosting' : (invoice.type === 'license_plan' ? 'License' : 'Service') }}
                                </td>
                                <td class="py-4 sm:py-5 px-3 sm:px-4 align-top text-right font-mono font-bold text-sm text-gray-950 whitespace-nowrap">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 4. TOTALS CALCULATION (Sub Total & Total) -->
                <div class="border-t border-gray-200 pt-4 pb-6 flex justify-end">
                    <div class="w-full sm:w-80 space-y-2 text-xs text-right">
                        <div class="flex justify-between py-1.5 border-b border-gray-100">
                            <span class="font-bold text-gray-600">Sub Total</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b-2 border-gray-800 text-base font-black text-gray-950">
                            <span>Total Amount Due</span>
                            <span class="font-mono text-emerald-700">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                        </div>
                    </div>
                </div>

                <!-- 5. TOTAL IN WORDS & TRANSACTIONS SECTION -->
                <div class="space-y-6 pt-2">
                    <!-- Total in words -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 sm:p-4 text-xs flex flex-col sm:flex-row sm:items-baseline gap-1.5 sm:gap-2">
                        <span class="font-bold text-gray-700 shrink-0">Total Amount (in words):</span>
                        <span class="text-gray-900 font-semibold capitalize break-words">
                            Rupees {{ numberToWords(invoice.amount) }} only
                        </span>
                    </div>

                    <!-- Transactions Table -->
                    <div class="space-y-2.5">
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Transactions</h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 -mx-2 px-2 sm:mx-0 sm:px-0">
                            <table class="w-full min-w-[480px] sm:min-w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-900 text-xs font-bold border-b border-gray-200">
                                        <th class="py-3 px-3 sm:px-4">Transaction Date</th>
                                        <th class="py-3 px-3 sm:px-4">Gateway</th>
                                        <th class="py-3 px-3 sm:px-4">Transaction ID</th>
                                        <th class="py-3 px-3 sm:px-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="text-xs text-gray-800 divide-y divide-gray-200">
                                    <tr>
                                        <td class="py-3 px-3 sm:px-4 font-medium whitespace-nowrap">
                                            {{ invoice.paid_at ? formatOrdinalDate(invoice.paid_at) : formatOrdinalDate(invoice.created_at) }}
                                        </td>
                                        <td class="py-3 px-3 sm:px-4 text-gray-600">
                                            {{ invoice.payment_method || 'Online Payment' }}
                                        </td>
                                        <td class="py-3 px-3 sm:px-4 font-mono text-gray-700 break-all">
                                            {{ invoice.payment_id || (invoice.status === 'paid' ? 'Completed' : 'Pending') }}
                                        </td>
                                        <td class="py-3 px-3 sm:px-4 text-right font-mono font-bold text-gray-950 whitespace-nowrap">
                                            {{ formatCurrency(invoice.amount, invoice.currency) }}
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50 font-bold">
                                        <td colspan="3" class="py-3 px-3 sm:px-4 text-right text-gray-700 uppercase tracking-wider text-[11px]">Balance Due</td>
                                        <td class="py-3 px-3 sm:px-4 text-right font-mono text-sm text-gray-950 whitespace-nowrap">
                                            {{ formatCurrency(invoice.status === 'paid' ? 0 : invoice.amount, invoice.currency) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 6. CLEAN MINIMAL FOOTER -->
                <div class="mt-14 pt-8 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 gap-3">
                    <div class="space-y-0.5 text-center sm:text-left">
                        <div class="font-semibold text-gray-700">Thank you for choosing Nimbus by VMCore!</div>
                        <div class="text-[11px] text-gray-400">For support, contact {{ company?.support_email || 'support@vmcore.in' }}</div>
                    </div>
                    <div class="text-center sm:text-right text-[11px] text-gray-400 space-y-0.5">
                        <div>PDF Generated on {{ formatOrdinalDate(new Date()) }}</div>
                        <div>Nimbus by VMCore &bull; Computer Generated Invoice</div>
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
        margin: 10mm 12mm 10mm 12mm;
    }

    html, body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-size: 10.5pt !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Force all wrapper containers to full width on print */
    .min-h-screen,
    .md\:pl-64,
    main,
    main > div,
    .max-w-7xl,
    .invoice-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        border: none !important;
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
