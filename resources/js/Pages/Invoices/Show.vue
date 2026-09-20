<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const formatDateTime = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Tax breakdown calculations (assuming 18% GST inclusive for INR)
const numericAmount = computed(() => parseFloat(props.invoice.amount) || 0);
const isINR = computed(() => (props.invoice.currency || 'INR') === 'INR');

const taxableValue = computed(() => {
    if (isINR.value) {
        return (numericAmount.value / 1.18).toFixed(2);
    }
    return numericAmount.value.toFixed(2);
});

const totalGst = computed(() => {
    if (isINR.value) {
        return (numericAmount.value - parseFloat(taxableValue.value)).toFixed(2);
    }
    return '0.00';
});

const halfGst = computed(() => {
    return (parseFloat(totalGst.value) / 2).toFixed(2);
});

// Convert number to Indian words
const numberToWords = (num, currency = 'INR') => {
    const a = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];
    const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    const inWords = (n) => {
        let str = '';
        if (n >= 10000000) {
            str += inWords(Math.floor(n / 10000000)) + ' Crore ';
            n %= 10000000;
        }
        if (n >= 100000) {
            str += inWords(Math.floor(n / 100000)) + ' Lakh ';
            n %= 100000;
        }
        if (n >= 1000) {
            str += inWords(Math.floor(n / 1000)) + ' Thousand ';
            n %= 1000;
        }
        if (n >= 100) {
            str += inWords(Math.floor(n / 100)) + ' Hundred ';
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

    const whole = Math.floor(num);
    const fraction = Math.round((num - whole) * 100);

    let result = inWords(whole);
    if (!result) result = 'Zero';

    if (currency === 'INR') {
        result += ' Indian Rupees';
    } else {
        result += ' ' + currency;
    }

    if (fraction > 0) {
        result += ' and ' + inWords(fraction) + ' Paise';
    }

    return result + ' Only';
};
</script>

<template>
    <Head :title="'Tax Invoice ' + invoice.invoice_number" />

    <AuthenticatedLayout>
        <!-- On-Screen Action Bar (Hidden during Print) -->
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
                                Invoice <span class="font-mono text-emerald-600">{{ invoice.invoice_number }}</span>
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
                        <p class="text-xs text-gray-500 mt-0.5">Official GST-compliant tax document and accounting receipt.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button 
                        @click="copyInvoiceLink"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-gray-700 border border-gray-200 rounded-lg text-xs font-semibold transition-all shadow-xs"
                    >
                        <span class="material-symbols-rounded text-sm">{{ copied ? 'check' : 'link' }}</span>
                        <span>{{ copied ? 'Link Copied!' : 'Copy Link' }}</span>
                    </button>

                    <button 
                        @click="triggerPrint"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm active:scale-95"
                    >
                        <span class="material-symbols-rounded text-base">print</span>
                        <span>Print / Save PDF</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Preview Wrapper Canvas -->
        <div class="py-4 sm:py-8 -my-8 -mx-6 lg:-mx-8 px-3 sm:px-6 bg-slate-100/70 print:bg-white print:p-0 print:m-0 print:border-none">
            
            <!-- Standard A4 Invoice Sheet Container -->
            <div class="invoice-sheet bg-white max-w-[840px] mx-auto rounded-2xl shadow-xl border border-gray-200/90 p-8 sm:p-12 print:max-w-full print:shadow-none print:border-none print:p-0 print:m-0 print:rounded-none">
                
                <!-- Top Decorative Accent Bar -->
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-700 rounded-t-lg -mt-8 sm:-mt-12 -mx-8 sm:-mx-12 mb-8 print:-mt-0 print:-mx-0 print:mb-6 print:rounded-none"></div>

                <!-- 1. HEADER: Company Letterhead & Document Meta -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b border-gray-200 no-break">
                    <!-- Left: Issuer / Company Details -->
                    <div class="space-y-2.5 max-w-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 p-2.5 shadow-sm text-white flex-shrink-0">
                                <ApplicationLogo class="h-6 w-6 fill-white" />
                            </div>
                            <div>
                                <div class="text-xs font-bold uppercase tracking-widest text-emerald-600">{{ company?.brand || 'NIMBUS CLOUD' }}</div>
                                <h1 class="text-lg font-black text-gray-950 tracking-tight leading-tight">{{ company?.name || 'VMCore Technologies Pvt. Ltd.' }}</h1>
                            </div>
                        </div>

                        <div class="text-[11px] text-gray-600 leading-relaxed space-y-0.5 pt-1">
                            <p class="font-medium text-gray-800">{{ company?.address_line1 || '#104, Tech Park Boulevard' }}</p>
                            <p>{{ company?.address_line2 || 'Indiranagar, Bangalore, Karnataka - 560038, India' }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-1 font-mono text-[10px] text-gray-700">
                                <span>GSTIN: <strong class="font-bold text-gray-900">{{ company?.gstin || '29AADCV1234F1Z5' }}</strong></span>
                                <span>PAN: <strong class="font-bold text-gray-900">{{ company?.pan || 'AADCV1234F' }}</strong></span>
                            </div>
                            <div class="text-[10px] text-gray-500 pt-0.5">
                                <span>{{ company?.email || 'billing@vmcore.in' }}</span> • 
                                <span>{{ company?.phone || '+91 80 4567 8900' }}</span> • 
                                <span>{{ company?.website || 'https://nimbus.vmcore.in' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Tax Invoice Title & Metadata -->
                    <div class="sm:text-right space-y-2">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                TAX INVOICE
                            </span>
                            <div class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mt-1">ORIGINAL FOR RECIPIENT</div>
                        </div>

                        <div class="pt-1">
                            <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Invoice Number</div>
                            <div class="text-xl font-black font-mono text-gray-950 tracking-tight">{{ invoice.invoice_number }}</div>
                        </div>

                        <!-- Date Grid -->
                        <div class="text-xs text-gray-600 space-y-1 pt-1">
                            <div class="flex sm:justify-end gap-2">
                                <span class="text-gray-400">Invoice Date:</span>
                                <span class="font-bold text-gray-900">{{ formatDate(invoice.created_at) }}</span>
                            </div>
                            <div class="flex sm:justify-end gap-2">
                                <span class="text-gray-400">Payment Due:</span>
                                <span class="font-bold text-gray-900">
                                    {{ invoice.due_date ? formatDate(invoice.due_date) : (invoice.paid_at ? formatDate(invoice.paid_at) : 'Due on Receipt') }}
                                </span>
                            </div>
                            <div v-if="invoice.period_start && invoice.period_end" class="flex sm:justify-end gap-2 text-[11px]">
                                <span class="text-gray-400">Billing Term:</span>
                                <span class="font-semibold text-emerald-700">
                                    {{ formatDate(invoice.period_start) }} – {{ formatDate(invoice.period_end) }}
                                </span>
                            </div>
                            <div class="flex sm:justify-end gap-2 text-[10px] text-gray-500">
                                <span>Place of Supply:</span>
                                <span class="font-medium text-gray-700">29 - Karnataka (State Code)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PARTIES & TARGET RESOURCE GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-6 border-b border-gray-200 no-break">
                    <!-- Billed To (Buyer) -->
                    <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Billed To (Customer)</span>
                            <span class="text-[10px] font-mono text-gray-500 font-semibold">{{ invoice.user?.customer_code || ('CUST-' + (invoice.user?.uuid ? invoice.user.uuid.substring(0, 8).toUpperCase() : '001')) }}</span>
                        </div>
                        <div class="text-sm font-bold text-gray-950 pt-0.5">
                            {{ invoice.billing_details?.customer_name || invoice.user?.name || 'Valued Customer' }}
                        </div>
                        <div class="text-xs text-gray-600 break-all">
                            {{ invoice.billing_details?.customer_email || invoice.user?.email }}
                        </div>
                        <div class="text-[11px] text-gray-500 pt-1">
                            <span>Status: </span>
                            <strong class="text-emerald-700 font-semibold uppercase">Verified Nimbus Subscriber</strong>
                        </div>
                    </div>

                    <!-- Service Provisioning & Deployment Target -->
                    <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Target Resource & Scope</span>
                            <span class="text-[10px] font-semibold text-emerald-700 uppercase bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                {{ invoice.type === 'hosting_plan' ? 'Managed Cloud' : (invoice.type === 'license_plan' ? 'License Key' : 'Service') }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-700 pt-0.5 flex items-center justify-between">
                            <span class="text-gray-400">Bound Domain:</span>
                            <span class="font-mono font-bold text-gray-900">
                                {{ invoice.billing_details?.domain || invoice.hostingAccount?.domain || 'N/A' }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-700 flex items-center justify-between">
                            <span class="text-gray-400">Server Node:</span>
                            <span class="font-semibold text-gray-900">
                                {{ invoice.billing_details?.server || invoice.hostingAccount?.server?.name || 'Managed Production Cluster' }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-700 flex items-center justify-between">
                            <span class="text-gray-400">Committed Cycle:</span>
                            <span class="font-semibold text-emerald-700">
                                {{ invoice.billing_details?.billing_cycle || '1 Year Annual Term' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. ITEMIZED CHARGES TABLE -->
                <div class="py-6 no-break">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-300 bg-slate-100/70 text-gray-600 text-[10px] font-black uppercase tracking-wider">
                                <th class="py-2.5 px-3 w-8">#</th>
                                <th class="py-2.5 px-3">Service Description & Term</th>
                                <th class="py-2.5 px-3 text-center w-20">SAC</th>
                                <th class="py-2.5 px-3 text-center w-24">Cycle</th>
                                <th class="py-2.5 px-3 text-center w-12">Qty</th>
                                <th class="py-2.5 px-3 text-right w-28">Rate</th>
                                <th class="py-2.5 px-3 text-right w-28">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-4 px-3 align-top font-mono text-xs text-gray-400">01</td>
                                <td class="py-4 px-3 align-top space-y-1">
                                    <div class="text-sm font-bold text-gray-950">
                                        {{ invoice.plan_name || 'Managed Cloud Hosting Service' }}
                                    </div>
                                    <div class="text-xs text-gray-600 leading-relaxed">
                                        {{ invoice.description || 'Nimbus Cloud High-Performance Server & Managed Hosting Service' }}
                                    </div>
                                    <div v-if="invoice.billing_details?.domain || invoice.hostingAccount?.domain" class="text-[11px] font-mono text-emerald-700 pt-0.5">
                                        Primary Hostname: {{ invoice.billing_details?.domain || invoice.hostingAccount?.domain }}
                                    </div>
                                    <div v-if="invoice.period_start && invoice.period_end" class="text-[10px] text-gray-400">
                                        Service Active Period: {{ formatDate(invoice.period_start) }} through {{ formatDate(invoice.period_end) }}
                                    </div>
                                </td>
                                <td class="py-4 px-3 align-top text-center font-mono text-xs text-gray-600">
                                    998315
                                </td>
                                <td class="py-4 px-3 align-top text-center text-xs text-gray-700 font-medium">
                                    {{ invoice.billing_details?.billing_cycle || '1 Year' }}
                                </td>
                                <td class="py-4 px-3 align-top text-center font-mono text-xs text-gray-700">
                                    1
                                </td>
                                <td class="py-4 px-3 align-top text-right font-mono text-xs font-semibold text-gray-800">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                                <td class="py-4 px-3 align-top text-right font-mono text-sm font-bold text-gray-950">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 4. TOTALS & SUMMARY COMPUTATION -->
                <div class="border-t-2 border-gray-300 pt-6 grid grid-cols-1 md:grid-cols-2 gap-8 no-break">
                    <!-- Left Column: Amount in Words & Payment Settlement -->
                    <div class="space-y-4">
                        <!-- Amount in Words -->
                        <div class="bg-slate-50 border border-gray-200 rounded-xl p-3.5 space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Total Amount Chargeable (in Words)</span>
                            <div class="text-xs font-bold text-gray-900 leading-relaxed italic">
                                {{ numberToWords(numericAmount, invoice.currency) }}
                            </div>
                        </div>

                        <!-- Payment & Settlement Record -->
                        <div class="border border-gray-200 rounded-xl p-3.5 space-y-2 bg-white">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">Payment Record</span>
                                <span 
                                    :class="invoice.status === 'paid' ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200'"
                                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
                                >
                                    {{ invoice.status === 'paid' ? 'Paid & Settled' : 'Payment Awaiting' }}
                                </span>
                            </div>

                            <div class="text-xs space-y-1 text-gray-600">
                                <div class="flex justify-between">
                                    <span>Payment Method:</span>
                                    <strong class="text-gray-900">{{ invoice.payment_method || 'Razorpay Gateway' }}</strong>
                                </div>
                                <div v-if="invoice.payment_id" class="flex justify-between font-mono text-[11px]">
                                    <span>Transaction Ref ID:</span>
                                    <strong class="text-gray-900 break-all">{{ invoice.payment_id }}</strong>
                                </div>
                                <div v-if="invoice.paid_at" class="flex justify-between text-[11px]">
                                    <span>Settled Timestamp:</span>
                                    <strong class="text-gray-900">{{ formatDateTime(invoice.paid_at) }}</strong>
                                </div>
                            </div>

                            <!-- If Pending: Show Direct Bank Wire & UPI Instructions -->
                            <div v-if="invoice.status === 'pending'" class="mt-3 pt-3 border-t border-amber-200/80 bg-amber-50/50 -mx-3.5 -mb-3.5 p-3.5 rounded-b-xl space-y-1.5 text-[11px] text-amber-900">
                                <div class="font-bold flex items-center gap-1 text-amber-800 uppercase tracking-wider text-[10px]">
                                    <span class="material-symbols-rounded text-sm">account_balance</span>
                                    Direct NEFT / IMPS / UPI Instructions
                                </div>
                                <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 font-mono text-[10px] text-gray-700 pt-0.5">
                                    <span>Bank: <strong>{{ company?.bank_name || 'HDFC Bank' }}</strong></span>
                                    <span>A/C: <strong>{{ company?.bank_account || '50200088991122' }}</strong></span>
                                    <span>IFSC: <strong>{{ company?.bank_ifsc || 'HDFC0001234' }}</strong></span>
                                    <span>UPI: <strong>{{ company?.bank_upi || 'vmcore@hdfcbank' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Financial Calculation Breakdown -->
                    <div class="space-y-2">
                        <div class="space-y-2 bg-slate-50 border border-gray-200 rounded-xl p-4">
                            <div class="flex justify-between text-xs text-gray-600">
                                <span>Gross Subtotal</span>
                                <span class="font-mono font-semibold text-gray-900">{{ formatCurrency(invoice.amount, invoice.currency) }}</span>
                            </div>

                            <div v-if="isINR" class="space-y-1.5 pt-2 border-t border-gray-200">
                                <div class="flex justify-between text-[11px] text-gray-500">
                                    <span>Net Taxable Value (Base)</span>
                                    <span class="font-mono">₹{{ taxableValue }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-gray-500">
                                    <span>Central GST (CGST 9%)</span>
                                    <span class="font-mono">₹{{ halfGst }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-gray-500">
                                    <span>State GST (SGST 9%)</span>
                                    <span class="font-mono">₹{{ halfGst }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-emerald-700 font-semibold pt-0.5">
                                    <span>Total Taxes (18% GST Included)</span>
                                    <span class="font-mono">₹{{ totalGst }}</span>
                                </div>
                            </div>
                            <div v-else class="flex justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-200">
                                <span>Export of Services (Zero Rated)</span>
                                <span class="font-mono">$0.00</span>
                            </div>

                            <div class="border-t-2 border-gray-300 pt-3 flex justify-between items-baseline">
                                <div>
                                    <div class="text-sm font-black text-gray-950 uppercase tracking-wide">Total Invoice Value</div>
                                    <div class="text-[10px] text-gray-400">All applicable taxes included</div>
                                </div>
                                <div class="text-2xl font-black font-mono text-emerald-700">
                                    {{ formatCurrency(invoice.amount, invoice.currency) }}
                                </div>
                            </div>
                        </div>

                        <!-- Amount Paid & Outstanding Balance -->
                        <div class="border border-gray-200 rounded-xl p-3.5 space-y-1.5 bg-white">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Total Amount Paid:</span>
                                <span class="font-mono font-bold text-gray-900">
                                    {{ formatCurrency(invoice.status === 'paid' ? invoice.amount : 0, invoice.currency) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm font-bold border-t border-gray-100 pt-1.5">
                                <span :class="invoice.status === 'paid' ? 'text-gray-700' : 'text-amber-700'">Net Balance Due:</span>
                                <span 
                                    :class="invoice.status === 'paid' ? 'text-emerald-700' : 'text-amber-700'"
                                    class="font-mono font-black text-base"
                                >
                                    {{ formatCurrency(invoice.status === 'paid' ? 0 : invoice.amount, invoice.currency) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. OFFICIAL STAMP & SIGNATURE BLOCK -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-6 no-break">
                    <!-- Left: Verified Seal -->
                    <div class="flex items-center gap-3">
                        <div class="h-16 w-16 rounded-full border-2 border-dashed border-emerald-600/70 p-1 flex items-center justify-center text-center">
                            <div class="h-full w-full rounded-full bg-emerald-50 border border-emerald-300 flex flex-col items-center justify-center p-1 text-emerald-800">
                                <span class="material-symbols-rounded text-base leading-none">verified</span>
                                <span class="text-[7px] font-black uppercase tracking-tighter leading-tight mt-0.5">OFFICIAL TAX</span>
                                <span class="text-[6px] font-bold uppercase tracking-tight">DOCUMENT</span>
                            </div>
                        </div>
                        <div class="text-[10px] text-gray-500 leading-tight">
                            <div class="font-bold text-gray-800 uppercase">{{ company?.name || 'VMCore Technologies Pvt. Ltd.' }}</div>
                            <div>Digitally Authenticated & Tax Verified</div>
                            <div class="font-mono text-[9px] text-gray-400">Doc Ref: {{ invoice.invoice_number }}</div>
                        </div>
                    </div>

                    <!-- Right: Authorized Signatory -->
                    <div class="text-center sm:text-right space-y-1">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">For {{ company?.name || 'VMCore Technologies Pvt. Ltd.' }}</div>
                        <div class="py-1 flex sm:justify-end">
                            <!-- Stylized Signature Mark -->
                            <svg class="h-9 w-32 text-slate-700" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 40 Q 30 10 50 35 T 90 25 T 130 45 T 170 20 T 190 35" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
                                <path d="M40 50 Q 80 48 160 45" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3"/>
                            </svg>
                        </div>
                        <div class="text-xs font-bold text-gray-900">Authorized Signatory</div>
                        <div class="text-[10px] text-gray-400">Electronic Verification Valid without Physical Signature</div>
                    </div>
                </div>

                <!-- 6. LEGAL DECLARATION & TERMS FOOTER -->
                <div class="mt-8 pt-6 border-t border-gray-100 text-[10px] text-gray-500 space-y-2 no-break">
                    <div class="font-semibold text-gray-700 uppercase tracking-wider text-[9px]">Terms & Conditions & Regulatory Declaration:</div>
                    <p class="leading-relaxed whitespace-pre-line text-gray-500">
                        {{ company?.invoice_terms || "1. All hosting services and server licenses are billed in advance for the committed period.\n2. Cloud services renew automatically at agreed renewal rates unless written cancellation is received 14 days prior to due date.\n3. This is an electronically generated Tax Invoice under Section 13(2) of the Information Technology Act, 2000 and requires no physical signature." }}
                    </p>
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-gray-400 text-[9px] border-t border-gray-100">
                        <div>Billing Support: <a :href="'mailto:' + (company?.email || 'billing@vmcore.in')" class="text-emerald-700 underline font-medium">{{ company?.email || 'billing@vmcore.in' }}</a></div>
                        <div>Official Customer Portal: <a :href="company?.website || 'https://nimbus.vmcore.in'" target="_blank" class="text-emerald-700 underline font-medium">{{ company?.website || 'https://nimbus.vmcore.in' }}</a></div>
                        <div>Page 1 of 1</div>
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
        color: #0f172a !important;
        font-size: 11pt !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Flatten and optimize invoice sheet */
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

    /* Prevent awkward pagination cuts across elements */
    .no-break,
    tr,
    table,
    .grid {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }

    a {
        text-decoration: none !important;
        color: inherit !important;
    }
}
</style>
