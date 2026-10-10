<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import {
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronDown,
    ChevronRight,
    CircleHelp,
    Cloud,
    Copy,
    FileText,
    Github,
    Mail,
    Menu,
    Moon,
    Phone,
    Printer,
    RotateCcw,
    Server,
    ShieldCheck,
    Sun,
    Terminal,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    page: {
        type: Object,
        required: true,
    },
    otherPages: {
        type: Array,
        default: () => [],
    },
});

const pageCtx = usePage();
const user = computed(() => pageCtx.props.auth?.user);

const contactEmail = computed(() => {
    return pageCtx.props.siteSettings?.company_email || 'billing@roook.cloud';
});

const contactPhone = computed(() => {
    return pageCtx.props.siteSettings?.company_phone || '+91 8849259933';
});

const companyName = computed(() => {
    return pageCtx.props.siteSettings?.company_name || 'Roook Hosting';
});

// Theme handling (synced with localStorage & rook-theme)
const lightTheme = ref(false);
const menuOpen = ref(false);
const productsOpen = ref(false);
const copiedNotice = ref(false);

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

const cleanDescription = computed(() => {
    if (!props.page?.content) {
        return `${props.page?.title || 'Legal Resource'} — Roook Managed Cloud Hosting official policies and client documentation.`;
    }
    const text = props.page.content.replace(/<[^>]*>?/gm, '').replace(/\s+/g, ' ').trim();
    return text.length > 160 ? text.substring(0, 157) + '...' : text;
});

const getPageIcon = (slug) => {
    switch (slug) {
        case 'privacy':
            return ShieldCheck;
        case 'terms':
            return FileText;
        case 'refund':
            return RotateCcw;
        case 'support':
            return CircleHelp;
        default:
            return FileText;
    }
};

const getPageBadge = (slug) => {
    switch (slug) {
        case 'privacy':
            return 'Privacy & GDPR';
        case 'terms':
            return 'Terms & SLA';
        case 'refund':
            return '7-Day Guarantee';
        case 'support':
            return '2-Hour Response';
        default:
            return 'Official Policy';
    }
};

const copyPageUrl = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        copiedNotice.value = true;
        setTimeout(() => {
            copiedNotice.value = false;
        }, 2200);
    } catch (e) {}
};

