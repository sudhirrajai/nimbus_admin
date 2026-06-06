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
