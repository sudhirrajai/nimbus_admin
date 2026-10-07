<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Moon, ShieldCheck, Sun } from 'lucide-vue-next';

defineProps({
    kicker: {
        type: String,
        default: 'A thoughtful start',
    },
    title: {
        type: String,
        default: 'Create your account',
    },
    subtitle: {
        type: String,
        default: 'Set up your Roook workspace and keep your infrastructure close.',
    },
});

const lightTheme = ref(false);

onMounted(() => {
    try {
        lightTheme.value = window.localStorage.getItem('rook-theme') === 'light';
    } catch {
        lightTheme.value = false;
    }
});

const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    try {
        window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
    } catch {}
};
</script>

<template>
    <main class="rook-site auth-site" :data-theme="lightTheme ? 'light' : 'dark'">
        <div class="auth-frame">
            <!-- Left Branding & Story Column -->
            <aside class="auth-story" aria-label="About Roook">
                <div class="auth-story-top">
                    <Link :href="route('home')" class="brand auth-brand" aria-label="Roook home">
                        <span class="brand-mark" aria-hidden="true">r</span><span>roook</span>
                    </Link>
                    <span class="auth-edition">MANAGED HOSTING</span>
                </div>

                <div class="auth-story-content">
                    <div class="eyebrow">A steadier way to run</div>
                    <h1>Your work deserves a calm place to land.</h1>
                    <p>Infrastructure care from people who learn your stack, keep an eye on the details, and stay close when it matters.</p>
                    
                    <div class="auth-illustration" aria-hidden="true">
                        <div class="orbit orbit-one" />
                        <div class="orbit orbit-two" />
                        <div class="server-node">
                            <span class="server-mark">r</span>
                            <i /><i /><i />
                        </div>
                        <div class="node-caption">
                            <span class="node-dot" /> OPERATIONS — STEADY
                        </div>
                        <span class="auth-coordinate coordinate-a">45°31′ N</span>
                        <span class="auth-coordinate coordinate-b">STACK / 04</span>
                    </div>
                </div>

                <div class="auth-story-foot">
                    <ShieldCheck :size="16" :stroke-width="1.7" aria-hidden="true" />
                    <span>Careful by default. Human when it counts.</span>
                </div>
            </aside>

            <!-- Right Workspace Column -->
            <section class="auth-workspace" aria-labelledby="auth-title">
                <div class="auth-topbar">
                    <Link :href="route('home')" class="auth-back">
                        <ArrowLeft :size="15" aria-hidden="true" /> Back to Roook
                    </Link>

                    <button
                        class="theme-toggle auth-theme-toggle cursor-pointer"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        :aria-pressed="lightTheme"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>
                </div>

                <div class="auth-content">
                    <div class="auth-mobile-brand">
                        <Link :href="route('home')" class="brand" aria-label="Roook home">
                            <span class="brand-mark" aria-hidden="true">r</span><span>roook</span>
                        </Link>
                    </div>

                    <div class="auth-form-heading">
                        <div v-if="kicker" class="eyebrow">{{ kicker }}</div>
                        <h2 v-if="title" id="auth-title">{{ title }}</h2>
                        <p v-if="subtitle">{{ subtitle }}</p>
                    </div>

                    <slot />
                </div>

                <div class="auth-workspace-foot">
                    <span>&copy; {{ new Date().getFullYear() }} Roook Hosting</span>
                    <span>YOUR INFRASTRUCTURE, IN GOOD HANDS</span>
                </div>
            </section>
        </div>
    </main>
</template>
