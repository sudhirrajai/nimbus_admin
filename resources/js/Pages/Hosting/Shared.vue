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
    Zap,
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

const sharedPlans = computed(() => {
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
                    'Ultra-fast NVMe PCIe 4.0 Storage',
                    'Free SSL certificates & HTTP/2',
                    'Automated offsite daily backups',
                    '24/7 Monitoring & 2-Hour SLA',
                ],
            };
        });
    }

    return [
        {
            name: 'Starter Cloud',
            slug: 'starter-cloud',
            description: 'Ideal for WordPress, single business websites, and small web apps.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 316 : 390) : (annual.value ? 4 : 5),
            caption: annual.value ? 'Billed annually · ₹3,800 / yr' : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹4,790 / yr' : 'Renews at $59 / yr',
            discountPercent: 20,
            featured: false,
            items: [
                '1 Website production container',
                '15 GB NVMe PCIe 4.0 disk space',
                'Unmetered high-speed bandwidth',
                'Free auto-renewing SSL',
                'Automated daily backups',
            ],
        },
        {
            name: 'Business Cloud',
            slug: 'business-cloud',
            description: 'For busy eCommerce stores and multi-site agencies.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 665 : 790) : (annual.value ? 8 : 10),
            caption: annual.value ? 'Billed annually · ₹7,990 / yr' : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹9,990 / yr' : 'Renews at $119 / yr',
            discountPercent: 20,
            featured: true,
            items: [
                'Up to 3 Websites / Domains',
                '40 GB NVMe PCIe 4.0 disk space',
                'Dedicated Redis Object Cache',
                'Daily backups (30-day retention)',
                'Priority engineer support channel',
            ],
        },
        {
            name: 'Enterprise Cloud',
            slug: 'enterprise-cloud',
            description: 'For high-concurrency client sites and production fleets.',
            symbol: isINR ? '₹' : '$',
            displayAmount: isINR ? (annual.value ? 1415 : 1690) : (annual.value ? 16 : 20),
            caption: annual.value ? 'Billed annually · ₹16,990 / yr' : 'Billed monthly · cancel anytime',
            renewalText: isINR ? 'Renews at ₹19,990 / yr' : 'Renews at $249 / yr',
            discountPercent: 15,
            featured: false,
            items: [
                'Unlimited Websites & Subdomains',
                '100 GB NVMe PCIe 4.0 disk space',
                'Custom offsite snapshot backup rules',
                'Dedicated infrastructure engineer',
                'SLA & Zero-Downtime Guarantee',
            ],
        },
    ];
});

