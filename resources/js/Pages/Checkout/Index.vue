<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    ArrowRight,
    Building2,
    Check,
    CircleHelp,
    Lock,
    MapPin,
    Moon,
    ShieldCheck,
    Sun,
    User,
} from 'lucide-vue-next';

const props = defineProps({
    managedPlans: {
        type: Array,
        default: () => [],
    },
    initialPlan: {
        type: String,
        default: 'starter-cloud',
    },
    initialBilling: {
        type: String,
        default: 'yearly',
    },
    initialCurrency: {
        type: String,
        default: 'INR',
    },
    razorpayKey: {
        type: String,
        default: '',
    },
    user: {
        type: Object,
        default: null,
    },
});

// Theme handling
const lightTheme = ref(false);
const toggleTheme = () => {
    lightTheme.value = !lightTheme.value;
    try {
        window.localStorage.setItem('rook-theme', lightTheme.value ? 'light' : 'dark');
    } catch (e) {}
};

// Fallback plans matching Landing page exactly
const fallbackPlans = [
    {
        id: 1,
        slug: 'starter-cloud',
        name: 'Starter Cloud',
        description: 'For a small app or a site ready to leave shared hosting.',
        price_inr: 3800,
        price_usd: 49,
        monthly_price_inr: 390,
        monthly_price_usd: 5,
        renewal_price_inr: 4790,
        renewal_price_usd: 59,
        renewal_monthly_price_inr: 490,
        renewal_monthly_price_usd: 6,
        billing_period: '/year',
    },
    {
        id: 2,
        slug: 'business-cloud',
        name: 'Business Cloud',
        description: 'For growing teams that need room and a steady hand.',
        price_inr: 7990,
        price_usd: 99,
        monthly_price_inr: 790,
        monthly_price_usd: 10,
        renewal_price_inr: 9990,
        renewal_price_usd: 119,
        renewal_monthly_price_inr: 990,
        renewal_monthly_price_usd: 12,
        billing_period: '/year',
    },
    {
        id: 3,
        slug: 'enterprise-cloud',
        name: 'Enterprise Cloud',
        description: 'For complex systems, compliance needs, or many properties.',
        price_inr: 16990,
        price_usd: 199,
        monthly_price_inr: 1690,
        monthly_price_usd: 20,
        renewal_price_inr: 19990,
        renewal_price_usd: 249,
        renewal_monthly_price_inr: 1990,
        renewal_monthly_price_usd: 25,
        billing_period: '/year',
    },
];

const availablePlans = computed(() => {
    return props.managedPlans && props.managedPlans.length > 0 ? props.managedPlans : fallbackPlans;
});

// Selection state
const selectedPlanId = ref(null);
const billing = ref(props.initialBilling === 'monthly' ? 'monthly' : 'yearly');
const currency = ref(props.initialCurrency === 'USD' ? 'USD' : 'INR');
const domainChoice = ref('have'); // 'have' or 'later'
const domain = ref('');

// 3-Step Wizard state
const currentStep = ref(1);

