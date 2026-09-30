<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Activity,
    ArrowDownRight,
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronDown,
    CircleHelp,
    Cloud,
    Code2,
    Copy,
    Cpu,
    Database,
    ExternalLink,
    Github,
    HardDrive,
    Layers,
    Lock,
    Mail,
    Menu,
    Moon,
    Plus,
    Server,
    Shield,
    ShieldCheck,
    Sun,
    Terminal,
    Workflow,
    X,
    Zap,
} from 'lucide-vue-next';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const lightTheme = ref(false);
const menuOpen = ref(false);
const productsOpen = ref(false);
const productsDropdownRef = ref(null);
const motionReady = ref(false);
const openFaq = ref(0);
const copiedInstall = ref(false);
const selectedCurrency = ref('USD');

const installCommand = 'curl -fsSL https://nimbus-host.vmcore.in/install.sh | bash';

const copyInstall = async () => {
    try {
        await navigator.clipboard.writeText(installCommand);
        copiedInstall.value = true;
        setTimeout(() => {
            copiedInstall.value = false;
        }, 2500);
    } catch (e) {
        console.error('Failed to copy', e);
    }
};

const nimbusFeatures = [
    {
        icon: Cpu,
        tag: 'PERFORMANCE',
        title: 'Lightweight Core (<25MB RAM)',
        copy: 'Unlike legacy control panels that consume gigabytes of system memory, Nimbus runs lean with near-zero daemon overhead.',
    },
    {
        icon: Terminal,
        tag: 'CONTAINERS',
        title: 'Docker & Compose Native',
        copy: 'Deploy and manage containerized applications, multi-service Docker Compose files, and automated volume mounts with 1 click.',
    },
    {
        icon: ShieldCheck,
        tag: 'SECURITY',
        title: 'Automated SSL & Firewalls',
        copy: 'Automatic Let’s Encrypt certificate issuance and renewal, managed UFW port rules, and integrated fail2ban intrusion prevention.',
    },
    {
        icon: Zap,
        tag: 'WEB SERVER',
        title: 'Nginx, HTTP/3 & Brotli',
        copy: 'Pre-tuned Nginx reverse proxy configuration supporting HTTP/3, Brotli/Gzip compression, WebSockets, and custom upstream rules.',
    },
    {
        icon: Database,
        tag: 'DATABASES',
        title: 'MySQL, Postgres & Redis',
        copy: 'One-click database provisioning, user privilege boundaries, automated local snapshots, and scheduled off-site S3 uploads.',
    },
    {
        icon: Workflow,
        tag: 'CI/CD',
        title: 'Git Push-to-Deploy',
        copy: 'Connect GitHub, GitLab, or Bitbucket webhooks for zero-downtime automated deployment on every push to your production branch.',
    },
];

const nimbusFaqs = [
    {
        question: 'What Linux distributions are supported?',
        answer: 'Nimbus is officially tested and optimized for clean installations of Ubuntu 20.04, 22.04, and 24.04 LTS, as well as Debian 11 (Bullseye) and Debian 12 (Bookworm).',
    },
    {
        question: 'How does licensing and activation work?',
        answer: 'After purchasing or generating your license key in your client workspace, simply run `nimbus activate <YOUR-LICENSE-KEY>` on your server terminal. The panel will immediately unlock your tier capabilities.',
    },
    {
        question: 'Can I migrate my existing sites to Nimbus?',
        answer: 'Yes. Nimbus includes automated file, database, and Nginx vhost import utilities to help you smoothly migrate existing websites from standard LAMP/LEMP stacks, cPanel, or Plesk.',
    },
    {
        question: 'What is the difference between Nimbus and Managed Hosting?',
        answer: 'Nimbus is our standalone software product for developers who want to manage their own VPS or bare-metal server. If you prefer our engineering team to handle the servers, updates, backups, and 24/7 reliability for you, our Managed Hosting service is the ideal choice.',
    },
    {
        question: 'Where are automated backups stored?',
        answer: 'Backups can be stored locally on your server or automatically streamed to external S3-compatible cloud storage (AWS S3, Cloudflare R2, Backblaze B2, or MinIO).',
    },
];

let revealObserver = null;