const faqs = [
    {
        question: 'How is Roook different from regular shared hosting?',
        answer: 'Traditional shared hosts cram thousands of accounts onto a single slow server with spinning hard drives. Roook isolates every client into a dedicated cloud container with dedicated CPU/RAM slices and Enterprise PCIe 4.0 NVMe SSDs. If another tenant gets traffic, your site stays lightning fast.',
    },
    {
        question: 'Can you migrate my existing website from cPanel/Hostinger/GoDaddy?',
        answer: 'Yes! We offer 100% Free White-Glove Migration. Once you complete checkout, our engineers copy your files, MySQL databases, and SSL certificates with 0 minutes of downtime.',
    },
    {
        question: 'Can I choose my server location?',
        answer: 'Yes! During checkout, you can select between India (Mumbai BOM1) for ultra-fast latency across India and APAC, or USA (East Coast IAD1) for global traffic.',
    },
    {
        question: 'What is your refund policy?',
        answer: 'We provide an unconditional 7-Day Money-Back Guarantee. If you are not satisfied for any reason, email billing@roook.cloud for a full 100% refund.',
    },
];

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>Shared Web Hosting — Ultra-Fast NVMe SSD Hosting | Roook</title>
        <meta name="description" content="High-speed shared cloud hosting powered by PCIe Gen4 NVMe SSDs, free automated SSL, 99.98% uptime, and 2-hour setup. Deploy in India Mumbai or USA datacenters." />
        <meta name="keywords" content="shared web hosting, fast nvme hosting, wordpress hosting, india shared hosting, usa web hosting, nimbus panel" />
        <meta property="og:title" content="Shared Web Hosting — Ultra-Fast NVMe SSD Hosting | Roook" />
        <meta property="og:description" content="High-speed shared cloud hosting powered by PCIe Gen4 NVMe SSDs, free automated SSL, 99.98% uptime, and 2-hour setup." />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="https://roook.cloud/hosting/shared" />
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
                            <Link :href="route('products.hosting.managed')" @click="closeMenu" class="dropdown-item">
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

                            <Link :href="route('products.hosting.shared')" @click="closeMenu" class="dropdown-item bg-[var(--surface-hi)]">
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

                    <Link :href="route('products.hosting.shared')" class="text-[var(--accent)] font-semibold">Shared Hosting</Link>
                    <Link :href="route('products.hosting.managed')">Managed Cloud</Link>
                    <a href="#why-roook">Why Roook</a>
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
                        Get Started <ArrowUpRight :size="13" />
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
                        <div class="eyebrow hero-kicker">High-Performance NVMe Hosting</div>
                        <h1>
                            Shared Web Hosting.<br />
                            <span class="highlight">Without noisy neighbors.</span>
                        </h1>
                        <p class="hero-lede">
                            Tired of slow, overcrowded shared hosts? Roook provides container-isolated shared hosting with pure Enterprise PCIe Gen4 NVMe storage, automated SSL, and 99.98% verified uptime.
                        </p>
                        <div class="hero-actions">
                            <a class="button button-primary" href="#pricing">
                                View Web Hosting Plans <ArrowRight :size="15" />
                            </a>
                            <Link class="button button-outline" :href="route('checkout', { plan: 'starter-cloud' })">
                                Start with Starter Plan <ArrowDownRight :size="15" />
                            </Link>
                        </div>
                        <div class="hero-note flex items-center gap-3">
                            <span class="flex items-center gap-1 text-xs text-[var(--text-soft)]">
                                <Check :size="14" class="text-emerald-500" /> 7-Day Money-Back Guarantee
                            </span>
                            <span class="flex items-center gap-1 text-xs text-[var(--text-soft)]">
                                <Check :size="14" class="text-emerald-500" /> Free Migration from Any Host
                            </span>
                        </div>
                    </div>

                    <!-- Performance Comparison Card -->
                    <div class="terminal-wrap">
                        <div class="terminal">
                            <div class="terminal-bar">
                                <div class="terminal-dots"><i /><i /><i /></div>
                                <span>benchmarks / storage-speed</span>
                                <span class="terminal-live">NVMe 4.0</span>
                            </div>
                            <div class="terminal-content">
                                <p class="terminal-line">
                                    <span class="line-index">01</span>
                                    <span class="terminal-command"><span class="line-prompt">&gt;</span> fio --rw=randread --bs=4k --ioengine=libaio</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">02</span>
                                    <span><span class="line-ok">✓</span> Roook NVMe PCIe 4.0: <strong class="text-emerald-400">4,200 MB/s</strong> read throughput</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">03</span>
                                    <span><span class="line-quiet">✕</span> Traditional Shared Host SATA: ~520 MB/s (8x slower)</span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">04</span>
                                    <span><span class="line-ok">✓</span> Average TTFB response: <strong class="text-emerald-400">&lt; 160ms</strong></span>
                                </p>
                                <p class="terminal-line">
                                    <span class="line-index">05</span>
                                    <span><span class="line-ok">✓</span> Datacenters: 🇮🇳 Mumbai BOM1 &bull; 🇺🇸 USA IAD1</span>
                                </p>
                                <div class="terminal-divider" />
                                <div class="terminal-footer">
                                    <span>Isolated Resource Slices</span>
                                    <strong>Instant WordPress / PHP / Node</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Datacenters Trust Strip -->
            <section class="trust-strip">
                <div class="shell trust-layout">
                    <div class="trust-intro">Datacenter Locations<br />Selectable at checkout.</div>
                    <div class="trust-item">
                        <span class="trust-number">🇮🇳 Mumbai, India</span>
                        <span class="trust-caption">CtrlS / Equinix Tier IV<br />Sub-20ms India latency</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">🇺🇸 USA East Coast</span>
                        <span class="trust-caption">Ashburn Tier IV Backbone<br />Direct Atlantic connectivity</span>
                    </div>
                    <div class="trust-item">
                        <span class="trust-number">&lt; 2 Hours</span>
                        <span class="trust-caption">Human Setup SLA<br />Every account engineer verified</span>
                    </div>
                </div>
            </section>

            <!-- Why Roook Section -->
            <section class="section" id="why-roook">
                <div class="shell">
                    <div class="section-top">
                        <div>
                            <div class="eyebrow">The Roook Difference</div>
                            <h2 class="section-heading">Web hosting done with engineer care</h2>
                            <p class="section-intro">We stripped away the clutter of bloated shared hosts and replaced it with high-speed simplicity.</p>
                        </div>
                    </div>

                    <div class="feature-grid">
                        <article class="feature-card">
                            <span class="feature-index">01</span>
                            <div class="feature-icon"><Zap :size="17" /></div>
                            <span class="feature-tag">PURE NVME SPEED</span>
                            <h3>Instant Load Times</h3>
                            <p>PCIe Gen4 enterprise NVMe drives mean your database queries execute in microseconds, dramatically improving your Google Core Web Vitals.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">02</span>
                            <div class="feature-icon"><ShieldCheck :size="17" /></div>
                            <span class="feature-tag">SECURITY FIRST</span>
                            <h3>Free Automated SSL &amp; Firewall</h3>
                            <p>Every website gets free, automated SSL certificates that never expire, backed by web application firewall rules and automated malware scans.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">03</span>
                            <div class="feature-icon"><Workflow :size="17" /></div>
                            <span class="feature-tag">EASY MIGRATION</span>
                            <h3>White-Glove Site Migration</h3>
                            <p>Moving from another host? Our engineers handle the complete file transfer, database sync, and DNS handover without downtime.</p>
                        </article>

                        <article class="feature-card">
                            <span class="feature-index">04</span>
                            <div class="feature-icon"><Database :size="17" /></div>
                            <span class="feature-tag">DATA RECOVERY</span>
                            <h3>Automated Daily Backups</h3>
                            <p>Never lose a file or post. Backups are created automatically every night and retained safely offsite for instant 1-click restoration.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section class="section" id="pricing">
                <div class="shell">
                    <div class="section-top pricing-top">
                        <div>
                            <div class="eyebrow">Transparent Pricing</div>
                            <h2 class="section-heading">Choose your hosting plan</h2>
                            <p class="section-intro">Simple, honest pricing without surprise renewal jumps. Pick your plan to begin.</p>
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
                        <article v-for="plan in sharedPlans" :key="plan.name" :class="['price-card', { featured: plan.featured }]">
                            <span v-if="plan.featured" class="popular-label">Most popular</span>
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
                                <span>Get {{ plan.name }}</span>
                                <ArrowRight :size="14" />
                            </Link>

                            <div class="plan-rule" />
                            <div class="plan-list-label">What's Included</div>
                            <ul class="plan-list">
                                <li v-for="item in plan.items" :key="item">
                                    <Check :size="14" />{{ item }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section class="section" id="faq">
                <div class="shell faq-layout">
                    <div class="faq-aside">
                        <div class="eyebrow">Everything you need to know</div>
                        <h2 class="section-heading">Web Hosting FAQs</h2>
                        <p class="section-intro">Have a question about migration or package sizing? Ask our infrastructure desk directly.</p>
                        <a class="faq-contact" :href="`mailto:${contactEmail}?subject=Web%20Hosting%20Question`">
                            <Mail :size="14" /> Talk to an engineer
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
                            We handle the servers. You ship the code. High-speed NVMe shared and managed hosting.
                        </p>
                    </div>
                    <div class="footer-group">
                        <h3>Hosting Products</h3>
                        <div class="footer-links">
                            <Link :href="route('products.hosting.shared')">Shared Web Hosting</Link>
                            <Link :href="route('products.hosting.managed')">Managed Cloud Hosting</Link>
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
                        <h3>Support &amp; Locations</h3>
                        <div class="footer-links">
                            <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
                            <a :href="`tel:${companyPhone}`">{{ companyPhone }}</a>
                            <span>🇮🇳 Mumbai &bull; 🇺🇸 USA East</span>
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
