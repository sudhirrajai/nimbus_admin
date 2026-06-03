<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-[#0f172a] selection:bg-indigo-500/30">
        <!-- Background Decorative Elements -->
        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-indigo-600/20 blur-[120px]"></div>
        <div class="absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-purple-600/20 blur-[120px]"></div>
        
        <div class="relative z-10 w-full px-6 sm:max-w-md">
            <!-- Logo Section -->
            <div class="flex flex-col items-center mb-8 animate-fade-in" style="animation-delay: 0.1s">
                <Link href="/">
                    <div class="group relative flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-800 p-4 transition-all hover:scale-110 hover:shadow-2xl hover:shadow-indigo-500/20 glass-morphism">
                        <ApplicationLogo class="h-12 w-12 fill-white transition-all group-hover:scale-110" />
                    </div>
                </Link>
                <h1 class="mt-6 text-3xl font-bold tracking-tight text-white">VMCORE</h1>
                <p class="mt-2 text-sm text-slate-400">Manage your infrastructure with ease</p>
            </div>

            <!-- Content Card -->
            <div class="glass-morphism overflow-hidden rounded-3xl p-8 animate-fade-in" style="animation-delay: 0.2s">
                <slot />
            </div>

            <!-- Footer Links -->
            <div class="mt-8 flex justify-center space-x-6 text-xs text-slate-500 animate-fade-in" style="animation-delay: 0.3s">
                <a href="#" class="hover:text-indigo-400 transition-colors">Privacy Policy</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-indigo-400 transition-colors">Terms of Service</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-indigo-400 transition-colors">Help Center</a>
            </div>
        </div>
    </div>
</template>

