<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    page: Object,
    otherPages: Array
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <Head :title="page.title + ' — Nimbus by VMCore'" />

    <div class="page-layout">
        <!-- Header / Navbar -->
        <header class="page-navbar">
            <div class="page-navbar__inner">
                <Link href="/" class="page-navbar__brand">
                    <div class="page-navbar__logo-box">
                        <img src="/favicon.png?v=2" alt="Nimbus" class="page-navbar__logo-img" />
                    </div>
                    <span class="page-navbar__brand-text">Nimbus <span class="page-navbar__brand-sub">by VMCore</span></span>
                </Link>
                <div class="page-navbar__actions">
                    <Link href="/" class="btn btn--outline btn--sm">Back to Home</Link>
                    <Link :href="route('register')" class="btn btn--primary btn--sm">Get Started</Link>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="page-main">
            <div class="page-container">
                <div class="page-grid">
                    <!-- Left Sidebar Links -->
                    <aside class="page-sidebar">
                        <div class="page-sidebar__card">
                            <h4 class="page-sidebar__title">Legal & Resources</h4>
                            <nav class="page-sidebar__nav">
                                <Link 
                                    v-for="item in otherPages" 
                                    :key="item.slug"
                                    :href="route('pages.show', item.slug)"
                                    class="page-sidebar__link"
                                    :class="{ 'page-sidebar__link--active': item.slug === page.slug }"
                                >
                                    {{ item.title }}
                                </Link>
                                <a href="https://nimbus-docs.vmcore.in/" target="_blank" class="page-sidebar__link">
                                    Documentation <span class="ext-icon">↗</span>
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <!-- Right Content Area -->
                    <article class="page-content">
                        <div class="page-content__card">
                            <div class="page-content__body" v-html="page.content"></div>
                        </div>
                    </article>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="page-footer">
            <div class="page-container text-center">
                <p>© 2026 Nimbus by VMCore. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
    --color-primary: #10B981;
    --color-primary-hover: #059669;
    --color-bg: #FAFBFC;
    --color-card: #FFFFFF;
    --color-border: #E5E7EB;
    --color-heading: #111827;
    --color-body: #6B7280;
    --font: 'Inter', sans-serif;
    --transition: 200ms ease;
}

.page-layout {
    font-family: var(--font);
    background: var(--color-bg);
    color: var(--color-body);
    min-h-screen: 100vh;
    display: flex;
    flex-direction: column;
}

.page-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
    width: 100%;
}

/* Navbar */
.page-navbar {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--color-border);
    position: sticky;
    top: 0;
    z-index: 10;
}
.page-navbar__inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
}
.page-navbar__brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.page-navbar__logo-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.page-navbar__logo-img {
    width: 20px;
    height: 20px;
    object-fit: contain;
}
.page-navbar__brand-text {
    font-weight: 700;
    font-size: 16px;
    color: var(--color-heading);
}
.page-navbar__brand-sub {
    font-weight: 500;
    font-size: 11px;
    opacity: 0.8;
    color: var(--color-body);
}
.page-navbar__actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 13px;
    border-radius: 8px;
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
    border: 1.5px solid transparent;
    padding: 8px 16px;
    line-height: 1;
}
.btn--primary {
    background: var(--color-primary);
    color: #ffffff;
    border-color: var(--color-primary);
}
.btn--primary:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
}
.btn--outline {
    background: #ffffff;
    color: var(--color-heading);
    border-color: var(--color-border);
}
.btn--outline:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

/* Layout Grid */
.page-main {
    flex: 1;
    padding: 40px 0 60px;
}
.page-grid {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 32px;
    align-items: start;
}

/* Sidebar */
.page-sidebar__card {
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 20px;
}
.page-sidebar__title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-heading);
    margin-bottom: 16px;
}
.page-sidebar__nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.page-sidebar__link {
    font-size: 14px;
    font-weight: 500;
    color: var(--color-body);
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all var(--transition);
}
.page-sidebar__link:hover {
    background: #f3f4f6;
    color: var(--color-heading);
}
.page-sidebar__link--active {
    background: rgba(16, 185, 129, 0.08);
    color: var(--color-primary);
    font-weight: 600;
}
.ext-icon {
    font-size: 10px;
    color: #9ca3af;
}

/* Content Area */
.page-content__card {
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 40px;
}
.page-content__body h1 {
    font-size: 28px;
    font-weight: 800;
    color: var(--color-heading);
    margin-bottom: 24px;
    line-height: 1.25;
}
.page-content__body h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading);
    margin-top: 32px;
    margin-bottom: 16px;
}
.page-content__body h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--color-heading);
    margin-top: 24px;
    margin-bottom: 12px;
}
.page-content__body p {
    font-size: 15px;
    line-height: 1.6;
    color: var(--color-body);
    margin-bottom: 16px;
}
.page-content__body a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}
.page-content__body a:hover {
    text-decoration: underline;
}

/* Footer */
.page-footer {
    border-top: 1px solid var(--color-border);
    padding: 24px 0;
    background: #ffffff;
    font-size: 13px;
    color: #9ca3af;
}
.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .page-grid {
        grid-template-columns: 1fr;
    }
    .page-sidebar {
        order: 2;
    }
    .page-content {
        order: 1;
    }
    .page-content__card {
        padding: 24px;
    }
}
</style>