const goToStep = (targetStep) => {
    errorMessage.value = '';
    domainError.value = '';

    // If attempting to advance past step 1 without plan
    if (targetStep > 1 && !selectedPlan.value) {
        errorMessage.value = 'Please select a hosting plan to continue.';
        currentStep.value = 1;
        return;
    }

    // If attempting to advance past step 2 with invalid domain
    if (targetStep > 2 && domainChoice.value === 'have') {
        if (!domain.value || !validDomain(domain.value)) {
            domainError.value = 'Please enter a valid domain name (e.g. yourcompany.com) or select "Skip domain setup for now".';
            currentStep.value = 2;
            return;
        }
    }

    currentStep.value = targetStep;
    try {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {}
};

// Customer & Billing Address State (GST/Tax ID removed)
const name = ref(props.user?.name || '');
const email = ref(props.user?.email || '');
const phone = ref(props.user?.phone || '');
const companyName = ref(props.user?.company_name || '');
const address = ref(props.user?.address || '');
const city = ref(props.user?.city || '');
const state = ref(props.user?.state || '');
const postalCode = ref(props.user?.postal_code || '');
const country = ref(props.user?.country || 'India');

const countries = [
    'India',
    'United States',
    'United Kingdom',
    'Canada',
    'Australia',
    'Singapore',
    'United Arab Emirates',
    'Germany',
    'France',
    'Netherlands',
    'Ireland',
    'Japan',
    'Other',
];

// Form validation and state
const errorMessage = ref('');
const domainError = ref('');
const isProcessing = ref(false);

const selectedPlan = computed(() => {
    if (!selectedPlanId.value) return availablePlans.value[0] || null;
    return availablePlans.value.find((p) => p.id === selectedPlanId.value || p.slug === selectedPlanId.value) || availablePlans.value[0];
});

// Pricing calculations
const getMonthlyRate = (plan) => {
    if (!plan) return 0;
    if (currency.value === 'INR') {
        return plan.monthly_price_inr ? Number(plan.monthly_price_inr) : Math.round(Number(plan.price_inr) / 10);
    }
    return plan.monthly_price_usd ? Number(plan.monthly_price_usd) : Math.round(Number(plan.price_usd) / 10);
};

const getYearlyMonthlyEquivalent = (plan) => {
    if (!plan) return 0;
    if (currency.value === 'INR') {
        return Math.round(Number(plan.price_inr) / 12);
    }
    return Math.round(Number(plan.price_usd) / 12);
};

const getRenewalPrice = (plan) => {
    if (!plan) return 0;
    const isINR = currency.value === 'INR';
    if (billing.value === 'monthly') {
        if (isINR) {
            return Number(plan.renewal_monthly_price_inr || plan.monthly_price_inr || Math.round(Number(plan.renewal_price_inr || plan.price_inr) / 10));
        }
        return Number(plan.renewal_monthly_price_usd || plan.monthly_price_usd || Math.round(Number(plan.renewal_price_usd || plan.price_usd) / 10));
    } else {
        if (isINR) {
            return Number(plan.renewal_price_inr || plan.price_inr);
        }
        return Number(plan.renewal_price_usd || plan.price_usd);
    }
};

const getCurrentPrice = (plan) => {
    if (!plan) return 0;
    if (billing.value === 'monthly') {
        return getMonthlyRate(plan);
    }
    return currency.value === 'INR' ? Number(plan.price_inr) : Number(plan.price_usd);
};

const getSavings = (plan) => {
    if (!plan) return 0;
    const renewal = getRenewalPrice(plan);
    const current = getCurrentPrice(plan);
    return (renewal > current) ? (renewal - current) : 0;
};

const getDiscountPercent = (plan) => {
    if (!plan) return 0;
    const renewal = getRenewalPrice(plan);
    const savings = getSavings(plan);
    return (renewal > 0 && savings > 0) ? Math.round((savings / renewal) * 100) : 0;
};

const getTotalPrice = computed(() => {
    if (!selectedPlan.value) return 0;
    return getCurrentPrice(selectedPlan.value);
});

const getRazorpayChargeINR = computed(() => {
    if (!selectedPlan.value) return 0;
    const plan = selectedPlan.value;
    if (billing.value === 'monthly') {
        return plan.monthly_price_inr ? Number(plan.monthly_price_inr) : Math.round(Number(plan.price_inr) / 10);
    }
    return Number(plan.price_inr);
});

function validDomain(value) {
    if (!value) return false;
    const clean = value.trim().toLowerCase().replace(/\.$/, '');
    if (clean.length > 253 || !clean.includes('.') || clean.includes('://') || clean.includes('/') || clean.includes('@')) {
        return false;
    }
    return clean.split('.').every((label) => /^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i.test(label));
}

onMounted(() => {
    try {
        if (window.localStorage.getItem('rook-theme') === 'light') {
            lightTheme.value = true;
        }
    } catch (e) {}

    // Currency timezone auto-detect or URL parameter priority
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const queryCurrency = urlParams.get('currency');
        if (queryCurrency) {
            currency.value = queryCurrency.toUpperCase() === 'USD' ? 'USD' : 'INR';
        } else if (props.initialCurrency && props.initialCurrency !== 'INR') {
            currency.value = props.initialCurrency;
        } else {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (tz && (tz === 'Asia/Kolkata' || tz.includes('Calcutta') || tz.includes('Kolkata'))) {
                currency.value = 'INR';
            } else if (props.initialCurrency) {
                currency.value = props.initialCurrency;
            }
        }
    } catch (e) {}

    // Initial plan match
    if (props.initialPlan) {
        const target = props.initialPlan.toLowerCase();
        const found = availablePlans.value.find((p) =>
            p.slug.toLowerCase() === target ||
            p.slug.toLowerCase().replace('-cloud', '') === target ||
            p.name.toLowerCase().includes(target)
        );
        if (found) {
            selectedPlanId.value = found.id;
        } else if (availablePlans.value.length > 0) {
            selectedPlanId.value = availablePlans.value[0].id;
        }
    } else if (availablePlans.value.length > 0) {
        selectedPlanId.value = availablePlans.value[0].id;
    }

    // Load Razorpay checkout script if needed
    if (!document.getElementById('razorpay-checkout-script')) {
        const script = document.createElement('script');
        script.id = 'razorpay-checkout-script';
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);
    }
});

