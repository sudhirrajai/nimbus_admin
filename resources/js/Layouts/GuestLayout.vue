<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Moon, Sun } from 'lucide-vue-next';

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
</script>

<template>
    <div
        class="rook-site min-h-screen flex flex-col justify-between"
        :data-theme="lightTheme ? 'light' : 'dark'"
    >
        <!-- Top Auth Nav Bar -->
        <header class="w-full px-6 py-5 flex items-center justify-between">
            <Link
                :href="route('home')"
                class="inline-flex items-center gap-2 text-xs font-mono text-[var(--text-muted)] hover:text-[var(--text)] transition-colors"
            >
                <ArrowLeft :size="14" />
                <span>Back to home</span>
            </Link>

            <button
                type="button"
                @click="toggleTheme"
                class="theme-toggle"
                :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                :aria-pressed="lightTheme"
            >
                <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                <Sun v-else :size="15" aria-hidden="true" />
            </button>
        </header>

        <!-- Main Card Area -->
        <main class="flex-1 flex flex-col justify-center items-center px-4 py-8">
            <div class="w-full sm:max-w-md">
                <!-- Brand Badge -->
                <div class="flex flex-col items-center mb-6">
                    <Link :href="route('home')" class="brand mb-3" aria-label="Home">
                        <span class="brand-mark" aria-hidden="true">r</span>
                        <span>ook</span>
                    </Link>
                </div>

                <!-- Form Card -->
                <div class="w-full rounded-2xl border border-[var(--edge)] bg-[var(--panel)] p-7 sm:p-9 shadow-2xl backdrop-blur-xl">
                    <slot />
                </div>
            </div>
        </main>

        <!-- Footer Links -->
        <footer class="w-full py-6 px-4 text-center text-xs text-[var(--text-muted)]">
            <div class="flex justify-center items-center gap-4">
                <Link :href="route('pages.show', 'privacy')" class="hover:text-[var(--text)] transition-colors">Privacy Policy</Link>
                <span>&bull;</span>
                <Link :href="route('pages.show', 'terms')" class="hover:text-[var(--text)] transition-colors">Terms of Service</Link>
                <span>&bull;</span>
                <Link :href="route('products.nimbus')" class="hover:text-[var(--text)] transition-colors">Nimbus Software</Link>
            </div>
            <div class="mt-2 text-[11px] text-[var(--text-muted)]/60">
                &copy; {{ new Date().getFullYear() }} Rook Hosting by VMCore. All rights reserved.
            </div>
        </footer>
    </div>
</template>