const printDocument = () => {
    window.print();
};

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head>
        <title>{{ page.title + ' — Roook Hosting' }}</title>
        <meta name="description" :content="cleanDescription" />
        <meta property="og:title" :content="page.title + ' — Roook Hosting'" />
        <meta property="og:description" :content="cleanDescription" />
        <meta property="og:type" content="article" />
        <meta name="twitter:title" :content="page.title + ' — Roook Hosting'" />
        <meta name="twitter:description" :content="cleanDescription" />
        <link rel="canonical" :href="`https://roook.cloud/p/${page.slug}`" />
    </Head>

    <div
        class="rook-site"
        :data-theme="lightTheme ? 'light' : 'dark'"
    >
        <!-- Sticky Site Header -->
        <header class="site-header">
            <div class="shell header-inner">
                <Link class="brand" :href="route('home')" aria-label="Roook home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>roook</span>
                </Link>

                <nav :class="['nav-links', { 'is-open': menuOpen }]" aria-label="Main navigation">
                    <!-- Products Dropdown -->
                    <div class="products-dropdown-container">
                        <button
                            type="button"
                            @click.stop="productsOpen = !productsOpen"
                            class="products-dropdown-trigger font-medium"
                            :aria-expanded="productsOpen"
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

                    <Link :href="route('products.hosting.managed')" @click="closeMenu">Managed Cloud</Link>
                    <Link :href="route('products.hosting.shared')" @click="closeMenu">Shared Hosting</Link>
                    <a :href="`${route('home')}#pricing`" @click="closeMenu">Pricing</a>
                    <a :href="`${route('home')}#features`" @click="closeMenu">Features</a>
                    <a :href="`${route('home')}#status`" @click="closeMenu">Status</a>
                </nav>

                <div class="nav-actions">
                    <template v-if="user">
                        <Link :href="route('dashboard')" class="button button-outline button-small">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')">
                            Login
                        </Link>
                    </template>

                    <button
                        class="theme-toggle"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                    >
                        <Moon v-if="lightTheme" :size="15" />
                        <Sun v-else :size="15" />
                    </button>

                    <Link class="button button-primary button-small" :href="`${route('home')}#pricing`">
                        Get started <ArrowUpRight :size="13" />
                    </Link>

                    <button
                        class="menu-toggle"
                        type="button"
                        @click="menuOpen = !menuOpen"
                        aria-label="Toggle mobile menu"
                    >
                        <X v-if="menuOpen" :size="17" />
                        <Menu v-else :size="17" />
                    </button>
                </div>
            </div>
        </header>

        <main id="top">
            <!-- Policy Hero Header -->
            <section class="legal-hero">
                <div class="legal-hero-bg" aria-hidden="true" />
                <div class="shell">
                    <div class="legal-hero-content">
                        <!-- Breadcrumbs -->
                        <nav class="legal-breadcrumbs" aria-label="Breadcrumb">
                            <Link :href="route('home')" class="breadcrumb-link">Home</Link>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-current">Legal &amp; Policies</span>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-active">{{ page.title }}</span>
                        </nav>

                        <div class="flex items-center gap-2.5 flex-wrap mb-3 mt-4">
                            <span class="legal-badge">
                                {{ getPageBadge(page.slug) }}
                            </span>
                            <span class="legal-verified-tag">
                                <Check :size="12" class="text-emerald-500" /> Verified &amp; Active
                            </span>
                        </div>

                        <h1 class="legal-title">{{ page.title }}</h1>

                        <p class="legal-subtitle">
                            Official operational and commercial standards governing Roook Hosting accounts, cloud infrastructure, and customer data.
                        </p>

                        <!-- Document Toolbar -->
                        <div class="legal-toolbar">
                            <div class="legal-meta-item">
                                <span class="meta-label">Applies to:</span>
                                <span class="meta-value">All roook.cloud customers &amp; servers</span>
                            </div>
                            <div class="legal-meta-divider" />
                            <div class="legal-meta-item">
                                <span class="meta-label">Revision:</span>
                                <span class="meta-value">October 2026 Edition</span>
                            </div>

                            <div class="legal-toolbar-actions">
                                <button
                                    type="button"
                                    @click="copyPageUrl"
                                    class="legal-tool-btn"
                                    title="Copy document URL"
                                >
                                    <Copy :size="13" />
                                    <span>{{ copiedNotice ? 'Copied Link!' : 'Share' }}</span>
                                </button>
                                <button
                                    type="button"
                                    @click="printDocument"
                                    class="legal-tool-btn"
                                    title="Print or save as PDF"
                                >
                                    <Printer :size="13" />
                                    <span>Print</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Document Content & Sidebar Grid -->
            <section class="legal-body-section">
                <div class="shell">
                    <div class="legal-layout-grid">
                        <!-- Left Navigation Sidebar -->
                        <aside class="legal-sidebar">
                            <div class="legal-sidebar-sticky">
                                <div class="legal-nav-panel">
                                    <div class="legal-nav-head">
                                        <FileText :size="14" class="text-[var(--accent)]" />
                                        <span>Policies &amp; Resources</span>
                                    </div>

                                    <nav class="legal-nav-list" aria-label="Policy document navigation">
                                        <Link
                                            v-for="item in otherPages"
                                            :key="item.slug"
                                            :href="route('pages.show', item.slug)"
                                            :class="['legal-nav-item', { 'is-active': item.slug === page.slug }]"
                                        >
                                            <component :is="getPageIcon(item.slug)" :size="15" class="legal-item-icon" />
                                            <span class="legal-item-title">{{ item.title }}</span>
                                            <ChevronRight :size="13" class="legal-item-arrow" />
                                        </Link>
                                    </nav>
                                </div>

                                <!-- Quick Support Card in Sidebar -->
                                <div class="legal-support-card">
                                    <div class="legal-support-title">Questions about this policy?</div>
                                    <p class="legal-support-desc">
                                        Our billing and operations desk is available to clarify any terms before or after your deployment.
                                    </p>
                                    <div class="legal-support-actions">
                                        <a :href="`mailto:${contactEmail}?subject=Inquiry%20regarding%20${encodeURIComponent(page.title)}`" class="legal-support-link">
                                            <Mail :size="13" />
                                            <span>{{ contactEmail }}</span>
                                        </a>
                                        <a :href="`tel:${contactPhone}`" class="legal-support-link">
                                            <Phone :size="13" />
                                            <span>{{ contactPhone }}</span>
                                        </a>
                                    </div>
                                    <div class="legal-support-sla">
                                        <span class="sla-dot" />
                                        <span>Engineer response within max 2 hours</span>
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <!-- Right Content Area -->
                        <article class="legal-article">
                            <div class="legal-article-card">
                                <!-- Article HTML content rendered dynamically -->
                                <div class="legal-rendered-content" v-html="page.content" />

                                <!-- Bottom Agreement Callout -->
                                <div class="legal-agreement-box">
                                    <div class="flex items-start gap-3">
                                        <div class="legal-agreement-icon">
                                            <ShieldCheck :size="18" />
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-[var(--text)] uppercase tracking-wider mb-1">
                                                Commitment to Transparency
                                            </div>
                                            <p class="text-xs text-[var(--text-soft)] leading-relaxed m-0">
                                                By ordering a managed or shared cloud service from <strong>{{ companyName }}</strong>, you are covered under our <strong>7-Day Money-Back Guarantee</strong> and enterprise <strong>2-Hour Provisioning SLA</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagination / Next Documents -->
                                <div class="legal-other-links">
                                    <div class="text-xs font-mono text-[var(--text-muted)] uppercase tracking-wider mb-3 font-semibold">
                                        Other Reference Documents
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <Link
                                            v-for="other in otherPages.filter(p => p.slug !== page.slug)"
                                            :key="other.slug"
                                            :href="route('pages.show', other.slug)"
                                            class="other-policy-chip"
                                        >
                                            <component :is="getPageIcon(other.slug)" :size="14" class="text-[var(--accent)]" />
                                            <span>{{ other.title }}</span>
                                            <ArrowRight :size="12" class="ml-auto text-[var(--text-muted)]" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <!-- Standard Site Footer -->
        <footer class="site-footer">
            <div class="shell">
                <div class="footer-top">
                    <div class="footer-brand-col">
                        <Link class="brand" :href="route('home')" aria-label="Roook home">
                            <span class="brand-mark" aria-hidden="true">r</span>
                            <span>roook</span>
                        </Link>
                        <p class="footer-brand-copy">
                            We handle the servers. You ship the code. High-performance managed cloud and shared hosting with 24/7 human care.
                        </p>
                    </div>
                    <div class="footer-group">
                        <h3>Hosting Solutions</h3>
                        <div class="footer-links">
                            <Link :href="route('products.hosting.managed')">Managed Cloud Hosting</Link>
                            <Link :href="route('products.hosting.shared')">Shared Web Hosting</Link>
                            <Link :href="route('products.nimbus')">Nimbus Control Panel</Link>
                            <a :href="`${route('home')}#pricing`">Pricing Plans</a>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Compliance &amp; Legal</h3>
                        <div class="footer-links">
                            <Link :href="route('pages.show', 'terms')">Terms of Service</Link>
                            <Link :href="route('pages.show', 'privacy')">Privacy Policy</Link>
                            <Link :href="route('pages.show', 'refund')">Refund &amp; Cancellation</Link>
                            <Link :href="route('pages.show', 'support')">Help Center &amp; Support</Link>
                        </div>
                    </div>
                    <div class="footer-group">
                        <h3>Global Datacenters</h3>
                        <div class="footer-links">
                            <span>🇮🇳 Mumbai (BOM1 &bull; Tier IV)</span>
                            <span>🇺🇸 USA East (IAD1 &bull; Tier IV)</span>
                            <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
                            <a :href="`tel:${contactPhone}`">{{ contactPhone }}</a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span>© {{ currentYear }} Roook Hosting. All rights reserved.</span>
                    <a class="footer-status" :href="`${route('home')}#status`">All Systems Operational</a>
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

