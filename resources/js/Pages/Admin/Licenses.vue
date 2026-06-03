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
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white">
                        Admin Panel: Licenses
                    </h2>
                    <p class="text-slate-400 mt-2">Oversee and manage all issued VMCORE licenses.</p>
                </div>
                <button @click="generatingLicense = true" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all hover:shadow-lg hover:shadow-indigo-500/25 flex items-center gap-2 premium-gradient">
                    <span class="material-symbols-rounded">add</span>
                    Generate License
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="glass-morphism rounded-3xl overflow-hidden border border-white/5">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/10">
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">User</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">License Key</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Installation</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Panel Admin</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Plan</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Status</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Expires</th>
                            <th class="p-6 text-xs font-bold uppercase tracking-widest text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="license in licenses" :key="license.id" class="hover:bg-white/5 transition-colors">
                            <td class="p-6">
                                <div class="text-sm font-bold text-white">{{ license.user.name }}</div>
                                <div class="text-xs text-slate-500">{{ license.user.email }}</div>
                            </td>
                            <td class="p-6">
                                <code class="text-xs text-indigo-300 font-mono">{{ license.license_key }}</code>
                            </td>
                            <td class="p-6">
                                <div v-if="license.domain" class="text-sm font-bold text-indigo-200">{{ license.domain }}</div>
                                <div v-if="license.server_ip" class="text-xs text-slate-400 font-mono">IP: {{ license.server_ip }}</div>
                                <div v-if="!license.domain && !license.server_ip" class="text-xs text-slate-500 italic">Not activated yet</div>
                            </td>
                            <td class="p-6">
                                <div v-if="license.admin_name" class="text-sm font-bold text-white">{{ license.admin_name }}</div>
                                <div v-if="license.admin_email" class="text-xs text-slate-400">{{ license.admin_email }}</div>
                                <div v-if="!license.admin_name && !license.admin_email" class="text-xs text-slate-500 italic">No admin registered</div>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter" 
                                    :class="{
                                        'bg-slate-500/10 text-slate-400': license.plan === 'free',
                                        'bg-indigo-500/10 text-indigo-400': license.plan === 'pro',
                                        'bg-purple-500/10 text-purple-400': license.plan === 'enterprise'
                                    }">
                                    {{ license.plan }}
                                </span>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter"
                                    :class="license.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                                    {{ license.status }}
                                </span>
                            </td>
                            <td class="p-6 text-sm text-slate-400">
                                {{ license.expires_at || 'Never' }}
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-3">
                                    <button @click="editLicense(license)" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                                        <span class="material-symbols-rounded">edit</span>
                                    </button>
                                    <button @click="deleteLicense(license.id)" class="text-red-400 hover:text-red-300 transition-colors">
                                        <span class="material-symbols-rounded">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal (Simple overlay for now) -->
        <div v-if="editingLicense" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="glass-morphism rounded-[2.5rem] p-10 w-full max-w-md border border-white/10 animate-fade-in">
                <h3 class="text-2xl font-bold text-white mb-6">Edit License</h3>
                
                <form @submit.prevent="updateLicense" class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Status</label>
                        <select v-model="form.status" class="w-full bg-slate-900 border border-white/10 rounded-xl text-white p-4 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Expiry Date</label>
                        <input type="date" v-model="form.expires_at" class="w-full bg-slate-900 border border-white/10 rounded-xl text-white p-4 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="editingLicense = null" class="flex-1 px-6 py-4 rounded-xl border border-white/10 text-white hover:bg-white/5 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 px-6 py-4 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition-all premium-gradient shadow-lg shadow-indigo-600/20">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generate Modal -->
        <div v-if="generatingLicense" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="glass-morphism rounded-[2.5rem] p-10 w-full max-w-md border border-white/10 animate-fade-in">
                <h3 class="text-2xl font-bold text-white mb-6">Generate License</h3>
                
                <form @submit.prevent="generateLicense" class="space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">User Email</label>
                        <input type="email" v-model="generateForm.email" required placeholder="user@example.com" class="w-full bg-slate-900 border border-white/10 rounded-xl text-white p-4 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Plan</label>
                        <select v-model="generateForm.plan" class="w-full bg-slate-900 border border-white/10 rounded-xl text-white p-4 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Expiry Date (Optional)</label>
                        <input type="date" v-model="generateForm.expires_at" class="w-full bg-slate-900 border border-white/10 rounded-xl text-white p-4 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" @click="generatingLicense = false; generateForm.reset()" class="flex-1 px-6 py-4 rounded-xl border border-white/10 text-white hover:bg-white/5 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 px-6 py-4 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition-all premium-gradient shadow-lg shadow-indigo-600/20" :disabled="generateForm.processing">
                            {{ generateForm.processing ? 'Generating...' : 'Generate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
