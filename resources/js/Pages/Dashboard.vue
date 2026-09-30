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
    <Head title="Workspace Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-[11px] font-mono font-medium text-[var(--accent)] tracking-wider uppercase flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)] animate-pulse" />
                        Live Infrastructure Workspace
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-[var(--text)] mt-1 font-display">
                        Overview &amp; Services
                    </h1>
                    <p class="text-xs text-[var(--text-soft)] mt-0.5">
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
                </div>
            </div>
        </template>

        <div class="space-y-6">
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

            <!-- Stats Overview (shadcn Cards) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Managed Cloud -->
                <div class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-5 hover:border-[var(--edge-strong)] transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Managed Cloud</span>
                        <div class="h-8 w-8 rounded-lg bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center">
                            <Cloud :size="16" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-bold font-display text-[var(--text)]">{{ activeHosting.length }}</div>
                        <div class="text-[11px] text-[var(--text-soft)] mt-1 flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]" />
                            Production instances managed by SRE
                        </div>
                    </div>
                </div>

                <!-- Nimbus Self-Host -->
                <div class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-5 hover:border-[var(--edge-strong)] transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Nimbus Panels</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <Terminal :size="16" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-bold font-display text-[var(--text)]">{{ activeLicenses.length }}</div>
                        <div class="text-[11px] text-[var(--text-soft)] mt-1">
                            Self-hosted server licenses
                        </div>
                    </div>
                </div>

                <!-- Open Invoices -->
                <div class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-5 hover:border-[var(--edge-strong)] transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Invoices</span>
                        <div class="h-8 w-8 rounded-lg bg-[var(--panel-hi)] text-[var(--text-soft)] flex items-center justify-center">
                            <Receipt :size="16" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-bold font-display text-[var(--text)]">{{ pendingInvoices.length }}</div>
                        <div class="text-[11px] text-[var(--text-soft)] mt-1">
                            {{ pendingInvoices.length > 0 ? 'Pending payments due' : 'All accounts settled' }}
                        </div>
                    </div>
                </div>

                <!-- Target Uptime SLA -->
                <div class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-5 hover:border-[var(--edge-strong)] transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Uptime SLA</span>
                        <div class="h-8 w-8 rounded-lg bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center">
                            <Activity :size="16" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-bold font-display text-[var(--text)]">99.99%</div>
                        <div class="text-[11px] text-[var(--accent)] mt-1 font-mono">
                            High availability cluster target
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Hub (shadcn action grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link 
                    :href="route('hosting.client.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Cloud :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate">
                            Managed Hosting
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] truncate">Nodes &amp; 1-click SSO launch</div>
                    </div>
                </Link>

                <Link 
                    :href="route('self-host.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Terminal :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate">
                            Nimbus Self-Host
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] truncate">Keys &amp; curl install command</div>
                    </div>
                </Link>

                <Link 
                    :href="route('store.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-[var(--panel-hi)] text-[var(--text)] border border-[var(--edge)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Layers :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate">
                            Store &amp; Packages
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] truncate">Deploy or upgrade plans</div>
                    </div>
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-4 hover:border-[var(--edge-strong)] hover:bg-[var(--panel-hi)] transition-all flex items-center gap-3.5 group"
                >
                    <div class="h-10 w-10 rounded-lg bg-[var(--panel-hi)] text-[var(--text-muted)] border border-[var(--edge)] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Receipt :size="18" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-[var(--text)] group-hover:text-[var(--accent)] transition-colors truncate">
                            Invoices &amp; Receipts
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] truncate">Download billing proofs</div>
                    </div>
                </Link>
            </div>

            <!-- Active Managed Cloud Instances -->
            <div v-if="hostingAccounts.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <Cloud :size="18" class="text-[var(--accent)]" />
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Active Managed Cloud Instances</h2>
                    </div>
                    <Link :href="route('hosting.client.index')" class="text-xs text-[var(--accent)] hover:underline font-medium flex items-center gap-1">
                        <span>View All ({{ hostingAccounts.length }})</span>
                        <ChevronRight :size="13" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="account in hostingAccounts.slice(0, 4)" 
                        :key="account.id"
                        class="rounded-xl border border-[var(--edge)] p-4 bg-[var(--panel-hi)]/60 hover:bg-[var(--panel-hi)] hover:border-[var(--edge-strong)] transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold font-mono uppercase tracking-wider text-[var(--accent)]">{{ account.plan_name }}</span>
                            <div class="text-sm font-bold text-[var(--text)] font-mono truncate mt-0.5">{{ account.domain }}</div>
                            <div class="text-[11px] text-[var(--text-muted)] mt-0.5">Node: {{ account.server?.ip_address || 'Dedicated Managed VPS' }}</div>
                        </div>
                        <a 
                            :href="route('hosting.accounts.client-sso', account.id)"
                            target="_blank"
                            class="button button-primary button-small shrink-0 self-start sm:self-auto inline-flex items-center gap-1.5"
                        >
                            <LogIn :size="13" />
                            <span>1-Click Login</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Self-Hosted Nimbus Licenses -->
            <div v-if="licenses.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <Terminal :size="18" class="text-[var(--accent)]" />
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Nimbus Control Panel Licenses</h2>
                    </div>
                    <Link :href="route('self-host.index')" class="text-xs text-[var(--accent)] hover:underline font-medium flex items-center gap-1">
                        <span>View All ({{ licenses.length }})</span>
                        <ChevronRight :size="13" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="license in licenses.slice(0, 4)" 
                        :key="license.id"
                        class="rounded-xl border border-[var(--edge)] p-4 bg-[var(--panel-hi)]/60 hover:bg-[var(--panel-hi)] hover:border-[var(--edge-strong)] transition-all space-y-2.5"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold font-mono uppercase tracking-wider text-[var(--accent)]">{{ license.plan }} License</span>
                            <span 
                                :class="license.status === 'active' ? 'bg-[var(--green-wash)] text-[var(--accent)] border-[var(--edge-strong)]' : 'bg-red-500/10 text-red-400 border-red-500/20'"
                                class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase tracking-wider border"
                            >
                                {{ license.status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-[var(--page)] border border-[var(--edge)] font-mono text-xs text-[var(--text)]">
                            <span class="truncate font-semibold">{{ license.license_key }}</span>
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
                        <div class="text-[11px] text-[var(--text-muted)]">Bound IP: {{ license.server_ip || 'Awaiting activation' }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices Table -->
            <div v-if="invoices.length > 0" class="rounded-xl border border-[var(--edge)] bg-[var(--panel)] p-6 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--edge)]">
                    <div class="flex items-center gap-2.5">
                        <Receipt :size="18" class="text-[var(--accent)]" />
                        <h2 class="text-sm font-bold text-[var(--text)] font-display">Recent Billing &amp; Invoices</h2>
                    </div>
                    <Link :href="route('invoices.index')" class="text-xs text-[var(--accent)] hover:underline font-medium flex items-center gap-1">
                        <span>All Invoices</span>
                        <ChevronRight :size="13" />
                    </Link>
                </div>

                <div class="divide-y divide-[var(--edge)]">
                    <div 
                        v-for="inv in invoices.slice(0, 5)" 
                        :key="inv.id" 
                        class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
                    >
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-[var(--text)]">#{{ inv.invoice_number }}</span>
                            <span class="text-[var(--text-muted)]">{{ formatDate(inv.created_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <span class="font-mono font-bold text-[var(--text)]">₹{{ Number(inv.amount).toLocaleString('en-IN') }}</span>
                            <span 
                                :class="inv.status === 'paid' ? 'bg-[var(--green-wash)] text-[var(--accent)] border-[var(--edge-strong)]' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
                                class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase tracking-wider border"
                            >
                                {{ inv.status }}
                            </span>
                            <Link 
                                :href="route('invoices.show', inv.uuid || inv.id)" 
                                class="p-1 text-[var(--text-muted)] hover:text-[var(--text)] rounded transition-colors"
                                title="View Invoice"
                            >
                                <ExternalLink :size="14" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for new users -->
            <div v-if="licenses.length === 0 && hostingAccounts.length === 0" class="rounded-xl border border-dashed border-[var(--edge-strong)] bg-[var(--panel)] p-12 text-center">
                <div class="h-12 w-12 rounded-full bg-[var(--green-wash)] text-[var(--accent)] flex items-center justify-center mx-auto mb-3">
                    <Cloud :size="24" />
                </div>
                <h3 class="text-sm font-bold text-[var(--text)] font-display">Welcome to Your Managed Workspace</h3>
                <p class="text-xs text-[var(--text-muted)] mt-1 max-w-sm mx-auto">
                    You haven’t deployed any servers yet. Choose between fully managed cloud hosting or self-hosting on your own VPS with Nimbus.
                </p>
                <div class="mt-5 flex items-center justify-center gap-3">
                    <Link 
                        :href="route('store.index')" 
                        class="button button-primary button-small"
                    >
                        Browse Managed Hosting Packages
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
