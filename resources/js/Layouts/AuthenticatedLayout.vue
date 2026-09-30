<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Sun, Moon } from 'lucide-vue-next';

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
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-gray-200 bg-white md:flex md:flex-col transition-all duration-300">
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center gap-3 border-b border-gray-200 px-6">
                <Link :href="route('dashboard')" class="brand flex items-center" aria-label="Rook Dashboard">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span class="text-xl font-bold tracking-tight text-gray-900">ook</span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 space-y-1.5 px-4 py-6 overflow-y-auto">
                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-3 mb-2">Workspace</div>
                
                <Link 
                    :href="route('dashboard')" 
                    :class="[
                        isRouteActive('dashboard') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all"
                >
                    <span class="material-symbols-rounded text-lg">dashboard</span>
                    Dashboard
                </Link>

                <Link 
                    :href="route('hosting.client.index')" 
                    :class="[
                        route().current('hosting.client.*') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                >
                    <span class="material-symbols-rounded text-lg">cloud_done</span>
                    Managed Hosting
                </Link>

                <Link 
                    :href="route('store.index')" 
                    :class="[
                        route().current('store.*') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                >
                    <span class="material-symbols-rounded text-lg">storefront</span>
                    Store &amp; Packages
                </Link>

                <Link 
                    :href="route('subscription')" 
                    :class="[
                        isRouteActive('subscription') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                >
                    <span class="material-symbols-rounded text-lg">card_membership</span>
                    My Subscriptions
                </Link>

                <Link 
                    :href="route('invoices.index')" 
                    :class="[
                        route().current('invoices.*') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                >
                    <span class="material-symbols-rounded text-lg">receipt_long</span>
                    Invoices
                </Link>

                <Link 
                    :href="route('tickets.index')" 
                    :class="[
                        route().current('tickets.*') 
                            ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                            : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                    ]"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                >
                    <span class="material-symbols-rounded text-lg">support_agent</span>
                    Support Tickets
                </Link>

                <div v-if="$page.props.auth.user.is_admin" class="pt-6">
                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-3 mb-2">Administration</div>
                    
                    <Link 
                        :href="route('admin.licenses.index')" 
                        :class="[
                            isRouteActive('admin.licenses.index') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all"
                    >
                        <span class="material-symbols-rounded text-lg">vpn_key</span>
                        Licenses
                    </Link>

                    <Link 
                        :href="route('admin.hosting.index')" 
                        :class="[
                            route().current('admin.hosting.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">dns</span>
                        Managed Hosting
                    </Link>

                    <Link 
                        :href="route('admin.invoices.index')" 
                        :class="[
                            route().current('admin.invoices.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">receipt_long</span>
                        Invoices
                    </Link>

                    <Link 
                        :href="route('admin.users.index')" 
                        :class="[
                            isRouteActive('admin.users.index') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">group</span>
                        Users
                    </Link>

                    <Link 
                        :href="route('admin.settings.index')" 
                        :class="[
                            isRouteActive('admin.settings.index') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">settings</span>
                        Settings
                    </Link>

                    <Link 
                        :href="route('admin.pages.index')" 
                        :class="[
                            route().current('admin.pages.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">article</span>
                        Manage Pages
                    </Link>

                    <Link 
                        :href="route('admin.plans.index')" 
                        :class="[
                            route().current('admin.plans.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">payments</span>
                        Manage Plans
                    </Link>

                    <Link 
                        :href="route('admin.testimonials.index')" 
                        :class="[
                            route().current('admin.testimonials.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">reviews</span>
                        Testimonials
                    </Link>

                    <Link 
                        :href="route('admin.feedback.index')" 
                        :class="[
                            route().current('admin.feedback.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">rate_review</span>
                        Client Feedback
                    </Link>

                    <Link 
                        :href="route('admin.tickets.index')" 
                        :class="[
                            route().current('admin.tickets.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">support_agent</span>
                        Support Tickets
                    </Link>

                    <Link 
                        :href="route('admin.releases.index')" 
                        :class="[
                            isRouteActive('admin.releases.index') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">cloud_upload</span>
                        Nimbus Releases
                    </Link>

                    <Link 
                        :href="route('admin.reports.index')" 
                        :class="[
                            isRouteActive('admin.reports.index') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">bug_report</span>
                        Bug Reports
                    </Link>
                </div>
            </nav>

            <!-- Sidebar Footer / Quick User Card -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3 px-2 py-1.5 rounded-lg bg-slate-50 border border-gray-200">
                    <div class="h-8 w-8 rounded-lg bg-emerald-500 flex items-center justify-center text-xs font-bold text-white uppercase shadow-sm shadow-emerald-500/10">
                        {{ $page.props.auth.user.name.substring(0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-gray-900 truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[10px] text-gray-500 truncate">{{ $page.props.auth.user.email }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Menu (Slide-out Sidebar) -->
        <div v-if="showingNavigationDropdown" class="fixed inset-0 z-50 flex md:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="showingNavigationDropdown = false"></div>

            <!-- Drawer Container -->
            <div class="relative flex w-full max-w-[280px] sm:max-w-xs flex-1 flex-col bg-white border-r border-gray-200 pt-4 pb-4 animate-slide-in shadow-2xl z-10">
                <!-- Drawer Header with integrated close button -->
                <div class="flex shrink-0 items-center justify-between gap-2.5 px-5 pb-4 border-b border-gray-200">
                    <Link :href="route('dashboard')" @click="showingNavigationDropdown = false" class="brand flex items-center" aria-label="Rook Dashboard">
                        <span class="brand-mark" aria-hidden="true">r</span>
                        <span class="text-xl font-bold tracking-tight text-gray-900">ook</span>
                    </Link>
                    <button @click="showingNavigationDropdown = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-slate-100 transition-colors" aria-label="Close menu">
                        <span class="material-symbols-rounded text-xl">close</span>
                    </button>
                </div>

                <!-- Nav list inside Mobile Drawer -->
                <nav class="mt-4 flex-1 space-y-1.5 px-4 overflow-y-auto">
                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-3 mb-2">Workspace</div>
                    <Link 
                        :href="route('dashboard')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            isRouteActive('dashboard') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all"
                    >
                        <span class="material-symbols-rounded text-lg">dashboard</span>
                        Dashboard
                    </Link>

                    <Link 
                        :href="route('hosting.client.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('hosting.client.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">cloud_done</span>
                        Managed Hosting
                    </Link>

                    <Link 
                        :href="route('store.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('store.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">storefront</span>
                        Store &amp; Packages
                    </Link>

                    <Link 
                        :href="route('subscription')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            isRouteActive('subscription') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">card_membership</span>
                        My Subscriptions
                    </Link>

                    <Link 
                        :href="route('invoices.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('invoices.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">receipt_long</span>
                        Invoices
                    </Link>

                    <Link 
                        :href="route('tickets.index')" 
                        @click="showingNavigationDropdown = false"
                        :class="[
                            route().current('tickets.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                        ]"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                    >
                        <span class="material-symbols-rounded text-lg">support_agent</span>
                        Support Tickets
                    </Link>

                    <div v-if="$page.props.auth.user.is_admin" class="pt-5">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-3 mb-2">Administration</div>
                        <Link 
                            :href="route('admin.licenses.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.licenses.index') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all"
                        >
                            <span class="material-symbols-rounded text-lg">vpn_key</span>
                            Licenses
                        </Link>
                        <Link 
                            :href="route('admin.hosting.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.hosting.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">dns</span>
                            Managed Hosting
                        </Link>
                        <Link 
                            :href="route('admin.invoices.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.invoices.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">receipt_long</span>
                            Invoices
                        </Link>

                        <Link 
                            :href="route('admin.users.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.users.index') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">group</span>
                            Users
                        </Link>
                        <Link 
                            :href="route('admin.settings.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.settings.index') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">settings</span>
                            Settings
                        </Link>

                        <Link 
                            :href="route('admin.pages.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.pages.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">article</span>
                            Manage Pages
                        </Link>

                        <Link 
                            :href="route('admin.plans.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.plans.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">payments</span>
                            Manage Plans
                        </Link>

                        <Link 
                            :href="route('admin.testimonials.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.testimonials.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">reviews</span>
                            Testimonials
                        </Link>

                        <Link 
                            :href="route('admin.feedback.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.feedback.*') 
                                ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">rate_review</span>
                            Client Feedback
                        </Link>

                        <Link 
                            :href="route('admin.tickets.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                route().current('admin.tickets.*') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">support_agent</span>
                            Support Tickets
                        </Link>

                        <Link 
                            :href="route('admin.releases.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.releases.index') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">cloud_upload</span>
                            Nimbus Releases
                        </Link>

                        <Link 
                            :href="route('admin.reports.index')" 
                            @click="showingNavigationDropdown = false"
                            :class="[
                                isRouteActive('admin.reports.index') 
                                    ? 'bg-slate-50 text-emerald-600 font-semibold border-l-2 border-emerald-500' 
                                    : 'text-gray-500 hover:bg-slate-50 hover:text-gray-900'
                            ]"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all mt-1"
                        >
                            <span class="material-symbols-rounded text-lg">bug_report</span>
                            Bug Reports
                        </Link>
                    </div>
                </nav>

                <!-- Mobile Drawer Footer: User profile & Logout -->
                <div class="border-t border-gray-200 p-3 mt-auto">
                    <div class="flex items-center justify-between gap-2 px-2.5 py-2 rounded-lg bg-slate-50 border border-gray-200">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-emerald-500 flex items-center justify-center text-xs font-bold text-white uppercase shrink-0 shadow-sm shadow-emerald-500/10">
                                {{ $page.props.auth.user.name.substring(0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-gray-900 truncate">{{ $page.props.auth.user.name }}</div>
                                <div class="text-[10px] text-gray-500 truncate">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-colors shrink-0" title="Log Out">
                            <span class="material-symbols-rounded text-lg">logout</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Shell Top Header Navbar -->
        <div class="md:pl-64 flex flex-col flex-1 min-h-screen min-w-0">
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between border-b border-[var(--edge)] bg-[var(--panel)]/95 backdrop-blur-md px-4 sm:px-6 print:hidden">
                <!-- Left: Hamburger + Page Title / Breadcrumbs -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <button @click="showingNavigationDropdown = true" class="inline-flex items-center justify-center rounded-lg p-2 text-[var(--text-soft)] hover:bg-[var(--panel-hi)] md:hidden outline-none shrink-0" aria-label="Open navigation menu">
                        <span class="material-symbols-rounded">menu</span>
                    </button>

                    <!-- Breadcrumbs (Tablet & Desktop) -->
                    <nav class="hidden sm:flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[var(--text-muted)] truncate">
                        <span class="text-[var(--text-muted)] shrink-0">{{ $page.props.auth.user.is_admin && route().current('admin.*') ? 'Admin' : 'App' }}</span>
                        <span class="material-symbols-rounded text-xs select-none text-[var(--text-muted)] shrink-0">chevron_right</span>
                        <span class="text-[var(--text)] truncate">{{ pageTitle }}</span>
                    </nav>

                    <!-- Page Title (Mobile Only) -->
                    <span class="sm:hidden text-sm font-bold text-[var(--text)] truncate">{{ pageTitle }}</span>
                </div>

                <!-- Right: Actions/Dropdown -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-[var(--edge)] bg-[var(--panel-hi)] text-[var(--text-soft)] hover:text-[var(--text)] hover:border-[var(--edge-strong)] transition-all outline-none"
                        :title="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        aria-label="Toggle theme"
                    >
                        <Moon v-if="lightTheme" :size="16" aria-hidden="true" />
                        <Sun v-else :size="16" aria-hidden="true" />
                    </button>

                    <Dropdown align="right" width="48" content-classes="py-1 bg-[var(--panel)] border border-[var(--edge)] shadow-xl rounded-lg">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2.5 rounded-lg bg-[var(--panel-hi)] p-1.5 pl-2 sm:pl-3 border border-[var(--edge)] hover:border-[var(--edge-strong)] transition-all outline-none">
                                <span class="hidden sm:inline text-xs font-semibold text-[var(--text)] truncate max-w-[120px]">{{ $page.props.auth.user.name }}</span>
                                <div class="h-6 w-6 rounded bg-[var(--accent)] flex items-center justify-center text-[10px] font-bold text-[var(--accent-ink)] uppercase shadow-sm shrink-0">
                                    {{ $page.props.auth.user.name.substring(0, 2) }}
                                </div>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')" class="rounded-md hover:bg-[var(--panel-hi)] text-[var(--text-soft)] hover:text-[var(--text)]"> 
                                <span class="flex items-center gap-2 text-xs">
                                    <span class="material-symbols-rounded text-sm text-[var(--text-muted)]">person</span>
                                    My Profile 
                                </span>
                            </DropdownLink>
                            <div class="my-1 border-t border-[var(--edge)]"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left rounded-md hover:bg-[var(--panel-hi)] text-red-500 hover:text-red-400">
                                <span class="flex items-center gap-2 text-xs">
                                    <span class="material-symbols-rounded text-sm text-red-500">logout</span>
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
                    &copy; {{ new Date().getFullYear() }} Rook Hosting by VMCore. All rights reserved.
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
