<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900 text-slate-100 selection:bg-slate-800">
        <div>
            <Link href="/" class="flex flex-col items-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-800 border border-slate-700 p-4 shadow-lg shadow-emerald-500/5">
                    <ApplicationLogo class="h-12 w-12 fill-white" />
                </div>
                <h1 class="mt-4 text-2xl font-bold tracking-tight text-white uppercase">VMCORE</h1>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 border border-slate-700 shadow-md overflow-hidden sm:rounded-xl">
            <slot />
        </div>

        <!-- Footer Links -->
        <div class="mt-8 flex justify-center space-x-6 text-xs text-slate-500">
            <a href="#" class="hover:text-slate-300 transition-colors">Privacy Policy</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-slate-300 transition-colors">Terms of Service</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-slate-300 transition-colors">Help Center</a>
        </div>
    </div>
</template>
