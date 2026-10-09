<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AnimatedMetric from '@/Components/AnimatedMetric.vue';
import {
    Activity,
    ArrowDownRight,
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronDown,
    CircleHelp,
    Clock3,
    Cloud,
    Code2,
    Database,
    Github,
    HardDrive,
    Layers,
    Mail,
    Menu,
    Moon,
    Plus,
    Server,
    ShieldCheck,
    Sun,
    Terminal,
    Workflow,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    managedHostingPlans: {
        type: Array,
        default: () => [],
    },
    selfHostedPlans: {
        type: Array,
        default: () => [],
    },
    testimonials: {
        type: Array,
        default: () => [],
    },
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const contactEmail = computed(() => {
    return page.props.siteSettings?.company_email || 'hello@roook.host';
});

const contactPhone = computed(() => {
    return page.props.siteSettings?.company_phone || '+91 8849259933';
});

const companyName = computed(() => {
    return page.props.siteSettings?.company_name || 'Roook Hosting';
});

const annual = ref(false);
const selectedCurrency = ref('USD');
const openFaq = ref(0);
const lightTheme = ref(false);
const menuOpen = ref(false);
const motionReady = ref(false);
const productsOpen = ref(false);
const productsDropdownRef = ref(null);

const features = [
    {
        icon: Activity,
        tag: 'OBSERVABILITY',
        title: '24/7 monitoring',
        copy: 'We watch uptime, capacity, and application health around the clock, with a human ready to investigate alerts.',
    },
    {
        icon: Database,
        tag: 'RECOVERY',
        title: 'Automated backups',
        copy: 'Daily snapshots, clear retention windows, and a documented restore path for when you need one.',
    },
    {
        icon: ShieldCheck,
        tag: 'EDGE SECURITY',
        title: 'Security hardening & WAF',
        copy: 'Hardened server defaults, managed firewall rules, and web application firewall protection.',
    },
    {
        icon: Cloud,
        tag: 'NETWORK',
        title: 'SSL & DNS',
        copy: 'Certificate renewals and DNS changes are handled carefully, with a clear record of what changed.',
    },
    {
        icon: Workflow,
        tag: 'PERFORMANCE',
        title: 'Performance tuning & CDN',
        copy: 'We tune caching and delivery around your workload, with CDN configuration where it helps.',
    },
    {
        icon: Server,
        tag: 'LIFECYCLE',
        title: 'Updates & patching',
        copy: 'Operating-system and infrastructure updates are planned, applied, and monitored by your team.',
    },
    {
        icon: Code2,
        tag: 'DEPLOYMENT',
        title: 'Staging environments',
        copy: 'Test changes in an isolated environment before they reach production.',
    },
    {
        icon: CircleHelp,
        tag: 'HUMAN SUPPORT',
        title: 'Expert support',
        copy: 'Talk directly with an infrastructure engineer who understands your setup and owns the next step.',
    },
];

const stacks = [
    ['Laravel', 'PHP'],
    ['WordPress', 'CMS'],
    ['Node.js', 'JS'],
    ['Shopify', 'Commerce'],
    ['PHP', 'Runtime'],
    ['Docker', 'Containers'],
    ['PostgreSQL', 'Database'],
    ['Redis', 'Cache'],
    ['Nginx', 'Web'],
    ['GitHub Actions', 'CI/CD'],
];

const pricing = computed(() => {
    const isINR = selectedCurrency.value === 'INR';
    const symbol = isINR ? '₹' : '$';

    if (props.managedHostingPlans && props.managedHostingPlans.length > 0) {
        return props.managedHostingPlans.map((plan) => {
            const baseYearly = isINR ? Number(plan.price_inr) : Number(plan.price_usd);
            const baseMonthly = isINR 
                ? (plan.monthly_price_inr ? Number(plan.monthly_price_inr) : (baseYearly > 0 ? Math.round(baseYearly / 10) : null))
                : (plan.monthly_price_usd ? Number(plan.monthly_price_usd) : (baseYearly > 0 ? Math.round(baseYearly / 10) : null));

            const isCustom = !baseYearly || baseYearly <= 0;
            let displayAmount = null;
            let caption = '';

            if (!isCustom) {
                if (annual.value) {
                    // Yearly billed: show monthly equivalent
                    displayAmount = Math.round(baseYearly / 12);
                    caption = `Billed annually · ${symbol}${isINR ? baseYearly.toLocaleString('en-IN') : baseYearly} / year`;
                } else {
                    // Monthly billed
                    displayAmount = baseMonthly;
                    caption = 'Billed monthly · cancel anytime';
                }
            }

            const items = Array.isArray(plan.features) && plan.features.length > 0
                ? plan.features
                : [
                    'Fully managed cloud environment',
                    '24/7 uptime monitoring & alerts',
                    'Daily automated offsite backups',
                    'Free auto-renewing SSL & firewall',
                ];

            return {
                id: plan.id,
                name: plan.name,
                description: plan.description || 'High-performance cloud servers fully maintained, secured, and backed up by our engineers.',
                symbol: symbol,
                displayAmount: displayAmount,
                isCustom: isCustom,
                unit: isCustom ? '' : '/ month',
                caption: isCustom ? 'Scoped to your setup' : caption,
                items: items,
                action: plan.cta_text || (isCustom ? 'Talk to an engineer' : (plan.is_popular ? 'Start free migration' : `Deploy ${plan.name}`)),
                featured: Boolean(plan.is_popular),
                rawPlan: plan,
            };
        });
    }

    return [
        {
            name: 'Starter Cloud',
            description: 'For a small app or a site ready to leave shared hosting.',
            symbol: symbol,
            displayAmount: isINR ? (annual.value ? 317 : 390) : (annual.value ? 4 : 5),
            isCustom: false,
            unit: '/ month',
            caption: annual.value ? `Billed annually · ${symbol}${isINR ? '3,800' : '49'} / year` : 'Billed monthly · cancel anytime',
            items: [
                'One production application',
                'Managed updates & security',
                'Daily backups · 14-day retention',
                'Email support',
            ],
            action: 'Deploy Starter Cloud',
            featured: false,
        },
        {
            name: 'Business Cloud',
            description: 'For growing teams that need room and a steady hand.',
            symbol: symbol,
            displayAmount: isINR ? (annual.value ? 665 : 790) : (annual.value ? 8 : 10),
            isCustom: false,
            unit: '/ month',
            caption: annual.value ? `Billed annually · ${symbol}${isINR ? '7,990' : '99'} / year` : 'Billed monthly · cancel anytime',
            items: [
                'Up to 3 production applications',
                'Everything in Starter',
                'Daily backups · 30-day retention',
                'Priority engineer response',
                'Staging environment included',
            ],
            action: 'Deploy Business Cloud',
            featured: true,
        },
        {
            name: 'Enterprise Cloud',
            description: 'For complex systems, compliance needs, or many properties.',
            symbol: symbol,
            displayAmount: isINR ? (annual.value ? 1415 : 1690) : (annual.value ? 16 : 20),
            isCustom: false,
            unit: '/ month',
            caption: annual.value ? `Billed annually · ${symbol}${isINR ? '16,990' : '199'} / year` : 'Billed monthly · cancel anytime',
            items: [
                'Multi-server fleet orchestration',
                'Dedicated engineer channel',
                'Custom backup retention',
                'SLA & compliance support',
            ],
            action: 'Deploy Enterprise Cloud',
        },
    ];
});

const customerStories = computed(() => {
    if (props.testimonials && props.testimonials.length > 0) {
        return props.testimonials.map((t) => {
            const initials = (t.name || 'CU')
                .trim()
                .split(/\s+/)
                .map((w) => w[0])
                .join('')
                .substring(0, 2)
                .toUpperCase();

            return {
                name: t.name,
                role: t.role,
                company: t.company,
                quote: t.quote,
                initials: initials,
            };
        });
    }

    return [
        {
            name: 'Alex Morgan',
            role: 'Technical Director',
            company: 'Northline Studio',
            quote: 'We used to lose half a day every time a server needed attention. Now we have a person who knows our stack—and the rest of us can get back to client work.',
            initials: 'AM',
        },
        {
            name: 'Jamie Lee',
            role: 'Founder',
            company: 'Fieldnote Commerce',
            quote: 'The migration was planned, tested, and refreshingly uneventful. We knew exactly who to ask at every step.',
            initials: 'JL',
        },
        {
            name: 'Ravi Kapoor',
            role: 'Engineering Lead',
            company: 'Common Ground',
            quote: 'I don’t need another dashboard. I need someone to notice when something’s off and help me fix it. That’s been the difference.',
            initials: 'RK',
        },
    ];
});

const faqs = [
    {
        question: 'Will moving to Roook cause downtime?',
        answer: 'We plan the move around your application and its traffic patterns. Most migrations are staged, tested, and switched over during a low-traffic window; we agree on the cutover plan with you before anything changes. The exact downtime risk depends on your stack and DNS setup.',
    },
    {
        question: 'How do backups and restores work?',
        answer: 'Backups run automatically on a daily schedule, with retention based on your plan. If you need a restore, contact your Roook engineer and we’ll confirm the recovery point and walk through the restore before applying it. We’ll document what is covered during onboarding.',
    },
    {
        question: 'How quickly will someone respond?',
        answer: 'You reach an infrastructure engineer directly. Business customers receive priority handling; response windows and support coverage are confirmed in the service terms for your plan. We don’t promise an instant fix for every issue, but you’ll know who owns the next step.',
    },
    {
        question: 'Can the environment scale as we grow?',
        answer: 'Yes. We review resource use and traffic with you, then propose a right-sized change before capacity becomes a surprise. Some workloads need application changes as well as more compute, so we’ll be clear about the trade-offs and costs first.',
    },
    {
        question: 'Can we cancel or move away later?',
        answer: 'Your code and data stay yours. If you decide to leave, we’ll provide an export and a reasonable handover plan. Plan terms, notice periods, and any migration assistance are agreed up front—there’s no lock-in hidden in the infrastructure.',
    },
    {
        question: 'What does Roook do about security?',
        answer: 'Roook handles routine operating-system updates, access controls, firewall configuration, and certificate maintenance as part of managed hosting. Security is shared work: your application code, credentials, and third-party services still matter. We’ll be explicit about the boundary during setup.',
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

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        return;
    }

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

    const handleOutsideClick = (e) => {
        if (productsDropdownRef.value && !productsDropdownRef.value.contains(e.target)) {
            productsOpen.value = false;
        }
    };
    document.addEventListener('click', handleOutsideClick);
    window._cleanupProductsDropdown = () => document.removeEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
    if (revealObserver) {
        revealObserver.disconnect();
    }
    if (window._cleanupProductsDropdown) {
        window._cleanupProductsDropdown();
    }
});

