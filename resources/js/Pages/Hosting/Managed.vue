<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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
    plans: {
        type: Array,
        default: () => [],
    },
    datacenters: {
        type: Array,
        default: () => [],
    },
    canLogin: Boolean,
    canRegister: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const contactEmail = computed(() => {
    return page.props.siteSettings?.company_email || 'billing@roook.cloud';
});

const companyPhone = computed(() => {
    return page.props.siteSettings?.company_phone || '+91 8849259933';
});

const annual = ref(true);
const selectedCurrency = ref('INR');
const openFaq = ref(0);
const lightTheme = ref(false);
const menuOpen = ref(false);
const productsOpen = ref(false);

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

const closeMenu = () => {
    menuOpen.value = false;
    productsOpen.value = false;
};

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const managedPlans = computed(() => {
    const isINR = selectedCurrency.value === 'INR';
    const symbol = isINR ? '₹' : '$';

    if (props.plans && props.plans.length > 0) {
        return props.plans.map((p) => {
            const baseYearly = isINR ? Number(p.price_inr) : Number(p.price_usd);
            const baseMonthly = isINR 
                ? (p.monthly_price_inr ? Number(p.monthly_price_inr) : Math.round(baseYearly / 10))
                : (p.monthly_price_usd ? Number(p.monthly_price_usd) : Math.round(baseYearly / 10));

            const renewalYearly = isINR ? Number(p.renewal_price_inr || p.price_inr) : Number(p.renewal_price_usd || p.price_usd);
            const renewalMonthly = isINR ? Number(p.renewal_monthly_price_inr || p.monthly_price_inr || Math.round(renewalYearly / 10)) : Number(p.renewal_monthly_price_usd || p.monthly_price_usd || Math.round(renewalYearly / 10));

            const displayAmount = annual.value ? Math.round(baseYearly / 12) : baseMonthly;
            const savings = annual.value ? (renewalYearly - baseYearly) : (renewalMonthly - baseMonthly);
            const discountPercent = savings > 0 ? Math.round((savings / (annual.value ? renewalYearly : renewalMonthly)) * 100) : 0;

            return {
                id: p.id,
                name: p.name,
                slug: p.slug,
                description: p.description,
                symbol,
                displayAmount,
                caption: annual.value ? `Billed annually · ${symbol}${isINR ? baseYearly.toLocaleString('en-IN') : baseYearly} / yr` : 'Billed monthly · cancel anytime',
                renewalText: annual.value ? `Renews at ${symbol}${isINR ? renewalYearly.toLocaleString('en-IN') : renewalYearly}/yr` : `Renews at ${symbol}${isINR ? renewalMonthly.toLocaleString('en-IN') : renewalMonthly}/mo`,
                discountPercent,
                featured: Boolean(p.is_popular),
                items: Array.isArray(p.features) && p.features.length > 0 ? p.features : [
                    'Isolated Docker container environment',
                    '24/7 uptime monitoring & alerts',
                    'Automated offsite snapshots',
                    'Free SSL & custom domain setup',
                ],
            };
        });
    }

    return [
        {
            name: 'Starter Cloud',
            slug: 'starter-cloud',
            description: 'For small production apps or migrating away from slow shared hosts.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 316 : 390) : (annual.value ? 4 : 5),
            caption: annual.value ? (isINR ? 'Billed annually · ₹3,800 / yr' : 'Billed annually · $49 / yr') : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹4,790 / yr' : 'Renews at $59 / yr',
            discountPercent: 20,
            featured: false,
            items: [
                '1 Production application container',
                'Dedicated RAM & CPU quota',
                'Daily automated backups (14 days)',
                'Free auto-renewing SSL certificates',
                '2-Hour provisioning SLA',
            ],
        },
        {
            name: 'Business Cloud',
            slug: 'business-cloud',
            description: 'For fast-growing companies and high-traffic customer portals.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 665 : 790) : (annual.value ? 8 : 10),
            caption: annual.value ? (isINR ? 'Billed annually · ₹7,990 / yr' : 'Billed annually · $99 / yr') : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹9,990 / yr' : 'Renews at $119 / yr',
            discountPercent: 20,
            featured: true,
            items: [
                'Up to 3 production containers',
                'Dedicated Redis caching layer',
                'Daily backups with 30-day retention',
                'Priority engineer on-call SLA',
                'Staging environment included',
            ],
        },
        {
            name: 'Enterprise Cloud',
            slug: 'enterprise-cloud',
            description: 'For mission-critical workloads requiring dedicated infrastructure.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 1415 : 1690) : (annual.value ? 16 : 20),
            caption: annual.value ? (isINR ? 'Billed annually · ₹16,990 / yr' : 'Billed annually · $199 / yr') : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹19,990 / yr' : 'Renews at $249 / yr',
            discountPercent: 15,
            featured: false,
            items: [
                'Multi-container fleet orchestration',
                'Custom offsite snapshot retention',
                'Dedicated technical account engineer',
                '99.98% financial SLA backing',
                'White-glove migration assistance',
            ],
        },
    ];
});

