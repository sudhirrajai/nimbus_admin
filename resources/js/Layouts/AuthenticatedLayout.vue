<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Sun, 
    Moon, 
    LayoutDashboard, 
    Cloud, 
    ShoppingBag, 
    CreditCard, 
    Receipt, 
    LifeBuoy, 
    Key, 
    Server, 
    Users, 
    Settings, 
    FileText, 
    Layers, 
    Quote, 
    MessageSquare, 
    Package, 
    Bug, 
    LogOut, 
    Menu, 
    X, 
    User, 
    ChevronRight,
    Terminal
} from 'lucide-vue-next';

const lightTheme = ref(false);

onMounted(() => {
    const saved = window.localStorage.getItem('rook-theme');
    if (saved === 'light') {
        lightTheme.value = true;
    }
});

const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
};

const showingNavigationDropdown = ref(false);
const page = usePage();

const pageTitle = computed(() => {
    if (route().current('dashboard')) return 'Dashboard';
    if (route().current('self-host.*')) return 'Nimbus Self-Host';
    if (route().current('hosting.client.*')) return 'Managed Cloud Hosting';
    if (route().current('store.*')) return 'Store & Packages';
    if (route().current('subscription')) return 'My Subscriptions';
    if (route().current('invoices.*')) return 'Invoices & Receipts';
    if (route().current('admin.licenses.index')) return 'Licenses';
    if (route().current('admin.hosting.*')) return 'Managed Hosting';
    if (route().current('admin.invoices.*')) return 'Invoices & Revenue';
    if (route().current('admin.users.index')) return 'Users';
    if (route().current('admin.settings.index')) return 'Settings';
    if (route().current('admin.pages.*')) return 'Manage Pages';
    if (route().current('admin.plans.*')) return 'Manage Plans';
    if (route().current('admin.testimonials.*')) return 'Testimonials';
    if (route().current('admin.feedback.*')) return 'Client Feedback & Reviews';
    if (route().current('admin.tickets.*')) return 'Support Tickets Management';
    if (route().current('tickets.*')) return 'Support Tickets';
    if (route().current('admin.releases.index')) return 'Releases';
    if (route().current('admin.reports.index')) return 'Bug Reports';
    if (route().current('profile.edit')) return 'Profile';
    return 'Dashboard';
});

const isRouteActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="min-h-screen rook-dashboard font-sans overflow-x-hidden" :data-theme="lightTheme ? 'light' : 'dark'">
        
        <!-- Desktop Left Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-[var(--edge)] bg-[var(--panel)] md:flex md:flex-col transition-all duration-300">
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center justify-between border-b border-[var(--edge)] px-5">
                <Link :href="route('dashboard')" class="brand flex items-center" aria-label="Roook Dashboard">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span class="text-xl font-bold tracking-tight text-[var(--text)] font-display">roook</span>
                </Link>
                <span class="text-[9px] font-mono uppercase tracking-widest text-[var(--accent)] px-2 py-0.5 rounded border border-[var(--edge-strong)] bg-[var(--green-wash)] flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)] animate-pulse"></span>
                    Console
                </span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 space-y-1 px-3 py-5 overflow-y-auto">
                <div class="text-[10px] font-mono font-semibold text-[var(--text-muted)] uppercase tracking-wider px-3 mb-2 flex items-center gap-2">
                    <span>01 // Workspace</span>
                    <span class="h-px flex-1 bg-[var(--edge)]"></span>
                </div>
                
                <Link 
                    :href="route('dashboard')" 
                    :class="[
                        isRouteActive('dashboard') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <LayoutDashboard :size="15" :stroke-width="1.8" :class="isRouteActive('dashboard') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Dashboard</span>
                </Link>

                <Link 
                    :href="route('hosting.client.index')" 
                    :class="[
                        route().current('hosting.client.*') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <Cloud :size="15" :stroke-width="1.8" :class="route().current('hosting.client.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Managed Hosting</span>
                </Link>

                <Link 
                    :href="route('self-host.index')" 
                    :class="[
                        route().current('self-host.*') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <Terminal :size="15" :stroke-width="1.8" :class="route().current('self-host.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Nimbus Self-Host</span>
                </Link>

                <Link 
                    :href="route('store.index')" 
                    :class="[
                        route().current('store.*') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <ShoppingBag :size="15" :stroke-width="1.8" :class="route().current('store.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Store &amp; Packages</span>
                </Link>

                <Link 
                    :href="route('subscription')" 
                    :class="[
                        isRouteActive('subscription') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <CreditCard :size="15" :stroke-width="1.8" :class="isRouteActive('subscription') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>My Subscriptions</span>
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    :class="[
                        route().current('invoices.*') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <Receipt :size="15" :stroke-width="1.8" :class="route().current('invoices.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Invoices</span>
                </Link>

                <Link 
                    :href="route('tickets.index')" 
                    :class="[
                        route().current('tickets.*') 
                            ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                            : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                    ]"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                >
                    <LifeBuoy :size="15" :stroke-width="1.8" :class="route().current('tickets.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                    <span>Support Tickets</span>
                </Link>

                <div v-if="$page.props.auth.user.is_admin" class="pt-5">
                    <div class="text-[10px] font-mono font-semibold text-[var(--text-muted)] uppercase tracking-wider px-3 mb-2 flex items-center gap-2">
                        <span>02 // Administration</span>
                        <span class="h-px flex-1 bg-[var(--edge)]"></span>
                    </div>
                    
                    <Link 
                        :href="route('admin.licenses.index')" 
                        :class="[
                            isRouteActive('admin.licenses.index') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Key :size="15" :stroke-width="1.8" :class="isRouteActive('admin.licenses.index') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Licenses</span>
                    </Link>

                    <Link 
                        :href="route('admin.hosting.index')" 
                        :class="[
                            route().current('admin.hosting.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Server :size="15" :stroke-width="1.8" :class="route().current('admin.hosting.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Managed Hosting</span>
                    </Link>

                    <Link 
                        :href="route('admin.invoices.index')" 
                        :class="[
                            route().current('admin.invoices.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Receipt :size="15" :stroke-width="1.8" :class="route().current('admin.invoices.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Invoices</span>
                    </Link>

                    <Link 
                        :href="route('admin.users.index')" 
                        :class="[
                            isRouteActive('admin.users.index') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Users :size="15" :stroke-width="1.8" :class="isRouteActive('admin.users.index') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Users</span>
                    </Link>

                    <Link 
                        :href="route('admin.settings.index')" 
                        :class="[
                            isRouteActive('admin.settings.index') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Settings :size="15" :stroke-width="1.8" :class="isRouteActive('admin.settings.index') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Settings</span>
                    </Link>

                    <Link 
                        :href="route('admin.pages.index')" 
                        :class="[
                            route().current('admin.pages.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <FileText :size="15" :stroke-width="1.8" :class="route().current('admin.pages.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Manage Pages</span>
                    </Link>

                    <Link 
                        :href="route('admin.plans.index')" 
                        :class="[
                            route().current('admin.plans.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Layers :size="15" :stroke-width="1.8" :class="route().current('admin.plans.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Manage Plans</span>
                    </Link>

                    <Link 
                        :href="route('admin.nimbus.index')" 
                        :class="[
                            route().current('admin.nimbus.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Terminal :size="15" :stroke-width="1.8" :class="route().current('admin.nimbus.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Nimbus Product</span>
                    </Link>

                    <Link 
                        :href="route('admin.testimonials.index')" 
                        :class="[
                            route().current('admin.testimonials.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Quote :size="15" :stroke-width="1.8" :class="route().current('admin.testimonials.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Testimonials</span>
                    </Link>

                    <Link 
                        :href="route('admin.feedback.index')" 
                        :class="[
                            route().current('admin.feedback.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <MessageSquare :size="15" :stroke-width="1.8" :class="route().current('admin.feedback.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Client Feedback</span>
                    </Link>

                    <Link 
                        :href="route('admin.tickets.index')" 
                        :class="[
                            route().current('admin.tickets.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <LifeBuoy :size="15" :stroke-width="1.8" :class="route().current('admin.tickets.*') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Support Tickets</span>
                    </Link>

                    <Link 
                        :href="route('admin.releases.index')" 
                        :class="[
                            isRouteActive('admin.releases.index') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Package :size="15" :stroke-width="1.8" :class="isRouteActive('admin.releases.index') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Nimbus Releases</span>
                    </Link>

                    <Link 
                        :href="route('admin.reports.index')" 
                        :class="[
                            isRouteActive('admin.reports.index') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all group hover:translate-x-0.5"
                    >
                        <Bug :size="15" :stroke-width="1.8" :class="isRouteActive('admin.reports.index') ? 'text-[var(--accent)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text)] transition-colors'" />
                        <span>Bug Reports</span>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar Footer / User Profile Card -->
            <div class="border-t border-[var(--edge)] p-3 bg-[var(--panel)]">
                <div class="flex items-center justify-between gap-2 p-2 rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)] hover:border-[var(--edge-strong)] transition-all">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="h-8 w-8 rounded-lg bg-[var(--green-wash)] border border-[var(--edge-strong)] flex items-center justify-center text-xs font-mono font-bold text-[var(--accent)] shrink-0">
                            {{ $page.props.auth.user.name.substring(0, 2).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-[var(--text)] truncate font-display">{{ $page.props.auth.user.name }}</div>
                            <div class="text-[10px] font-mono text-[var(--text-muted)] truncate flex items-center gap-1.5 mt-0.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>
                                <span>{{ $page.props.auth.user.is_admin ? 'Admin' : 'Client' }}</span>
                            </div>
                        </div>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-[var(--text-muted)] hover:text-rose-400 p-1.5 rounded-lg hover:bg-[var(--panel)] transition-colors" title="Log Out">
                        <LogOut :size="14" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Menu (Slide-out Sidebar) -->
        <div v-if="showingNavigationDropdown" class="fixed inset-0 z-50 flex md:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showingNavigationDropdown = false"></div>

            <!-- Drawer Container -->
            <div class="relative flex w-full max-w-[280px] sm:max-w-xs flex-1 flex-col bg-[var(--panel)] border-r border-[var(--edge)] pt-4 pb-4 animate-slide-in shadow-2xl z-10">
                <!-- Drawer Header with integrated close button -->
                <div class="flex shrink-0 items-center justify-between gap-2.5 px-5 pb-4 border-b border-[var(--edge)]">
                    <Link :href="route('dashboard')" @click="showingNavigationDropdown = false" class="brand flex items-center" aria-label="Roook Dashboard">
                        <span class="brand-mark" aria-hidden="true">r</span>
                        <span class="text-xl font-bold tracking-tight text-[var(--text)] font-display">roook</span>
                    </Link>
                    <button @click="showingNavigationDropdown = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-[var(--panel-hi)] transition-colors" aria-label="Close menu">
                        <X :size="18" />
                    </button>
                </div>

                <!-- Nav list inside Mobile Drawer -->
                <nav class="mt-4 flex-1 space-y-1 px-3 overflow-y-auto">
                    <div class="text-[10px] font-mono font-semibold text-[var(--text-muted)] uppercase tracking-wider px-3 mb-2 flex items-center gap-2">
                        <span>01 // Workspace</span>
                        <span class="h-px flex-1 bg-[var(--edge)]"></span>
                    </div>

                    <Link 
                        :href="route('dashboard')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            isRouteActive('dashboard') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <LayoutDashboard :size="15" :stroke-width="1.8" />
                        <span>Dashboard</span>
                    </Link>

                    <Link 
                        :href="route('hosting.client.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('hosting.client.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <Cloud :size="15" :stroke-width="1.8" />
                        <span>Managed Hosting</span>
                    </Link>

                    <Link 
                        :href="route('self-host.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('self-host.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <Terminal :size="15" :stroke-width="1.8" />
                        <span>Nimbus Self-Host</span>
                    </Link>

                    <Link 
                        :href="route('store.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('store.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <ShoppingBag :size="15" :stroke-width="1.8" />
                        <span>Store &amp; Packages</span>
                    </Link>

                    <Link 
                        :href="route('subscription')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            isRouteActive('subscription') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <CreditCard :size="15" :stroke-width="1.8" />
                        <span>My Subscriptions</span>
                    </Link>

                    <Link 
                        :href="route('invoices.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('invoices.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <Receipt :size="15" :stroke-width="1.8" />
                        <span>Invoices</span>
                    </Link>

                    <Link 
                        :href="route('tickets.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('tickets.*') 
                                ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                        ]"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                    >
                        <LifeBuoy :size="15" :stroke-width="1.8" />
                        <span>Support Tickets</span>
                    </Link>

                    <div v-if="$page.props.auth.user.is_admin" class="pt-5">
                        <div class="text-[10px] font-mono font-semibold text-[var(--text-muted)] uppercase tracking-wider px-3 mb-2 flex items-center gap-2">
                            <span>02 // Administration</span>
                            <span class="h-px flex-1 bg-[var(--edge)]"></span>
                        </div>

                        <Link 
                            :href="route('admin.licenses.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.licenses.index') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Key :size="15" :stroke-width="1.8" />
                            <span>Licenses</span>
                        </Link>

                        <Link 
                            :href="route('admin.hosting.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.hosting.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Server :size="15" :stroke-width="1.8" />
                            <span>Managed Hosting</span>
                        </Link>

                        <Link 
                            :href="route('admin.invoices.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.invoices.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Receipt :size="15" :stroke-width="1.8" />
                            <span>Invoices</span>
                        </Link>

                        <Link 
                            :href="route('admin.users.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.users.index') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Users :size="15" :stroke-width="1.8" />
                            <span>Users</span>
                        </Link>

                        <Link 
                            :href="route('admin.settings.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.settings.index') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Settings :size="15" :stroke-width="1.8" />
                            <span>Settings</span>
                        </Link>

                        <Link 
                            :href="route('admin.pages.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.pages.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <FileText :size="15" :stroke-width="1.8" />
                            <span>Manage Pages</span>
                        </Link>

                        <Link 
                            :href="route('admin.plans.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.plans.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Layers :size="15" :stroke-width="1.8" />
                            <span>Manage Plans</span>
                        </Link>

                        <Link 
                            :href="route('admin.nimbus.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.nimbus.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Terminal :size="15" :stroke-width="1.8" />
                            <span>Nimbus Product</span>
                        </Link>

                        <Link 
                            :href="route('admin.testimonials.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.testimonials.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Quote :size="15" :stroke-width="1.8" />
                            <span>Testimonials</span>
                        </Link>

                        <Link 
                            :href="route('admin.feedback.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.feedback.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <MessageSquare :size="15" :stroke-width="1.8" />
                            <span>Client Feedback</span>
                        </Link>

                        <Link 
                            :href="route('admin.tickets.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.tickets.*') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <LifeBuoy :size="15" :stroke-width="1.8" />
                            <span>Support Tickets</span>
                        </Link>

                        <Link 
                            :href="route('admin.releases.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.releases.index') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Package :size="15" :stroke-width="1.8" />
                            <span>Nimbus Releases</span>
                        </Link>

                        <Link 
                            :href="route('admin.reports.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.reports.index') 
                                    ? 'bg-[var(--green-wash)] text-[var(--accent)] font-semibold border border-[var(--edge-strong)]/60 shadow-xs' 
                                    : 'text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)]'
                            ]"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all"
                        >
                            <Bug :size="15" :stroke-width="1.8" />
                            <span>Bug Reports</span>
                        </Link>
                    </div>
                </nav>

                <!-- Mobile Drawer Footer: User profile & Logout -->
                <div class="border-t border-[var(--edge)] p-3 mt-auto bg-[var(--panel)]">
                    <div class="flex items-center justify-between gap-2 p-2 rounded-xl border border-[var(--edge)] bg-[var(--panel-hi)]">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-[var(--green-wash)] border border-[var(--edge-strong)] flex items-center justify-center text-xs font-mono font-bold text-[var(--accent)] shrink-0">
                                {{ $page.props.auth.user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-[var(--text)] truncate font-display">{{ $page.props.auth.user.name }}</div>
                                <div class="text-[10px] font-mono text-[var(--text-muted)] truncate flex items-center gap-1.5 mt-0.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)]"></span>
                                    <span>{{ $page.props.auth.user.is_admin ? 'Admin' : 'Client' }}</span>
                                </div>
                            </div>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-[var(--text-muted)] hover:text-rose-400 p-1.5 rounded-lg hover:bg-[var(--panel)] transition-colors" title="Log Out">
                            <LogOut :size="14" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Shell Top Header Navbar -->
        <div class="md:pl-64 flex flex-col flex-1 min-h-screen min-w-0">
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between border-b border-[var(--edge)] bg-[var(--panel)]/90 backdrop-blur-md px-4 sm:px-6 print:hidden">
                <!-- Left: Hamburger + Page Title / Breadcrumbs -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <button @click="showingNavigationDropdown = true" class="inline-flex items-center justify-center rounded-lg p-2 text-[var(--text-soft)] hover:bg-[var(--panel-hi)] hover:text-[var(--text)] md:hidden outline-none shrink-0 transition-colors" aria-label="Open navigation menu">
                        <Menu :size="18" />
                    </button>

                    <!-- Breadcrumbs (Tablet & Desktop) -->
                    <nav class="hidden sm:flex items-center gap-2 text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)] truncate">
                        <span class="text-[var(--text-muted)] shrink-0">{{ $page.props.auth.user.is_admin && route().current('admin.*') ? 'Roook // Admin' : 'Roook // Console' }}</span>
                        <ChevronRight :size="12" class="text-[var(--text-muted)] shrink-0" />
                        <span class="text-[var(--text)] font-semibold truncate">{{ pageTitle }}</span>
                    </nav>

                    <!-- Page Title (Mobile Only) -->
                    <span class="sm:hidden text-sm font-bold text-[var(--text)] truncate font-display">{{ pageTitle }}</span>
                </div>

                <!-- Center: Live cluster SLA pill (Desktop) -->
                <div class="hidden xl:flex items-center gap-2 px-3 py-1 rounded-full border border-[var(--edge)] bg-[var(--panel-hi)] text-[10px] font-mono text-[var(--text-soft)]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--accent)] animate-pulse"></span>
                    <span>Cluster SRE Active · SLA 99.99%</span>
                </div>

                <!-- Right: Actions/Dropdown -->
                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="theme-toggle"
                        :title="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>

                    <Dropdown align="right" width="48" content-classes="py-1 bg-[var(--panel)] border border-[var(--edge)] shadow-xl rounded-lg">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2.5 rounded-lg bg-[var(--panel-hi)] p-1.5 pl-2.5 sm:pl-3 border border-[var(--edge)] hover:border-[var(--edge-strong)] transition-all outline-none">
                                <span class="hidden sm:inline text-xs font-semibold text-[var(--text)] truncate max-w-[120px]">{{ $page.props.auth.user.name }}</span>
                                <div class="h-6 w-6 rounded bg-[var(--accent)] flex items-center justify-center text-[10px] font-mono font-bold text-[var(--accent-ink)] uppercase shadow-sm shrink-0">
                                    {{ $page.props.auth.user.name.substring(0, 2) }}
                                </div>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')" class="rounded-md hover:bg-[var(--panel-hi)] text-[var(--text-soft)] hover:text-[var(--text)]"> 
                                <span class="flex items-center gap-2 text-xs">
                                    <User :size="14" class="text-[var(--text-muted)]" />
                                    My Profile 
                                </span>
                            </DropdownLink>
                            <div class="my-1 border-t border-[var(--edge)]"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left rounded-md hover:bg-[var(--panel-hi)] text-rose-400 hover:text-rose-300">
                                <span class="flex items-center gap-2 text-xs">
                                    <LogOut :size="14" class="text-rose-400" />
                                    Log Out
                                </span>
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Header Inner (Slots) -->
            <div v-if="$slots.header" class="border-b border-[var(--edge)] bg-[var(--panel)] py-4 sm:py-6 px-4 sm:px-6 lg:px-8 print:hidden min-w-0">
                <div class="mx-auto max-w-7xl min-w-0">
                    <slot name="header" />
                </div>
            </div>

            <!-- Page Main Content Container -->
            <main class="flex-1 py-5 sm:py-8 px-4 sm:px-6 lg:px-8 bg-[var(--page)] min-w-0">
                <div class="mx-auto max-w-7xl min-w-0">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-[var(--edge)] py-4 sm:py-6 px-4 sm:px-6 lg:px-8 bg-[var(--panel)] print:hidden min-w-0">
                <div class="mx-auto max-w-7xl text-center text-xs text-[var(--text-muted)]">
                    &copy; {{ new Date().getFullYear() }} Roook Hosting by VMCore. All rights reserved.
                </div>
            </footer>
        </div>
    </div>
</template>

<style>
@keyframes slideIn {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}
.animate-slide-in {
    animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@media print {
    aside,
    header,
    footer,
    .print\:hidden {
        display: none !important;
    }

    .md\:pl-64 {
        padding-left: 0 !important;
    }

    main,
    main > div,
    .max-w-7xl {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        background: transparent !important;
    }

    .min-h-screen {
        min-height: auto !important;
    }

    body, html {
        background-color: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