onMounted(() => {
    const saved = window.localStorage.getItem('rook-theme');
    if (saved === 'light') {
        lightTheme.value = true;
    }

    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz && (tz === 'Asia/Kolkata' || tz.includes('Calcutta') || tz.includes('Kolkata'))) {
            selectedCurrency.value = 'INR';
        }
    } catch (e) {}

    // Load Razorpay script if needed
    if (!document.getElementById('razorpay-script')) {
        const script = document.createElement('script');
        script.id = 'razorpay-script';
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);
    }

    const handleOutsideClick = (e) => {
        if (productsDropdownRef.value && !productsDropdownRef.value.contains(e.target)) {
            productsOpen.value = false;
        }
    };
    document.addEventListener('click', handleOutsideClick);
    window._cleanupNimbusDropdown = () => document.removeEventListener('click', handleOutsideClick);

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
    const revealTargets = document.querySelectorAll('[data-reveal]');
    if (!revealTargets.length) return;

    revealObserver = new IntersectionObserver(
        (entries, activeObserver) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    activeObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -32px 0px' }
    );
    revealTargets.forEach((target) => revealObserver.observe(target));
    motionReady.value = true;
});

onUnmounted(() => {
    if (revealObserver) revealObserver.disconnect();
    if (window._cleanupNimbusDropdown) window._cleanupNimbusDropdown();
});

const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
};

const closeMenu = () => {
    menuOpen.value = false;
    productsOpen.value = false;
};