const handleCheckoutSubmit = async () => {
    errorMessage.value = '';
    domainError.value = '';

    if (!selectedPlan.value) {
        currentStep.value = 1;
        errorMessage.value = 'Please select a hosting plan to continue.';
        return;
    }

    if (domainChoice.value === 'have') {
        if (!domain.value || !validDomain(domain.value)) {
            currentStep.value = 2;
            domainError.value = 'Please enter a valid domain name, such as yourcompany.com.';
            return;
        }
    }

    if (!name.value || !name.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter your full name.';
        return;
    }

    if (!email.value || !email.value.trim() || !email.value.includes('@')) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter a valid work or account email.';
        return;
    }

    if (!phone.value || !phone.value.trim() || phone.value.trim().length < 6) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter a valid phone or mobile number for account verification & alerts.';
        return;
    }

    if (!address.value || !address.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter your street address (flat, building, road).';
        return;
    }

    if (!city.value || !city.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter your city.';
        return;
    }

    if (!state.value || !state.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter your state or province.';
        return;
    }

    if (!postalCode.value || !postalCode.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please enter your postal / PIN code.';
        return;
    }

    if (!country.value || !country.value.trim()) {
        currentStep.value = 3;
        errorMessage.value = 'Please select your country.';
        return;
    }

    isProcessing.value = true;

    try {
        const payload = {
            plan: selectedPlan.value.slug,
            billing_cycle: billing.value,
            currency: currency.value,
            domain_choice: domainChoice.value,
            domain: domainChoice.value === 'have' ? domain.value.trim() : '',
            name: name.value.trim(),
            email: email.value.trim(),
            phone: phone.value.trim(),
            company_name: companyName.value.trim(),
            address: address.value.trim(),
            city: city.value.trim(),
            state: state.value.trim(),
            postal_code: postalCode.value.trim(),
            country: country.value.trim(),
            tax_id: '',
        };

        const res = await axios.post(route('payment.initiate-hosting'), payload);
        const data = res.data;

        const options = {
            key: data.key_id,
            amount: data.amount,
            currency: "INR",
            name: "Roook Managed Hosting",
            description: `${data.plan_name} (${billing.value === 'monthly' ? 'Monthly' : 'Annual'} Subscription)`,
            order_id: data.order_id,
            handler: function (response) {
                // Submit form to verify
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = route('payment.verify-hosting');

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const params = {
                    _token: csrfToken,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    name: name.value.trim(),
                    email: email.value.trim(),
                    phone: phone.value.trim(),
                    company_name: companyName.value.trim(),
                    address: address.value.trim(),
                    city: city.value.trim(),
                    state: state.value.trim(),
                    postal_code: postalCode.value.trim(),
                    country: country.value.trim(),
                    tax_id: '',
                    domain_choice: domainChoice.value,
                    domain: domainChoice.value === 'have' ? domain.value.trim() : '',
                    plan: selectedPlan.value.slug,
                    billing_cycle: billing.value,
                    currency: currency.value,
                };

                for (const key in params) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            },
            prefill: {
                name: name.value.trim(),
                email: email.value.trim(),
                contact: phone.value.trim(),
            },
            theme: {
                color: "#10B981",
            },
            modal: {
                ondismiss: function () {
                    isProcessing.value = false;
                }
            }
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    } catch (err) {
        isProcessing.value = false;
        console.error('Payment initiation error:', err);
        errorMessage.value = err.response?.data?.message || 'Failed to initialize payment gateway. Please try again.';
    }
};
</script>

