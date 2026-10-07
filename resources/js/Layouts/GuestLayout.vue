<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Moon, Sun, CheckCircle2 } from 'lucide-vue-next';

defineProps({
    kicker: {
        type: String,
        default: '— A THOUGHTFUL START',
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
        class="rook-site min-h-screen flex flex-col lg:flex-row bg-[#080d0a] text-[#e7eee9] font-sans selection:bg-[#87d7a0]/25 selection:text-[#f2fbf5]"
        :data-theme="lightTheme ? 'light' : 'dark'"
    >
        <!-- LEFT COLUMN: Ambient Brand Showcase -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[48%] flex-col justify-between p-10 xl:p-14 border-r border-[#15251c] relative overflow-hidden bg-gradient-to-b from-[#081510] via-[#06120d] to-[#040c09] select-none">
            <!-- Radial glow & technical grid background -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_40%_40%,rgba(16,185,129,0.08),transparent_65%)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#13281d14_1px,transparent_1px),linear-gradient(to_bottom,#13281d14_1px,transparent_1px)] bg-[size:44px_44px] pointer-events-none"></div>

            <!-- Top Left: Logo & Managed Hosting Tag -->
            <div class="relative z-10 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-3 group">
                    <div class="w-7 h-7 rounded-lg bg-[#0e2419] border border-[#1d432f] text-[#34d399] flex items-center justify-center font-bold text-sm tracking-tight shadow-xs group-hover:border-[#34d399]/50 transition-colors">
                        r
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white font-sans">roook</span>
                </Link>

                <div class="font-mono text-[10px] tracking-[0.25em] text-[#34d399]/90 uppercase font-semibold">
                    MANAGED HOSTING
                </div>
            </div>

            <!-- Center Content: Editorial Hero & Telemetry Illustration -->
            <div class="relative z-10 my-auto py-8">
                <!-- Kicker -->
                <div class="font-mono text-xs text-[#34d399] tracking-wider uppercase mb-5 font-medium">
                    — A STEADIER WAY TO RUN
                </div>

                <!-- Display Title -->
                <h1 class="text-4xl xl:text-[46px] font-bold text-white tracking-tight leading-[1.12] mb-5 font-display">
                    Your work<br />
                    deserves a calm<br />
                    place to land.
                </h1>

                <!-- Body text -->
                <p class="text-sm xl:text-[15px] text-[#8ca398] leading-relaxed max-w-md mb-12">
                    Infrastructure care from people who learn your stack, keep an eye on the details, and stay close when it matters.
                </p>

                <!-- Minimalist Telemetry Visual Card -->
                <div class="relative w-full max-w-[380px] h-[210px] mx-auto flex items-center justify-center">
                    <!-- Coordinate Tag Top-Left -->
                    <span class="absolute left-0 top-0 font-mono text-[10px] text-[#2c533e] tracking-widest">
                        45°31' N
                    </span>

                    <!-- Coordinate Tag Bottom-Right -->
                    <span class="absolute right-0 bottom-2 font-mono text-[10px] text-[#2c533e] tracking-widest">
                        STACK / 04
                    </span>

                    <!-- Dual Orbital Ellipses -->
                    <svg class="absolute inset-0 w-full h-full overflow-visible pointer-events-none" viewBox="0 0 380 210">
                        <!-- Orbit 1 (Solid soft ring) -->
                        <ellipse cx="190" cy="105" rx="145" ry="52" transform="rotate(-15 190 105)" stroke="#1a422e" stroke-width="1.2" fill="none" opacity="0.8" />
                        <!-- Orbit 2 (Dashed ring) -->
                        <ellipse cx="190" cy="105" rx="135" ry="44" transform="rotate(22 190 105)" stroke="#133624" stroke-width="1" stroke-dasharray="4 3" fill="none" opacity="0.7" />
                        <!-- Subtle orbit node dots -->
                        <circle cx="58" cy="92" r="2" fill="#34d399" opacity="0.7" />
                        <circle cx="320" cy="118" r="1.5" fill="#34d399" opacity="0.4" />
                    </svg>

                    <!-- Central Server Pod Node -->
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-24 h-24 rounded-2xl bg-[#091811] border border-[#1b3f2c] shadow-[0_0_35px_rgba(16,185,129,0.08)] p-3 flex flex-col justify-between">
                            <div class="flex justify-end">
                                <span class="w-4 h-4 rounded bg-[#0e251a] border border-[#235039] text-[#34d399] text-[9px] font-bold flex items-center justify-center">r</span>
                            </div>
                            <div class="space-y-1.5 pb-1">
                                <div class="h-1 bg-[#10b981] rounded-full w-12"></div>
                                <div class="h-1 bg-[#10b981]/60 rounded-full w-9"></div>
                                <div class="h-1 bg-[#10b981]/30 rounded-full w-5"></div>
                            </div>
                        </div>

                        <!-- Steady Status Pill -->
                        <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#071710] border border-[#163827] shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34d399] animate-pulse"></span>
                            <span class="font-mono text-[9px] text-[#34d399] tracking-widest uppercase font-medium">OPERATIONS — STEADY</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Left Statement -->
            <div class="relative z-10 flex items-center gap-2 text-xs text-[#71887c]">
                <CheckCircle2 :size="15" class="text-[#34d399] shrink-0" />
                <span>Careful by default. Human when it counts.</span>
            </div>
        </div>

        <!-- RIGHT COLUMN: Authentication Form -->
        <div class="w-full lg:w-1/2 xl:w-[52%] flex flex-col justify-between min-h-screen bg-[#070b09] p-6 sm:p-10 lg:p-12 xl:p-16">
            <!-- Top Nav Bar: Back to Roook & Theme Toggle -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 text-xs font-medium text-[#8ca398] hover:text-white transition-colors"
                >
                    <ArrowLeft :size="14" />
                    <span>Back to Roook</span>
                </Link>

                <!-- Mobile Only Brand in center header -->
                <div class="flex lg:hidden items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-[#0e2419] border border-[#1d432f] text-[#34d399] flex items-center justify-center font-bold text-xs">
                        r
                    </div>
                    <span class="text-sm font-bold text-white">roook</span>
                </div>

                <button
                    type="button"
                    @click="toggleTheme"
                    class="w-8 h-8 rounded-full border border-[#1b2720] hover:border-[#2a3c31] text-[#8ca398] hover:text-white flex items-center justify-center transition-all bg-[#0a110d]"
                    :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                >
                    <Moon v-if="lightTheme" :size="14" />
                    <Sun v-else :size="14" />
                </button>
            </div>

            <!-- Middle Form Content -->
            <div class="w-full max-w-[420px] mx-auto my-auto py-8 sm:py-10">
                <!-- Header (kicker, title, subtitle) -->
                <div v-if="kicker" class="font-mono text-xs text-[#34d399] tracking-wider uppercase mb-3 font-medium">
                    {{ kicker }}
                </div>
                <h2 v-if="title" class="text-3xl sm:text-4xl font-bold text-white tracking-tight leading-tight font-display mb-2">
                    {{ title }}
                </h2>
                <p v-if="subtitle" class="text-xs sm:text-sm text-[#7e9287] mb-8 leading-relaxed">
                    {{ subtitle }}
                </p>

                <!-- Form Slot -->
                <slot />
            </div>

            <!-- Bottom Footer Strip -->
            <div class="w-full flex flex-col sm:flex-row items-center justify-between text-xs text-[#52665b] pt-6 gap-2 border-t border-[#131d17]/80">
                <div>&copy; {{ new Date().getFullYear() }} Roook Hosting</div>
                <div class="font-mono text-[10px] tracking-[0.2em] text-[#34d399]/70 uppercase">
                    YOUR INFRASTRUCTURE, IN GOOD HANDS
                </div>
            </div>
        </div>
    </div>
</template>