const handlePlanAction = async (plan) => {
    if (!user.value) {
        router.visit(route('login'));
        return;
    }

    if (plan.slug === 'free' || plan.price_inr === 0) {
        router.post(route('licenses.free'));
        return;
    }

    // Direct to store with self-hosted tab or initiate checkout
    router.visit(route('store.index', { tab: 'self_hosted' }));
};

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Nimbus Control Panel — Modern Linux Server Software" />

    <div
        class="rook-site"
        :data-theme="lightTheme ? 'light' : 'dark'"
        :data-motion-ready="motionReady ? 'true' : undefined"
    >
        <!-- Sticky Site Header -->
        <header class="site-header">
            <div class="shell header-inner">
                <Link :href="route('home')" class="brand" aria-label="Home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>rook</span>
                </Link>

                <nav :class="['nav-links', { 'is-open': menuOpen }]" aria-label="Main navigation">
                    <!-- Products Dropdown -->
                    <div class="products-dropdown-container" ref="productsDropdownRef">
                        <button
                            type="button"
                            @click.stop="productsOpen = !productsOpen"
                            class="products-dropdown-trigger"
                            :aria-expanded="productsOpen"
                        >
                            <span>Products</span>
                            <ChevronDown :size="13" :class="['dropdown-arrow', { 'is-rotated': productsOpen }]" aria-hidden="true" />
                        </button>
                        
                        <div v-show="productsOpen" class="products-dropdown-menu">
                            <Link :href="route('home')" @click="closeMenu" class="dropdown-item">
                                <div class="dropdown-item-icon">
                                    <Cloud :size="16" />
                                </div>
                                <div class="dropdown-item-text">
                                    <div class="dropdown-item-title">
                                        Managed Cloud Hosting
                                        <span class="dropdown-badge">Flagship</span>
                                    </div>
                                    <p class="dropdown-item-desc">Enterprise managed servers, SRE care &amp; 99.99% uptime.</p>
                                </div>
                            </Link>

                            <Link :href="route('products.nimbus')" @click="closeMenu" class="dropdown-item">
                                <div class="dropdown-item-icon nimbus-icon">
                                    <Terminal :size="16" />
                                </div>
                                <div class="dropdown-item-text">
                                    <div class="dropdown-item-title">
                                        Nimbus Control Panel
                                        <span class="dropdown-badge nimbus-badge">Software</span>
                                    </div>
                                    <p class="dropdown-item-desc">Self-hosted Linux server management &amp; Docker.</p>
                                </div>
                            </Link>

                            <a :href="`${route('home')}#stack`" @click="closeMenu" class="dropdown-item">
                                <div class="dropdown-item-icon">
                                    <Layers :size="16" />
                                </div>
                                <div class="dropdown-item-text">
                                    <div class="dropdown-item-title">App Stacks &amp; Runtimes</div>
                                    <p class="dropdown-item-desc">Laravel, WordPress, Node.js, Docker &amp; databases.</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <a href="#features" @click="closeMenu">Features</a>
                    <a href="#terminal" @click="closeMenu">Installer</a>
                    <a href="#pricing" @click="closeMenu">Licensing</a>
                    <a href="#faq" @click="closeMenu">FAQ</a>
                    <Link :href="route('home')" @click="closeMenu">Managed Hosting</Link>
                </nav>

                <div class="nav-actions">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="button button-outline button-small">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" aria-label="Login">
                            Login
                        </Link>
                    </template>

                    <button
                        class="theme-toggle"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        :aria-pressed="lightTheme"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>

                    <a class="button button-primary button-small" href="#pricing">
                        Get License <ArrowUpRight :size="13" aria-hidden="true" />
                    </a>

                    <button
                        class="menu-toggle"
                        type="button"
                        :aria-label="menuOpen ? 'Close navigation menu' : 'Open navigation menu'"
                        :aria-expanded="menuOpen"
                        @click="menuOpen = !menuOpen"
                    >
                        <X v-if="menuOpen" :size="17" aria-hidden="true" />
                        <Menu v-else :size="17" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <main id="top">
            <!-- Nimbus Hero Section -->
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero-grid-overlay" aria-hidden="true" />
                <div class="shell hero-layout">
                    <div class="hero-copy">
                        <div class="eyebrow hero-kicker">
                            STANDALONE SERVER CONTROL PANEL
                        </div>
                        <h1 id="hero-title">
                            Own your servers.<span class="highlight">Deploy without limits.</span>
                        </h1>
                        <p class="hero-lede">
                            Nimbus is a modern, lightweight Linux server management platform. Effortlessly configure Docker apps, automated SSL certificates, Nginx reverse proxies, and databases on any Ubuntu or Debian VPS.
                        </p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#pricing">
                                Get Nimbus License <ArrowRight :size="15" aria-hidden="true" />
                            </a>
                            <a class="button button-outline" href="#terminal">
                                View One-Line Install <ArrowDownRight :size="15" aria-hidden="true" />
                            </a>
                        </div>
                        <div class="hero-note">
                            <Check :size="14" aria-hidden="true" /> Compatible with any VPS provider (Hetzner, DigitalOcean, AWS, Linode)
                        </div>
                    </div>

                    <!-- Curl Installer Terminal Box -->
                    <div class="terminal-wrap" id="terminal" aria-label="Nimbus Quick Install Terminal">
                        <div class="terminal">
                            <div class="terminal-bar">
                                <div class="terminal-dots" aria-hidden="true"><i /><i /><i /></div>
                                <span>quick-install / bash</span>
                                <span class="terminal-live">v2.4 LTS</span>
                            </div>
                            <div class="terminal-content">
                                <p class="terminal-line">
                                    <span class="line-index">01</span>
                                    <span class="terminal-command">
                                        <span class="line-prompt">#</span> Run on a clean Ubuntu or Debian server:
                                    </span>
                                </p>
                                <div class="p-3 my-2 rounded-lg bg-[var(--surface-deep)] border border-[var(--edge)] flex items-center justify-between gap-3">
                                    <code class="text-xs font-mono text-[var(--green-bright)] overflow-x-auto whitespace-nowrap">
                                        curl -fsSL https://nimbus-host.vmcore.in/install.sh | bash
                                    </code>
                                    <button
                                        type="button"
                                        @click="copyInstall"
                                        class="px-2.5 py-1.5 rounded-md text-[11px] font-mono font-medium border border-[var(--edge)] bg-[var(--panel)] hover:bg-[var(--panel-hi)] text-[var(--text)] transition-colors flex items-center gap-1.5 shrink-0"
                                    >
                                        <Check v-if="copiedInstall" :size="13" class="text-[var(--accent)]" />
                                        <Copy v-else :size="13" />
                                        <span>{{ copiedInstall ? 'Copied!' : 'Copy' }}</span>
                                    </button>
                                </div>
                                <p class="terminal-line">
                                    <span class="line-index">02</span>
                                    <span>
                                        <span class="line-ok">✓</span> System requirements verified (Ubuntu/Debian)
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">03</span>
                                    <span>
                                        <span class="line-ok">✓</span> Docker Engine, Nginx &amp; SSL stack configured
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">04</span>
                                    <span>
                                        <span class="line-prompt">&gt;</span> Panel ready at: https://your-server-ip:8443<span class="cursor" aria-hidden="true" />
                                    </span>
                                </p>
                                <div class="terminal-divider" />
                                <div class="terminal-footer">
                                    <span>nimbus / core / bootstrap-lts</span>
                                    <strong>Sub-25MB RAM footprint</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Architecture & Features Section -->
            <section class="section" id="features" aria-labelledby="features-title" data-reveal>
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">Engineered for Devs &amp; Sysadmins</div>
                            <h2 class="section-heading" id="features-title">Everything you need. Zero unnecessary bloat.</h2>
                            <p class="section-intro">
                                Built from the ground up to replace clunky, memory-heavy control panels with a blazing fast, developer-friendly interface.
                            </p>
                        </div>
                        <span class="section-index">01 / ARCHITECTURE</span>
                    </div>

                    <div class="feature-grid">
                        <article
                            v-for="(feature, index) in nimbusFeatures"
                            :key="feature.title"
                            class="feature-card"
                        >
                            <span class="feature-index">{{ String(index + 1).padStart(2, '0') }}</span>
                            <div class="feature-icon">
                                <component :is="feature.icon" :size="17" :stroke-width="1.7" aria-hidden="true" />
                            </div>
                            <span class="feature-tag">{{ feature.tag }}</span>
                            <h3>{{ feature.title }}</h3>
                            <p>{{ feature.copy }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Software Licensing & Purchase Section -->
            <section class="section" id="pricing" aria-labelledby="pricing-title" data-reveal>
                <div class="shell">
                    <div class="section-top pricing-top">
                        <div>
                            <div class="eyebrow">Simple, transparent licensing</div>
                            <h2 class="section-heading" id="pricing-title">Choose the right tier for your servers.</h2>
                            <p class="section-intro">
                                Start with our free Community license or upgrade for multi-server orchestration, automated off-site S3 backups, and priority updates.
                            </p>
                        </div>
                        <div class="billing-control" role="group" aria-label="Currency selection">
                            <button
                                type="button"
                                @click="selectedCurrency = 'USD'"
                                :aria-pressed="selectedCurrency === 'USD'"
                            >
                                USD ($)
                            </button>
                            <button
                                type="button"
                                @click="selectedCurrency = 'INR'"
                                :aria-pressed="selectedCurrency === 'INR'"
                            >
                                INR (₹)
                            </button>
                        </div>
                    </div>

                    <div class="pricing-grid">
                        <article
                            v-for="plan in plans"
                            :key="plan.id"
                            :class="['price-card', { featured: plan.slug === 'pro' }]"
                        >
                            <span v-if="plan.slug === 'pro'" class="popular-label">Most popular</span>
                            <div class="plan-name">{{ plan.name }} License</div>
                            <p class="plan-note">
                                {{ plan.slug === 'free' ? 'For personal projects, dev machines, and single server setups.' : plan.slug === 'pro' ? 'For production apps, agencies, and teams running client servers.' : 'For enterprise infrastructure, fleets, and mission-critical clusters.' }}
                            </p>
                            <div class="plan-price">
                                <template v-if="plan.price_inr === 0">
                                    <span class="price-amount">Free</span>
                                    <span class="price-unit">/ forever</span>
                                </template>
                                <template v-else>
                                    <span class="price-amount">
                                        {{ selectedCurrency === 'INR' ? `₹${plan.price_inr}` : `$${plan.price_usd}` }}
                                    </span>
                                    <span class="price-unit">/ month</span>
                                </template>
                            </div>
                            <div class="billing-caption">
                                {{ plan.price_inr === 0 ? 'No credit card required' : 'Cancel anytime · Instant license key activation' }}
                            </div>

                            <button
                                type="button"
                                @click="handlePlanAction(plan)"
                                :class="['button', plan.slug === 'pro' ? 'button-primary' : 'button-outline', 'plan-cta', 'w-full']"
                            >
                                <span>{{ plan.slug === 'free' ? 'Get Free License' : `Buy ${plan.name} License` }}</span>
                                <ArrowRight :size="14" aria-hidden="true" />
                            </button>

                            <div class="plan-rule" />
                            <div class="plan-list-label">Included Capabilities</div>
                            <ul class="plan-list">
                                <li><Check :size="14" aria-hidden="true" />{{ plan.slug === 'free' ? '1 Active Server Node' : plan.slug === 'pro' ? 'Up to 5 Server Nodes' : 'Unlimited Server Nodes' }}</li>
                                <li><Check :size="14" aria-hidden="true" />Unlimited domains &amp; web applications</li>
                                <li><Check :size="14" aria-hidden="true" />Docker &amp; Compose container orchestration</li>
                                <li><Check :size="14" aria-hidden="true" />Automated Let’s Encrypt SSL &amp; Nginx tuning</li>
                                <li><Check :size="14" aria-hidden="true" />{{ plan.slug === 'free' ? 'Community Forum Support' : 'Priority Security Updates &amp; Offsite Backups' }}</li>
                                <li v-if="plan.slug === 'enterprise'"><Check :size="14" aria-hidden="true" />Custom white-label branding &amp; Lead Engineer support</li>
                            </ul>
                        </article>
                    </div>

                    <!-- Looking for full Managed Care Banner -->
                    <div class="mt-12 p-6 rounded-xl border border-[var(--edge)] bg-[var(--panel)] flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div>
                            <div class="text-xs font-bold font-mono uppercase tracking-wider text-[var(--accent)]">Don't want to manage Linux servers yourself?</div>
                            <h3 class="text-base font-bold text-[var(--text)] mt-1">Explore our fully Managed Cloud Hosting</h3>
                            <p class="text-xs text-[var(--text-soft)] mt-0.5">Let our infrastructure team handle server provisioning, security, updates, and 24/7 monitoring for you.</p>
                        </div>
                        <Link :href="route('home')" class="button button-outline button-small whitespace-nowrap">
                            View Managed Hosting Plans <ArrowRight :size="13" />
                        </Link>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="section" id="faq" aria-labelledby="faq-title" data-reveal>
                <div class="shell faq-layout">
                    <div class="faq-aside">
                        <div class="eyebrow">Common Questions</div>
                        <h2 class="section-heading" id="faq-title">About Nimbus Software.</h2>
                        <p class="section-intro">Have a question about installation, licensing, or server compatibility? Everything you need to know.</p>
                        <a class="faq-contact" href="mailto:support@vmcore.in?subject=Nimbus%20Software%20Question">
                            <Mail :size="14" aria-hidden="true" /> Ask support team
                        </a>
                    </div>
                    <div class="faq-list">
                        <article
                            v-for="(faq, index) in nimbusFaqs"
                            :key="faq.question"
                            class="faq-item"
                        >
                            <h3 style="margin: 0;">
                                <button
                                    class="faq-question"
                                    type="button"
                                    :aria-expanded="openFaq === index"
                                    @click="openFaq = openFaq === index ? null : index"
                                >
                                    <span>{{ faq.question }}</span>
                                    <Plus :size="17" aria-hidden="true" />
                                </button>
                            </h3>
                            <div v-if="openFaq === index" class="faq-answer">
                                {{ faq.answer }}
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <!-- Site Footer -->
        <footer class="site-footer">
            <div class="shell">
                <div class="footer-top">
                    <div class="footer-brand-col">
                        <Link :href="route('home')" class="brand">
                            <span class="brand-mark" aria-hidden="true">r</span>
                            <span>rook</span>
                        </Link>
                        <p class="footer-brand-copy">
                            Managed Cloud Hosting &amp; Developer Infrastructure by VMCore. High-performance software and 24/7 reliability care.
                        </p>
                    </div>
                    <div class="footer-group">
                        <h3>Products</h3>
                        <div class="footer-links">
                            <Link :href="route('home')">Managed Cloud Hosting</Link>
                            <Link :href="route('products.nimbus')">Nimbus Control Panel</Link>
                            <a :href="`${route('home')}#stack`">Supported Stacks</a>
                            <a :href="`${route('home')}#pricing`">Hosting Pricing</a>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Nimbus Links</h3>
                        <div class="footer-links">
                            <a href="#terminal">Install Command</a>
                            <a href="#pricing">License Keys</a>
                            <a href="#features">Architecture</a>
                            <Link :href="route('dashboard')">Client Workspace</Link>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Contact</h3>
                        <div class="footer-links">
                            <a href="mailto:support@vmcore.in">support@vmcore.in</a>
                            <Link :href="route('tickets.index')">Support Tickets</Link>
                            <a :href="`${route('home')}#status`">System Status</a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span>© {{ currentYear }} Nimbus by VMCore. All rights reserved.</span>
                    <a class="footer-status" :href="`${route('home')}#status`">All Systems Operational</a>
                    <div class="footer-socials">
                        <a href="https://github.com" target="_blank" rel="noreferrer" aria-label="GitHub">
                            <Github :size="15" aria-hidden="true" />
                        </a>
                        <a href="mailto:support@vmcore.in" aria-label="Email">
                            <Mail :size="15" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
