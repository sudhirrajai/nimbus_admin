<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const pageTitle = computed(() => {
    if (route().current('dashboard')) return 'Dashboard';
    if (route().current('admin.licenses.index')) return 'Licenses';
    if (route().current('profile.edit')) return 'Profile';
    return 'Dashboard';
});

const isRouteActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-gray-900 selection:bg-emerald-500/10 font-sans">
        
        <!-- Desktop Left Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 border-r border-gray-200 bg-white md:flex md:flex-col transition-all duration-300">
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center gap-3 border-b border-gray-200 px-6">
                <Link :href="route('dashboard')" class="flex items-center gap-2.5 group">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 p-2 shadow-lg shadow-emerald-500/10 transition-transform group-hover:scale-105">
                        <ApplicationLogo class="h-5 w-5 fill-white" />
                    </div>
                    <span class="text-base font-bold tracking-tight text-gray-900">Nimbus by VMCore</span>
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
            <div class="fixed inset-0 bg-black/40" @click="showingNavigationDropdown = false"></div>

            <!-- Drawer Container -->
            <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white border-r border-gray-200 pt-5 pb-4 animate-slide-in">
                <!-- Close Button -->
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button @click="showingNavigationDropdown = false" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="material-symbols-rounded text-white">close</span>
                    </button>
                </div>

                <div class="flex shrink-0 items-center gap-2.5 px-6 pb-4 border-b border-gray-200">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 p-2">
                        <ApplicationLogo class="h-5 w-5 fill-white" />
                    </div>
                    <span class="text-base font-bold tracking-tight text-gray-900">Nimbus by VMCore</span>
                </div>

                <!-- Nav list inside Mobile Drawer -->
                <nav class="mt-6 flex-1 space-y-1.5 px-4 overflow-y-auto">
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

                    <div v-if="$page.props.auth.user.is_admin" class="pt-6">
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
                    </div>
                </nav>
            </div>
        </div>

        <!-- Desktop Shell Top Header Navbar -->
        <div class="md:pl-64 flex flex-col flex-1">
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white/80 backdrop-blur-md px-6">
                <!-- Left: Hamburger + Breadcrumbs -->
                <div class="flex items-center gap-4">
                    <button @click="showingNavigationDropdown = true" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-slate-50 md:hidden outline-none">
                        <span class="material-symbols-rounded">menu</span>
                    </button>

                    <!-- Breadcrumbs -->
                    <nav class="hidden sm:flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-550">
                        <span class="text-gray-500">{{ $page.props.auth.user.is_admin && route().current('admin.*') ? 'Admin' : 'App' }}</span>
                        <span class="material-symbols-rounded text-xs select-none text-gray-400">chevron_right</span>
                        <span class="text-gray-900">{{ pageTitle }}</span>
                    </nav>
                </div>

                <!-- Right: Actions/Dropdown -->
                <div class="flex items-center gap-4">
                    <Dropdown align="right" width="48" content-classes="py-1 bg-white border border-gray-200">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2 rounded-lg bg-slate-50 p-1.5 pl-3 border border-gray-200 hover:bg-slate-100 transition-all outline-none">
                                <span class="text-xs font-semibold text-gray-700">{{ $page.props.auth.user.name }}</span>
                                <div class="h-6 w-6 rounded bg-emerald-500 flex items-center justify-center text-[10px] font-bold text-white uppercase shadow-sm shadow-emerald-500/10">
                                    {{ $page.props.auth.user.name.substring(0, 2) }}
                                </div>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')" class="rounded-md"> 
                                <span class="flex items-center gap-2 text-xs text-gray-700">
                                    <span class="material-symbols-rounded text-sm text-gray-500">person</span>
                                    My Profile 
                                </span>
                            </DropdownLink>
                            <div class="my-1 border-t border-gray-200"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left rounded-md">
                                <span class="flex items-center gap-2 text-xs text-red-600">
                                    <span class="material-symbols-rounded text-sm text-red-500">logout</span>
                                    Log Out
                                </span>
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Header Inner (Slots) -->
            <div v-if="$slots.header" class="border-b border-gray-200 bg-white py-6 px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <slot name="header" />
                </div>
            </div>

            <!-- Page Main Content Container -->
            <main class="flex-1 py-8 px-6 lg:px-8 bg-slate-50">
                <div class="mx-auto max-w-7xl">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 py-6 px-6 lg:px-8 bg-white/40">
                <div class="mx-auto max-w-7xl text-center text-xs text-gray-500">
                    &copy; {{ new Date().getFullYear() }} Nimbus by VMCore. All rights reserved.
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
</style>
