<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Activity,
    ArrowRight,
    ArrowUpRight,
    Check,
    CheckCircle,
    ChevronRight,
    Cloud,
    Copy,
    ExternalLink,
    HardDrive,
    Layers,
    LogIn,
    Receipt,
    Server,
    ShieldCheck,
    Terminal,
} from 'lucide-vue-next';

const props = defineProps({
    licenses: {
        type: Array,
        default: () => [],
    },
    hostingAccounts: {
        type: Array,
        default: () => [],
    },
    invoices: {
        type: Array,
        default: () => [],
    },
    hostingRequests: {
        type: Array,
        default: () => [],
    },
});

const copiedKey = ref(null);

const activeLicenses = computed(() => props.licenses.filter(l => l.status === 'active'));
const activeHosting = computed(() => props.hostingAccounts.filter(h => h.status === 'active'));
const pendingInvoices = computed(() => props.invoices.filter(i => i.status === 'pending'));

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const copyToClipboard = async (text, id) => {
    try {
        await navigator.clipboard.writeText(text);
        copiedKey.value = id;
        setTimeout(() => {
            copiedKey.value = null;
        }, 2000);
    } catch (e) {
        console.error('Failed to copy', e);
    }
};
</script>

<template>
    <Head title="Dashboard - Rook" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="eyebrow !text-[10px]">
                        Live Infrastructure Workspace
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[var(--text)] mt-1 font-display">
                        Overview &amp; Services
                    </h1>
                    <p class="text-xs sm:text-sm text-[var(--text-soft)] mt-0.5">
                        Real-time status of your managed cloud nodes, Nimbus control panels, and billing.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="button button-primary button-small inline-flex items-center gap-1.5 shadow-sm"
                    >
                        <span>Deploy Services</span>
                        <ArrowUpRight :size="13" />
                    </Link>
                    <Link 
                        :href="route('tickets.index')" 
                        class="button button-outline button-small inline-flex items-center gap-1.5"
                    >
                        <span>SRE Support</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-7">
            <!-- Flash Message Alerts -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error" class="animate-in fade-in duration-200">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-xl bg-[var(--green-wash)] border border-[var(--edge-strong)] text-[var(--text)] text-xs font-medium">
                    <CheckCircle :size="16" class="text-[var(--accent)] shrink-0" />
                    <p>{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-medium">
                    <Activity :size="16" class="text-red-400 shrink-0" />
                    <p>{{ $page.props.errors.error }}</p>
                </div>
            </div>

            <!-- Stats Overview (Authentic Feature Cards) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Managed Cloud -->
                <div class="feature-card !min-h-0 p-5 rounded-xl border border-[var(--edge)] bg-gradient-to-br from-[var(--panel)] to-transparent hover:border-[var(--edge-strong)] hover:bg-[var(--green-wash)]/30 transition-all flex flex-col justify-between group">
                    <div class="flex items-center justify-between">
                        <div class="feature-icon !w-8 !h-8 !rounded-lg">
                            <Cloud :size="16" :stroke-width="1.8" />
                        </div>
                        <span class="feature-index">01</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold font-display text-[var(--text)] tracking-tight">{{ activeHosting.length }}</div>
                        <div class="feature-tag !mt-1 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                            <span>Managed Cloud Instances</span>
                        </div>
                    </div>
                </div>

                <!-- Nimbus Self-Host -->
                <div class="feature-card !min-h-0 p-5 rounded-xl border border-[var(--edge)] bg-gradient-to-br from-[var(--panel)] to-transparent hover:border-[var(--edge-strong)] hover:bg-[var(--green-wash)]/30 transition-all flex flex-col justify-between group">
                    <div class="flex items-center justify-between">
                        <div class="feature-icon !w-8 !h-8 !rounded-lg">
                            <Terminal :size="16" :stroke-width="1.8" />
                        </div>
                        <span class="feature-index">02</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold font-display text-[var(--text)] tracking-tight">{{ activeLicenses.length }}</div>
                        <div class="feature-tag !mt-1 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                            <span>Nimbus Server Panels</span>
                        </div>
                    </div>
                </div>

                <!-- Open Invoices -->
                <div class="feature-card !min-h-0 p-5 rounded-xl border border-[var(--edge)] bg-gradient-to-br from-[var(--panel)] to-transparent hover:border-[var(--edge-strong)] hover:bg-[var(--green-wash)]/30 transition-all flex flex-col justify-between group">
                    <div class="flex items-center justify-between">
                        <div class="feature-icon !w-8 !h-8 !rounded-lg">
                            <Receipt :size="16" :stroke-width="1.8" />
                        </div>
                        <span class="feature-index">03</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold font-display text-[var(--text)] tracking-tight">{{ pendingInvoices.length }}</div>
                        <div class="feature-tag !mt-1 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full" :class="pendingInvoices.length > 0 ? 'bg-amber-400' : 'bg-[var(--accent)]'" />
                            <span>{{ pendingInvoices.length > 0 ? 'Pending Invoices Due' : 'All Accounts Settled' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Target Uptime SLA -->
                <div class="feature-card !min-h-0 p-5 rounded-xl border border-[var(--edge)] bg-gradient-to-br from-[var(--panel)] to-transparent hover:border-[var(--edge-strong)] hover:bg-[var(--green-wash)]/30 transition-all flex flex-col justify-between group">
                    <div class="flex items-center justify-between">
                        <div class="feature-icon !w-8 !h-8 !rounded-lg">
                            <Activity :size="16" :stroke-width="1.8" />
                        </div>
                        <span class="feature-index">04</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-bold font-display text-[var(--text)] tracking-tight">99.99%</div>
                        <div class="feature-tag !mt-1 flex items-center gap-1.5 text-[var(--accent)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)] animate-pulse" />
                            <span>Target Availability SLA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Hub -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <Link 
                    :href="route('hosting.client.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center justify-between group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg border border-[var(--edge-strong)] bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Cloud :size="16" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate font-display">
                                Managed Hosting
                            </div>
                            <div class="text-[10px] text-[var(--text-muted)] font-mono truncate">Production Nodes</div>
                        </div>
                    </div>
                    <ArrowUpRight :size="14" class="text-[var(--text-muted)] group-hover:text-[var(--accent)] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </Link>

                <Link 
                    :href="route('self-host.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center justify-between group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg border border-[var(--edge-strong)] bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Terminal :size="16" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate font-display">
                                Nimbus Self-Host
                            </div>
                            <div class="text-[10px] text-[var(--text-muted)] font-mono truncate">Licenses &amp; CLI</div>
                        </div>
                    </div>
                    <ArrowUpRight :size="14" class="text-[var(--text-muted)] group-hover:text-[var(--accent)] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </Link>

                <Link 
                    :href="route('store.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center justify-between group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg border border-[var(--edge-strong)] bg-[var(--panel-hi)] text-[var(--text)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Layers :size="16" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate font-display">
                                Store &amp; Packages
                            </div>
                            <div class="text-[10px] text-[var(--text-muted)] font-mono truncate">Deploy New Specs</div>
                        </div>
                    </div>
                    <ArrowUpRight :size="14" class="text-[var(--text-muted)] group-hover:text-[var(--accent)] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center justify-between group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg border border-[var(--edge-strong)] bg-[var(--panel-hi)] text-[var(--text-muted)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <Receipt :size="16" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate font-display">
                                Invoices &amp; Billing
                            </div>
                            <div class="text-[10px] text-[var(--text-muted)] font-mono truncate">Download Receipts</div>
                        </div>
                    </div>
                    <ArrowUpRight :size="14" class="text-[var(--text-muted)] group-hover:text-[var(--accent)] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all shrink-0" />
                </Link>
            </div>

            <!-- Active Managed Cloud Instances -->
            <div v-if="hostingAccounts.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg border border-[var(--edge-strong)] bg-[var(--green-wash)] flex items-center justify-center text-[var(--accent)]">
                            <Cloud :size="14" :stroke-width="1.8" />
                        </div>
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Active Managed Cloud Instances</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline font-mono text-[10px] text-[var(--text-muted)] tracking-wider">01 // PRODUCTION</span>
                        <Link :href="route('hosting.client.index')" class="text-xs text-[var(--accent)] hover:underline font-mono flex items-center gap-1">
                            <span>View All ({{ hostingAccounts.length }})</span>
                            <ChevronRight :size="13" />
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="account in hostingAccounts.slice(0, 4)" 
                        :key="account.id"
                        class="rounded-xl border border-[var(--edge)] p-4 bg-[var(--panel-hi)]/60 hover:bg-[var(--panel-hi)] hover:border-[var(--edge-strong)] transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-bold font-mono uppercase tracking-widest px-2 py-0.5 rounded bg-[var(--green-wash)] text-[var(--accent)] border border-[var(--edge-strong)]">
                                    {{ account.plan_name }}
                                </span>
                                <span class="text-[10px] font-mono text-[var(--text-muted)] flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                                    Active
                                </span>
                            </div>
                            <div class="text-sm font-bold text-[var(--text)] font-mono truncate mt-1.5">{{ account.domain }}</div>
                            <div class="text-[11px] text-[var(--text-muted)] font-mono mt-0.5">Node: {{ account.server?.ip_address || 'Dedicated Managed Node' }}</div>
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="button button-primary button-small shrink-0 self-start sm:self-auto inline-flex items-center gap-1.5"
                        >
                            <LogIn :size="13" />
                            <span>1-Click Launch</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Self-Hosted Nimbus Licenses -->
            <div v-if="licenses.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg border border-[var(--edge-strong)] bg-[var(--green-wash)] flex items-center justify-center text-[var(--accent)]">
                            <Terminal :size="14" :stroke-width="1.8" />
                        </div>
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Nimbus Control Panel Licenses</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline font-mono text-[10px] text-[var(--text-muted)] tracking-wider">02 // LICENSES</span>
                        <Link :href="route('self-host.index')" class="text-xs text-[var(--accent)] hover:underline font-mono flex items-center gap-1">
                            <span>View All ({{ licenses.length }})</span>
                            <ChevronRight :size="13" />
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="license in licenses.slice(0, 4)" 
                        :key="license.id"
                        class="rounded-xl border border-[var(--edge)] p-4 bg-[var(--panel-hi)]/60 hover:bg-[var(--panel-hi)] hover:border-[var(--edge-strong)] transition-all space-y-2.5"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-bold font-mono uppercase tracking-widest px-2 py-0.5 rounded bg-[var(--green-wash)] text-[var(--accent)] border border-[var(--edge-strong)]">
                                {{ license.plan }} License
                            </span>
                            <span 
                                :class="license.status === 'active' ? 'bg-[var(--green-wash)] text-[var(--accent)] border-[var(--edge-strong)]' : 'bg-red-500/10 text-red-400 border-red-500/20'"
                                class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-widest border"
                            >
                                {{ license.status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-[var(--page)] border border-[var(--edge)] font-mono text-xs text-[var(--text)]">
                            <span class="truncate font-semibold tracking-wider">{{ license.license_key }}</span>
                            <button
                                type="button"
                                @click="copyToClipboard(license.license_key, license.id)"
                                class="text-[var(--text-muted)] hover:text-[var(--text)] transition-colors p-1"
                                title="Copy Key"
                            >
                                <Check v-if="copiedKey === license.id" :size="13" class="text-[var(--accent)]" />
                                <Copy v-else :size="13" />
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono text-[var(--text-muted)]">
                            <span>Bound IP: {{ license.server_ip || 'Awaiting activation' }}</span>
                            <span class="text-[10px]">Nimbus v2.4 CLI</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices Table -->
            <div v-if="invoices.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg border border-[var(--edge-strong)] bg-[var(--green-wash)] flex items-center justify-center text-[var(--accent)]">
                            <Receipt :size="14" :stroke-width="1.8" />
                        </div>
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Recent Billing &amp; Invoices</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline font-mono text-[10px] text-[var(--text-muted)] tracking-wider">03 // BILLING</span>
                        <Link :href="route('invoices.index')" class="text-xs text-[var(--accent)] hover:underline font-mono flex items-center gap-1">
                            <span>All Invoices</span>
                            <ChevronRight :size="13" />
                        </Link>
                    </div>
                </div>

                <div class="divide-y divide-[var(--edge)]">
                    <div 
                        v-for="inv in invoices.slice(0, 5)" 
                        :key="inv.id" 
                        class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
                    >
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-[var(--text)]">#{{ inv.invoice_number }}</span>
                            <span class="text-[var(--text-muted)] font-mono text-[11px]">{{ formatDate(inv.created_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <span class="font-mono font-bold text-[var(--text)]">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                            <span 
                                :class="inv.status === 'paid' ? 'bg-[var(--green-wash)] text-[var(--accent)] border-[var(--edge-strong)]' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
                                class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-widest border"
                            >
                                {{ inv.status }}
                            </span>
                            <Link 
                                :href="route('invoices.show', inv.uuid || inv.id)" 
                                class="button button-outline button-small !min-h-[28px] !px-2.5 !text-[11px] inline-flex items-center gap-1"
                                title="View Invoice"
                            >
                                <span>Receipt</span>
                                <ExternalLink :size="12" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for new users -->
            <div v-if="licenses.length === 0 && hostingAccounts.length === 0" class="rounded-xl border border-dashed border-[var(--edge-strong)] bg-[var(--panel)] p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-[var(--green-wash)] border border-[var(--edge-strong)] text-[var(--accent)] flex items-center justify-center mx-auto mb-3">
                    <Cloud :size="22" :stroke-width="1.8" />
                </div>
                <div class="eyebrow justify-center !text-[10px] mb-1">Getting Started</div>
                <h3 class="text-base font-bold text-[var(--text)] font-display">Welcome to Your Managed Infrastructure</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1.5 max-w-sm mx-auto">
                    You haven’t deployed any servers yet. Choose between fully managed cloud hosting or self-hosting on your own VPS with Nimbus.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="button button-primary button-small"
                    >
                        Browse Managed Hosting Packages
                    </Link>
                    <Link 
                        :href="route('self-host.index')" 
                        class="button button-outline button-small"
                    >
                        Explore Nimbus Self-Host
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
