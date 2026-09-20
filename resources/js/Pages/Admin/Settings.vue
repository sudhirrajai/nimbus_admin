<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object
});

const form = useForm({
    site_name: props.settings.site_name,
    allow_registration: props.settings.allow_registration,
    maintenance_mode: props.settings.maintenance_mode,
    free_license_limit: props.settings.free_license_limit,
    license_expiry_days: props.settings.license_expiry_days,
    razorpay_enabled: props.settings.razorpay_enabled,
    razorpay_mode: props.settings.razorpay_mode,
    company_name: props.settings.company_name || 'VMCore Technologies Pvt. Ltd.',
    company_address_line1: props.settings.company_address_line1 || '#104, Tech Park Boulevard',
    company_address_line2: props.settings.company_address_line2 || 'Indiranagar, Bangalore, Karnataka - 560038, India',
    company_gstin: props.settings.company_gstin || '29AADCV1234F1Z5',
    company_pan: props.settings.company_pan || 'AADCV1234F',
    company_email: props.settings.company_email || 'billing@vmcore.in',
    company_phone: props.settings.company_phone || '+91 (0) 80-4567-8900',
    company_website: props.settings.company_website || 'https://nimbus.vmcore.in',
    bank_name: props.settings.bank_name || 'HDFC Bank Ltd.',
    bank_account: props.settings.bank_account || '50200088991122',
    bank_ifsc: props.settings.bank_ifsc || 'HDFC0001234',
    bank_branch: props.settings.bank_branch || 'Indiranagar Branch, Bangalore',
    bank_upi: props.settings.bank_upi || 'vmcore@hdfcbank',
    invoice_terms: props.settings.invoice_terms || '',
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    System Settings
                </h2>
                <p class="text-xs text-gray-500 mt-1">Configure global application behaviors, license distribution rules, and billing modes.</p>
            </div>
        </template>

        <div class="max-w-4xl space-y-6">
            <!-- Success/Error Alert -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.error || $page.props.flash?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.error || $page.props.flash?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p class="text-xs font-medium">{{ $page.props.errors?.error || $page.props.flash?.error }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- System Configuration Block -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-lg">settings</span>
                            Platform Settings
                        </h3>
                        <p class="text-xs text-gray-550 mt-1">General branding and user access controls.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="site_name" class="block text-xs font-semibold text-gray-700 mb-2">Platform Name</label>
                            <input 
                                id="site_name"
                                type="text"
                                v-model="form.site_name"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                required
                            />
                            <div v-if="form.errors.site_name" class="text-xs text-red-500 mt-1">{{ form.errors.site_name }}</div>
                        </div>
                    </div>

                    <div class="pt-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xs font-semibold text-gray-900">Allow Registration</span>
                                <span class="text-[10px] text-gray-500">Enable/disable new customer sign-ups.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.allow_registration" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <div>
                                <span class="block text-xs font-semibold text-gray-900">Maintenance Mode</span>
                                <span class="text-[10px] text-gray-500">Put the front-facing website offline for standard maintenance.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.maintenance_mode" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- License Rules Configuration Block -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-lg">vpn_key</span>
                            License Policy Settings
                        </h3>
                        <p class="text-xs text-gray-550 mt-1">Configure allocation caps and durations for credentials.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="free_license_limit" class="block text-xs font-semibold text-gray-700 mb-2">Free Licenses Per User</label>
                            <input 
                                id="free_license_limit"
                                type="number"
                                v-model="form.free_license_limit"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                required
                            />
                            <div v-if="form.errors.free_license_limit" class="text-xs text-red-500 mt-1">{{ form.errors.free_license_limit }}</div>
                        </div>

                        <div>
                            <label for="license_expiry_days" class="block text-xs font-semibold text-gray-700 mb-2">Free License Duration (Days)</label>
                            <input 
                                id="license_expiry_days"
                                type="number"
                                v-model="form.license_expiry_days"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                required
                            />
                            <div v-if="form.errors.license_expiry_days" class="text-xs text-red-500 mt-1">{{ form.errors.license_expiry_days }}</div>
                        </div>
                    </div>
                </div>

                <!-- Billing Gateway Configuration Block -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-lg">payments</span>
                            Billing & Gateway Options
                        </h3>
                        <p class="text-xs text-gray-550 mt-1">Manage Razorpay integration parameters.</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">Enable Billing Integration</span>
                            <span class="text-[10px] text-gray-500">Allow users to pay for premium upgrades on the dashboard.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.razorpay_enabled" class="sr-only peer" />
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div v-if="form.razorpay_enabled" class="grid grid-cols-1 gap-6 md:grid-cols-2 pt-2 animate-fade-in">
                        <div>
                            <label for="razorpay_mode" class="block text-xs font-semibold text-gray-700 mb-2">Integration Mode</label>
                            <select 
                                id="razorpay_mode"
                                v-model="form.razorpay_mode"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                            >
                                <option value="sandbox">Sandbox (Testing)</option>
                                <option value="live">Live (Production)</option>
                            </select>
                            <div v-if="form.errors.razorpay_mode" class="text-xs text-red-500 mt-1">{{ form.errors.razorpay_mode }}</div>
                        </div>
                    </div>
                </div>

                <!-- Company & Tax Invoice Settings Block -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-600 text-lg">receipt_long</span>
                            Company & Tax Invoice Settings
                        </h3>
                        <p class="text-xs text-gray-550 mt-1">Configure official letterhead, GSTIN, and company banking details printed on customer invoices.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="company_name" class="block text-xs font-semibold text-gray-700 mb-2">Legal Company / Entity Name</label>
                            <input 
                                id="company_name"
                                type="text"
                                v-model="form.company_name"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="e.g. VMCore Technologies Pvt. Ltd."
                            />
                        </div>

                        <div>
                            <label for="company_gstin" class="block text-xs font-semibold text-gray-700 mb-2">GSTIN / Tax Registration No.</label>
                            <input 
                                id="company_gstin"
                                type="text"
                                v-model="form.company_gstin"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all uppercase"
                                placeholder="e.g. 29AADCV1234F1Z5"
                            />
                        </div>

                        <div>
                            <label for="company_pan" class="block text-xs font-semibold text-gray-700 mb-2">PAN / Corporate Tax ID</label>
                            <input 
                                id="company_pan"
                                type="text"
                                v-model="form.company_pan"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all uppercase"
                                placeholder="e.g. AADCV1234F"
                            />
                        </div>

                        <div>
                            <label for="company_email" class="block text-xs font-semibold text-gray-700 mb-2">Billing Support Email</label>
                            <input 
                                id="company_email"
                                type="email"
                                v-model="form.company_email"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="billing@vmcore.in"
                            />
                        </div>

                        <div>
                            <label for="company_phone" class="block text-xs font-semibold text-gray-700 mb-2">Billing Phone / Hotline</label>
                            <input 
                                id="company_phone"
                                type="text"
                                v-model="form.company_phone"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="+91 80 4567 8900"
                            />
                        </div>

                        <div>
                            <label for="company_website" class="block text-xs font-semibold text-gray-700 mb-2">Website URL</label>
                            <input 
                                id="company_website"
                                type="text"
                                v-model="form.company_website"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="https://nimbus.vmcore.in"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label for="company_address_line1" class="block text-xs font-semibold text-gray-700 mb-2">Address Line 1</label>
                            <input 
                                id="company_address_line1"
                                type="text"
                                v-model="form.company_address_line1"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="#104, Tech Park Boulevard"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label for="company_address_line2" class="block text-xs font-semibold text-gray-700 mb-2">Address Line 2 (City, State, Pin, Country)</label>
                            <input 
                                id="company_address_line2"
                                type="text"
                                v-model="form.company_address_line2"
                                class="w-full text-sm border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition-all"
                                placeholder="Indiranagar, Bangalore, Karnataka - 560038, India"
                            />
                        </div>
                    </div>

                    <!-- Bank Details Sub-section -->
                    <div class="border-t border-gray-100 pt-5 space-y-4">
                        <div class="text-xs font-bold text-gray-800 uppercase tracking-wider">Bank Transfer & UPI Details (For Invoices)</div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Bank Name</label>
                                <input type="text" v-model="form.bank_name" class="w-full text-xs rounded-lg border-gray-200" placeholder="HDFC Bank" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Account Number</label>
                                <input type="text" v-model="form.bank_account" class="w-full text-xs rounded-lg border-gray-200" placeholder="50200088991122" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">IFSC Code</label>
                                <input type="text" v-model="form.bank_ifsc" class="w-full text-xs rounded-lg border-gray-200 uppercase" placeholder="HDFC0001234" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Branch</label>
                                <input type="text" v-model="form.bank_branch" class="w-full text-xs rounded-lg border-gray-200" placeholder="Indiranagar, Bangalore" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">UPI ID</label>
                                <input type="text" v-model="form.bank_upi" class="w-full text-xs rounded-lg border-gray-200" placeholder="vmcore@hdfcbank" />
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions Note -->
                    <div class="border-t border-gray-100 pt-5">
                        <label for="invoice_terms" class="block text-xs font-semibold text-gray-700 mb-2">Invoice Terms & Legal Declaration</label>
                        <textarea 
                            id="invoice_terms"
                            v-model="form.invoice_terms"
                            rows="3"
                            class="w-full text-xs border-gray-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm font-sans"
                            placeholder="Standard payment and electronic document terms..."
                        ></textarea>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="flex justify-end pt-2">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white font-bold px-6 py-2.5 rounded-lg text-xs uppercase tracking-wider transition-all shadow-sm flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="material-symbols-rounded animate-spin text-sm">cached</span>
                        <span v-else class="material-symbols-rounded text-sm">save</span>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
