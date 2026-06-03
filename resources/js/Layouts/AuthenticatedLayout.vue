<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#0f172a] text-slate-200 selection:bg-indigo-500/30">
        <!-- Background Decorative Elements -->
        <div class="fixed -left-24 -top-24 h-96 w-96 rounded-full bg-indigo-600/10 blur-[120px]"></div>
        <div class="fixed -right-24 -bottom-24 h-96 w-96 rounded-full bg-purple-600/10 blur-[120px]"></div>

        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-white/5 bg-[#0f172a]/80 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 justify-between items-center">
                    <div class="flex items-center gap-10">
                        <!-- Logo -->
                        <Link :href="route('dashboard')" class="flex items-center gap-3 group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 p-2 glass-morphism transition-all group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-indigo-500/20">
                                <ApplicationLogo class="h-6 w-6 fill-white" />
                            </div>
                            <span class="text-xl font-bold tracking-tight text-white">VMCORE</span>
                        </Link>

                        <!-- Desktop Navigation -->
                        <div class="hidden space-x-1 sm:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:bg-white/5">
                                Dashboard
                            </NavLink>
                            <NavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.licenses.index')" :active="route().current('admin.licenses.index')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:bg-white/5 text-indigo-400">
                                Admin Panel
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                        <!-- Notifications/Search could go here -->
                        
                        <!-- Settings Dropdown -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="flex items-center gap-3 rounded-xl bg-white/5 p-1.5 pl-3 border border-white/10 hover:bg-white/10 transition-all outline-none">
                                        <span class="text-sm font-medium text-slate-300">{{ $page.props.auth.user.name }}</span>
                                        <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-xs font-bold text-white uppercase tracking-tighter">
                                            {{ $page.props.auth.user.name.substring(0, 2) }}
                                        </div>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="p-1">
                                        <DropdownLink :href="route('profile.edit')" class="rounded-lg"> Profile </DropdownLink>
                                        <div class="my-1 border-t border-white/5"></div>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="rounded-lg text-red-400 hover:text-red-300 hover:bg-red-500/10">
                                            Log Out
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger (Mobile) -->
                    <div class="flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-400 hover:bg-white/5 transition-all outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-white/5 bg-[#0f172a] animate-fade-in">
                <div class="space-y-1 pb-3 pt-2 px-4">
                    <Link :href="route('dashboard')" class="block px-4 py-3 rounded-xl text-base font-medium text-slate-300 hover:bg-white/5 transition-all" :class="{ 'bg-indigo-600/10 text-indigo-400': route().current('dashboard') }">
                        Dashboard
                    </Link>
                </div>

                <!-- Responsive Settings Options -->
                <div class="border-t border-white/5 pb-1 pt-4">
                    <div class="px-8 mb-4">
                        <div class="text-base font-medium text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-slate-500">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="px-4 space-y-1 pb-4">
                        <Link :href="route('profile.edit')" class="block px-4 py-3 rounded-xl text-base font-medium text-slate-300 hover:bg-white/5 transition-all"> Profile </Link>
                        <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-3 rounded-xl text-base font-medium text-red-400 hover:bg-red-500/10 transition-all">
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="relative z-10 py-10 animate-fade-in" style="animation-delay: 0.1s">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="relative z-10 pb-20 animate-fade-in" style="animation-delay: 0.2s">
            <slot />
        </main>

        <!-- Global Footer -->
        <footer class="relative z-10 border-t border-white/5 py-10">
            <div class="mx-auto max-w-7xl px-4 text-center text-sm text-slate-500">
                &copy; {{ new Date().getFullYear() }} VMCORE Central. All rights reserved.
            </div>
        </footer>
    </div>
</template>

<style>
/* Reset some default Breeze styles that might interfere */
.glass-morphism {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
}
</style>