<style scoped>
/* Legal Hero Section */
.legal-hero {
    position: relative;
    padding: 3.5rem 0 2.5rem;
    background: var(--panel);
    border-bottom: 1px solid var(--edge);
    overflow: hidden;
}

.legal-hero-bg {
    position: absolute;
    inset: 0;
    opacity: 0.04;
    background-image: radial-gradient(var(--text) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
}

.legal-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-family: var(--font-mono, monospace);
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.breadcrumb-link {
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.15s;
}

.breadcrumb-link:hover {
    color: var(--accent);
}

.breadcrumb-separator {
    color: var(--edge);
}

.breadcrumb-current {
    color: var(--text-soft);
}

.breadcrumb-active {
    color: var(--accent);
    font-weight: 600;
}

.legal-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.65rem;
    font-family: var(--font-mono, monospace);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    background: rgba(16, 185, 129, 0.15);
    color: var(--accent);
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.legal-verified-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.7rem;
    font-family: var(--font-mono, monospace);
    color: var(--text-soft);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    background: var(--panel-hi);
    border: 1px solid var(--edge);
}

.legal-title {
    font-size: 2.25rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--text);
    margin: 0.5rem 0 0.75rem;
    line-height: 1.15;
}

@media (min-width: 640px) {
    .legal-title {
        font-size: 2.75rem;
    }
}

