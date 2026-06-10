<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    release: Object,
    urls: Object,
});

const fileInput = ref(null);
const copiedField = ref(null);

const form = useForm({
    release_file: null,
});

const handleFileChange = (e) => {
    form.release_file = e.target.files[0];
};

const submitUpload = () => {
    if (!form.release_file) return;

    form.post(route('admin.releases.upload'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const deleteRelease = () => {
    if (confirm('Are you sure you want to delete this release package? Remote servers will not be able to download/install this version until a new release is uploaded.')) {
        router.delete(route('admin.releases.destroy'));
    }
};

const copyToClipboard = (text, fieldName) => {
    navigator.clipboard.writeText(text).then(() => {
        copiedField.value = fieldName;
        setTimeout(() => {
            if (copiedField.value === fieldName) {
                copiedField.value = null;
            }
        }, 2000);
    });
};
</script>

<template>
    <Head title="Nimbus Releases" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    Nimbus Release Management
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Upload new Nimbus builds and copy installer urls for client servers.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Success/Error Alert -->
            <div v-if="$page.props.flash?.success || $page.props.errors?.release_file || $page.props.errors?.error" class="animate-fade-in">
                <div v-if="$page.props.flash?.success" class="flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.errors?.release_file || $page.props.errors?.error" class="flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200 text-red-750">
                    <span class="material-symbols-rounded text-lg">error</span>
                    <p class="text-xs font-medium">{{ $page.props.errors?.release_file || $page.props.errors?.error }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left/Middle Panel: Release Status & Installation Instructions -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Active Release Status Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-150">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 flex items-center gap-2">
                                <span class="material-symbols-rounded text-emerald-500">package</span>
                                Active Release
                            </h3>
                            <div v-if="release" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Live
                            </div>
                            <div v-else class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 border border-amber-200 text-amber-700">
                                No Release Uploaded
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div v-if="release" class="grid grid-cols-2 md:grid-cols-4 gap-6 py-2">
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Version</span>
                                <span class="text-sm font-extrabold text-gray-950 font-mono bg-slate-50 px-2 py-0.5 rounded border border-gray-200 inline-block">{{ release.version }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-1">ZIP Size</span>
                                <span class="text-sm font-bold text-gray-800">{{ release.size }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-1">Uploaded On</span>
                                <span class="text-sm font-bold text-gray-800">{{ release.uploaded_at }}</span>
                            </div>
                        </div>

                        <!-- Empty State message -->
                        <div v-else class="text-center py-8">
                            <span class="material-symbols-rounded text-4xl text-gray-300 mb-2">cloud_off</span>
                            <p class="text-sm font-semibold text-gray-650">No release zip package found</p>
                            <p class="text-xs text-gray-450 mt-0.5">Upload a nimbus.zip in the side panel to deploy the installer</p>
                        </div>
                    </div>

                    <!-- Client Installation Guide Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-rounded text-emerald-500">terminal</span>
                            Client Setup Commands
                        </h3>

                        <!-- Command 1: Installer -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block">Nimbus Installer Command</label>
                            <div class="relative flex items-center bg-slate-900 text-slate-100 rounded-lg overflow-hidden border border-slate-800">
                                <code class="text-xs font-mono px-4 py-3.5 pr-20 overflow-x-auto whitespace-nowrap w-full">
                                    curl -sSL <span class="text-emerald-400">{{ urls.install }}</span> | sudo bash
                                </code>
                                <button 
                                    @click="copyToClipboard(`curl -sSL ${urls.install} | sudo bash`, 'install')"
                                    class="absolute right-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white rounded px-2.5 py-1.5 text-xs font-bold transition-all shadow-md flex items-center gap-1"
                                >
                                    <span class="material-symbols-rounded text-sm">
                                        {{ copiedField === 'install' ? 'check' : 'content_copy' }}
                                    </span>
                                    <span>{{ copiedField === 'install' ? 'Copied' : 'Copy' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Command 2: Uninstaller -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block">Nimbus Uninstaller Command</label>
                            <div class="relative flex items-center bg-slate-900 text-slate-100 rounded-lg overflow-hidden border border-slate-800">
                                <code class="text-xs font-mono px-4 py-3.5 pr-20 overflow-x-auto whitespace-nowrap w-full">
                                    curl -sSL <span class="text-emerald-400">{{ urls.uninstall }}</span> | sudo bash
                                </code>
                                <button 
                                    @click="copyToClipboard(`curl -sSL ${urls.uninstall} | sudo bash`, 'uninstall')"
                                    class="absolute right-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white rounded px-2.5 py-1.5 text-xs font-bold transition-all shadow-md flex items-center gap-1"
                                >
                                    <span class="material-symbols-rounded text-sm">
                                        {{ copiedField === 'uninstall' ? 'check' : 'content_copy' }}
                                    </span>
                                    <span>{{ copiedField === 'uninstall' ? 'Copied' : 'Copy' }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Command 3: Direct Zip Download -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block">ZIP Archive Download URL</label>
                            <div class="relative flex items-center bg-slate-900 text-slate-100 rounded-lg overflow-hidden border border-slate-800">
                                <code class="text-xs font-mono px-4 py-3.5 pr-20 overflow-x-auto whitespace-nowrap w-full text-emerald-400">
                                    {{ urls.download }}
                                </code>
                                <button 
                                    @click="copyToClipboard(urls.download, 'download')"
                                    class="absolute right-2 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white rounded px-2.5 py-1.5 text-xs font-bold transition-all shadow-md flex items-center gap-1"
                                >
                                    <span class="material-symbols-rounded text-sm">
                                        {{ copiedField === 'download' ? 'check' : 'content_copy' }}
                                    </span>
                                    <span>{{ copiedField === 'download' ? 'Copied' : 'Copy' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Upload Form -->
                <div class="space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 flex items-center gap-2 mb-4">
                            <span class="material-symbols-rounded text-emerald-500">cloud_upload</span>
                            Publish Build
                        </h3>

                        <form @submit.prevent="submitUpload" class="space-y-5">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-2">
                                    Upload nimbus.zip
                                </label>
                                <div class="border-2 border-dashed border-gray-200 hover:border-emerald-400 rounded-lg p-5 text-center transition-all bg-slate-50 cursor-pointer relative">
                                    <input 
                                        type="file" 
                                        ref="fileInput"
                                        @change="handleFileChange"
                                        accept=".zip" 
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <span class="material-symbols-rounded text-3xl text-gray-400 mb-1.5">upload_file</span>
                                    <p class="text-xs font-bold text-gray-700">
                                        {{ form.release_file ? form.release_file.name : 'Select or drag ZIP package' }}
                                    </p>
                                    <p v-if="!form.release_file" class="text-[10px] text-gray-400 mt-0.5">
                                        ZIP archive must contain install.sh and uninstall.sh at the root level
                                    </p>
                                    <p v-else class="text-[10px] text-emerald-600 mt-0.5 font-semibold">
                                        Size: {{ (form.release_file.size / 1024 / 1024).toFixed(2) }} MB
                                    </p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div v-if="form.progress" class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-1.5 transition-all duration-300" :style="{ width: `${form.progress.percentage}%` }"></div>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="!form.release_file || form.processing"
                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs uppercase tracking-wider py-3 px-4 rounded-lg shadow-md hover:shadow-emerald-500/10 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="material-symbols-rounded text-base animate-pulse" v-if="form.processing">sync</span>
                                <span class="material-symbols-rounded text-base" v-else>publish</span>
                                <span>{{ form.processing ? 'Uploading...' : 'Publish Release' }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- Delete Release Card -->
                    <div v-if="release" class="bg-red-50/50 border border-red-200 rounded-lg p-6 shadow-sm">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-red-800 flex items-center gap-2 mb-2">
                            <span class="material-symbols-rounded text-sm">warning</span>
                            Danger Zone
                        </h4>
                        <p class="text-[10px] text-red-600 mb-4 font-semibold leading-relaxed">
                            Deleting the current release ZIP package will disable client dynamic setups until a new release is uploaded.
                        </p>
                        <button 
                            @click="deleteRelease"
                            class="w-full inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-4 rounded-lg shadow-sm transition-all"
                        >
                            <span class="material-symbols-rounded text-base">delete_forever</span>
                            Delete Release
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
