<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    plans: Array,
    selfHostedPlans: Array,
    managedHostingPlans: Array,
    testimonials: Array,
});

const scrolled = ref(false);
const mobileMenuOpen = ref(false);
const openFaq = ref(null);
const selectedCurrency = ref('USD');
const activeServiceTab = ref('managed_hosting'); // 'managed_hosting' or 'self_hosted'
const copiedInstall = ref(false);

const installUrl = computed(() => {
    if (typeof window !== 'undefined' && window.location.origin && window.location.origin.includes('vmcore.in')) {
        return `${window.location.origin}/install.sh`;
    }
    return 'https://nimbus-host.vmcore.in/install.sh';
});
const installCommand = computed(() => `curl -fsSL ${installUrl.value} | bash`);

const copyInstallCommand = async () => {
    try {
        await navigator.clipboard.writeText(installCommand.value);
        copiedInstall.value = true;
        setTimeout(() => { copiedInstall.value = false; }, 2000);
    } catch (e) {
        console.error('Copy failed:', e);
    }
};

const getPlanFeatures = (plan) => {
    if (!plan || !plan.features) return [];
    if (Array.isArray(plan.features)) return plan.features;
    if (typeof plan.features === 'string') {
        try {
            const parsed = JSON.parse(plan.features);
            if (Array.isArray(parsed)) return parsed;
        } catch (e) {
            return plan.features.split('\n').map(s => s.trim()).filter(Boolean);
        }
    }
    return [];
};