.legal-subtitle {
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--text-soft);
    max-width: 720px;
    margin: 0 0 1.75rem;
}

.legal-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--edge);
    font-size: 0.75rem;
}

.legal-meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.meta-label {
    color: var(--text-muted);
}

.meta-value {
    color: var(--text);
    font-family: var(--font-mono, monospace);
    font-weight: 600;
}

.legal-meta-divider {
    width: 1px;
    height: 14px;
    background: var(--edge);
    display: none;
}

@media (min-width: 640px) {
    .legal-meta-divider {
        display: block;
    }
}

.legal-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
}

.legal-tool-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    background: var(--panel-hi);
    border: 1px solid var(--edge);
    color: var(--text-soft);
    font-size: 0.75rem;
    font-family: var(--font-mono, monospace);
    cursor: pointer;
    transition: all 0.15s ease;
}

.legal-tool-btn:hover {
    background: var(--page);
    color: var(--accent);
    border-color: var(--accent);
}

/* Legal Body Layout */
.legal-body-section {
    padding: 3rem 0 5rem;
}

.legal-layout-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
}

@media (min-width: 1024px) {
    .legal-layout-grid {
        grid-template-columns: 320px 1fr;
        gap: 3rem;
    }
}

/* Sidebar */
.legal-sidebar-sticky {
    position: sticky;
    top: 5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.legal-nav-panel {
    background: var(--panel);
    border: 1px solid var(--edge);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.legal-nav-head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1rem;
    font-size: 0.75rem;
    font-family: var(--font-mono, monospace);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text);
    background: var(--panel-hi);
    border-bottom: 1px solid var(--edge);
}

.legal-nav-list {
    display: flex;
    flex-direction: column;
    padding: 0.35rem;
}

.legal-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    font-size: 0.825rem;
    color: var(--text-soft);
    text-decoration: none;
    transition: all 0.15s ease;
}

.legal-nav-item:hover {
    background: var(--panel-hi);
    color: var(--text);
}

