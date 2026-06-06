<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 text-gray-900 selection:bg-emerald-500/10">
        <div>
            <Link href="/" class="flex flex-col items-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white border border-gray-200 p-4 shadow-md shadow-emerald-500/5">
                    <ApplicationLogo class="h-12 w-12 fill-emerald-500" />
                </div>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-gray-900">Nimbus by VMCore</h1>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white border border-gray-200 shadow-md overflow-hidden sm:rounded-xl">
            <slot />
        </div>

        <!-- Footer Links -->
        <div class="mt-8 flex justify-center space-x-6 text-xs text-gray-550">
            <a href="#" class="hover:text-gray-900 transition-colors">Privacy Policy</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-gray-900 transition-colors">Terms of Service</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-gray-900 transition-colors">Help Center</a>
        </div>
    </div>
</template>
