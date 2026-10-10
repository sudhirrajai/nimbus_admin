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

// Fallback plans if none found in database
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
        renewal_price_inr: 3800,
        renewal_price_usd: 49,
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
        renewal_price_inr: 7990,
        renewal_price_usd: 99,
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

// Customer & Professional Billing Address State
const name = ref(props.user?.name || '');
const email = ref(props.user?.email || '');
const phone = ref(props.user?.phone || '');
const companyName = ref(props.user?.company_name || '');
const address = ref(props.user?.address || '');
const city = ref(props.user?.city || '');
const state = ref(props.user?.state || '');
const postalCode = ref(props.user?.postal_code || '');
const country = ref(props.user?.country || 'India');
const taxId = ref('');

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
        return plan.monthly_price_inr || Math.round(Number(plan.price_inr) / 10);
    }
    return plan.monthly_price_usd || Math.round(Number(plan.price_usd) / 10);
};

const getYearlyMonthlyEquivalent = (plan) => {
    if (!plan) return 0;
    if (currency.value === 'INR') {
        return Math.round(Number(plan.price_inr) / 12);
    }
    return Math.round(Number(plan.price_usd) / 12);
};

const getTotalPrice = computed(() => {
    if (!selectedPlan.value) return 0;
    const plan = selectedPlan.value;
    if (billing.value === 'monthly') {
        return getMonthlyRate(plan);
    }
    return currency.value === 'INR' ? Number(plan.price_inr) : Number(plan.price_usd);
});

const getRazorpayChargeINR = computed(() => {
    if (!selectedPlan.value) return 0;
    const plan = selectedPlan.value;
    if (billing.value === 'monthly') {
        return plan.monthly_price_inr || Math.round(Number(plan.price_inr) / 10);
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
        errorMessage.value = 'Please select a hosting plan to continue.';
        return;
    }

    if (domainChoice.value === 'have') {
        if (!domain.value || !validDomain(domain.value)) {
            domainError.value = 'Please enter a valid domain name, such as yourcompany.com.';
            return;
        }
    }

    if (!name.value || !name.value.trim()) {
        errorMessage.value = 'Please enter your full name.';
        return;
    }

    if (!email.value || !email.value.trim() || !email.value.includes('@')) {
        errorMessage.value = 'Please enter a valid work or account email.';
        return;
    }

    if (!phone.value || !phone.value.trim() || phone.value.trim().length < 6) {
        errorMessage.value = 'Please enter a valid phone or mobile number for account verification & alerts.';
        return;
    }

    if (!address.value || !address.value.trim()) {
        errorMessage.value = 'Please enter your street address (flat, building, road).';
        return;
    }

    if (!city.value || !city.value.trim()) {
        errorMessage.value = 'Please enter your city.';
        return;
    }

    if (!state.value || !state.value.trim()) {
        errorMessage.value = 'Please enter your state or province.';
        return;
    }

    if (!postalCode.value || !postalCode.value.trim()) {
        errorMessage.value = 'Please enter your postal / PIN code.';
        return;
    }

    if (!country.value || !country.value.trim()) {
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
            tax_id: taxId.value.trim(),
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
                    tax_id: taxId.value.trim(),
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
                <li class="is-current">
                    <span>01</span>
                    <strong>Hosting Plan</strong>
                </li>
                <li class="is-current">
                    <span>02</span>
                    <strong>Domain Setup</strong>
                </li>
                <li class="is-current">
                    <span>03</span>
                    <strong>Billing &amp; Tax Info</strong>
                </li>
            </ol>

            <form class="checkout-layout" @submit.prevent="handleCheckoutSubmit" noValidate data-testid="form-checkout">
                <!-- Left Column: Setup Fields -->
                <div class="checkout-form-column">
                    <!-- Section 01: Choose your plan -->
                    <section class="checkout-section" aria-labelledby="plan-heading">
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
                                <span class="checkout-option-name">{{ plan.name }}</span>
                                <span class="checkout-option-price">
                                    {{ currency === 'INR' ? `₹${billing === 'monthly' ? getMonthlyRate(plan) : getYearlyMonthlyEquivalent(plan)}` : `$${billing === 'monthly' ? getMonthlyRate(plan) : getYearlyMonthlyEquivalent(plan)}` }}
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
                    </section>

                    <!-- Section 02: Primary Domain Setup -->
                    <section class="checkout-section" aria-labelledby="domain-heading">
                        <div class="checkout-section-head">
                            <span class="checkout-step-number">02</span>
                            <div>
                                <h2 id="domain-heading">Primary domain setup</h2>
                                <p>Provide the website address you want provisioned on this managed server.</p>
                            </div>
                        </div>

                        <!-- Domain Choice Selection -->
                        <fieldset class="domain-choice">
                            <legend>Domain choice</legend>
                            <label :class="['domain-choice-option', { selected: domainChoice === 'have' }]">
                                <input
                                    type="radio"
                                    name="domainChoice"
                                    value="have"
                                    v-model="domainChoice"
                                    data-testid="radio-domain-have"
                                />
                                <span>
                                    <strong>I have a domain</strong>
                                    <small>Tell us the domain you’d like to host.</small>
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
                                    <strong>I’ll add it later</strong>
                                    <small>We will assign a temporary staging address and help you configure DNS later.</small>
                                </span>
                            </label>
                        </fieldset>

                        <!-- Domain input when user has a domain -->
                        <div v-if="domainChoice === 'have'" class="checkout-field domain-field">
                            <label for="checkout-domain">Domain name <span class="required-mark" aria-hidden="true">*</span></label>
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
                            <p v-if="domainError" class="checkout-inline-error" role="alert" data-testid="error-checkout-domain">
                                {{ domainError }}
                            </p>
                        </div>
                    </section>

                    <!-- Section 03: Account & Professional Billing Details -->
                    <section class="checkout-section" aria-labelledby="billing-heading">
                        <div class="checkout-section-head">
                            <span class="checkout-step-number">03</span>
                            <div>
                                <h2 id="billing-heading">Billing &amp; Tax information</h2>
                                <p>Entered information is used for compliant tax invoices and your official client letterhead.</p>
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

                        <!-- Organization & Tax Details (Optional) -->
                        <div class="checkout-group-heading">
                            <Building2 :size="13" /> Company &amp; Tax (Optional)
                        </div>
                        <div class="checkout-fields">
                            <div class="checkout-field">
                                <label for="checkout-company">Company / Organization name</label>
                                <input
                                    id="checkout-company"
                                    v-model="companyName"
                                    type="text"
                                    autoComplete="organization"
                                    placeholder="Acme Technologies Pvt Ltd"
                                    data-testid="input-checkout-company"
                                />
                                <span class="checkout-field-hint">Printed on your tax invoice if provided.</span>
                            </div>

                            <div class="checkout-field">
                                <label for="checkout-tax-id">GSTIN / Tax ID</label>
                                <input
                                    id="checkout-tax-id"
                                    v-model="taxId"
                                    type="text"
                                    placeholder="e.g. 29ABCDE1234F1Z5"
                                    data-testid="input-checkout-tax-id"
                                />
                                <span class="checkout-field-hint">For B2B input tax credit on your official GST receipt.</span>
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
                        <span class="review-step">02 / 02</span>
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
                            {{ domainChoice === 'later' ? 'Add later (temporary hostname)' : (domain.trim() || 'Not entered yet') }}
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

                    <div class="review-total">
                        <span>{{ billing === 'monthly' ? 'Monthly total' : 'Annual total' }}</span>
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

                    <!-- Payment Button -->
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