.legal-nav-item.is-active {
    background: rgba(16, 185, 129, 0.12);
    color: var(--accent);
    font-weight: 600;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.legal-item-icon {
    flex-shrink: 0;
    color: var(--text-muted);
    transition: color 0.15s;
}

.legal-nav-item.is-active .legal-item-icon {
    color: var(--accent);
}

.legal-item-title {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.legal-item-arrow {
    flex-shrink: 0;
    color: var(--edge-strong, var(--edge));
    transition: transform 0.15s;
}

.legal-nav-item:hover .legal-item-arrow,
.legal-nav-item.is-active .legal-item-arrow {
    color: var(--accent);
    transform: translateX(2px);
}

/* Quick Support Card */
.legal-support-card {
    background: var(--panel);
    border: 1px solid var(--edge);
    border-radius: 12px;
    padding: 1.25rem;
}

.legal-support-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 0.35rem;
}

.legal-support-desc {
    font-size: 0.75rem;
    line-height: 1.5;
    color: var(--text-muted);
    margin: 0 0 1rem;
}

.legal-support-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.legal-support-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-family: var(--font-mono, monospace);
    color: var(--text-soft);
    text-decoration: none;
    transition: color 0.15s;
}

.legal-support-link:hover {
    color: var(--accent);
}

.legal-support-sla {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    color: var(--text-muted);
    font-family: var(--font-mono, monospace);
    border-top: 1px solid var(--edge);
    padding-top: 0.75rem;
}

.sla-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
}

/* Article Card */
.legal-article-card {
    background: var(--panel);
    border: 1px solid var(--edge);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

@media (min-width: 640px) {
    .legal-article-card {
        padding: 3rem 2.5rem;
    }
}

/* Rendered HTML Typography */
.legal-rendered-content :deep(h1) {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    margin: 0 0 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--edge);
}

.legal-rendered-content :deep(h2) {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.02em;
    margin: 2.25rem 0 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed var(--edge);
}

.legal-rendered-content :deep(h3) {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.015em;
    margin: 1.75rem 0 0.5rem;
}

.legal-rendered-content :deep(p) {
    font-size: 0.925rem;
    line-height: 1.75;
    color: var(--text-soft);
    margin: 0 0 1.25rem;
}

.legal-rendered-content :deep(ul),
.legal-rendered-content :deep(ol) {
    margin: 0 0 1.25rem;
    padding-left: 1.5rem;
    color: var(--text-soft);
}

.legal-rendered-content :deep(li) {
    font-size: 0.925rem;
    line-height: 1.7;
    margin-bottom: 0.5rem;
}

.legal-rendered-content :deep(strong) {
    color: var(--text);
    font-weight: 600;
}

.legal-rendered-content :deep(a) {
    color: var(--accent);
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 500;
}

.legal-rendered-content :deep(blockquote) {
    margin: 1.5rem 0;
    padding: 0.85rem 1.25rem;
    border-left: 3px solid var(--accent);
    background: var(--panel-hi);
    border-radius: 0 8px 8px 0;
    color: var(--text-soft);
    font-size: 0.9rem;
    line-height: 1.6;
}

/* Agreement Box */
.legal-agreement-box {
    margin-top: 3rem;
    padding: 1.25rem;
    background: var(--panel-hi);
    border: 1px solid var(--edge);
    border-radius: 12px;
}

.legal-agreement-icon {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(16, 185, 129, 0.15);
    color: var(--accent);
    border: 1px solid rgba(16, 185, 129, 0.25);
}

/* Other References */
.legal-other-links {
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid var(--edge);
}

.other-policy-chip {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: var(--panel-hi);
    border: 1px solid var(--edge);
    color: var(--text-soft);
    text-decoration: none;
    font-size: 0.825rem;
    font-weight: 500;
    transition: all 0.15s ease;
}

.other-policy-chip:hover {
    background: var(--page);
    border-color: var(--accent);
    color: var(--text);
}
</style>
