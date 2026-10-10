<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Clock,
    Download,
    ExternalLink,
    HardDrive,
    HelpCircle,
    Layers,
    Mail,
    Moon,
    ShieldCheck,
    Sun,
} from 'lucide-vue-next';

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
    latestInvoice: {
        type: Object,
        default: null,
    },
});

const lightTheme = ref(false);
const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    try {
        window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
    } catch (e) {}
};

onMounted(() => {
    try {
        if (window.localStorage.getItem('rook-theme') === 'light') {
            lightTheme.value = true;
        }
    } catch (e) {}
});

const targetCompletionTime = computed(() => {
    const created = props.account.created_at ? new Date(props.account.created_at) : new Date();
    const target = new Date(created.getTime() + 2 * 60 * 60 * 1000);
    return target.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
});

const formatCurrency = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN');
};
</script>

<template>
    <Head title="Order Confirmed — Provisioning Pending" />

    <main class="rook-site checkout-site" :data-theme="lightTheme ? 'light' : 'dark'">
        <!-- Sticky Header -->
        <header class="checkout-header">
            <div class="shell checkout-header-inner">
                <Link :href="route('home')" class="brand" aria-label="Roook Home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>roook</span>
                </Link>

                <div class="checkout-header-right">
                    <span class="checkout-header-note">
                        <CheckCircle2 :size="14" class="text-emerald-500" aria-hidden="true" />
                        Order Confirmed
                    </span>
                    <button
                        class="theme-toggle"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <div class="shell checkout-main max-w-3xl pb-16">
            <!-- Hero Confirmation Banner -->
            <div class="text-center py-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 mb-4 shadow-sm">
                    <CheckCircle2 :size="28" />
                </div>
                <div class="eyebrow text-emerald-600 font-bold uppercase tracking-wider text-xs">
                    {{ account.status === 'active' ? 'Account Provisioned • Server Live' : 'Payment Received • Order Confirmed' }}
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-[var(--text)] mt-1">
                    {{ account.status === 'active' ? 'Your hosting environment is active and live!' : 'Your hosting environment is being prepared.' }}
                </h1>
                <p class="text-sm text-[var(--text-soft)] max-w-lg mx-auto mt-2">
                    {{ account.status === 'active' ? 'Your server container has been provisioned, DNS/SSL is operational, and your control panel credentials are ready.' : 'Thank you for your order! Your payment was verified and our infrastructure engineering team has received your deployment request.' }}
                </p>

                <!-- Email Verification & Invoice Dispatch Notice -->
                <div class="mt-5 p-4 rounded-xl border border-emerald-500/25 bg-emerald-500/10 flex items-start sm:items-center gap-3 text-left text-xs max-w-xl mx-auto shadow-sm">
                    <div class="p-2 rounded-lg bg-emerald-500/20 text-emerald-600 shrink-0">
                        <Mail :size="16" />
                    </div>
                    <div class="space-y-0.5">
                        <strong class="font-bold text-emerald-700 dark:text-emerald-400 block text-xs">Account verification link &amp; receipt dispatched</strong>
                        <p class="text-[var(--text-soft)] text-[11px] leading-relaxed">
                            We’ve sent an account verification link and official payment invoice to <span class="font-mono font-bold text-[var(--text)]">{{ user?.email || account.user?.email || 'your email' }}</span>. Please check your inbox and verify your email.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ACTIVE / PROVISIONED CARD (When status is active) -->
            <div v-if="account.status === 'active'" class="mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-6 sm:p-8 backdrop-blur-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-emerald-500/20 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span class="font-bold text-xs uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                Current Status: Provisioned &amp; 100% Operational
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-[var(--text)] mt-1">
                            Server Environment Ready
                        </h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="button button-primary button-small inline-flex items-center gap-2 shadow-sm font-bold"
                        >
                            <ExternalLink :size="13" /> 1-Click Login to Control Panel
                        </a>
                    </div>
                </div>

                <p class="text-xs text-[var(--text-soft)] mt-4 leading-relaxed">
                    Your isolated Linux container has been provisioned, Nginx and PHP runtimes configured, auto-renewing Let's Encrypt SSL active, and real-time monitoring enabled. You can log in directly into your control panel with 1-click single sign-on below.
                </p>

                <!-- SLA Step Progress Tracker - All Completed -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-emerald-500/30">
                        <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-[11px]">
                            <CheckCircle2 :size="13" /> 1. Payment Verified
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">Verified via Razorpay</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-emerald-500/30">
                        <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-[11px]">
                            <CheckCircle2 :size="13" /> 2. Server Allocated
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">Container active</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-emerald-500/30">
                        <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-[11px]">
                            <CheckCircle2 :size="13" /> 3. Domain &amp; SSL
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">SSL secured</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-emerald-500/30 ring-1 ring-emerald-500/20">
                        <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-[11px]">
                            <CheckCircle2 :size="13" /> 4. Access Live
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">SSO Ready</div>
                    </div>
                </div>
            </div>

            <!-- PROVISIONING SLA CARD (When status is pending) -->
            <div v-else class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 p-6 sm:p-8 backdrop-blur-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-amber-500/20 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                            <span class="font-bold text-xs uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Current Status: Pending Verification &amp; Server Provisioning
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-[var(--text)] mt-1">
                            Estimated Setup Time: Under 2 Hours
                        </h2>
                    </div>

                    <div class="bg-[var(--panel)] px-4 py-2 rounded-xl border border-[var(--edge)] shrink-0 text-right">
                        <span class="text-[10px] text-[var(--text-muted)] block uppercase tracking-wider font-semibold">Target Ready Time</span>
                        <span class="text-sm font-bold font-mono text-[var(--text)]">by ~{{ targetCompletionTime }}</span>
                    </div>
                </div>

                <p class="text-xs text-[var(--text-soft)] mt-4 leading-relaxed">
                    Our engineering team is currently setting up your isolated server environment, tuning Nginx and PHP runtime limits, configuring Let's Encrypt SSL, and activating intrusion firewalls. As soon as server setup completes, your live access credentials and single sign-on will appear in your client dashboard and be dispatched to your email.
                </p>

                <!-- SLA Step Progress Tracker -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-emerald-500/30">
                        <div class="flex items-center gap-1.5 text-emerald-600 font-bold text-[11px]">
                            <CheckCircle2 :size="13" /> 1. Payment Verified
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">Instant via Razorpay</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-amber-500/40 ring-1 ring-amber-500/20">
                        <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-bold text-[11px]">
                            <Clock :size="13" class="animate-spin" /> 2. Server Allocation
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">In progress (&lt; 2 hrs)</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-[var(--edge)] opacity-70">
                        <div class="flex items-center gap-1.5 text-[var(--text-soft)] font-medium text-[11px]">
                            <Layers :size="13" /> 3. Domain &amp; SSL
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">Automated cutover</div>
                    </div>

                    <div class="bg-[var(--panel)] p-3 rounded-xl border border-[var(--edge)] opacity-70">
                        <div class="flex items-center gap-1.5 text-[var(--text-soft)] font-medium text-[11px]">
                            <HardDrive :size="13" /> 4. Access Live
                        </div>
                        <div class="text-[10px] text-[var(--text-muted)] mt-1">Credentials ready</div>
                    </div>
                </div>
            </div>

            <!-- ORDER SUMMARY CARD -->
            <div class="mt-8 rounded-2xl border border-[var(--edge)] bg-[var(--panel)] p-6 sm:p-8 space-y-5">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[var(--text-muted)]">
                    Order Details &amp; Subscription Terms
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1">
                        <span class="text-[var(--text-muted)]">Hosting Package</span>
                        <div class="font-bold text-sm text-[var(--text)]">{{ account.plan_name || account.package_name }}</div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[var(--text-muted)]">Assigned Primary Domain</span>
                        <div class="font-bold text-sm font-mono text-emerald-600">{{ account.domain }}</div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[var(--text-muted)]">Billing Cycle</span>
                        <div class="font-bold text-sm text-[var(--text)] capitalize">{{ account.billing_cycle || 'Yearly' }} Subscription</div>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[var(--text-muted)]">Amount Paid</span>
                        <div class="font-bold text-sm text-[var(--text)] font-mono">{{ formatCurrency(account.initial_price) }}</div>
                    </div>

                    <div class="space-y-1" v-if="account.renews_at">
                        <span class="text-[var(--text-muted)]">Next Renewal Date</span>
                        <div class="font-medium text-[var(--text)]">{{ new Date(account.renews_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}</div>
                    </div>

                    <div class="space-y-1" v-if="latestInvoice">
                        <span class="text-[var(--text-muted)]">Invoice Reference</span>
                        <div class="font-medium font-mono text-[var(--text)]">#{{ latestInvoice.invoice_number }}</div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="pt-5 border-t border-[var(--edge)] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                        <a v-if="account.status === 'active'" :href="route('hosting.accounts.client-sso', account.id)" target="_blank" class="button button-primary button-small w-full sm:w-auto justify-center font-bold">
                            <ExternalLink :size="13" /> 1-Click Control Panel Login
                        </a>
                        <Link :href="route('hosting.client.index')" :class="account.status === 'active' ? 'button button-outline button-small' : 'button button-primary button-small'" class="w-full sm:w-auto justify-center">
                            Go to Hosting Dashboard <ArrowRight :size="13" />
                        </Link>
                        <Link v-if="latestInvoice" :href="route('invoices.show', latestInvoice.id)" class="button button-outline button-small w-full sm:w-auto justify-center">
                            <Download :size="13" /> View Invoice
                        </Link>
                    </div>

                    <a href="mailto:support@roook.cloud?subject=Question%20regarding%20Hosting%20Setup" class="text-xs text-[var(--text-muted)] hover:text-[var(--accent)] flex items-center gap-1.5 transition-colors">
                        <HelpCircle :size="13" /> Need assistance? Contact ops
                    </a>
                </div>
            </div>
        </div>
    </main>
</template>
