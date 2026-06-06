<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Nimbus by VMCore | Modern Server Management" />

    <div class="landing-page bg-[#F8FAFC] text-[#111827] min-vh-100 overflow-x-hidden selection:bg-emerald-500/10">
        <!-- Navigation -->
        <nav class="fixed-top px-4 py-3 bg-white/80 backdrop-blur-md border-b border-[#E5E7EB] shadow-sm">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="brand d-flex align-items-center gap-2.5">
                    <div class="logo-box bg-emerald-500 p-2 rounded-lg shadow-sm shadow-emerald-500/10">
                        <i class="material-symbols-rounded text-white text-base">cloud</i>
                    </div>
                    <span class="fw-bold tracking-tight text-lg text-gray-900">Nimbus by VMCore</span>
                </div>
                <div class="nav-links d-none d-md-flex gap-4">
                    <a href="#features" class="text-gray-500 hover:text-gray-900 font-semibold text-xs uppercase tracking-wider text-decoration-none transition">Features</a>
                    <a href="#pricing" class="text-gray-500 hover:text-gray-900 font-semibold text-xs uppercase tracking-wider text-decoration-none transition">Pricing</a>
                    <a href="https://docs.vmcore.in" class="text-gray-500 hover:text-gray-900 font-semibold text-xs uppercase tracking-wider text-decoration-none transition">Docs</a>
                </div>
                <div class="auth-actions d-flex gap-2 align-items-center">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-emerald btn-sm px-4 rounded-pill fw-semibold shadow-sm">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-link text-gray-600 hover:text-gray-900 text-xs font-semibold uppercase tracking-wider text-decoration-none px-3">Login</Link>
                        <Link :href="route('register')" class="btn btn-emerald btn-sm px-4 rounded-pill fw-bold text-white shadow-sm">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section pt-32 pb-20 px-4">
            <div class="container text-center">
                <div class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-pill mb-4 animate__animated animate__fadeInDown text-[10px] font-bold uppercase tracking-wider">
                    Introducing Nimbus v2.0
                </div>
                <h1 class="display-4 fw-bold mb-4 tracking-tighter text-gray-900 animate__animated animate__fadeInUp">
                    The Modern Standard for <br/>
                    <span class="text-gradient">Server Management</span>
                </h1>
                <p class="lead text-gray-500 max-w-2xl mx-auto mb-5 animate__animated animate__fadeInUp animate__delay-1s text-sm">
                    Empower your infrastructure with Nimbus. A lightweight, high-performance control panel designed for developers who value speed, security, and aesthetics.
                </p>
                <div class="hero-actions d-flex justify-content-center gap-3 animate__animated animate__fadeInUp animate__delay-1s">
                    <Link :href="route('register')" class="btn btn-emerald btn-lg px-5 rounded-pill shadow-lg shadow-emerald-500/10 fw-semibold">Create Free Account</Link>
                    <a href="#demo" class="btn btn-outline-dark bg-white border-gray-300 hover:bg-gray-50 text-gray-700 btn-lg px-5 rounded-pill fw-semibold">Live Demo</a>
                </div>

                <!-- Dashboard Preview -->
                <div class="dashboard-preview mt-16 animate__animated animate__zoomIn animate__delay-2s">
                    <div class="preview-wrapper bg-white border border-[#E5E7EB] p-2 rounded-3xl shadow-xl shadow-gray-200/50">
                        <img src="https://via.placeholder.com/1200x800/f8fafc/111827?text=Nimbus+Dashboard+Preview" alt="Nimbus Preview" class="img-fluid rounded-2xl shadow-sm">
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="py-24 bg-white border-y border-[#E5E7EB]">
            <div class="container">
                <div class="text-center mb-16">
                    <h2 class="display-6 fw-bold mb-3 text-gray-900">Enterprise Features</h2>
                    <p class="text-gray-500 text-sm">Built for scale, secured by design.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-4" v-for="(feat, i) in features" :key="i">
                        <div class="feature-card p-5 bg-white border border-[#E5E7EB] rounded-3xl h-100 transition">
                            <div class="feat-icon mb-4 text-emerald-600 bg-emerald-50 p-3 rounded-2xl d-inline-block shadow-sm">
                                <i class="material-symbols-rounded fs-2">{{ feat.icon }}</i>
                            </div>
                            <h5 class="fw-bold mb-3 text-gray-900">{{ feat.title }}</h5>
                            <p class="text-gray-500 leading-relaxed text-xs">{{ feat.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 bg-[#F8FAFC]">
            <div class="container">
                <div class="text-center mb-16">
                    <h2 class="display-6 fw-bold mb-3 text-gray-900">Simple Pricing</h2>
                    <p class="text-gray-500 text-sm">Choose the plan that fits your growth.</p>
                </div>
                <div class="row justify-content-center g-4 align-items-stretch">
                    <div class="col-md-4" v-for="(plan, i) in plans" :key="i">
                        <div class="pricing-card p-5 h-100 rounded-3xl border transition" :class="plan.popular ? 'bg-white border-2 border-emerald-500 shadow-xl shadow-emerald-500/5 relative scale-105 z-10' : 'bg-white border-[#E5E7EB]'">
                            <div v-if="plan.popular" class="popular-badge absolute -top-4 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-[9px] fw-bold px-4 py-1.5 rounded-full uppercase tracking-widest">Most Popular</div>
                            <h5 class="fw-bold text-gray-500 mb-2 uppercase tracking-widest text-[10px]">{{ plan.name }}</h5>
                            <div class="d-flex align-items-end gap-1 mb-4">
                                <span class="display-5 fw-bold text-gray-900">{{ plan.price }}</span>
                                <span class="text-gray-400 mb-2 text-sm">/mo</span>
                            </div>
                            <ul class="list-unstyled mb-8 space-y-3">
                                <li v-for="feat in plan.features" :key="feat" class="d-flex align-items-center gap-3 text-gray-600 text-xs">
                                    <i class="material-symbols-rounded text-emerald-500 text-sm">check_circle</i>
                                    {{ feat }}
                                </li>
                            </ul>
                            <Link :href="route('register')" class="btn w-100 py-3 rounded-xl fw-bold text-xs uppercase tracking-wider transition" :class="plan.popular ? 'btn-emerald' : 'btn-outline-dark bg-white border-gray-300 hover:bg-gray-50 text-gray-700'">
                                Select {{ plan.name }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-[#E5E7EB] bg-white text-center">
            <div class="container">
                <div class="mb-4 d-flex justify-content-center align-items-center gap-2">
                    <div class="logo-box bg-emerald-50 p-2 rounded shadow-sm">
                        <i class="material-symbols-rounded text-emerald-600 fs-5">cloud</i>
                    </div>
                    <span class="fw-bold text-gray-900">Nimbus by VMCore</span>
                </div>
                <p class="text-gray-500 text-xs mb-0">© 2026 Nimbus by VMCore. All rights reserved.</p>
                <div class="social-links d-flex justify-content-center gap-4 mt-4">
                    <a href="#" class="text-gray-400 hover:text-gray-900 transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-900 transition"><i class="fab fa-github"></i></a>
                    <a href="#" class="text-gray-400 hover:text-gray-900 transition"><i class="fab fa-discord"></i></a>
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
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.btn-emerald {
    background-color: #10B981;
    color: white;
    border: none;
}

.btn-emerald:hover {
    background-color: #059669;
    color: white;
}

.btn-outline-dark:hover {
    background-color: #f8fafc;
    border-color: #d1d5db;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
    border-color: #a7f3d0 !important;
}

.transition { transition: all 0.3s ease; }
.tracking-tighter { letter-spacing: -0.05em; }

.dashboard-preview {
    perspective: 1000px;
}

.preview-wrapper {
    transform: rotateX(8deg) translateY(10px);
    transition: transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.dashboard-preview:hover .preview-wrapper {
    transform: rotateX(0deg) translateY(0px);
}

/* Custom spacing utilities */
.pt-32 { padding-top: 8rem; }
.pb-20 { padding-bottom: 5rem; }
.mt-16 { margin-top: 4rem; }
.mb-8 { margin-bottom: 2rem; }
.space-y-3 > * + * { margin-top: 0.75rem; }
</style>