const faqs = [
    {
        question: 'What is Managed Cloud Hosting vs Unmanaged VPS?',
        answer: 'With an unmanaged VPS, you are responsible for Linux security patches, web server configuration (Nginx), database tuning, SSL renewals, and emergency 3 a.m. downtime. With Roook Managed Cloud Hosting, our infrastructure engineers handle the entire stack for you.',
    },
    {
        question: 'Which Datacenter locations are available?',
        answer: 'We currently deploy from two Tier IV facilities: India (Mumbai BOM1) for sub-20ms latency across the Indian subcontinent and APAC, and USA East Coast (Ashburn IAD1) connected to major global transit backbones.',
    },
    {
        question: 'Will moving to Roook cause downtime?',
        answer: 'No. Our engineers coordinate a staged migration. We import your databases and files, test the container on a temporary URL, and switch DNS during your lowest-traffic window with zero downtime.',
    },
    {
        question: 'What is your refund guarantee?',
        answer: 'All managed cloud hosting plans come with an unconditional 7-day money-back guarantee. If you are not fully satisfied, you receive a 100% full refund.',
    },
];

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>Managed Cloud Hosting — Fast, Isolated &amp; Fully Handled | Roook</title>
        <meta name="description" content="Enterprise managed cloud hosting powered by NVMe SSDs, isolated Docker containers, automated daily snapshots, and a 2-hour provisioning SLA. Datacenters in India Mumbai &amp; USA." />
        <meta name="keywords" content="managed cloud hosting, fast nvme hosting, mumbai cloud servers, usa cloud hosting, nimbus control panel, isolated docker hosting" />
        <meta property="og:title" content="Managed Cloud Hosting — Fast, Isolated &amp; Fully Handled | Roook" />
        <meta property="og:description" content="Enterprise managed cloud hosting powered by NVMe SSDs, isolated Docker containers, automated daily snapshots, and a 2-hour provisioning SLA." />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="https://roook.cloud/hosting/managed" />
    </Head>

    <div class="rook-site" :data-theme="lightTheme ? 'light' : 'dark'">
        <!-- Sticky Site Header -->
        <header class="site-header">
            <div class="shell header-inner">
                <Link class="brand" :href="route('home')" aria-label="Roook home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>roook</span>
                </Link>

                <nav :class="['nav-links', { 'is-open': menuOpen }]" aria-label="Main navigation">
                    <div class="products-dropdown-container">
                        <button
                            type="button"
                            @click.stop="productsOpen = !productsOpen"
                            class="products-dropdown-trigger font-bold text-[var(--accent)]"
                        >
                            <span>Products</span>
                            <ChevronDown :size="13" :class="['dropdown-arrow', { 'is-rotated': productsOpen }]" />
                        </button>

                        <div v-show="productsOpen" class="products-dropdown-menu">
                            <Link :href="route('products.hosting.managed')" @click="closeMenu" class="dropdown-item bg-[var(--surface-hi)]">
                                <div class="dropdown-item-icon">
                                    <Cloud :size="16" />
                                </div>
                                <div class="dropdown-item-text">
                                    <div class="dropdown-item-title">
                                        Managed Cloud Hosting
                                        <span class="dropdown-badge">Flagship</span>
                                    </div>
                                    <p class="dropdown-item-desc">Enterprise managed servers, SRE care &amp; 99.98% uptime.</p>
                                </div>
                            </Link>

                            <Link :href="route('products.hosting.shared')" @click="closeMenu" class="dropdown-item">
                                <div class="dropdown-item-icon">
                                    <Server :size="16" />
                                </div>
                                <div class="dropdown-item-text">
                                    <div class="dropdown-item-title">Shared Web Hosting</div>
                                    <p class="dropdown-item-desc">High-speed NVMe storage, free SSL &amp; WordPress ready.</p>
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
                        </div>
                    </div>

                    <Link :href="route('products.hosting.managed')" class="text-[var(--accent)] font-semibold">Managed Cloud</Link>
                    <Link :href="route('products.hosting.shared')">Shared Hosting</Link>
                    <a href="#features">Architecture</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <div class="nav-actions">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="button button-outline button-small">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')">Login</Link>
                    </template>

                    <button class="theme-toggle" type="button" @click="toggleTheme" aria-label="Toggle theme">
                        <Moon v-if="lightTheme" :size="15" />
                        <Sun v-else :size="15" />
                    </button>

                    <a class="button button-primary button-small" href="#pricing">
                        Deploy Now <ArrowUpRight :size="13" />
                    </a>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="hero">
                <div class="hero-grid-overlay" aria-hidden="true" />
                <div class="shell hero-layout">
                    <div class="hero-copy">
                        <div class="eyebrow hero-kicker">Enterprise SRE Managed Cloud</div>
                        <h1>
                            Managed Cloud Hosting.<br />
                            <span class="highlight">Engineered for speed.</span>
                        </h1>
                        <p class="hero-lede">
                            Dedicated cloud environments fully looked after by infrastructure engineers. Ultra-fast NVMe storage, isolated Docker containers, automated daily snapshots, and a guaranteed 2-hour provisioning window.
                        </p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#pricing">
                                Choose Cloud Plan <ArrowRight :size="15" />
                            </a>
                            <Link class="button button-outline" :href="route('checkout', { plan: 'starter-cloud' })">
                                Quick Checkout <ArrowDownRight :size="15" />
                            </Link>
                        </div>
                        <div class="hero-note flex items-center gap-3">
                            <span class="flex items-center gap-1 text-xs text-[var(--text-soft)]">
                                <Check :size="14" class="text-emerald-500" /> 7-Day Money-Back Guarantee
                            </span>
                            <span class="flex items-center gap-1 text-xs text-[var(--text-soft)]">
                                <Check :size="14" class="text-emerald-500" /> Free White-Glove Migration
                            </span>
                        </div>
                    </div>

                    <!-- Datacenter & Node Visual -->
                    <div class="terminal-wrap">
                        <div class="terminal">
                            <div class="terminal-bar">
                                <div class="terminal-dots"><i /><i /><i /></div>
                                <span>roook / cluster / telemetry</span>
                                <span class="terminal-live">BOM1 · IAD1</span>
                            </div>
                            <div class="terminal-content">
                                <p class="terminal-line">
                                    <span class="line-index">01</span>
                                    <span class="terminal-command"><span class="line-prompt">&gt;</span> roook cluster status --global</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">02</span>
                                    <span><span class="line-ok">✓</span> [BOM1] India (Mumbai Tier IV): <strong class="text-emerald-400">99.98% Uptime</strong> · NVMe PCIe 4.0</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">03</span>
                                    <span><span class="line-ok">✓</span> [IAD1] USA (East Coast Tier IV): <strong class="text-emerald-400">99.99% Uptime</strong> · Global Backbone</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">04</span>
                                    <span><span class="line-ok">✓</span> Automated container isolation: <strong class="text-emerald-400">ACTIVE</strong></span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">05</span>
                                    <span><span class="line-ok">✓</span> Daily automated offsite snapshots: <strong class="text-emerald-400">VERIFIED</strong></span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">06</span>
                                    <span><span class="line-prompt">&gt;</span> Provisioning queue response: &lt; 2 Hours SLA</span>
                                </p>
                                <div class="terminal-divider" />
                                <div class="terminal-footer">
                                    <span>24/7 Proactive Monitoring</span>
                                    <strong>Zero Server Overhead</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Datacenters Strip -->
            <section class="trust-strip">
                <div class="shell trust-layout">
                    <div class="trust-intro">Enterprise Datacenters<br />Selected for low latency.</div>
                    <div class="trust-item">
                        <span class="trust-number">🇮🇳 Mumbai</span>
                        <span class="trust-caption">Tier IV Equinix / CtrlS<br />BOM1 &bull; Lowest latency APAC</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">🇺🇸 USA East</span>
                        <span class="trust-caption">Tier IV CoreSite / Ashburn<br />IAD1 &bull; Global internet backbone</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">99.98%</span>
                        <span class="trust-caption">Financially-backed SLA<br />Every minute accounted for</span>
                    </div>
                </div>
            </section>

            <!-- Architecture / Features Section -->
            <section class="section" id="features">
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">Enterprise Architecture</div>
                            <h2 class="section-heading">Why developers trust Roook Managed Cloud</h2>
                            <p class="section-intro">Everything is maintained by experienced engineers so you can ship features without operational dread.</p>
                        </div>
                    </div>

                    <div class="feature-grid">
                        <article class="feature-card">
                            <span class="feature-index">01</span>
                            <div class="feature-icon"><Cloud :size="17" /></div>
                            <span class="feature-tag">CONTAINER ISOLATION</span>
                            <h3>Dedicated Resource Quotas</h3>
                            <p>No noisy neighbors. Your application runs inside an isolated container with dedicated CPU, RAM, and PCIe Gen4 NVMe disk I/O.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">02</span>
                            <div class="feature-icon"><ShieldCheck :size="17" /></div>
                            <span class="feature-tag">SECURITY &amp; WAF</span>
                            <h3>Edge Firewall &amp; Auto SSL</h3>
                            <p>Automated Let's Encrypt TLS certificates, custom HTTP/2 &amp; HTTP/3 configurations, brute-force mitigation, and managed WAF rules.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">03</span>
                            <div class="feature-icon"><Database :size="17" /></div>
                            <span class="feature-tag">DATA SAFETY</span>
                            <h3>Daily Automated Snapshots</h3>
                            <p>Encrypted daily offsite backups with 14 to 30 days retention. Recover any point in time with one message to your engineering team.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">04</span>
                            <div class="feature-icon"><Activity :size="17" /></div>
                            <span class="feature-tag">OBSERVABILITY</span>
                            <h3>2-Hour Provisioning SLA</h3>
                            <p>Every account is verified and provisioned within 2 hours. Our proactive monitoring alerts our team the moment resource spikes occur.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Pricing Plans -->
            <section class="section" id="pricing">
                <div class="shell">
                    <div class="section-top pricing-top">
                        <div>
                            <div class="eyebrow">Predictable Monthly Billing</div>
                            <h2 class="section-heading">Select your managed cloud size</h2>
                            <p class="section-intro">Choose your initial plan. Upgrade or scale anytime directly from your client dashboard.</p>
                        </div>

                        <div class="billing-control flex-wrap gap-2">
                            <div class="inline-flex rounded-lg border border-[var(--edge)] p-0.5 bg-[var(--surface-deep)]">
                                <button type="button" @click="annual = false" :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': !annual }" class="px-2.5 py-1 text-xs rounded transition">
                                    Monthly
                                </button>
                                <button type="button" @click="annual = true" :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': annual }" class="px-2.5 py-1 text-xs rounded transition">
                                    Yearly <span class="save-label">-20%</span>
                                </button>
                            </div>

                            <div class="inline-flex rounded-lg border border-[var(--edge)] p-0.5 bg-[var(--surface-deep)]">
                                <button type="button" @click="selectedCurrency = 'INR'" :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': selectedCurrency === 'INR' }" class="px-2.5 py-1 text-xs rounded transition">
                                    INR (₹)
                                </button>
                                <button type="button" @click="selectedCurrency = 'USD'" :class="{ 'bg-[var(--panel-hi)] text-[var(--accent)] font-bold': selectedCurrency === 'USD' }" class="px-2.5 py-1 text-xs rounded transition">
                                    USD ($)
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-grid">
                        <article v-for="plan in managedPlans" :key="plan.name" :class="['price-card', { featured: plan.featured }]">
                            <span v-if="plan.featured" class="popular-label">Most chosen</span>
                            <div class="plan-name flex items-center justify-between">
                                <span>{{ plan.name }}</span>
                                <span v-if="plan.discountPercent > 0" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                    -{{ plan.discountPercent }}%
                                </span>
                            </div>
                            <p class="plan-note">{{ plan.description }}</p>
                            <div class="plan-price">
                                <span class="price-amount">{{ plan.symbol }}{{ Number(plan.displayAmount).toLocaleString() }}</span>
                                <span class="price-unit">/ month</span>
                            </div>
                            <div class="billing-caption">{{ plan.caption }}</div>
                            <div class="text-[10px] text-[var(--text-muted)] font-mono mt-1">{{ plan.renewalText }}</div>

                            <Link
                                :href="route('checkout', { plan: plan.slug, billing: annual ? 'yearly' : 'monthly', currency: selectedCurrency })"
                                :class="['button', plan.featured ? 'button-primary' : 'button-outline', 'plan-cta']"
                            >
                                <span>Deploy {{ plan.name }}</span>
                                <ArrowRight :size="14" />
                            </Link>

                            <div class="plan-rule" />
                            <div class="plan-list-label">Included Specifications</div>
                            <ul class="plan-list">
                                <li v-for="item in plan.items" :key="item">
                                    <Check :size="14" />{{ item }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <!-- FAQs -->
            <section class="section" id="faq">
                <div class="shell faq-layout">
                    <div class="faq-aside">
                        <div class="eyebrow">Clear Answers</div>
                        <h2 class="section-heading">Frequently Asked Questions</h2>
                        <p class="section-intro">Have questions before choosing your cluster? Our engineers are ready to walk you through your setup.</p>
                        <a class="faq-contact" :href="`mailto:${contactEmail}?subject=Managed%20Hosting%20Question`">
                            <Mail :size="14" /> Contact an engineer
                        </a>
                    </div>
                    <div class="faq-list">
                        <article v-for="(faq, index) in faqs" :key="faq.question" class="faq-item">
                            <h3>
                                <button class="faq-question" type="button" @click="toggleFaq(index)">
                                    <span>{{ faq.question }}</span>
                                    <Plus :size="17" />
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
                        <Link class="brand" :href="route('home')">
                            <span class="brand-mark">r</span>
                            <span>roook</span>
                        </Link>
                        <p class="footer-brand-copy">
                            We handle the servers. You ship the code. Managed cloud hosting backed by 24/7 human engineers.
                        </p>
                    </div>
                    <div class="footer-group">
                        <h3>Hosting Products</h3>
                        <div class="footer-links">
                            <Link :href="route('products.hosting.managed')">Managed Cloud Hosting</Link>
                            <Link :href="route('products.hosting.shared')">Shared Web Hosting</Link>
                            <Link :href="route('products.nimbus')">Nimbus Control Panel</Link>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Compliance &amp; Legal</h3>
                        <div class="footer-links">
                            <Link :href="route('pages.show', 'terms')">Terms of Service</Link>
                            <Link :href="route('pages.show', 'privacy')">Privacy Policy</Link>
                            <Link :href="route('pages.show', 'refund')">Refund Policy</Link>
                            <Link :href="route('pages.show', 'support')">Help Center &amp; SLA</Link>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Contact Desk</h3>
                        <div class="footer-links">
                            <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
                            <a :href="`tel:${companyPhone}`">{{ companyPhone }}</a>
                            <span>Mumbai &bull; USA Regions</span>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span>© {{ currentYear }} Roook Hosting. All rights reserved.</span>
                    <span class="footer-status">All Systems Operational</span>
                </div>
            </div>
        </footer>
    </div>
</template>