<template>
    <Head title="Order Managed Hosting — Roook" />

    <main class="rook-site checkout-site" :data-theme="lightTheme ? 'light' : 'dark'">
        <!-- Sticky Header matching Roook design -->
        <header class="checkout-header">
            <div class="shell checkout-header-inner">
                <Link :href="route('home')" class="brand" aria-label="Roook Home" data-testid="link-checkout-home">
                    <span class="brand-mark" aria-hidden="true">r</span>
                    <span>roook</span>
                </Link>

                <div class="checkout-header-right">
                    <span class="checkout-header-note">
                        <ShieldCheck :size="14" aria-hidden="true" />
                        A careful start · 2-Hour Provisioning SLA
                    </span>
                    <button
                        class="theme-toggle"
                        type="button"
                        @click="toggleTheme"
                        :aria-label="`Switch to ${lightTheme ? 'dark' : 'light'} theme`"
                        data-testid="button-checkout-theme"
                    >
                        <Moon v-if="lightTheme" :size="15" aria-hidden="true" />
                        <Sun v-else :size="15" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <div class="shell checkout-main">
            <Link :href="route('home') + '#pricing'" class="checkout-back" data-testid="link-back-pricing">
                <ArrowLeft :size="14" aria-hidden="true" /> Back to plans
            </Link>

            <div class="checkout-heading">
                <div class="eyebrow">A thoughtful start</div>
                <h1>Let’s get the details right.</h1>
                <p>
                    Choose your hosting tier and tell us your primary domain. Your cloud server will be verified and provisioned on our infrastructure within max 2 hours.
                </p>
            </div>

            <!-- Step Progress -->
            <ol class="checkout-progress" aria-label="Checkout sections" data-testid="status-checkout-progress">
                <li
                    :class="{ 'is-current': currentStep === 1, 'is-completed': currentStep > 1 }"
                    @click="goToStep(1)"
                    role="button"
                    tabindex="0"
                >
                    <span>
                        <Check v-if="currentStep > 1" :size="12" />
                        <template v-else>01</template>
                    </span>
                    <strong>Hosting Plan</strong>
                </li>
                <li
                    :class="{ 'is-current': currentStep === 2, 'is-completed': currentStep > 2 }"
                    @click="currentStep > 1 ? goToStep(2) : null"
                    :role="currentStep > 1 ? 'button' : undefined"
                    :tabindex="currentStep > 1 ? 0 : undefined"
                >
                    <span>
                        <Check v-if="currentStep > 2" :size="12" />
                        <template v-else>02</template>
                    </span>
                    <strong>Domain Setup</strong>
                </li>
                <li
                    :class="{ 'is-current': currentStep === 3 }"
                    @click="currentStep > 2 ? goToStep(3) : null"
                    :role="currentStep > 2 ? 'button' : undefined"
                    :tabindex="currentStep > 2 ? 0 : undefined"
                >
                    <span>03</span>
                    <strong>Billing Details</strong>
                </li>
            </ol>

            <form class="checkout-layout" @submit.prevent="handleCheckoutSubmit" noValidate data-testid="form-checkout">
                <!-- Left Column: Setup Fields -->
                <div class="checkout-form-column">
                    <!-- Section 01: Choose your plan -->
                    <section v-show="currentStep === 1" class="checkout-section" aria-labelledby="plan-heading">
                        <div class="checkout-section-head">
                            <span class="checkout-step-number">01</span>
                            <div>
                                <h2 id="plan-heading">Choose your plan</h2>
                                <p>You can change your selection or billing period here.</p>
                            </div>
                        </div>

                        <!-- Plan Options Grid -->
                        <div class="checkout-plan-options" role="group" aria-label="Hosting plan">
                            <button
                                v-for="plan in availablePlans"
                                :key="plan.id"
                                type="button"
                                :class="['checkout-plan-option', { selected: selectedPlan?.id === plan.id }]"
                                :aria-pressed="selectedPlan?.id === plan.id"
                                @click="selectedPlanId = plan.id"
                                :data-testid="`button-select-plan-${plan.slug}`"
                            >
                                <span class="checkout-radio" aria-hidden="true">
                                    <Check v-if="selectedPlan?.id === plan.id" :size="12" />
                                </span>
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="checkout-option-name">{{ plan.name }}</span>
                                        <span v-if="getDiscountPercent(plan) > 0" class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 uppercase tracking-wider font-mono">
                                            Save {{ getDiscountPercent(plan) }}%
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-[var(--text-muted)] block mt-0.5">
                                        Renews at {{ currency === 'INR' ? '₹' : '$' }}{{ getRenewalPrice(plan).toLocaleString(currency === 'INR' ? 'en-IN' : 'en-US') }}{{ billing === 'monthly' ? '/mo' : '/yr' }}
                                    </span>
                                </div>
                                <span class="checkout-option-price shrink-0 text-right">
                                    {{ currency === 'INR' ? `₹${billing === 'monthly' ? getMonthlyRate(plan).toLocaleString('en-IN') : getYearlyMonthlyEquivalent(plan).toLocaleString('en-IN')}` : `$${billing === 'monthly' ? getMonthlyRate(plan) : getYearlyMonthlyEquivalent(plan)}` }}
                                    <small>/mo</small>
                                </span>
                            </button>
                        </div>

                        <!-- Billing Period & Currency Controls -->
                        <div class="checkout-billing">
                            <div class="flex items-center gap-3">
                                <span>Billing period</span>
                                <div class="billing-control" role="group" aria-label="Choose a billing period">
                                    <button
                                        type="button"
                                        @click="billing = 'monthly'"
                                        :aria-pressed="billing === 'monthly'"
                                        data-testid="button-checkout-monthly"
                                    >
                                        Monthly
                                    </button>
                                    <button
                                        type="button"
                                        @click="billing = 'yearly'"
                                        :aria-pressed="billing === 'yearly'"
                                        data-testid="button-checkout-yearly"
                                    >
                                        Yearly <span class="save-label">-20%</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                <span>Currency</span>
                                <div class="billing-control" role="group" aria-label="Choose currency">
                                    <button
                                        type="button"
                                        @click="currency = 'INR'"
                                        :aria-pressed="currency === 'INR'"
                                    >
                                        INR (₹)
                                    </button>
                                    <button
                                        type="button"
                                        @click="currency = 'USD'"
                                        :aria-pressed="currency === 'USD'"
                                    >
                                        USD ($)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="checkout-price-caveat" data-testid="text-illustrative-pricing">
                            Billed in INR through Razorpay secure gateway. 100% money-back guarantee within 7 days.
                        </p>

                        <!-- Step 1 Actions -->
                        <div class="mt-7 pt-5 border-t border-[var(--edge)] flex items-center justify-between gap-4">
                            <span class="text-[11px] text-[var(--text-muted)]">Step 1 of 3 · Next: Primary domain</span>
                            <button
                                type="button"
                                class="button button-primary cursor-pointer flex items-center gap-2 ml-auto"
                                @click="goToStep(2)"
                                data-testid="button-step1-continue"
                            >
                                <span>Continue to Domain Setup</span>
                                <ArrowRight :size="15" aria-hidden="true" />
                            </button>
                        </div>
                    </section>

                    <!-- Section 02: Primary Domain Setup -->
                    <section v-show="currentStep === 2" class="checkout-section" aria-labelledby="domain-heading">
                        <div class="checkout-section-head">
                            <span class="checkout-step-number">02</span>
                            <div>
                                <h2 id="domain-heading">Primary domain setup</h2>
                                <p>Provide an existing domain or skip this step to get a temporary staging address.</p>
                            </div>
                        </div>

                        <!-- Domain Choice Selection -->
                        <fieldset class="domain-choice">
                            <legend>Choose how you want to set up your domain</legend>
                            <label :class="['domain-choice-option', { selected: domainChoice === 'have' }]">
                                <input
                                    type="radio"
                                    name="domainChoice"
                                    value="have"
                                    v-model="domainChoice"
                                    data-testid="radio-domain-have"
                                />
                                <span>
                                    <strong>Use an existing domain</strong>
                                    <small>I already own a domain and want to connect it to this managed server.</small>
                                </span>
                            </label>

                            <label :class="['domain-choice-option', { selected: domainChoice === 'later' }]">
                                <input
                                    type="radio"
                                    name="domainChoice"
                                    value="later"
                                    v-model="domainChoice"
                                    data-testid="radio-domain-later"
                                />
                                <span>
                                    <strong>Skip domain setup for now</strong>
                                    <small>We will assign a temporary staging address. You can link your custom domain at any time later.</small>
                                </span>
                            </label>
                        </fieldset>

                        <!-- Domain input when user has a domain -->
                        <div v-if="domainChoice === 'have'" class="checkout-field domain-field mt-4">
                            <label for="checkout-domain">Your domain name <span class="required-mark" aria-hidden="true">*</span></label>
                            <input
                                id="checkout-domain"
                                v-model="domain"
                                type="text"
                                autoComplete="off"
                                spellCheck="false"
                                placeholder="yourcompany.com"
                                required
                                data-testid="input-checkout-domain"
                            />
                            <span class="checkout-field-hint">Enter your root domain or subdomain (e.g. example.com or app.example.com).</span>
                            <p v-if="domainError" class="checkout-inline-error" role="alert" data-testid="error-checkout-domain">
                                {{ domainError }}
                            </p>
                        </div>

                        <!-- Info banner when skipping domain setup -->
                        <div v-else class="mt-4 p-4 rounded-lg bg-[var(--green-wash)] border border-[color-mix(in_srgb,var(--accent)_35%,transparent)] text-xs text-[var(--text-soft)] flex items-start gap-3">
                            <Check :size="16" class="text-[var(--accent)] shrink-0 mt-0.5" />
                            <div>
                                <strong class="text-[var(--text)] block mb-0.5">Temporary staging address assigned</strong>
                                <span>A unique staging hostname will be assigned to your server during provisioning. You can link your custom production domain at any time directly from the dashboard with zero downtime.</span>
                            </div>
                        </div>

                        <!-- Step 2 Actions -->
                        <div class="mt-7 pt-5 border-t border-[var(--edge)] flex items-center justify-between gap-4">
                            <button
                                type="button"
                                class="button button-outline cursor-pointer flex items-center gap-2"
                                @click="goToStep(1)"
                                data-testid="button-step2-back"
                            >
                                <ArrowLeft :size="15" aria-hidden="true" />
                                <span>Back to Plan</span>
                            </button>

                            <button
                                type="button"
                                class="button button-primary cursor-pointer flex items-center gap-2"
                                @click="goToStep(3)"
                                data-testid="button-step2-continue"
                            >
                                <span>Continue to Billing Details</span>
                                <ArrowRight :size="15" aria-hidden="true" />
                            </button>
                        </div>
                    </section>

                    <!-- Section 03: Account & Professional Billing Details (GST removed) -->
                    <section v-show="currentStep === 3" class="checkout-section" aria-labelledby="billing-heading">
                        <div class="checkout-section-head">
                            <span class="checkout-step-number">03</span>
                            <div>
                                <h2 id="billing-heading">Billing &amp; Account details</h2>
                                <p>Entered information is used for your account profile and official invoice generation.</p>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="checkout-group-heading">
                            <User :size="13" /> Account Contact
                        </div>
                        <div class="checkout-fields">
                            <div class="checkout-field">
                                <label for="checkout-name">Full name <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-name"
                                    v-model="name"
                                    type="text"
                                    autoComplete="name"
                                    placeholder="Alex Morgan"
                                    required
                                    data-testid="input-checkout-name"
                                />
                            </div>

                            <div class="checkout-field">
                                <label for="checkout-email">Work / Account email <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-email"
                                    v-model="email"
                                    type="email"
                                    autoComplete="email"
                                    placeholder="alex@yourcompany.com"
                                    required
                                    data-testid="input-checkout-email"
                                />
                            </div>

                            <div class="checkout-field checkout-field-full">
                                <label for="checkout-phone">Phone / Mobile number <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-phone"
                                    v-model="phone"
                                    type="tel"
                                    autoComplete="tel"
                                    placeholder="+91 98765 43210"
                                    required
                                    data-testid="input-checkout-phone"
                                />
                                <span class="checkout-field-hint">Required for payment security, OTP verification, and critical infrastructure notices.</span>
                            </div>
                        </div>

                        <!-- Organization (Optional) -->
                        <div class="checkout-group-heading">
                            <Building2 :size="13" /> Company Name (Optional)
                        </div>
                        <div class="checkout-fields">
                            <div class="checkout-field checkout-field-full">
                                <label for="checkout-company">Company / Organization name</label>
                                <input
                                    id="checkout-company"
                                    v-model="companyName"
                                    type="text"
                                    autoComplete="organization"
                                    placeholder="Acme Technologies Pvt Ltd"
                                    data-testid="input-checkout-company"
                                />
                                <span class="checkout-field-hint">Included on your invoice if provided.</span>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="checkout-group-heading">
                            <MapPin :size="13" /> Official Billing Address
                        </div>
                        <div class="checkout-fields">
                            <div class="checkout-field checkout-field-full">
                                <label for="checkout-address">Street address <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-address"
                                    v-model="address"
                                    type="text"
                                    autoComplete="street-address"
                                    placeholder="Flat / Suite No., Building, Street Name"
                                    required
                                    data-testid="input-checkout-address"
                                />
                            </div>
                        </div>

                        <div class="checkout-fields-3 mt-3">
                            <div class="checkout-field">
                                <label for="checkout-city">City <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-city"
                                    v-model="city"
                                    type="text"
                                    autoComplete="address-level2"
                                    placeholder="Bangalore"
                                    required
                                    data-testid="input-checkout-city"
                                />
                            </div>

                            <div class="checkout-field">
                                <label for="checkout-state">State / Province <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-state"
                                    v-model="state"
                                    type="text"
                                    autoComplete="address-level1"
                                    placeholder="Karnataka"
                                    required
                                    data-testid="input-checkout-state"
                                />
                            </div>

                            <div class="checkout-field">
                                <label for="checkout-postal">PIN / Postal code <span class="required-mark" aria-hidden="true">*</span></label>
                                <input
                                    id="checkout-postal"
                                    v-model="postalCode"
                                    type="text"
                                    autoComplete="postal-code"
                                    placeholder="560001"
                                    required
                                    data-testid="input-checkout-postal"
                                />
                            </div>
                        </div>

                        <div class="checkout-fields mt-3">
                            <div class="checkout-field checkout-field-full">
                                <label for="checkout-country">Country <span class="required-mark" aria-hidden="true">*</span></label>
                                <select
                                    id="checkout-country"
                                    v-model="country"
                                    autoComplete="country-name"
                                    required
                                    data-testid="select-checkout-country"
                                >
                                    <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 3 Actions -->
                        <div class="mt-7 pt-5 border-t border-[var(--edge)] flex items-center justify-between gap-4">
                            <button
                                type="button"
                                class="button button-outline cursor-pointer flex items-center gap-2"
                                @click="goToStep(2)"
                                data-testid="button-step3-back"
                            >
                                <ArrowLeft :size="15" aria-hidden="true" />
                                <span>Back to Domain</span>
                            </button>

                            <button
                                type="submit"
                                class="button button-primary cursor-pointer flex items-center gap-2"
                                :disabled="isProcessing"
                                data-testid="button-step3-pay"
                            >
                                <span v-if="isProcessing">Initiating Gateway...</span>
                                <span v-else class="flex items-center gap-2">
                                    Pay {{ currency === 'INR' ? `₹${getTotalPrice.toLocaleString('en-IN')}` : `$${getTotalPrice}` }} with Razorpay
                                    <ArrowRight :size="15" aria-hidden="true" />
                                </span>
                            </button>
                        </div>
                    </section>

                    <div class="checkout-privacy">
                        <ShieldCheck :size="15" aria-hidden="true" />
                        <span>256-bit encrypted checkout. Your information is protected under strict privacy terms.</span>
                    </div>
                </div>

                <!-- Right Column: Sticky Order Review Sidebar -->
                <aside class="checkout-review" aria-labelledby="review-heading">
                    <div class="review-topline">
                        <span class="eyebrow">Order review</span>
                        <span class="review-step font-mono">Step 0{{ currentStep }} / 03</span>
                    </div>

                    <h2 id="review-heading">A clear view before you continue.</h2>

                    <div class="review-line">
                        <span>Plan</span>
                        <strong data-testid="text-review-plan">{{ selectedPlan?.name ?? 'Choose a plan' }}</strong>
                    </div>

                    <div class="review-line">
                        <span>Billing</span>
                        <strong data-testid="text-review-billing">{{ billing === 'monthly' ? 'Monthly Subscription' : 'Annual Subscription' }}</strong>
                    </div>

                    <div class="review-line">
                        <span>Domain</span>
                        <strong data-testid="text-review-domain">
                            <template v-if="domainChoice === 'later'">
                                Skipped (Temporary staging host)
                            </template>
                            <template v-else-if="domain.trim()">
                                {{ domain.trim() }}
                            </template>
                            <template v-else>
                                {{ currentStep === 1 ? 'Configure in Step 2' : 'Enter in Step 2' }}
                            </template>
                        </strong>
                    </div>

                    <div v-if="name.trim() || city.trim()" class="review-line">
                        <span>Billed To</span>
                        <strong class="text-right">
                            <div>{{ name.trim() || 'Customer' }}</div>
                            <small class="text-[10px] text-gray-500 font-normal">
                                {{ [city.trim(), country.trim()].filter(Boolean).join(', ') }}
                            </small>
                        </strong>
                    </div>

                    <!-- Introductory Discount / Difference Line -->
                    <div v-if="getSavings(selectedPlan) > 0" class="review-line text-emerald-600 dark:text-emerald-400">
                        <span class="flex items-center gap-1 font-semibold">
                            <span>Introductory Discount</span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/25 font-mono">{{ getDiscountPercent(selectedPlan) }}% OFF</span>
                        </span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">
                            -{{ currency === 'INR' ? '₹' : '$' }}{{ getSavings(selectedPlan).toLocaleString(currency === 'INR' ? 'en-IN' : 'en-US') }}
                        </strong>
                    </div>

                    <!-- Small Renewal Rate Text in Sidebar -->
                    <div class="review-line">
                        <span>Subsequent Renewal</span>
                        <div class="text-right">
                            <span class="text-[11px] font-mono text-[var(--text-soft)]">
                                {{ currency === 'INR' ? '₹' : '$' }}{{ getRenewalPrice(selectedPlan).toLocaleString(currency === 'INR' ? 'en-IN' : 'en-US') }} / {{ billing === 'monthly' ? 'month' : 'year' }}
                            </span>
                            <span class="block text-[9px] text-[var(--text-muted)]">Cancel or change anytime</span>
                        </div>
                    </div>

                    <div class="review-total">
                        <span>{{ billing === 'monthly' ? 'Due today (1st month)' : 'Due today (1st year)' }}</span>
                        <strong data-testid="text-review-total">
                            {{ currency === 'INR' ? `₹${getTotalPrice.toLocaleString('en-IN')}` : `$${getTotalPrice.toLocaleString('en-US')}` }}
                            <small>{{ billing === 'monthly' ? ' / month' : ' / year' }}</small>
                        </strong>
                    </div>

                    <p v-if="billing === 'yearly' && selectedPlan" class="review-equivalent">
                        Equivalent to {{ currency === 'INR' ? `₹${getYearlyMonthlyEquivalent(selectedPlan)}` : `$${getYearlyMonthlyEquivalent(selectedPlan)}` }} per month, billed annually.
                    </p>

                    <!-- Important 2-Hour SLA Verification Notice -->
                    <div class="review-caveat">
                        <strong>⚡ 2-Hour Provisioning Window:</strong>
                        <div class="mt-1">
                            Once payment is complete, your hosting account enters verification state. Our engineers will provision your environment on our server cluster and provide your credentials within max 2 hours.
                        </div>
                    </div>

                    <!-- Sidebar Navigation / Payment Button -->
                    <template v-if="currentStep === 1">
                        <button
                            class="button button-primary checkout-submit cursor-pointer"
                            type="button"
                            @click="goToStep(2)"
                            data-testid="button-sidebar-step1-continue"
                        >
                            <span class="flex items-center justify-center gap-2">
                                Continue to Domain Setup
                                <ArrowRight :size="15" aria-hidden="true" />
                            </span>
                        </button>
                    </template>
                    <template v-else-if="currentStep === 2">
                        <button
                            class="button button-primary checkout-submit cursor-pointer"
                            type="button"
                            @click="goToStep(3)"
                            data-testid="button-sidebar-step2-continue"
                        >
                            <span class="flex items-center justify-center gap-2">
                                Continue to Billing Details
                                <ArrowRight :size="15" aria-hidden="true" />
                            </span>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            class="button button-primary checkout-submit cursor-pointer"
                            type="submit"
                            :disabled="isProcessing"
                            data-testid="button-checkout-continue"
                        >
                            <span v-if="isProcessing">Initiating Gateway...</span>
                            <span v-else class="flex items-center justify-center gap-2">
                                Pay {{ currency === 'INR' ? `₹${getTotalPrice.toLocaleString('en-IN')}` : `$${getTotalPrice}` }} with Razorpay
                                <ArrowRight :size="15" aria-hidden="true" />
                            </span>
                        </button>
                    </template>

                    <div v-if="currency === 'USD'" class="text-[10px] text-[var(--text-muted)] text-center mt-2">
                        Processed as ₹{{ getRazorpayChargeINR.toLocaleString('en-IN') }} via Razorpay
                    </div>

                    <div v-if="errorMessage" class="checkout-notice" role="alert">
                        {{ errorMessage }}
                    </div>

                    <!-- Trust indicators -->
                    <div class="payment-status">
                        <span class="payment-status-indicator" style="background: var(--accent);" />
                        <div>
                            <strong>Instant Order Processing</strong>
                            <p>Powered by Razorpay. Cards, UPI, NetBanking, and Wallets accepted.</p>
                        </div>
                    </div>

                    <div class="checkout-help">
                        <CircleHelp :size="14" aria-hidden="true" />
                        <span>Questions before you start? <a href="mailto:support@vmcore.in?subject=Question%20about%20Roook%20Hosting%20Setup" data-testid="link-checkout-support">Talk with an engineer</a></span>
                    </div>
                </aside>
            </form>

            <footer class="checkout-footer">
                <span>© {{ new Date().getFullYear() }} Roook Hosting</span>
                <span>Careful by default. Human when it counts.</span>
            </footer>
        </div>
    </main>
</template>