const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const scrollToPricing = () => {
    const el = document.getElementById('pricing');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);

    try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz && (tz === 'Asia/Kolkata' || tz.includes('Calcutta') || tz.includes('Kolkata'))) {
            selectedCurrency.value = 'INR';
        }
    } catch (e) {
        console.error('Timezone auto-detection failed:', e);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const features = [
    { icon: 'domain', title: 'Domain Management', desc: 'Create and manage domains with automatic Nginx configuration, virtual hosts, and directory browsing — all from one UI.' },
    { icon: 'folder_open', title: 'Elite File Manager', desc: 'Web-based file manager with Ace Editor integration, in-depth search, double-click editing, and permissions control.' },
    { icon: 'lock', title: 'SSL Certificates', desc: 'One-click Let\'s Encrypt SSL with live certificate probing, auto-renewal, and automatic HTTPS redirects.' },
    { icon: 'database', title: 'Database Manager', desc: 'Create MySQL & MariaDB databases, manage users with granular permissions, and access phpMyAdmin seamlessly.' },
    { icon: 'code', title: 'Git & Deployment', desc: 'Full Git integration with commit, push, pull, branch management, and Personal Access Token authentication.' },
    { icon: 'terminal', title: 'Web Terminal', desc: 'Secure browser-based SSH terminal with real-time command execution directly from your dashboard.' },
    { icon: 'schedule', title: 'Supervisor & Cron', desc: 'Manage queue workers, monitor processes, and create visual cron schedules with human-readable expressions.' },
    { icon: 'monitoring', title: 'Live Monitoring', desc: 'Real-time CPU, RAM, Disk, and Network monitoring with process controls and resource usage tracking.' },
    { icon: 'web', title: 'WordPress Manager', desc: 'Manage themes, plugins, and users for WordPress installations with upcoming one-click install support.' },
];

const defaultSelfHostedPlans = [
    {
        name: 'Free Starter',
        slug: 'free',
        type: 'self_hosted',
        price_usd: 0,
        price_inr: 0,
        billing_period: 'forever',
        is_popular: false,
        features: ['1 Server', '3 Domains Limit', 'SSL Automation', 'File Manager', 'Community Support', 'Basic Monitoring'],
        cta_text: 'Start Free'
    },
    {
        name: 'Pro License',
        slug: 'pro',
        type: 'self_hosted',
        price_usd: 19,
        price_inr: 499,
        billing_period: '/year',
        is_popular: true,
        features: ['5 Servers Support', '50 Domains Limit', 'Git Auto-Deploy', 'Priority Support', 'Team Access', 'Advanced Security', 'WordPress Manager'],
        cta_text: 'Buy Pro Now'
    },
    {
        name: 'Enterprise License',
        slug: 'enterprise',
        type: 'self_hosted',
        price_usd: 49,
        price_inr: 1999,
        billing_period: '/year',
        is_popular: false,
        features: ['Unlimited Servers', '9999 Domains Limit', 'White Label Support', 'SLA Guarantee', 'Dedicated Manager', 'API Access', 'Custom Integrations'],
        cta_text: 'Buy Enterprise Now'
    }
];

const defaultManagedPlans = [
    {
        name: 'Starter Cloud',
        slug: 'starter-cloud',
        type: 'managed_hosting',
        price_usd: 49,
        renewal_price_usd: 59,
        price_inr: 3800,
        renewal_price_inr: 4790,
        billing_period: '/year',
        is_popular: false,
        features: [
            '1 vCPU & 2GB RAM Cloud Node',
            '30GB NVMe High-Speed Storage',
            'Fully Managed by VMCORE Team',
            'Free Auto-Renewing SSL',
            'Automated Daily Backups',
            'Standard Server Monitoring',
        ],
        cta_text: 'Get Managed Starter',
        description: 'Perfect for personal sites, blogs, and light production workloads.'
    },
    {
        name: 'Business Cloud',
        slug: 'business-cloud',
        type: 'managed_hosting',
        price_usd: 99,
        renewal_price_usd: 119,
        price_inr: 7990,
        renewal_price_inr: 9990,
        billing_period: '/year',
        is_popular: true,
        features: [
            '2 vCPU & 4GB RAM Dedicated Node',
            '80GB NVMe Enterprise Storage',
            'Fully Managed by Nimbus Engineers',
            'Priority 24/7 Operations Support',
            'Fail2ban & Advanced DDoS Shield',
            'Automated Hourly / Daily Backups',
            'Staging Environments & Git Deploy',
        ],
        cta_text: 'Deploy Business Cloud',
        description: 'High-performance cloud for e-commerce, agencies, and high-traffic sites.'
    },
    {
        name: 'Enterprise Cloud',
        slug: 'enterprise-cloud',
        type: 'managed_hosting',
        price_usd: 199,
        renewal_price_usd: 249,
        price_inr: 16990,
        renewal_price_inr: 19990,
        billing_period: '/year',
        is_popular: false,
        features: [
            '4 vCPU & 8GB RAM High-Performance Node',
            '160GB NVMe Extreme Storage',
            'Dedicated VMCORE SysAdmin Support',
            'Custom Nginx & PHP Optimization',
            'White Glove Migration Included',
            'Real-time Uptime SLA (99.9%)',
            'Custom Daemon & Supervisor Management',
        ],
        cta_text: 'Get Enterprise Cloud',
        description: 'Maximum speed, dedicated isolated resources, and direct sysadmin support.'
    }
];

const activePlans = computed(() => {
    if (activeServiceTab.value === 'managed_hosting') {
        return (props.managedHostingPlans && props.managedHostingPlans.length > 0)
            ? props.managedHostingPlans
            : defaultManagedPlans;
    }
    return (props.selfHostedPlans && props.selfHostedPlans.length > 0)
        ? props.selfHostedPlans
        : (props.plans && props.plans.length > 0 ? props.plans : defaultSelfHostedPlans);
});

const defaultTestimonials = [
    {
        id: 1,
        name: 'TestMe',
        role: 'Managed Cloud Client',
        company: 'TestMe',
        location: 'Mumbai, Maharashtra',
        quote: 'Best managed hosting service we have used. Got instant support, rock-solid 99.9% uptime, and direct sysadmin care that lets us focus entirely on our business.',
        rating: 5,
    },
    {
        id: 2,
        name: 'dmanindia',
        role: 'E-Commerce Website',
        company: 'dmanindia',
        location: 'Vapi, Gujarat, India',
        quote: 'Running an e-commerce website requires high speed and uninterrupted availability. Nimbus handles our customer traffic spikes and flash sales with effortless stability.',
        rating: 5,
    },
    {
        id: 3,
        name: 'Maharaj POS',
        role: 'Retail Point of Sale Systems',
        company: 'Maharaj POS',
        location: 'Panchmahal, Gujarat, India',
        quote: 'Our point-of-sale retail clients demand 24/7 reliability. Hosting on Nimbus fully managed cloud gave us instant response times, automated backups, and complete peace of mind.',
        rating: 5,
    },
];

const testimonialsList = computed(() => {
    return (props.testimonials && props.testimonials.length > 0)
        ? props.testimonials
        : defaultTestimonials;
});

const faqs = [
    { q: 'What are the two ways to use Nimbus?', a: 'Just like Coolify, Nimbus offers two clear pathways: (1) Self-Host Nimbus on your own VPS or bare-metal server (AWS, Hetzner, DigitalOcean) with 1-line installation and full control; or (2) Choose Fully Managed Cloud Hosting where the VMCORE engineering team handles servers, backups, uptime, and security for you.' },
    { q: 'Can I self-host it for free?', a: 'Yes! The Self-Hosted Starter plan is completely free forever and allows you to manage 1 server with 3 domains, automated SSL, file manager, and web terminal.' },
    { q: 'What is included in Fully Managed Cloud Hosting?', a: 'With Fully Managed Cloud, you do not need to manage any Linux servers or DevOps. We provide dedicated high-speed NVMe cloud instances, handle security updates, configure firewalls, take automated daily backups, and provide 24/7 technical monitoring.' },
    { q: 'Can I migrate between Self-Hosted and Managed Cloud?', a: 'Yes! Our team provides free white-glove site and database migration from any external host or self-hosted Nimbus node to our Managed Cloud at any time.' },
    { q: 'What operating systems are supported for self-hosting?', a: 'Nimbus installs on Ubuntu 22.04+ or Debian 11+ servers with root access and minimum 1GB RAM. It automatically configures Nginx, PHP 8.2+, MariaDB, Node.js, and Supervisor.' },
    { q: 'Can I request custom server specs or enterprise agreements?', a: 'Yes! Through our Managed Cloud Enterprise tier, our team designs custom high-availability clusters, dedicated IP blocks, and custom SLA agreements.' },
];
</script>

<template>
    <Head title="Nimbus by VMCore — Modern Server Management" />

    <div class="landing" :class="{ 'mobile-menu-open': mobileMenuOpen }">
        <!-- ======================== NAVBAR ======================== -->
        <nav class="navbar" :class="{ 'navbar--scrolled': scrolled }">
            <div class="navbar__inner">
                <Link href="/" class="navbar__brand">
                    <div class="navbar__logo-box">
                        <img src="/favicon.png?v=2" alt="Nimbus" class="navbar__logo-img" />
                    </div>
                    <span class="navbar__brand-text">Nimbus <span class="navbar__brand-sub">by VMCore</span></span>
                </Link>

                <div class="navbar__links">
                    <a href="#features" class="navbar__link">Features</a>
                    <a href="#how-it-works" class="navbar__link">How It Works</a>
                    <a href="#pricing" class="navbar__link">Pricing</a>
                    <a href="https://nimbus-docs.vmcore.in/" class="navbar__link" target="_blank">Docs</a>
                </div>

                <div class="navbar__actions">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn btn--primary btn--sm">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn--ghost btn--sm">Login</Link>
                        <Link :href="route('register')" class="btn btn--primary btn--sm">Get Started</Link>
                    </template>
                </div>

                <button class="navbar__hamburger" @click="mobileMenuOpen = !mobileMenuOpen">
                    <span class="material-symbols-rounded">{{ mobileMenuOpen ? 'close' : 'menu' }}</span>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div class="navbar__mobile" :class="{ 'navbar__mobile--open': mobileMenuOpen }">
                <a href="#features" class="navbar__mobile-link" @click="mobileMenuOpen = false">Features</a>
                <a href="#how-it-works" class="navbar__mobile-link" @click="mobileMenuOpen = false">How It Works</a>
                <a href="#pricing" class="navbar__mobile-link" @click="mobileMenuOpen = false">Pricing</a>
                <a href="https://nimbus-docs.vmcore.in/" class="navbar__mobile-link" target="_blank">Docs</a>
                <div class="navbar__mobile-actions">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn btn--primary btn--full">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn--outline btn--full">Login</Link>
                        <Link :href="route('register')" class="btn btn--primary btn--full">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- ======================== HERO ======================== -->
        <section class="hero">
            <div class="container hero__grid">
                <div class="hero__content">
                    <div class="hero__badge">
                        <span class="hero__badge-emoji">⚡</span>
                        <span class="hero__badge-text">SELF-HOST OR FULLY MANAGED CLOUD</span>
                    </div>

                    <h1 class="hero__title">
                        Deploy, Host &amp;<br />
                        <span class="hero__title-accent">Scale Any Server</span><br />
                        Without Complexity.
                    </h1>

                    <p class="hero__subtitle">
                        Like Coolify for modern web infrastructure — install Nimbus on your own VPS with a 1-line script, or let our team handle everything with <strong>Fully Managed Nimbus Cloud Hosting</strong>.
                    </p>

                    <div class="hero__ctas">
                        <a href="#deploy-options" class="btn btn--primary btn--lg">Choose Your Way</a>
                        <a href="#pricing" class="btn btn--outline btn--lg">View Plans &amp; Pricing</a>
                    </div>

                    <div class="hero__trust-checks">
                        <span class="hero__check"><span class="hero__check-icon">✓</span> 1-Line Self-Host Installer</span>
                        <span class="hero__check"><span class="hero__check-icon">✓</span> 100% Zero-DevOps Cloud</span>
                        <span class="hero__check"><span class="hero__check-icon">✓</span> Free SSL &amp; Automated Backups</span>
                    </div>
                </div>

                <div class="hero__visual">
                    <div class="hero__mockup">
                        <div class="mockup__chrome">
                            <div class="mockup__dots">
                                <span class="mockup__dot mockup__dot--red"></span>
                                <span class="mockup__dot mockup__dot--yellow"></span>
                                <span class="mockup__dot mockup__dot--green"></span>
                            </div>
                            <div class="mockup__url">nimbus-host.vmcore.in</div>
                        </div>
                        <div class="mockup__body">
                            <div class="mockup__sidebar">
                                <div class="mockup__sidebar-item mockup__sidebar-item--active">
                                    <span class="material-symbols-rounded" style="font-size: 14px">dashboard</span>
                                    <span>Dashboard</span>
                                </div>
                                <div class="mockup__sidebar-item">
                                    <span class="material-symbols-rounded" style="font-size: 14px">domain</span>
                                    <span>Domains</span>
                                </div>
                                <div class="mockup__sidebar-item">
                                    <span class="material-symbols-rounded" style="font-size: 14px">database</span>
                                    <span>Databases</span>
                                </div>
                                <div class="mockup__sidebar-item">
                                    <span class="material-symbols-rounded" style="font-size: 14px">terminal</span>
                                    <span>Terminal</span>
                                </div>
                                <div class="mockup__sidebar-item">
                                    <span class="material-symbols-rounded" style="font-size: 14px">folder</span>
                                    <span>Files</span>
                                </div>
                            </div>
                            <div class="mockup__main">
                                <div class="mockup__stats">
                                    <div class="mockup__stat">
                                        <span class="mockup__stat-label">CPU</span>
                                        <span class="mockup__stat-value">23%</span>
                                        <div class="mockup__stat-bar"><div class="mockup__stat-fill" style="width: 23%"></div></div>
                                    </div>
                                    <div class="mockup__stat">
                                        <span class="mockup__stat-label">RAM</span>
                                        <span class="mockup__stat-value">1.2 GB</span>
                                        <div class="mockup__stat-bar"><div class="mockup__stat-fill" style="width: 60%"></div></div>
                                    </div>
                                    <div class="mockup__stat">
                                        <span class="mockup__stat-label">Disk</span>
                                        <span class="mockup__stat-value">18 GB</span>
                                        <div class="mockup__stat-bar"><div class="mockup__stat-fill" style="width: 36%"></div></div>
                                    </div>
                                </div>
                                <div class="mockup__terminal">
                                    <div class="mockup__terminal-header">Terminal</div>
                                    <div class="mockup__terminal-body">
                                        <div class="mockup__terminal-line"><span class="mockup__terminal-prompt">$</span> git pull origin main</div>
                                        <div class="mockup__terminal-line mockup__terminal-line--muted">Already up to date.</div>
                                        <div class="mockup__terminal-line"><span class="mockup__terminal-prompt">$</span> npm run build</div>
                                        <div class="mockup__terminal-line mockup__terminal-line--success">✓ built in 842ms</div>
                                        <div class="mockup__terminal-line"><span class="mockup__terminal-prompt">$</span> <span class="mockup__terminal-cursor">_</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Icons -->
                    <div class="hero__float hero__float--1">
                        <i class="fab fa-github"></i>
                    </div>
                    <div class="hero__float hero__float--2">
                        <i class="fab fa-docker"></i>
                    </div>
                    <div class="hero__float hero__float--3">
                        <i class="fab fa-linux"></i>
                    </div>
                    <div class="hero__float hero__float--4">
                        <span class="material-symbols-rounded" style="font-size:18px">terminal</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== TWO WAYS TO DEPLOY ======================== -->
        <section id="deploy-options" class="deploy-options py-16 bg-slate-50/70 border-t border-b border-gray-200">
            <div class="container">
                <div class="section-header text-center max-w-2xl mx-auto mb-12">
                    <span class="section-header__label font-bold text-xs uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 inline-block mb-3">Two Ways to Deploy</span>
                    <h2 class="section-header__title text-3xl sm:text-4xl font-black text-gray-950 tracking-tight">Your Hardware or Ours.<br/>You Pick The Experience.</h2>
                    <p class="section-header__subtitle text-sm text-gray-600 mt-3 leading-relaxed">
                        Whether you want 100% root control over your own VPS or want us to manage the entire server infrastructure for you, Nimbus has you covered.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <!-- Card 1: Fully Managed Cloud (Highlighted) -->
                    <div class="bg-gradient-to-b from-emerald-50/40 to-white border-2 border-emerald-500 rounded-2xl p-8 shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider py-1.5 px-4 rounded-bl-xl shadow-xs">
                            Fully Managed • Zero Ops
                        </div>
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center mb-5 shadow-xs">
                                <span class="material-symbols-rounded text-2xl">cloud_sync</span>
                            </div>
                            <h3 class="text-xl font-black text-gray-950 mb-2">Fully Managed Cloud Hosting</h3>
                            <p class="text-xs text-gray-600 leading-relaxed mb-6">
                                Don't want to manage Linux kernels, firewall rules, security patches, or server crashes? We deploy, manage, and monitor your cloud instance 24/7 on high-speed NVMe infrastructure.
                            </p>

                            <div class="bg-emerald-100/60 border border-emerald-200/80 rounded-xl p-3.5 mb-6 text-xs text-emerald-950 font-medium space-y-1">
                                <div class="font-bold flex items-center gap-1.5 text-emerald-900">
                                    <span class="material-symbols-rounded text-sm">verified_user</span>
                                    Hands-off Cloud Infrastructure
                                </div>
                                <div class="text-[11px] text-emerald-850">
                                    We handle backups, security updates, uptime, and performance tuning. You just deploy your applications.
                                </div>
                            </div>

                            <ul class="space-y-2.5 text-xs text-gray-700 mb-6">
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>High-speed NVMe cloud instances with dedicated resources</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>Automated remote backups &amp; 24/7 system health monitoring</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>Free white-glove site migration by VMCORE engineers</span>
                                </li>
                            </ul>
                        </div>

                        <button 
                            @click="activeServiceTab = 'managed_hosting'; scrollToPricing()"
                            class="btn btn--primary btn--full font-bold"
                        >
                            Explore Managed Cloud Packages
                        </button>
                    </div>

                    <!-- Card 2: Self-Hosted -->
                    <div class="bg-white border-2 border-gray-200 hover:border-slate-400 rounded-2xl p-8 shadow-sm transition-all flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute top-0 right-0 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wider py-1.5 px-4 rounded-bl-xl border-l border-b border-gray-200">
                            Self-Host Nimbus
                        </div>
                        <div>
                            <div class="h-12 w-12 rounded-xl bg-slate-900 text-white flex items-center justify-center mb-5 shadow-xs">
                                <span class="material-symbols-rounded text-2xl">terminal</span>
                            </div>
                            <h3 class="text-xl font-black text-gray-950 mb-2">Self-Hosted On Your Servers</h3>
                            <p class="text-xs text-gray-600 leading-relaxed mb-6">
                                Bring your own VPS or bare-metal machine (Hetzner, AWS, DigitalOcean, Linode, OVH). Run a single command and unlock an elite server management control panel with zero vendor lock-in.
                            </p>

                            <div class="bg-slate-950 text-slate-200 rounded-xl p-3 text-xs font-mono flex items-center justify-between border border-slate-800 mb-6 gap-2">
                                <div class="flex items-center gap-2 overflow-x-auto min-w-0">
                                    <span class="text-emerald-400 font-bold select-none">$</span>
                                    <code class="truncate text-[11px] text-emerald-300 font-mono">{{ installCommand }}</code>
                                </div>
                                <button 
                                    type="button" 
                                    @click="copyInstallCommand" 
                                    class="shrink-0 text-[10px] uppercase font-bold tracking-wider px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 transition-colors flex items-center gap-1.5 cursor-pointer"
                                    :title="copiedInstall ? 'Copied to clipboard!' : 'Copy install command'"
                                >
                                    <span class="material-symbols-rounded text-xs leading-none">{{ copiedInstall ? 'check' : 'content_copy' }}</span>
                                    <span>{{ copiedInstall ? 'Copied' : 'Copy' }}</span>
                                </button>
                            </div>

                            <ul class="space-y-2.5 text-xs text-gray-700 mb-6">
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>Full root access &amp; 100% data sovereignty</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>Modular toolkit: Nginx, PHP, MySQL, Git, Cron &amp; SSL</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-600 font-bold">✓</span>
                                    <span>Free forever starter tier + affordable multi-server plans</span>
                                </li>
                            </ul>
                        </div>

                        <button 
                            @click="activeServiceTab = 'self_hosted'; scrollToPricing()"
                            class="btn btn--outline btn--full font-bold"
                        >
                            View Self-Hosted Plans
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== TRUST LOGOS ======================== -->
        <section class="trust">
            <div class="container">
                <p class="trust__text">Trusted by developers deploying thousands of applications worldwide.</p>
                <div class="trust__logos">
                    <div class="trust__logo"><i class="fab fa-github"></i><span>GitHub</span></div>
                    <div class="trust__logo"><i class="fab fa-docker"></i><span>Docker</span></div>
                    <div class="trust__logo"><span class="material-symbols-rounded" style="font-size:22px">dns</span><span>Nginx</span></div>
                    <div class="trust__logo"><i class="fab fa-ubuntu"></i><span>Ubuntu</span></div>
                    <div class="trust__logo"><i class="fab fa-laravel"></i><span>Laravel</span></div>
                    <div class="trust__logo"><span class="material-symbols-rounded" style="font-size:22px">database</span><span>PostgreSQL</span></div>
                </div>
            </div>
        </section>

        <!-- ======================== FEATURES ======================== -->
        <section id="features" class="features">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label">Features</span>
                    <h2 class="section-header__title">Everything You Need to<br/>Manage Modern Infrastructure</h2>
                    <p class="section-header__subtitle">Built for speed, security, and scale — from single servers to production fleets.</p>
                </div>

                <div class="features__grid">
                    <div class="feature-card" v-for="(feat, i) in features" :key="i">
                        <div class="feature-card__icon">
                            <span class="material-symbols-rounded">{{ feat.icon }}</span>
                        </div>
                        <h3 class="feature-card__title">{{ feat.title }}</h3>
                        <p class="feature-card__desc">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== HOW IT WORKS ======================== -->
        <section id="how-it-works" class="how-it-works">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label">How It Works</span>
                    <h2 class="section-header__title">Up and Running in Three Steps</h2>
                    <p class="section-header__subtitle">From bare metal to fully managed in under five minutes.</p>
                </div>

                <div class="steps">
                    <div class="step">
                        <div class="step__number">1</div>
                        <div class="step__connector"></div>
                        <h3 class="step__title">Connect Your Server</h3>
                        <p class="step__desc">Point any Ubuntu 22.04+ or Debian 11+ server with root access. Minimum 1GB RAM required.</p>
                    </div>
                    <div class="step">
                        <div class="step__number">2</div>
                        <div class="step__connector"></div>
                        <h3 class="step__title">Install Nimbus Agent</h3>
                        <p class="step__desc">Run a single curl command. Nimbus auto-installs Nginx, PHP, MariaDB, Node.js, Composer, and Supervisor.</p>
                    </div>
                    <div class="step">
                        <div class="step__number">3</div>
                        <h3 class="step__title">Deploy Instantly</h3>
                        <p class="step__desc">Manage domains, databases, Git deployments, SSL, and monitoring — all from your browser.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== DASHBOARD PREVIEW ======================== -->
        <section id="dashboard-preview" class="dashboard-preview">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label">Dashboard</span>
                    <h2 class="section-header__title">Beautiful Interface.<br/>Powerful Infrastructure.</h2>
                    <p class="section-header__subtitle">A modern control panel that makes server management feel effortless.</p>
                </div>

                <div class="dashboard-preview__showcase">
                    <div class="dashboard-preview__glow"></div>
                    <div class="dashboard-preview__mockup">
                        <div class="mockup__chrome">
                            <div class="mockup__dots">
                                <span class="mockup__dot mockup__dot--red"></span>
                                <span class="mockup__dot mockup__dot--yellow"></span>
                                <span class="mockup__dot mockup__dot--green"></span>
                            </div>
                            <div class="mockup__url">nimbus-host.vmcore.in/dashboard</div>
                        </div>
                        <div class="dashboard-preview__body">
                            <div class="dp__row">
                                <div class="dp__card dp__card--stat">
                                    <span class="dp__card-icon material-symbols-rounded">dns</span>
                                    <div><div class="dp__card-value">12</div><div class="dp__card-label">Active Servers</div></div>
                                </div>
                                <div class="dp__card dp__card--stat dp__card--success">
                                    <span class="dp__card-icon material-symbols-rounded">check_circle</span>
                                    <div><div class="dp__card-value">99.8%</div><div class="dp__card-label">Deployment Success</div></div>
                                </div>
                                <div class="dp__card dp__card--stat">
                                    <span class="dp__card-icon material-symbols-rounded">lock</span>
                                    <div><div class="dp__card-value">48</div><div class="dp__card-label">SSL Certificates</div></div>
                                </div>
                                <div class="dp__card dp__card--stat">
                                    <span class="dp__card-icon material-symbols-rounded">speed</span>
                                    <div><div class="dp__card-value">23%</div><div class="dp__card-label">Avg. CPU Usage</div></div>
                                </div>
                            </div>
                            <div class="dp__chart-placeholder">
                                <div class="dp__chart-title">CPU Usage — Last 24 Hours</div>
                                <svg viewBox="0 0 600 100" class="dp__chart-svg">
                                    <polyline fill="none" stroke="#10B981" stroke-width="2" points="0,80 40,70 80,65 120,50 160,55 200,40 240,35 280,45 320,30 360,25 400,35 440,28 480,20 520,30 560,25 600,22" />
                                    <polyline fill="url(#chartGrad)" stroke="none" points="0,80 40,70 80,65 120,50 160,55 200,40 240,35 280,45 320,30 360,25 400,35 440,28 480,20 520,30 560,25 600,22 600,100 0,100" />
                                    <defs><linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10B981" stop-opacity="0.15"/><stop offset="100%" stop-color="#10B981" stop-opacity="0"/></linearGradient></defs>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== BENEFITS ======================== -->
        <section class="benefits">
            <div class="container">
                <div class="benefits__grid">
                    <div class="benefits__visual">
                        <div class="benefits__illustration">
                            <div class="benefits__server-stack">
                                <div class="benefits__server" v-for="i in 3" :key="i">
                                    <div class="benefits__server-led"></div>
                                    <div class="benefits__server-bars">
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="benefits__content">
                        <span class="section-header__label">Why Nimbus</span>
                        <h2 class="benefits__title">Built for developers<br/>who ship fast.</h2>
                        <p class="benefits__desc">Everything you need to manage modern server infrastructure, without the complexity of traditional hosting panels.</p>
                        <ul class="benefits__list">
                            <li><span class="benefits__list-icon">✔</span> One-click deployments from Git</li>
                            <li><span class="benefits__list-icon">✔</span> Unlimited domains &amp; projects</li>
                            <li><span class="benefits__list-icon">✔</span> Secure SSH terminal in browser</li>
                            <li><span class="benefits__list-icon">✔</span> Automatic Let's Encrypt SSL</li>
                            <li><span class="benefits__list-icon">✔</span> Real-time server metrics</li>
                            <li><span class="benefits__list-icon">✔</span> Elite file manager with code editor</li>
                            <li><span class="benefits__list-icon">✔</span> WordPress management</li>
                            <li><span class="benefits__list-icon">✔</span> Supervisor &amp; Cron scheduler</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== PRICING ======================== -->
        <section id="pricing" class="pricing">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label font-bold text-xs uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 inline-block mb-3">Plans &amp; Pricing</span>
                    <h2 class="section-header__title text-3xl sm:text-4xl font-black text-gray-950 tracking-tight">Transparent, Predictable Pricing</h2>
                    <p class="section-header__subtitle text-sm text-gray-600 mt-2">Choose between Fully Managed Cloud Hosting or Self-Hosted Control Panel licenses.</p>
                </div>

                <!-- Dual Service Switcher: Managed Hosting vs Self-Hosted (Coolify model) -->
                <div class="flex justify-center mb-6">
                    <div class="inline-flex p-1.5 bg-gray-200/80 rounded-2xl border border-gray-300 shadow-2xs gap-1 max-w-full overflow-x-auto">
                        <button 
                            type="button"
                            @click="activeServiceTab = 'managed_hosting'"
                            :class="activeServiceTab === 'managed_hosting' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                        >
                            <span class="material-symbols-rounded text-base text-emerald-600">cloud</span>
                            Fully Managed Cloud Hosting
                        </button>
                        <button 
                            type="button"
                            @click="activeServiceTab = 'self_hosted'"
                            :class="activeServiceTab === 'self_hosted' ? 'bg-white text-gray-950 shadow-sm font-bold border border-gray-200' : 'text-gray-600 hover:text-gray-900 font-medium'"
                            class="px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 whitespace-nowrap"
                        >
                            <span class="material-symbols-rounded text-base text-purple-600">terminal</span>
                            Self-Hosted Nimbus Licenses
                        </button>
                    </div>
                </div>

                <!-- Dynamic Currency Switcher -->
                <div class="pricing-switcher-container">
                    <div class="pricing-switcher animate-fade-in">
                        <button 
                            type="button"
                            @click="selectedCurrency = 'USD'" 
                            :class="{ 'pricing-switcher__btn--active': selectedCurrency === 'USD' }" 
                            class="pricing-switcher__btn"
                        >
                            Global (USD)
                        </button>
                        <button 
                            type="button"
                            @click="selectedCurrency = 'INR'" 
                            :class="{ 'pricing-switcher__btn--active': selectedCurrency === 'INR' }" 
                            class="pricing-switcher__btn"
                        >
                            India (INR)
                        </button>
                    </div>
                </div>

                <div class="pricing__grid">
                    <div class="pricing-card" :class="{ 'pricing-card--popular': plan.is_popular }" v-for="(plan, i) in activePlans" :key="plan.slug || i">
                        <div v-if="plan.is_popular" class="pricing-card__badge">Most Popular</div>
                        <h3 class="pricing-card__name">{{ plan.name }}</h3>
                        <p v-if="plan.description" class="text-xs text-gray-500 mb-3 min-h-[30px] leading-relaxed">{{ plan.description }}</p>
                        <div class="pricing-card__price">
                            <span class="pricing-card__amount">
                                {{ 
                                    selectedCurrency === 'USD' 
                                        ? (plan.price_usd === 0 ? '$0' : '$' + plan.price_usd) 
                                        : (plan.price_inr === 0 ? '₹0' : '₹' + Number(plan.price_inr || 0).toLocaleString('en-IN'))
                                }}
                            </span>
                            <span class="pricing-card__period">{{ plan.billing_period }}</span>
                        </div>
                        <div v-if="(plan.renewal_price_inr && plan.renewal_price_inr !== plan.price_inr) || (plan.renewal_price_usd && plan.renewal_price_usd !== plan.price_usd)" class="text-[11px] text-emerald-700 font-mono mb-4">
                            Renews at: {{ selectedCurrency === 'USD' ? '$' + (plan.renewal_price_usd || plan.price_usd) : '₹' + Number(plan.renewal_price_inr || plan.price_inr).toLocaleString('en-IN') }}{{ plan.billing_period }}
                        </div>
                        <ul class="pricing-card__features">
                            <li v-for="feat in getPlanFeatures(plan)" :key="feat">
                                <span class="pricing-card__check">✓</span>
                                {{ feat }}
                            </li>
                        </ul>
                        <Link :href="$page.props.auth?.user ? route('dashboard') : route('register')" class="btn btn--full" :class="plan.is_popular ? 'btn--primary' : 'btn--outline'">
                            {{ plan.cta_text || (plan.price_usd > 0 || plan.price_inr > 0 ? 'Get Started' : 'Start Free') }}
                        </Link>
                    </div>
                </div>

                <!-- Custom Requirements / Self-Host Callout -->
                <div class="pricing-custom-callout">
                    <div class="pricing-custom-callout__content">
                        <div class="pricing-custom-callout__badge">
                            <span class="material-symbols-rounded text-sm">tune</span>
                            Custom Architecture &amp; Enterprise
                        </div>
                        <h3 class="pricing-custom-callout__title">
                            Want to self host your server or got custom requirements?
                        </h3>
                        <p class="pricing-custom-callout__desc">
                            Whether you need dedicated bare-metal infrastructure, custom cluster topologies, specialized compliance, or high-volume SLA, our engineers will build and optimize a tailored solution for your business.
                        </p>
                    </div>
                    <div class="pricing-custom-callout__action">
                        <a 
                            href="mailto:support@vmcore.in?subject=Custom%20Hosting%20%2F%20Self-Host%20Inquiry" 
                            class="btn btn--primary btn--lg"
                        >
                            <span class="material-symbols-rounded text-base">mail</span>
                            <span>Contact Us Now</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== TESTIMONIALS ======================== -->
        <section class="testimonials">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label font-bold text-xs uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 inline-block mb-3">Testimonials</span>
                    <h2 class="section-header__title text-3xl sm:text-4xl font-black text-gray-950 tracking-tight">Loved by Developers, Vendors &amp; Clients</h2>
                    <p class="section-header__subtitle text-sm text-gray-600 mt-2 max-w-2xl mx-auto">
                        For developers, vendors, and business clients who want to focus more on their core tasks and productivity while Nimbus powers their servers.
                    </p>
                </div>

                <div class="testimonials__grid">
                    <div class="testimonial-card" v-for="(t, i) in testimonialsList" :key="t.id || i">
                        <div class="testimonial-card__stars text-amber-400">
                            <span v-for="s in (t.rating || 5)" :key="s">★</span>
                        </div>
                        <p class="testimonial-card__quote">"{{ t.quote }}"</p>
                        <div class="testimonial-card__author">
                            <div class="testimonial-card__avatar">{{ t.name?.charAt(0) || 'C' }}</div>
                            <div>
                                <div class="testimonial-card__name">{{ t.name }}</div>
                                <div class="testimonial-card__role">
                                    {{ [t.role, t.company].filter(Boolean).join(' • ') }}
                                </div>
                                <div v-if="t.location" class="text-[11px] text-emerald-600 font-medium flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-rounded text-xs leading-none">location_on</span>
                                    <span>{{ t.location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== FAQ ======================== -->
        <section class="faq">
            <div class="container">
                <div class="section-header">
                    <span class="section-header__label">FAQ</span>
                    <h2 class="section-header__title">Frequently Asked Questions</h2>
                </div>

                <div class="faq__list">
                    <div class="faq-item" :class="{ 'faq-item--open': openFaq === i }" v-for="(item, i) in faqs" :key="i" @click="toggleFaq(i)">
                        <div class="faq-item__question">
                            <span>{{ item.q }}</span>
                            <span class="faq-item__toggle material-symbols-rounded">{{ openFaq === i ? 'remove' : 'add' }}</span>
                        </div>
                        <div class="faq-item__answer" v-show="openFaq === i">
                            <p>{{ item.a }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== FINAL CTA ======================== -->
        <section class="final-cta">
            <div class="container">
                <div class="final-cta__card">
                    <h2 class="final-cta__title">Ready to simplify<br/>your infrastructure?</h2>
                    <p class="final-cta__subtitle">Start deploying servers in minutes. Free forever, no credit card required.</p>
                    <Link :href="route('register')" class="btn btn--primary btn--lg">Create Free Account</Link>
                </div>
            </div>
        </section>

        <!-- ======================== FOOTER ======================== -->
        <footer class="site-footer">
            <div class="container">
                <div class="site-footer__grid">
                    <div class="site-footer__brand">
                        <div class="site-footer__logo">
                            <div class="navbar__logo-box"><img src="/favicon.png?v=2" alt="Nimbus" class="navbar__logo-img" /></div>
                            <span class="navbar__brand-text">Nimbus <span class="navbar__brand-sub">by VMCore</span></span>
                        </div>
                        <p class="site-footer__tagline">Modern, lightweight server management for developers.</p>
                    </div>

                    <div class="site-footer__col">
                        <h4 class="site-footer__heading">Product</h4>
                        <a href="#features" class="site-footer__link">Features</a>
                        <a href="#pricing" class="site-footer__link">Pricing</a>
                        <a href="#" class="site-footer__link">Roadmap</a>
                        <a href="#" class="site-footer__link">Changelog</a>
                    </div>
                    <div class="site-footer__col">
                        <h4 class="site-footer__heading">Resources</h4>
                        <a href="https://nimbus-docs.vmcore.in/" class="site-footer__link">Documentation</a>
                        <a href="#" class="site-footer__link">API</a>
                        <a href="https://github.com/sudhirrajai/Nimbus" class="site-footer__link">GitHub</a>
                    </div>
                    <div class="site-footer__col">
                        <h4 class="site-footer__heading">Company</h4>
                        <Link :href="route('pages.show', 'support')" class="site-footer__link">Support</Link>
                        <Link :href="route('pages.show', 'terms')" class="site-footer__link">Terms of Service</Link>
                        <Link :href="route('pages.show', 'privacy')" class="site-footer__link">Privacy Policy</Link>
                    </div>
                </div>
                <div class="site-footer__bottom">
                    <p>© 2026 Nimbus by VMCore. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* ============================================================
   DESIGN TOKENS
   ============================================================ */
:root {
    --color-primary: #10B981;
    --color-primary-hover: #059669;
    --color-bg: #FAFBFC;
    --color-bg-alt: #F3F4F6;
    --color-card: #FFFFFF;
    --color-border: #E5E7EB;
    --color-heading: #111827;
    --color-body: #6B7280;
    --color-success: #22C55E;
    --color-warning: #F59E0B;
    --radius: 16px;
    --radius-sm: 10px;
    --radius-xs: 6px;
    --max-width: 1280px;
    --transition: 250ms ease;
    --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ============================================================
   RESET & BASE
   ============================================================ */
.landing {
    font-family: var(--font);
    background: var(--color-bg);
    color: var(--color-body);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}
.landing *, .landing *::before, .landing *::after { box-sizing: border-box; }
.container { max-width: var(--max-width); margin: 0 auto; padding: 0 24px; }

/* ============================================================
   BUTTONS
   ============================================================ */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    font-family: var(--font); font-weight: 600; font-size: 14px;
    border-radius: var(--radius-sm); cursor: pointer; transition: all var(--transition);
    text-decoration: none; border: 1.5px solid transparent; line-height: 1;
}
.btn--sm { padding: 8px 18px; font-size: 13px; }
.btn--lg { padding: 14px 32px; font-size: 15px; }
.btn--full { width: 100%; padding: 12px 24px; }
.btn--primary { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
.btn--primary:hover { background: var(--color-primary-hover); border-color: var(--color-primary-hover); transform: translateY(-1px); color: #fff !important; }
.btn--outline { background: var(--color-card); color: var(--color-heading); border-color: var(--color-border); }
.btn--outline:hover { border-color: #d1d5db; background: #f9fafb; color: var(--color-heading) !important; }
.btn--ghost { background: transparent; color: var(--color-body); border-color: transparent; }
.btn--ghost:hover { color: var(--color-heading); }

/* ============================================================
   NAVBAR
   ============================================================ */
.navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    border-bottom: 1px solid transparent; transition: all var(--transition);
}
.navbar--scrolled { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px) saturate(180%); border-bottom-color: var(--color-border); }
.navbar__inner {
    width: 100%; max-width: var(--max-width); margin: 0 auto; padding: 0 24px;
    display: flex; align-items: center; justify-content: space-between; height: 64px;
}
.navbar__brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.navbar__logo-box {
    width: 32px; height: 32px; border-radius: 8px; background: var(--color-primary);
    display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.navbar__logo-img { width: 20px; height: 20px; object-fit: contain; }
.navbar__brand-text { font-weight: 700; font-size: 16px; color: var(--color-heading); }
.navbar__brand-sub { font-weight: 500; font-size: 11px; opacity: 0.8; color: var(--color-body); }
.navbar__links { display: flex; gap: 32px; }
.navbar__link {
    font-size: 14px; font-weight: 500; color: var(--color-body);
    text-decoration: none; transition: color var(--transition);
}
.navbar__link:hover { color: var(--color-heading); }
.navbar__actions { display: flex; align-items: center; gap: 12px; }
.navbar__hamburger { display: none; background: none; border: none; cursor: pointer; color: var(--color-heading); padding: 4px; }
.navbar__mobile { display: none; }

@media (max-width: 768px) {
    .navbar__links, .navbar__actions { display: none; }
    .navbar__hamburger { display: flex; }
    .navbar__mobile {
        display: none; flex-direction: column; gap: 8px;
        padding: 16px 24px 24px; background: var(--color-card);
        border-bottom: 1px solid var(--color-border);
    }
    .navbar__mobile--open { display: flex; }
    .navbar__mobile-link {
        font-size: 15px; font-weight: 500; color: var(--color-body);
        text-decoration: none; padding: 10px 0; border-bottom: 1px solid var(--color-border);
    }
    .navbar__mobile-actions { display: flex; flex-direction: column; gap: 10px; padding-top: 12px; }
}

/* ============================================================
   HERO
   ============================================================ */
.hero { padding: 140px 0 80px; }
.hero__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
.hero__badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);
    padding: 6px 16px; border-radius: 100px; margin-bottom: 24px;
}
.hero__badge-emoji { font-size: 14px; }
.hero__badge-text { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; color: var(--color-primary-hover); }
.hero__title { font-size: 56px; font-weight: 800; line-height: 1.08; color: var(--color-heading); margin: 0 0 24px; letter-spacing: -0.03em; }
.hero__title-accent { background: linear-gradient(135deg, #10B981, #059669); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero__subtitle { font-size: 18px; line-height: 1.7; color: var(--color-body); margin: 0 0 32px; max-width: 520px; }
.hero__ctas { display: flex; gap: 12px; margin-bottom: 28px; }
.hero__trust-checks { display: flex; gap: 20px; flex-wrap: wrap; }
.hero__check { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--color-body); }
.hero__check-icon { color: var(--color-primary); font-weight: 700; }

/* Hero Visual */
.hero__visual { position: relative; }
.mockup__chrome {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px; background: #f9fafb; border-bottom: 1px solid var(--color-border);
    border-radius: var(--radius) var(--radius) 0 0;
}
.mockup__dots { display: flex; gap: 6px; }
.mockup__dot { width: 10px; height: 10px; border-radius: 50%; }
.mockup__dot--red { background: #ef4444; }
.mockup__dot--yellow { background: #f59e0b; }
.mockup__dot--green { background: #22c55e; }
.mockup__url { font-size: 11px; color: var(--color-body); background: #fff; padding: 4px 12px; border-radius: 4px; border: 1px solid var(--color-border); margin-left: auto; margin-right: auto; }
.hero__mockup { background: var(--color-card); border: 1px solid var(--color-border); border-radius: var(--radius); overflow: hidden; box-shadow: 0 20px 60px -15px rgba(0,0,0,0.08); }
.mockup__body { display: flex; min-height: 260px; }
.mockup__sidebar { width: 140px; border-right: 1px solid var(--color-border); padding: 12px 8px; background: #fafbfc; flex-shrink: 0; }
.mockup__sidebar-item { display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 6px; font-size: 11px; color: var(--color-body); cursor: default; transition: all var(--transition); margin-bottom: 2px; }
.mockup__sidebar-item--active { background: rgba(16,185,129,0.08); color: var(--color-primary-hover); font-weight: 600; }
.mockup__main { flex: 1; padding: 14px; display: flex; flex-direction: column; gap: 12px; }
.mockup__stats { display: flex; gap: 10px; }
.mockup__stat { flex: 1; background: #fafbfc; border: 1px solid var(--color-border); border-radius: 8px; padding: 10px; }
.mockup__stat-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-body); }
.mockup__stat-value { font-size: 14px; font-weight: 700; color: var(--color-heading); display: block; margin: 2px 0 6px; }
.mockup__stat-bar { height: 4px; background: #e5e7eb; border-radius: 2px; }
.mockup__stat-fill { height: 100%; background: var(--color-primary); border-radius: 2px; }
.mockup__terminal { background: #1e293b; border-radius: 8px; overflow: hidden; flex: 1; }
.mockup__terminal-header { padding: 6px 12px; font-size: 10px; font-weight: 600; color: #94a3b8; border-bottom: 1px solid #334155; }
.mockup__terminal-body { padding: 10px 12px; font-family: 'JetBrains Mono', monospace; font-size: 11px; line-height: 1.7; }
.mockup__terminal-line { color: #e2e8f0; }
.mockup__terminal-line--muted { color: #64748b; }
.mockup__terminal-line--success { color: #34d399; }
.mockup__terminal-prompt { color: #10b981; margin-right: 6px; }
.mockup__terminal-cursor { animation: blink 1s step-end infinite; }
@keyframes blink { 50% { opacity: 0; } }

/* Floating Icons */
.hero__float {
    position: absolute; display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 10px; background: var(--color-card);
    border: 1px solid var(--color-border); color: var(--color-body);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06); font-size: 18px;
    animation: floatY 6s ease-in-out infinite;
}
.hero__float--1 { top: -10px; right: 20px; animation-delay: 0s; }
.hero__float--2 { bottom: 40px; right: -15px; animation-delay: 1.5s; }
.hero__float--3 { top: 40px; left: -15px; animation-delay: 3s; }
.hero__float--4 { bottom: -10px; left: 30px; animation-delay: 4.5s; }
@keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

@media (max-width: 768px) {
    .hero { padding: 100px 0 40px; }
    .hero__grid { grid-template-columns: 1fr; gap: 48px; }
    .hero__title { font-size: 36px; }
    .hero__visual { display: none; }
    .trust { padding: 32px 0; }
    .section-header { margin-bottom: 36px; }
    .features { padding: 48px 0; }
    .how-it-works { padding: 48px 0; }
    .dashboard-preview { padding: 48px 0; }
    .benefits { padding: 48px 0; }
    .pricing { padding: 48px 0; }
    .testimonials { padding: 48px 0; }
    .faq { padding: 48px 0; }
    .final-cta { padding: 48px 0; }
    .site-footer { padding: 32px 0 24px; }
}

/* ============================================================
   TRUST
   ============================================================ */
.trust { padding: 48px 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); background: var(--color-card); }
.trust__text { text-align: center; font-size: 13px; font-weight: 500; color: #9ca3af; margin: 0 0 24px; text-transform: uppercase; letter-spacing: 0.06em; }
.trust__logos { display: flex; justify-content: center; gap: 48px; flex-wrap: wrap; align-items: center; }
.trust__logo { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #9ca3af; opacity: 0.6; transition: opacity var(--transition); }
.trust__logo:hover { opacity: 1; }
.trust__logo i, .trust__logo .material-symbols-rounded { font-size: 22px; }

/* ============================================================
   SECTION HEADER
   ============================================================ */
.section-header { text-align: center; margin-bottom: 56px; }
.section-header__label {
    display: inline-block; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--color-primary); margin-bottom: 12px;
}
.section-header__title { font-size: 44px; font-weight: 700; color: var(--color-heading); margin: 0 0 16px; letter-spacing: -0.02em; line-height: 1.15; }
.section-header__subtitle { font-size: 18px; color: var(--color-body); margin: 0 auto; max-width: 560px; line-height: 1.6; }

@media (max-width: 768px) {
    .section-header__title { font-size: 28px; }
    .section-header__subtitle { font-size: 15px; }
}

/* ============================================================
   FEATURES
   ============================================================ */
.features { padding: 96px 0; }
.features__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.feature-card {
    background: var(--color-card); border: 1px solid var(--color-border);
    border-radius: var(--radius); padding: 28px; transition: all var(--transition);
}
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px -12px rgba(0,0,0,0.06); border-color: #d1d5db; }
.feature-card__icon {
    width: 44px; height: 44px; border-radius: var(--radius-sm); display: flex;
    align-items: center; justify-content: center; margin-bottom: 16px;
    background: rgba(16,185,129,0.08); color: var(--color-primary);
}
.feature-card__icon .material-symbols-rounded { font-size: 22px; }
.feature-card__title { font-size: 17px; font-weight: 650; color: var(--color-heading); margin: 0 0 8px; }
.feature-card__desc { font-size: 14px; line-height: 1.6; color: var(--color-body); margin: 0; }

@media (max-width: 768px) {
    .features__grid { grid-template-columns: 1fr; }
}

/* ============================================================
   HOW IT WORKS
   ============================================================ */
.how-it-works { padding: 96px 0; background: var(--color-bg-alt); }
.steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; position: relative; }
.step { text-align: center; padding: 0 32px; position: relative; }
.step__number {
    width: 48px; height: 48px; border-radius: 50%; border: 2px solid var(--color-primary);
    display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
    font-size: 18px; font-weight: 700; color: var(--color-primary); background: var(--color-card);
    position: relative; z-index: 2;
}
.step__connector {
    position: absolute; top: 24px; left: calc(50% + 28px); width: calc(100% - 56px);
    height: 2px; background: var(--color-border); z-index: 1;
}
.step:last-child .step__connector { display: none; }
.step__title { font-size: 18px; font-weight: 650; color: var(--color-heading); margin: 0 0 8px; }
.step__desc { font-size: 14px; line-height: 1.6; color: var(--color-body); margin: 0; }

@media (max-width: 768px) {
    .steps { grid-template-columns: 1fr; gap: 40px; }
    .step__connector { display: none; }
}

/* ============================================================
   DASHBOARD PREVIEW
   ============================================================ */
.dashboard-preview { padding: 96px 0; }
.dashboard-preview__showcase { position: relative; }
.dashboard-preview__glow {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
    width: 600px; height: 400px; background: radial-gradient(ellipse, rgba(16,185,129,0.06), transparent 70%);
    pointer-events: none; z-index: 0;
}
.dashboard-preview__mockup { position: relative; z-index: 1; background: var(--color-card); border: 1px solid var(--color-border); border-radius: var(--radius); overflow: hidden; box-shadow: 0 25px 80px -20px rgba(0,0,0,0.08); }
.dashboard-preview__body { padding: 20px; }
.dp__row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 16px; }
.dp__card { display: flex; align-items: center; gap: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); background: #fafbfc; }
.dp__card--success .dp__card-icon { color: var(--color-success); }
.dp__card-icon { font-size: 22px; color: var(--color-primary); }
.dp__card-value { font-size: 20px; font-weight: 700; color: var(--color-heading); }
.dp__card-label { font-size: 11px; color: var(--color-body); }
.dp__chart-placeholder { background: #fafbfc; border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 16px; }
.dp__chart-title { font-size: 11px; font-weight: 600; color: var(--color-body); margin-bottom: 8px; }
.dp__chart-svg { width: 100%; height: 80px; }

@media (max-width: 768px) {
    .dp__row { grid-template-columns: repeat(2, 1fr); }
}

/* ============================================================
   BENEFITS
   ============================================================ */
.benefits { padding: 96px 0; background: var(--color-bg-alt); }
.benefits__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
.benefits__visual { display: flex; align-items: center; justify-content: center; }
.benefits__illustration { padding: 48px; background: var(--color-card); border: 1px solid var(--color-border); border-radius: var(--radius); }
.benefits__server-stack { display: flex; flex-direction: column; gap: 12px; }
.benefits__server {
    display: flex; align-items: center; gap: 12px; padding: 16px 24px;
    background: #f9fafb; border: 1px solid var(--color-border); border-radius: var(--radius-sm);
    width: 280px;
}
.benefits__server-led { width: 8px; height: 8px; border-radius: 50%; background: var(--color-success); box-shadow: 0 0 8px rgba(34,197,94,0.4); }
.benefits__server-bars { display: flex; gap: 4px; flex: 1; }
.benefits__server-bars span { height: 6px; border-radius: 3px; background: var(--color-border); flex: 1; }
.benefits__server-bars span:first-child { background: var(--color-primary); flex: 0.6; }
.benefits__title { font-size: 36px; font-weight: 700; color: var(--color-heading); margin: 8px 0 12px; letter-spacing: -0.02em; line-height: 1.2; }
.benefits__desc { font-size: 16px; line-height: 1.7; color: var(--color-body); margin: 0 0 24px; }
.benefits__list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.benefits__list li { display: flex; align-items: center; gap: 10px; font-size: 15px; color: var(--color-heading); }
.benefits__list-icon { color: var(--color-primary); font-weight: 700; font-size: 14px; }

@media (max-width: 768px) {
    .benefits__grid { grid-template-columns: 1fr; gap: 40px; }
    .benefits__visual { order: 2; }
    .benefits__title { font-size: 28px; }
}

/* ============================================================
   PRICING
   ============================================================ */
.pricing { padding: 96px 0; background: var(--color-bg); }
.pricing-switcher-container {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
}
.pricing-switcher {
    display: inline-flex;
    background: var(--color-bg-alt);
    padding: 4px;
    border-radius: 9999px;
    border: 1px solid var(--color-border);
}
.pricing-switcher__btn {
    font-family: var(--font);
    font-size: 13px;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 9999px;
    border: none;
    background: transparent;
    color: var(--color-body);
    cursor: pointer;
    transition: all var(--transition);
}
.pricing-switcher__btn:hover {
    color: var(--color-heading);
}
.pricing-switcher__btn--active {
    background: var(--color-card);
    color: var(--color-primary);
    box-shadow: 0 4px 10px -2px rgba(0,0,0,0.05);
}
.pricing__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: stretch; }
.pricing-card {
    background: var(--color-card); border: 1.5px solid var(--color-border);
    border-radius: var(--radius); padding: 32px 28px; position: relative;
    display: flex; flex-direction: column; transition: all var(--transition);
}
.pricing-card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px -12px rgba(0,0,0,0.06); }
.pricing-card--popular { border-color: var(--color-primary); box-shadow: 0 0 0 1px var(--color-primary), 0 20px 60px -15px rgba(16,185,129,0.12); transform: scale(1.03); }
.pricing-card--popular:hover { transform: scale(1.03) translateY(-4px); }
.pricing-card__badge {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: var(--color-primary); color: #fff;
    font-size: 11px; font-weight: 700; padding: 4px 16px; border-radius: 100px;
    text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap;
}
.pricing-card__name { font-size: 16px; font-weight: 600; color: var(--color-body); text-transform: uppercase; letter-spacing: 0.06em; margin: 0 0 8px; }
.pricing-card__price { margin-bottom: 24px; }
.pricing-card__amount { font-size: 44px; font-weight: 800; color: var(--color-heading); letter-spacing: -0.03em; }
.pricing-card__period { font-size: 15px; color: var(--color-body); margin-left: 4px; }
.pricing-card__features { list-style: none; padding: 0; margin: 0 0 28px; flex: 1; display: flex; flex-direction: column; gap: 10px; }
.pricing-card__features li { font-size: 14px; color: var(--color-body); display: flex; align-items: center; gap: 10px; }
.pricing-card__check { color: var(--color-primary); font-weight: 700; font-size: 13px; flex-shrink: 0; }

@media (max-width: 768px) {
    .pricing__grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
    .pricing-card--popular { transform: none; }
    .pricing-card--popular:hover { transform: translateY(-4px); }
}

.pricing-custom-callout {
    margin-top: 48px;
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: var(--radius);
    padding: 36px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.03);
}
.pricing-custom-callout__content {
    max-width: 680px;
}
.pricing-custom-callout__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-primary);
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 4px 12px;
    border-radius: 9999px;
    margin-bottom: 12px;
}
.pricing-custom-callout__title {
    font-size: 22px;
    font-weight: 800;
    color: var(--color-heading);
    letter-spacing: -0.02em;
    margin: 0 0 8px;
}
.pricing-custom-callout__desc {
    font-size: 14px;
    line-height: 1.6;
    color: var(--color-body);
    margin: 0;
}
.pricing-custom-callout__action {
    flex-shrink: 0;
}
@media (max-width: 768px) {
    .pricing-custom-callout {
        flex-direction: column;
        align-items: flex-start;
        padding: 24px;
        gap: 20px;
    }
    .pricing-custom-callout__action {
        width: 100%;
    }
    .pricing-custom-callout__action .btn {
        width: 100%;
        justify-content: center;
    }
}

/* ============================================================
   TESTIMONIALS
   ============================================================ */
.testimonials { padding: 96px 0; background: var(--color-bg-alt); }
.testimonials__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.testimonial-card {
    background: var(--color-card); border: 1px solid var(--color-border);
    border-radius: var(--radius); padding: 28px; transition: all var(--transition);
}
.testimonial-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px -8px rgba(0,0,0,0.05); }
.testimonial-card__stars { color: #f59e0b; font-size: 14px; margin-bottom: 14px; letter-spacing: 2px; }
.testimonial-card__quote { font-size: 14px; line-height: 1.7; color: var(--color-heading); margin: 0 0 20px; font-style: italic; }
.testimonial-card__author { display: flex; align-items: center; gap: 12px; }
.testimonial-card__avatar {
    width: 36px; height: 36px; border-radius: 50%; background: var(--color-primary);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px;
}
.testimonial-card__name { font-size: 13px; font-weight: 600; color: var(--color-heading); }
.testimonial-card__role { font-size: 12px; color: var(--color-body); }

@media (max-width: 768px) {
    .testimonials__grid { grid-template-columns: 1fr; }
}

/* ============================================================
   FAQ
   ============================================================ */
.faq { padding: 96px 0; background: var(--color-bg); }
.faq__list { max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; }
.faq-item {
    background: var(--color-card); border: 1px solid var(--color-border);
    border-radius: var(--radius-sm); overflow: hidden; cursor: pointer;
    transition: all var(--transition);
}
.faq-item:hover { border-color: #d1d5db; }
.faq-item--open { border-color: var(--color-primary); box-shadow: 0 0 0 1px rgba(16,185,129,0.15); }
.faq-item__question {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px; font-size: 15px; font-weight: 600; color: var(--color-heading);
}
.faq-item__toggle { color: var(--color-body); font-size: 20px; transition: transform var(--transition); }
.faq-item__answer { padding: 0 20px 18px; }
.faq-item__answer p { font-size: 14px; line-height: 1.7; color: var(--color-body); margin: 0; }

/* ============================================================
   FINAL CTA
   ============================================================ */
.final-cta { padding: 96px 0; background: var(--color-bg-alt); }
.final-cta__card {
    text-align: center; padding: 80px 40px; border-radius: var(--radius);
    background: radial-gradient(ellipse at center, rgba(16,185,129,0.06), transparent 60%), var(--color-card);
    border: 1px solid var(--color-border);
}
.final-cta__title { font-size: 40px; font-weight: 700; color: var(--color-heading); margin: 0 0 16px; letter-spacing: -0.02em; line-height: 1.2; }
.final-cta__subtitle { font-size: 18px; color: var(--color-body); margin: 0 0 32px; }

@media (max-width: 768px) {
    .final-cta__title { font-size: 28px; }
    .final-cta__card { padding: 48px 24px; }
}

/* ============================================================
   FOOTER
   ============================================================ */
.site-footer { padding: 64px 0 32px; border-top: 1px solid var(--color-border); background: var(--color-card); }
.site-footer__grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }
.site-footer__logo { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.site-footer__tagline { font-size: 14px; color: var(--color-body); line-height: 1.6; margin: 0; max-width: 260px; }
.site-footer__heading { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-heading); margin: 0 0 16px; }
.site-footer__col { display: flex; flex-direction: column; gap: 10px; }
.site-footer__link { font-size: 14px; color: var(--color-body); text-decoration: none; transition: color var(--transition); }
.site-footer__link:hover { color: var(--color-heading); }
.site-footer__bottom { padding-top: 24px; border-top: 1px solid var(--color-border); text-align: center; }
.site-footer__bottom p { font-size: 13px; color: #9ca3af; margin: 0; }

@media (max-width: 768px) {
    .site-footer__grid { grid-template-columns: 1fr 1fr; gap: 32px; }
}
</style>