const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
};

const closeMenu = () => {
    menuOpen.value = false;
    productsOpen.value = false;
};

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Roook Hosting — Managed Cloud Hosting" />

    <div
        class="rook-site"
        :data-theme="lightTheme ? 'light' : 'dark'"
        :data-motion-ready="motionReady ? 'true' : undefined"
    >
        <!-- Sticky Site Header -->
        <header class="site-header">
            <div class="shell header-inner">
                <a class="brand" href="#top" aria-label="Roook home" data-testid="link-home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>roook</span>
                </a>

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
                            <a href="#features" @click="closeMenu" class="dropdown-item">
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
                            </a>

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

                            <a href="#stack" @click="closeMenu" class="dropdown-item">
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

                    <a href="#features" @click="closeMenu" data-testid="link-features">Features</a>
                    <a href="#pricing" @click="closeMenu" data-testid="link-pricing">Pricing</a>
                    <a href="#stack" @click="closeMenu" data-testid="link-stack">Stack</a>
                    <a href="#docs" @click="closeMenu" data-testid="link-docs">Docs</a>
                    <a href="#status" @click="closeMenu" data-testid="link-status">Status</a>
                </nav>

                <div class="nav-actions">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="button button-outline button-small">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            aria-label="Login to account"
                            data-testid="link-login-contact"
                        >
                            Login
                        </Link>
                    </template>

                    <button
                        class="theme-toggle"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        :aria-pressed="lightTheme"
                        data-testid="button-theme-toggle"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>

                    <a class="button button-primary button-small" href="#pricing" data-testid="link-get-started">
                        Get started <ArrowUpRight :size="13" aria-hidden="true" />
                    </a>

                    <button
                        class="menu-toggle"
                        type="button"
                        :aria-label="menuOpen ? 'Close navigation menu' : 'Open navigation menu'"
                        :aria-expanded="menuOpen"
                        @click="menuOpen = !menuOpen"
                        data-testid="button-mobile-menu"
                    >
                        <X v-if="menuOpen" :size="17" aria-hidden="true" />
                        <Menu v-else :size="17" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <main id="top">
            <!-- Hero Section -->
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero-grid-overlay" aria-hidden="true" />
                <div class="shell hero-layout">
                    <div class="hero-copy">
                        <div class="eyebrow hero-kicker">Managed hosting, minus the noise</div>
                        <h1 id="hero-title">
                            We handle the servers.<span class="highlight">You ship the code.</span>
                        </h1>
                        <p class="hero-lede">
                            A calm, capable infrastructure team for the work you want to be doing. We take care of servers, security, backups, and the messy parts of DevOps.
                        </p>
                        <div class="hero-actions">
                            <a
                                class="button button-primary"
                                :href="`mailto:${contactEmail}?subject=Start%20a%20Roook%20migration`"
                                data-testid="link-contact"
                            >
                                Start free migration <ArrowRight :size="15" aria-hidden="true" />
                            </a>
                            <a class="button button-outline" href="#pricing" data-testid="link-view-pricing">
                                View pricing <ArrowDownRight :size="15" aria-hidden="true" />
                            </a>
                        </div>
                        <div class="hero-note">
                            <Check :size="14" aria-hidden="true" /> Start with a no-pressure infrastructure review
                        </div>
                    </div>

                    <div class="terminal-wrap" aria-label="Illustration of a Roook migration in progress">
                        <div class="terminal">
                            <div class="terminal-bar">
                                <div class="terminal-dots" aria-hidden="true"><i /><i /><i /></div>
                                <span>migration / production</span>
                                <span class="terminal-live">in progress</span>
                            </div>
                            <div class="terminal-content" aria-live="polite">
                                <p class="terminal-line">
                                    <span class="line-index">01</span>
                                    <span class="terminal-command">
                                        <span class="line-prompt">&gt;</span> roook migrate --from app-prod-01
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">02</span>
                                    <span>
                                        <span class="line-ok">✓</span> Auditing runtime &amp; dependencies <span class="line-quiet">done</span>
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">03</span>
                                    <span>
                                        <span class="line-ok">✓</span> Provisioning isolated environment <span class="line-quiet">done</span>
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">04</span>
                                    <span>
                                        <span class="line-ok">✓</span> Syncing database &amp; uploaded assets <span class="line-quiet">done</span>
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">05</span>
                                    <span>
                                        <span class="line-ok">✓</span> TLS, backups, and monitoring enabled
                                    </span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">06</span>
                                    <span>
                                        <span class="line-prompt">&gt;</span> Ready for your review<span class="cursor" aria-hidden="true" />
                                    </span>
                                </p>
                                <div class="terminal-divider" />
                                <div class="terminal-footer">
                                    <span>roook / ops / migration-042</span>
                                    <strong>Engineer guided</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Trust Metrics Strip -->
            <section class="trust-strip" aria-label="Illustrative Roook service metrics">
                <div class="shell trust-layout">
                    <div class="trust-intro">Illustrative sample metrics<br />for this product concept.</div>
                    <div class="trust-item">
                        <span class="trust-number">
                            <AnimatedMetric :end="99.99" :decimals="2" suffix="%" />
                        </span>
                        <span class="trust-caption">target uptime<br />replace with verified data</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">
                            <AnimatedMetric :end="200" prefix="&lt; " suffix="ms" />
                        </span>
                        <span class="trust-caption">average response time<br />illustrative target</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">
                            <AnimatedMetric :end="1200" suffix="+" />
                        </span>
                        <span class="trust-caption">sites managed<br />sample figure</span>
                    </div>
                    <p class="trust-note">Replace all sample figures with verified operating data before publishing.</p>
                </div>
            </section>

            <!-- Logo Cloud -->
            <div class="shell logo-cloud" aria-label="Example teams Roook is built to support">
                <span class="logo-cloud-label">Made for teams like</span>
                <span class="logo-word">northstar</span>
                <span class="logo-word">Fieldnote</span>
                <span class="logo-word">KIN / STUDIO</span>
                <span class="logo-word">orbital</span>
                <span class="logo-word">COMMON GROUND</span>
            </div>

            <!-- Features Section -->
            <section class="section" id="features" aria-labelledby="features-title" data-reveal>
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">The whole stack, looked after</div>
                            <h2 class="section-heading" id="features-title">Your infrastructure shouldn’t be a second job.</h2>
                            <p class="section-intro">
                                Roook brings the everyday operational work into one steady relationship—so your team can stay focused on the product.
                            </p>
                        </div>
                        <span class="section-index">01 / WHAT WE HANDLE</span>
                    </div>
                    <div class="feature-grid">
                        <article
                            v-for="(feature, index) in features"
                            :key="feature.title"
                            class="feature-card"
                            :data-testid="`card-feature-${index + 1}`"
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

            <!-- Process Section -->
            <section class="section process-section" aria-labelledby="process-title" data-reveal>
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">A careful handover</div>
                            <h2 class="section-heading" id="process-title">Three steps. Then it’s off your plate.</h2>
                            <p class="section-intro">No big-bang rewrite. We learn what you have, agree on a plan, and look after it together.</p>
                        </div>
                        <span class="section-index">02 / HOW IT WORKS</span>
                    </div>
                    <div class="process-grid">
                        <article class="process-step">
                            <div class="step-top">
                                <span class="step-number">STEP 01</span>
                                <Code2 class="step-symbol" :size="18" aria-hidden="true" />
                            </div>
                            <h3>Connect</h3>
                            <p>We get to know your app, your deployment flow, and what a good migration looks like for your team.</p>
                            <span class="step-connector" aria-hidden="true" />
                        </article>
                        <article class="process-step">
                            <div class="step-top">
                                <span class="step-number">STEP 02</span>
                                <Workflow class="step-symbol" :size="18" aria-hidden="true" />
                            </div>
                            <h3>We migrate</h3>
                            <p>Your engineer builds the new environment, tests the move, and coordinates a cutover on your terms.</p>
                            <span class="step-connector" aria-hidden="true" />
                        </article>
                        <article class="process-step">
                            <div class="step-top">
                                <span class="step-number">STEP 03</span>
                                <Check class="step-symbol" :size="18" aria-hidden="true" />
                            </div>
                            <h3>You relax</h3>
                            <p>We keep watch, handle the routine work, and stay close when the next change is more than routine.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Tech Stack Section -->
            <section class="section" id="stack" aria-labelledby="stack-title" data-reveal>
                <div class="shell stack-layout">
                    <div>
                        <div class="eyebrow">Works with your stack</div>
                        <h2 class="section-heading" id="stack-title">Keep the tools your team already knows.</h2>
                        <p class="section-intro">We manage the infrastructure around your application—not a platform that asks you to start over.</p>
                    </div>
                    <div class="stack-content">
                        <div class="stack-chips">
                            <span
                                v-for="[name, category] in stacks"
                                :key="name"
                                class="stack-chip"
                            >
                                {{ name }}<span>{{ category }}</span>
                            </span>
                        </div>
                        <div class="stack-footnote">
                            <CircleHelp :size="15" aria-hidden="true" />
                            <span>Have a less common setup? Send us the architecture. We’ll tell you plainly whether we’re a fit.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Status Section -->
            <section class="section status-section" id="status" aria-labelledby="status-title" data-reveal>
                <div class="shell status-layout">
                    <div class="status-copy">
                        <div class="eyebrow">The boring bits, done well</div>
                        <h2 class="section-heading" id="status-title">Reliable is a practice, not a headline.</h2>
                        <p class="section-intro">We put the useful signals in view, keep operational targets explicit, and tell you what happened when something needs attention.</p>
                        <div class="metric-cards">
                            <div class="metric-card">
                                <span class="metric-value">
                                    <AnimatedMetric :end="99.99" :decimals="2" suffix="%" />
                                </span>
                                <span class="metric-label">UPTIME TARGET*</span>
                            </div>
                            <div class="metric-card">
                                <span class="metric-value">
                                    <AnimatedMetric :end="200" prefix="&lt; " suffix="ms" />
                                </span>
                                <span class="metric-label">TTFB TARGET*</span>
                            </div>
                            <div class="metric-card">
                                <span class="metric-value">Daily</span>
                                <span class="metric-label">AUTOMATED BACKUPS</span>
                            </div>
                            <div class="metric-card">
                                <span class="metric-value">30 days</span>
                                <span class="metric-label">BUSINESS RETENTION</span>
                            </div>
                        </div>
                    </div>
                    <div class="availability-panel">
                        <div class="availability-head">
                            <h3>Availability window</h3>
                            <span class="availability-badge">Sample view</span>
                        </div>
                        <p class="availability-sub">Illustrative 30-day service status</p>
                        <div class="uptime-bars" role="img" aria-label="Illustrative status history: 30 days shown as available">
                            <span v-for="index in 30" :key="index" class="uptime-bar" />
                        </div>
                        <div class="uptime-legend">
                            <span>30 days ago</span>
                            <span>Today</span>
                        </div>
                        <p class="status-sample-note">*Operational goals are design targets, not historical performance guarantees. Actual service terms depend on plan and workload.</p>
                        <a class="status-link" href="#contact">
                            Ask us about service terms <ArrowUpRight :size="13" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section class="section" id="pricing" aria-labelledby="pricing-title" data-reveal>
                <div class="shell">
                    <div class="section-top pricing-top">
                        <div>
                            <div class="eyebrow">Straightforward by design</div>
                            <h2 class="section-heading" id="pricing-title">One less moving part in your budget.</h2>
                            <p class="section-intro">Choose the level of care that fits today. We’ll talk through the infrastructure and quote before any work begins.</p>
                        </div>
                        <div class="billing-control flex-wrap gap-2" role="group" aria-label="Billing options">
                            <!-- Interval Toggle -->
                            <div class="inline-flex rounded-lg border border-[var(--edge)] p-0.5 bg-[var(--surface-deep)]">
                                <button
                                    type="button"
                                    @click="annual = false"
                                    :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': !annual }"
                                    class="px-2.5 py-1 text-xs rounded transition"
                                    data-testid="button-monthly"
                                >
                                    Monthly
                                </button>
                                <button
                                    type="button"
                                    @click="annual = true"
                                    :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': annual }"
                                    class="px-2.5 py-1 text-xs rounded transition"
                                    data-testid="button-yearly"
                                >
                                    Yearly <span class="save-label">-20%</span>
                                </button>
                            </div>

                            <!-- Currency Switcher -->
                            <div class="inline-flex rounded-lg border border-[var(--edge)] p-0.5 bg-[var(--surface-deep)]">
                                <button
                                    type="button"
                                    @click="selectedCurrency = 'USD'"
                                    :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': selectedCurrency === 'USD' }"
                                    class="px-2.5 py-1 text-xs rounded transition"
                                >
                                    USD ($)
                                </button>
                                <button
                                    type="button"
                                    @click="selectedCurrency = 'INR'"
                                    :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': selectedCurrency === 'INR' }"
                                    class="px-2.5 py-1 text-xs rounded transition"
                                >
                                    INR (₹)
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="pricing-grid">
                        <article
                            v-for="plan in pricing"
                            :key="plan.name"
                            :class="['price-card', { featured: plan.featured }]"
                            :data-testid="`card-plan-${plan.name.toLowerCase()}`"
                        >
                            <span v-if="plan.featured" class="popular-label">Most chosen</span>
                            <div class="plan-name">{{ plan.name }}</div>
                            <p class="plan-note">{{ plan.description }}</p>
                            <div class="plan-price">
                                <span v-if="plan.isCustom" class="price-amount" style="font-size: 31px;">
                                    Let’s talk
                                </span>
                                <template v-else>
                                    <span class="price-amount">{{ plan.symbol }}{{ plan.displayAmount != null ? Number(plan.displayAmount).toLocaleString(selectedCurrency === 'INR' ? 'en-IN' : 'en-US') : '0' }}</span>
                                    <span class="price-unit">{{ plan.unit }}</span>
                                </template>
                            </div>
                            <div class="billing-caption">
                                {{ plan.caption }}
                            </div>
                            <a
                                v-if="plan.isCustom"
                                :href="`mailto:${contactEmail}?subject=${encodeURIComponent(`Enterprise Hosting Inquiry — ${plan.name}`)}`"
                                :class="['button', plan.featured ? 'button-primary' : 'button-outline', 'plan-cta']"
                            >
                                {{ plan.action }} <ArrowRight :size="14" aria-hidden="true" />
                            </a>
                            <Link
                                v-else
                                :href="route('checkout', { plan: plan.rawPlan?.slug || plan.name.toLowerCase().replace(' cloud', ''), billing: annual ? 'yearly' : 'monthly', currency: selectedCurrency })"
                                :class="['button', plan.featured ? 'button-primary' : 'button-outline', 'plan-cta']"
                            >
                                <span>{{ plan.action }}</span>
                                <ArrowRight :size="14" aria-hidden="true" />
                            </Link>
                            <div class="plan-rule" />
                            <div class="plan-list-label">Included</div>
                            <ul class="plan-list">
                                <li v-for="item in plan.items" :key="item">
                                    <Check :size="14" aria-hidden="true" />{{ item }}
                                </li>
                            </ul>
                        </article>
                    </div>
                    <p class="pricing-footnote">
                        Managed hosting and support scope can be customized directly for your stack.
                    </p>
                </div>
            </section>

            <!-- Customer Stories Section -->
            <section class="section" aria-labelledby="stories-title" data-reveal>
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">Fewer 2 a.m. tabs</div>
                            <h2 class="section-heading" id="stories-title">Good infrastructure fades into the background.</h2>
                        </div>
                        <span class="section-index">03 / CUSTOMER STORIES</span>
                    </div>
                    <div class="quote-grid">
                        <article v-for="(story, idx) in customerStories" :key="idx" class="quote-card">
                            <div class="quote-mark" aria-hidden="true">“</div>
                            <blockquote>“{{ story.quote }}”</blockquote>
                            <div class="quote-person">
                                <span class="avatar-initials">{{ story.initials }}</span>
                                <div>
                                    <div class="person-name">{{ story.name }}</div>
                                    <div class="person-role">{{ story.role }}{{ story.company ? ' · ' + story.company : '' }}</div>
                                </div>
                            </div>
                        </article>
                    </div>
                    <p class="pricing-footnote">Verified reviews and stories from teams powered by our infrastructure.</p>
                </div>
            </section>

            <!-- FAQs & Docs Section -->
            <section class="section" id="docs" aria-labelledby="faq-title" data-reveal>
                <div class="shell faq-layout">
                    <div class="faq-aside">
                        <div class="eyebrow">Good questions, clear answers</div>
                        <h2 class="section-heading" id="faq-title">Before we touch a thing.</h2>
                        <p class="section-intro">We’ll walk through the specifics of your stack before making a plan. Here’s how the basics work.</p>
                        <a class="faq-contact" :href="`mailto:${contactEmail}?subject=Question%20for%20Roook`">
                            <Mail :size="14" aria-hidden="true" /> Ask an engineer
                        </a>
                    </div>
                    <div class="faq-list">
                        <article
                            v-for="(faq, index) in faqs"
                            :key="faq.question"
                            class="faq-item"
                        >
                            <h3 style="margin: 0;">
                                <button
                                    class="faq-question"
                                    :id="`faq-question-${index + 1}`"
                                    type="button"
                                    :aria-expanded="openFaq === index"
                                    :aria-controls="`faq-panel-${index + 1}`"
                                    @click="toggleFaq(index)"
                                    :data-testid="`button-faq-${index + 1}`"
                                >
                                    <span>{{ faq.question }}</span>
                                    <Plus :size="17" aria-hidden="true" />
                                </button>
                            </h3>
                            <div
                                v-if="openFaq === index"
                                class="faq-answer"
                                :id="`faq-panel-${index + 1}`"
                                role="region"
                                :aria-labelledby="`faq-question-${index + 1}`"
                            >
                                {{ faq.answer }}
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="contact-section" id="contact" aria-labelledby="contact-title" data-reveal>
                <div class="shell">
                    <div class="contact-panel">
                        <div class="contact-copy">
                            <div class="eyebrow">Bring us the messy diagram</div>
                            <h2 id="contact-title">Your next deploy can be the easy part.</h2>
                            <p>Tell us what you’re running. An engineer will help you find the sensible next step.</p>
                        </div>
                        <div class="contact-actions">
                            <a
                                class="button button-primary"
                                :href="`mailto:${contactEmail}?subject=Start%20a%20free%20Roook%20migration`"
                                data-testid="link-contact"
                            >
                                Start free migration <ArrowRight :size="15" aria-hidden="true" />
                            </a>
                            <a class="contact-email" :href="`mailto:${contactEmail}`">
                                or email {{ contactEmail }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Site Footer -->
        <footer class="site-footer">
            <div class="shell">
                <div class="footer-top">
                    <div class="footer-brand-col">
                        <a class="brand" href="#top" aria-label="Roook home">
                            <span class="brand-mark" aria-hidden="true">r</span>
                            <span>roook</span>
                        </a>
                        <p class="footer-brand-copy">
                            We handle the servers. You ship the code. Managed hosting with a human on the other end.
                        </p>
                    </div>
                    <div class="footer-group">
                        <h3>Explore</h3>
                        <div class="footer-links">
                            <a href="#features">Features</a>
                            <a href="#pricing">Pricing</a>
                            <a href="#stack">Supported stack</a>
                            <a href="#docs">FAQs &amp; docs</a>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Get help</h3>
                        <div class="footer-links">
                            <a :href="`mailto:${contactEmail}?subject=Existing%20customer%20support`">Client access / login help</a>
                            <a href="#status">Service status</a>
                            <a :href="`mailto:${contactEmail}`">Contact an engineer</a>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Elsewhere</h3>
                        <div class="footer-links">
                            <a href="https://github.com" target="_blank" rel="noreferrer">
                                GitHub <ArrowUpRight :size="11" aria-hidden="true" />
                            </a>
                            <a href="https://www.linkedin.com" target="_blank" rel="noreferrer">
                                LinkedIn <ArrowUpRight :size="11" aria-hidden="true" />
                            </a>
                            <a :href="`mailto:${contactEmail}`">
                                Email <ArrowUpRight :size="11" aria-hidden="true" />
                            </a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span>© {{ currentYear }} Roook Hosting. Made for the people who build.</span>
                    <a class="footer-status" href="#status">Sample status view</a>
                    <div class="footer-socials">
                        <a href="https://github.com" target="_blank" rel="noreferrer" aria-label="Roook on GitHub">
                            <Github :size="15" aria-hidden="true" />
                        </a>
                        <a :href="`mailto:${contactEmail}`" aria-label="Email Roook">
                            <Mail :size="15" aria-hidden="true" />
                        </a>
                        <a href="https://www.linkedin.com" target="_blank" rel="noreferrer" aria-label="Roook on LinkedIn">
                            <ArrowUpRight :size="15" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
