<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    licenses: Array
});

const form = useForm({
    status: '',
    expires_at: '',
});

const generateForm = useForm({
    email: '',
    plan: 'free',
    expires_at: '',
});

const editingLicense = ref(null);
const generatingLicense = ref(false);

const generateLicense = () => {
    generateForm.post(route('admin.licenses.generate'), {
        onSuccess: () => {
            generatingLicense.value = false;
            generateForm.reset();
        }
    });
};

const editLicense = (license) => {
    editingLicense.value = license;
    form.status = license.status;
    form.expires_at = license.expires_at ? new Date(license.expires_at).toISOString().split('T')[0] : '';
};

const updateLicense = () => {
    form.patch(route('admin.licenses.update', editingLicense.value.id), {
        onSuccess: () => editingLicense.value = null,
    });
};

const deleteLicense = (id) => {
    if (confirm('Are you sure you want to delete this license?')) {
        form.delete(route('admin.licenses.destroy', id));
    }
};
</script>

<template>
    <Head title="Manage Licenses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">
                        Licenses
                    </h2>
                    <p class="text-xs text-slate-400 mt-1">Oversee and manage all issued VMCORE licenses.</p>
                </div>
                <button 
                    @click="generatingLicense = true" 
                    class="bg-emerald-500 hover:bg-emerald-450 text-white px-4 py-2.5 rounded-lg text-xs font-semibold tracking-wide uppercase transition-all shadow-sm flex items-center gap-2"
                >
                    <span class="material-symbols-rounded text-sm">add</span>
                    Generate License
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-800 border border-slate-700 rounded-lg overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-700 text-slate-400">
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">License Key</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Installation</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Panel Admin</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider">Expires</th>
                                <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/80">
                            <tr v-for="license in licenses" :key="license.id" class="hover:bg-slate-900/40 transition-colors">
                                <td class="px-6 py-4.5">
                                    <div class="text-sm font-bold text-white">{{ license.user.name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ license.user.email }}</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <code class="text-xs text-emerald-400 font-mono bg-slate-900 px-2 py-1 border border-slate-700 rounded">{{ license.license_key }}</code>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div v-if="license.domain" class="text-xs font-bold text-white">{{ license.domain }}</div>
                                    <div v-if="license.server_ip" class="text-[10px] text-slate-400 font-mono mt-0.5">IP: {{ license.server_ip }}</div>
                                    <div v-if="!license.domain && !license.server_ip" class="text-xs text-slate-500 italic">Not activated yet</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <div v-if="license.admin_name" class="text-sm font-bold text-white">{{ license.admin_name }}</div>
                                    <div v-if="license.admin_email" class="text-xs text-slate-400 mt-0.5">{{ license.admin_email }}</div>
                                    <div v-if="!license.admin_name && !license.admin_email" class="text-xs text-slate-500 italic">No admin registered</div>
                                </td>
                                <td class="px-6 py-4.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset" 
                                        :class="{
                                            'bg-slate-700/50 text-slate-300 ring-slate-650': license.plan === 'free',
                                            'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20': license.plan === 'pro',
                                            'bg-purple-500/10 text-purple-400 ring-purple-500/20': license.plan === 'enterprise'
                                        }">
                                        {{ license.plan }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                        :class="{
                                            'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20': license.status === 'active',
                                            'bg-amber-500/10 text-amber-400 ring-amber-500/20': license.status === 'suspended',
                                            'bg-red-500/10 text-red-400 ring-red-500/20': license.status === 'expired' || license.status === 'revoked'
                                        }">
                                        {{ license.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 text-xs text-slate-400 font-medium">
                                    {{ license.expires_at || 'Never' }}
                                </td>
                                <td class="px-6 py-4.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button 
                                            @click="editLicense(license)" 
                                            class="text-slate-400 hover:text-white hover:bg-slate-700 p-1.5 rounded transition-colors"
                                            title="Edit License"
                                        >
                                            <span class="material-symbols-rounded text-sm">edit</span>
                                        </button>
                                        <button 
                                            @click="deleteLicense(license.id)" 
                                            class="text-slate-400 hover:text-red-400 hover:bg-red-500/5 p-1.5 rounded transition-colors"
                                            title="Delete License"
                                        >
                                            <span class="material-symbols-rounded text-sm">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal Dialog (Breeze style) -->
        <div v-if="editingLicense" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-8 w-full max-w-md shadow-2xl animate-fade-in relative text-slate-105">
                <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
                    <h3 class="text-lg font-bold text-white">Edit License</h3>
                    <button @click="editingLicense = null" class="text-slate-400 hover:text-slate-200">
                        <span class="material-symbols-rounded text-sm">close</span>
                    </button>
                </div>
                
                <form @submit.prevent="updateLicense" class="space-y-5">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Status</label>
                        <select v-model="form.status" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-sm text-slate-200 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Expiry Date</label>
                        <input type="date" v-model="form.expires_at" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-sm text-slate-200 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" />
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-700">
                        <button type="button" @click="editingLicense = null" class="flex-1 px-4 py-2 border border-slate-700 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-slate-700 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-colors shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generate Modal Dialog (Breeze style) -->
        <div v-if="generatingLicense" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-slate-800 border border-slate-700 rounded-lg p-8 w-full max-w-md shadow-2xl animate-fade-in relative text-slate-105">
                <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
                    <h3 class="text-lg font-bold text-white">Generate License</h3>
                    <button @click="generatingLicense = false; generateForm.reset()" class="text-slate-400 hover:text-slate-200">
                        <span class="material-symbols-rounded text-sm">close</span>
                    </button>
                </div>
                
                <form @submit.prevent="generateLicense" class="space-y-5">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">User Email</label>
                        <input type="email" v-model="generateForm.email" required placeholder="user@example.com" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-sm text-slate-200 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" />
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Plan</label>
                        <select v-model="generateForm.plan" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-sm text-slate-200 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Expiry Date (Optional)</label>
                        <input type="date" v-model="generateForm.expires_at" class="w-full bg-slate-900 border border-slate-700 rounded-lg text-sm text-slate-200 p-3 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" />
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-700">
                        <button type="button" @click="generatingLicense = false; generateForm.reset()" class="flex-1 px-4 py-2 border border-slate-700 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold uppercase tracking-wider hover:bg-slate-700 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-emerald-500 hover:bg-emerald-450 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition-colors shadow-sm" :disabled="generateForm.processing">
                            {{ generateForm.processing ? 'Generating...' : 'Generate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
