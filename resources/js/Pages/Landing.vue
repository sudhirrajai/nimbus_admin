<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="VMCORE | Modern Infrastructure" />

    <div class="landing-page bg-slate-950 text-white min-vh-100 overflow-x-hidden">
        <!-- Navigation -->
        <nav class="fixed-top px-4 py-3 bg-slate-950/50 backdrop-blur-md border-b border-white/5">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="brand d-flex align-items-center gap-2">
                    <div class="logo-box bg-primary p-2 rounded-lg">
                        <i class="material-symbols-rounded text-white">cloud</i>
                    </div>
                    <span class="fw-bold tracking-tight text-xl">VMCORE</span>
                </div>
                <div class="nav-links d-none d-md-flex gap-4">
                    <a href="#features" class="text-white-50 hover-text-white transition">Features</a>
                    <a href="#pricing" class="text-white-50 hover-text-white transition">Pricing</a>
                    <a href="https://docs.vmcore.in" class="text-white-50 hover-text-white transition">Docs</a>
                </div>
                <div class="auth-actions d-flex gap-2">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-outline-white btn-sm px-4 rounded-pill">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-link text-white text-decoration-none px-3">Login</Link>
                        <Link :href="route('register')" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section pt-32 pb-20 px-4">
            <div class="container text-center">
                <div class="badge bg-primary/10 text-primary border border-primary/20 px-3 py-1 rounded-pill mb-4 animate__animated animate__fadeInDown">
                    Introducing Nimbus v2.0
                </div>
                <h1 class="display-3 fw-bold mb-4 tracking-tighter animate__animated animate__fadeInUp">
                    The Modern Standard for <br/>
                    <span class="text-gradient">Server Management</span>
                </h1>
                <p class="lead text-white-50 max-w-2xl mx-auto mb-5 animate__animated animate__fadeInUp animate__delay-1s">
                    Empower your infrastructure with Nimbus. A lightweight, high-performance control panel designed for developers who value speed, security, and aesthetics.
                </p>
                <div class="hero-actions d-flex justify-content-center gap-3 animate__animated animate__fadeInUp animate__delay-1s">
                    <Link :href="route('register')" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg shadow-primary/20">Create Free Account</Link>
                    <a href="#demo" class="btn btn-outline-white btn-lg px-5 rounded-pill">Live Demo</a>
                </div>

                <!-- Dashboard Preview -->
                <div class="dashboard-preview mt-16 animate__animated animate__zoomIn animate__delay-2s">
                    <div class="preview-wrapper bg-white/5 border border-white/10 p-2 rounded-3xl backdrop-blur-sm">
                        <img src="https://via.placeholder.com/1200x800/1a1a2e/ffffff?text=Nimbus+Dashboard+Preview" alt="Nimbus Preview" class="img-fluid rounded-2xl shadow-2xl shadow-black/50">
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="py-24 bg-slate-900/50">
            <div class="container">
                <div class="text-center mb-16">
                    <h2 class="display-5 fw-bold mb-3">Enterprise Features</h2>
                    <p class="text-white-50">Built for scale, secured by design.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4" v-for="(feat, i) in features" :key="i">
                        <div class="feature-card p-5 bg-white/5 border border-white/10 rounded-3xl h-100 transition hover-bg-white/10">
                            <div class="feat-icon mb-4 text-primary bg-primary/10 p-3 rounded-2xl d-inline-block">
                                <i class="material-symbols-rounded fs-1">{{ feat.icon }}</i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ feat.title }}</h4>
                            <p class="text-white-50 leading-relaxed">{{ feat.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24">
            <div class="container">
                <div class="text-center mb-16">
                    <h2 class="display-5 fw-bold mb-3">Simple Pricing</h2>
                    <p class="text-white-50">Choose the plan that fits your growth.</p>
                </div>
                <div class="row justify-content-center g-4">
                    <div class="col-md-4" v-for="(plan, i) in plans" :key="i">
                        <div class="pricing-card p-5 h-100 rounded-3xl border transition" :class="plan.popular ? 'bg-primary/5 border-primary/30 shadow-2xl shadow-primary/10 relative scale-105 z-10' : 'bg-white/5 border-white/10'">
                            <div v-if="plan.popular" class="popular-badge absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-white text-xs fw-bold px-4 py-1 rounded-full uppercase tracking-widest">Most Popular</div>
                            <h5 class="fw-bold text-white-50 mb-2 uppercase tracking-widest text-xs">{{ plan.name }}</h5>
                            <div class="d-flex align-items-end gap-1 mb-4">
                                <span class="display-4 fw-bold">{{ plan.price }}</span>
                                <span class="text-white-50 mb-2">/mo</span>
                            </div>
                            <ul class="list-unstyled mb-8 space-y-4">
                                <li v-for="feat in plan.features" :key="feat" class="d-flex align-items-center gap-3 text-white-50">
                                    <i class="material-symbols-rounded text-primary text-sm">check_circle</i>
                                    {{ feat }}
                                </li>
                            </ul>
                            <Link :href="route('register')" class="btn w-100 py-3 rounded-xl fw-bold" :class="plan.popular ? 'btn-primary' : 'btn-outline-white'">
                                Select {{ plan.name }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-white/5 text-center">
            <div class="container">
                <div class="mb-4 d-flex justify-content-center align-items-center gap-2">
                    <div class="logo-box bg-white/10 p-1 rounded">
                        <i class="material-symbols-rounded text-white fs-5">cloud</i>
                    </div>
                    <span class="fw-bold">VMCORE</span>
                </div>
                <p class="text-white-50 text-sm mb-0">© 2026 VMCORE Infrastructure Solutions. All rights reserved.</p>
                <div class="social-links d-flex justify-content-center gap-4 mt-4">
                    <a href="#" class="text-white-50 hover-text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white-50 hover-text-white transition"><i class="fab fa-github"></i></a>
                    <a href="#" class="text-white-50 hover-text-white transition"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    data() {
        return {
            features: [
                { icon: 'bolt', title: 'Lightning Fast', desc: 'Optimized Nginx engine and real-time terminal for unmatched performance.' },
                { icon: 'shield_lock', title: 'Security First', desc: 'Built-in firewall, IP whitelisting, and automated SSL certificate management.' },
                { icon: 'deployed_code', title: 'Git Integration', desc: 'Deploy directly from GitHub, GitLab, or Bitbucket with automatic hooks.' },
                { icon: 'database', title: 'DB Manager', desc: 'Effortless MySQL & PostgreSQL management with advanced user permissions.' },
                { icon: 'terminal', title: 'Web Terminal', desc: 'A full-featured SSH terminal directly in your browser with secure key support.' },
                { icon: 'monitoring', title: 'Live Insights', desc: 'Real-time CPU, RAM, and Disk monitoring with custom alert notifications.' }
            ],
            plans: [
                { name: 'Free', price: '$0', features: ['1 Server', 'Unlimited Domains', 'Community Support', 'Basic Metrics'] },
                { name: 'Pro', price: '$19', popular: true, features: ['5 Servers', 'Advanced Security', 'Priority Support', 'Git Auto-Deploy', 'Team Access'] },
                { name: 'Enterprise', price: '$99', features: ['Unlimited Servers', 'White Label', 'SLA Guarantee', 'Dedicated Manager', 'API Access'] }
            ]
        }
    }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.landing-page {
    font-family: 'Inter', sans-serif;
}

.text-gradient {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
}

.btn-outline-white {
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-outline-white:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: white;
}

.hover-bg-white\/10:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-5px);
}

.transition { transition: all 0.3s ease; }
.tracking-tighter { letter-spacing: -0.05em; }

.dashboard-preview {
    perspective: 1000px;
}

.preview-wrapper {
    transform: rotateX(10deg) translateY(20px);
    transition: transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.dashboard-preview:hover .preview-wrapper {
    transform: rotateX(0deg) translateY(0px);
}

/* Custom spacing utilities since we aren't using Tailwind fully */
.pt-32 { padding-top: 8rem; }
.pb-20 { padding-bottom: 5rem; }
.mt-16 { margin-top: 4rem; }
.mb-8 { margin-bottom: 2rem; }
.space-y-4 > * + * { margin-top: 1rem; }
</style>
