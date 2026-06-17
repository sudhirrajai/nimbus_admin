<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    reports: Array
});

const activeFilter = ref('all');
const selectedReport = ref(null);
const lightboxImage = ref(null);

const filteredReports = computed(() => {
    if (activeFilter.value === 'all') return props.reports;
    return props.reports.filter(r => r.status === activeFilter.value);
});

const selectReport = (report) => {
    selectedReport.value = report;
};

const updateStatus = (report, newStatus) => {
    router.post(route('admin.reports.update-status', report.id), {
        status: newStatus
    }, {
        onSuccess: () => {
            // Update selected report reference to reflect changed status
            if (selectedReport.value && selectedReport.value.id === report.id) {
                selectedReport.value.status = newStatus;
            }
        }
    });
};

const deleteReport = (report) => {
    if (confirm('Are you sure you want to delete this bug report? This will delete the database record and associated screenshots.')) {
        router.delete(route('admin.reports.destroy', report.id), {
            onSuccess: () => {
                if (selectedReport.value && selectedReport.value.id === report.id) {
                    selectedReport.value = null;
                }
            }
        });
    }
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const openLightbox = (imagePath) => {
    lightboxImage.value = '/storage/' + imagePath;
};

const closeLightbox = () => {
    lightboxImage.value = null;
};
</script>

<template>
    <Head title="Bug Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    Bug & Issue Reports
                </h2>
                <p class="text-xs text-gray-500 mt-1">Monitor and address feedback, issues, and bug reports submitted by Nimbus panel installations.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Flash Notification -->
            <div v-if="$page.props.flash?.success" class="animate-fade-in flex items-center gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                <span class="material-symbols-rounded text-lg">check_circle</span>
                <p class="text-xs font-medium">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Filters & Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Pane: Reports List -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Filter Buttons -->
                    <div class="flex gap-2 bg-white p-1.5 border border-gray-200 rounded-lg shadow-sm w-max">
                        <button 
                            @click="activeFilter = 'all'" 
                            :class="[activeFilter === 'all' ? 'bg-slate-100 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900']"
                            class="px-4 py-1.5 rounded-md text-xs transition-all uppercase tracking-wider font-medium"
                        >
                            All ({{ reports.length }})
                        </button>
                        <button 
                            @click="activeFilter = 'pending'" 
                            :class="[activeFilter === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200 font-semibold' : 'text-gray-500 hover:text-gray-900']"
                            class="px-4 py-1.5 rounded-md text-xs transition-all uppercase tracking-wider font-medium border border-transparent"
                        >
                            Pending ({{ reports.filter(r => r.status === 'pending').length }})
                        </button>
                        <button 
                            @click="activeFilter = 'resolved'" 
                            :class="[activeFilter === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 font-semibold' : 'text-gray-500 hover:text-gray-900']"
                            class="px-4 py-1.5 rounded-md text-xs transition-all uppercase tracking-wider font-medium border border-transparent"
                        >
                            Resolved ({{ reports.filter(r => r.status === 'resolved').length }})
                        </button>
                        <button 
                            @click="activeFilter = 'closed'" 
                            :class="[activeFilter === 'closed' ? 'bg-slate-100 text-gray-650 font-semibold' : 'text-gray-500 hover:text-gray-900']"
                            class="px-4 py-1.5 rounded-md text-xs transition-all uppercase tracking-wider font-medium border border-transparent"
                        >
                            Closed ({{ reports.filter(r => r.status === 'closed').length }})
                        </button>
                    </div>

                    <!-- Reports Grid/List -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                        <div v-if="filteredReports.length === 0" class="p-8 text-center text-gray-500 italic text-sm">
                            No bug reports match the selected filter.
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li 
                                v-for="report in filteredReports" 
                                :key="report.id" 
                                @click="selectReport(report)"
                                :class="[selectedReport?.id === report.id ? 'bg-slate-50/80 border-l-2 border-emerald-500' : 'hover:bg-slate-50/30']"
                                class="p-5 flex items-center justify-between gap-4 cursor-pointer transition-all border-l-2 border-transparent"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                        <span class="text-xs font-bold text-gray-900 truncate max-w-[200px]" :title="report.admin_name">
                                            {{ report.admin_name || 'Anonymous Admin' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-mono">&#8226;</span>
                                        <span class="text-[11px] text-gray-500 font-mono truncate" :title="report.domain">
                                            {{ report.domain || 'localhost' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-700 line-clamp-2 leading-relaxed">
                                        {{ report.message }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-3">
                                        <span class="text-[10px] text-gray-400 font-medium">
                                            {{ formatDateTime(report.created_at) }}
                                        </span>
                                        <span v-if="report.screenshot_path" class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-semibold bg-emerald-50/50 px-2 py-0.5 rounded border border-emerald-100">
                                            <span class="material-symbols-rounded text-xs">image</span>
                                            Screenshot
                                        </span>
                                        <span v-if="report.images && report.images.length > 0" class="inline-flex items-center gap-1 text-[10px] text-purple-600 font-semibold bg-purple-50/50 px-2 py-0.5 rounded border border-purple-100">
                                            <span class="material-symbols-rounded text-xs">attach_file</span>
                                            {{ report.images.length }} Attachments
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border"
                                        :class="{
                                            'bg-amber-50 border-amber-200 text-amber-700': report.status === 'pending',
                                            'bg-emerald-50 border-emerald-200 text-emerald-700': report.status === 'resolved',
                                            'bg-slate-50 border-gray-200 text-gray-700': report.status === 'closed'
                                        }">
                                        {{ report.status }}
                                    </span>
                                    <span class="material-symbols-rounded text-gray-400 text-base">chevron_right</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Pane: Report Detail View -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 sticky top-24 space-y-6">
                        <div v-if="!selectedReport" class="py-12 text-center text-gray-400 italic text-xs flex flex-col items-center gap-2">
                            <span class="material-symbols-rounded text-3xl text-gray-300">wysiwyg</span>
                            Select a bug report from the list to view its complete details, screenshots, and licensing metadata.
                        </div>
                        <div v-else class="space-y-6 animate-fade-in">
                            <!-- Detail Header -->
                            <div class="pb-4 border-b border-gray-150 flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Report Details</h4>
                                    <p class="text-[10px] text-gray-500 font-medium">Ticket #{{ selectedReport.id }}</p>
                                </div>
                                <button 
                                    @click="deleteReport(selectedReport)" 
                                    class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-all"
                                    title="Delete Report"
                                >
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                            </div>

                            <!-- Status Manager -->
                            <div class="bg-slate-50 p-3 rounded-lg border border-gray-200 flex items-center justify-between gap-4">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Status</span>
                                <select 
                                    :value="selectedReport.status" 
                                    @change="updateStatus(selectedReport, $event.target.value)"
                                    class="bg-white border border-gray-200 rounded-md text-xs text-gray-900 py-1.5 pl-2 pr-8 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <!-- Submitter Info -->
                            <div class="space-y-3">
                                <h5 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Submitter Details</h5>
                                <div class="space-y-2 text-xs">
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400 font-medium">Admin:</span>
                                        <span class="text-gray-800 font-bold truncate max-w-[180px]">{{ selectedReport.admin_name || 'Anonymous' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400 font-medium">Email:</span>
                                        <span class="text-gray-850 font-medium truncate max-w-[180px] select-all">{{ selectedReport.admin_email || 'Not provided' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400 font-medium">Domain:</span>
                                        <span class="text-gray-800 font-bold font-mono truncate max-w-[180px] select-all">{{ selectedReport.domain || 'localhost' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400 font-medium">IP Address:</span>
                                        <span class="text-gray-800 font-medium font-mono select-all">{{ selectedReport.ip_address || 'Unknown' }}</span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400 font-medium">License Key:</span>
                                        <code v-if="selectedReport.license_key" class="text-[10px] text-emerald-800 font-mono bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded select-all">{{ selectedReport.license_key }}</code>
                                        <span v-else class="text-gray-400 italic">No license linked</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bug Message -->
                            <div class="space-y-2">
                                <h5 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Message</h5>
                                <div class="bg-slate-50 p-4 border border-gray-150 rounded-lg text-xs leading-relaxed text-gray-800 whitespace-pre-wrap max-h-48 overflow-y-auto">
                                    {{ selectedReport.message }}
                                </div>
                            </div>

                            <!-- Attachments (Live Screenshot & Images) -->
                            <div class="space-y-3" v-if="selectedReport.screenshot_path || (selectedReport.images && selectedReport.images.length > 0)">
                                <h5 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Attachments</h5>
                                
                                <div class="space-y-4">
                                    <!-- Live Screenshot -->
                                    <div v-if="selectedReport.screenshot_path" class="space-y-1">
                                        <span class="text-[10px] text-gray-400 font-medium">Live Screenshot:</span>
                                        <div 
                                            @click="openLightbox(selectedReport.screenshot_path)" 
                                            class="group relative border border-gray-200 rounded-lg overflow-hidden cursor-pointer shadow-sm hover:shadow transition-all aspect-video bg-slate-50"
                                        >
                                            <img :src="'/storage/' + selectedReport.screenshot_path" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300" />
                                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                <span class="material-symbols-rounded text-white text-lg bg-black/60 p-2.5 rounded-full shadow-lg">zoom_in</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Other Images -->
                                    <div v-if="selectedReport.images && selectedReport.images.length > 0" class="space-y-1.5">
                                        <span class="text-[10px] text-gray-400 font-medium">Attached Images:</span>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div 
                                                v-for="(img, idx) in selectedReport.images" 
                                                :key="idx"
                                                @click="openLightbox(img)"
                                                class="group relative border border-gray-250 rounded-md overflow-hidden cursor-pointer shadow-sm hover:shadow transition-all aspect-square bg-slate-50"
                                            >
                                                <img :src="'/storage/' + img" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <span class="material-symbols-rounded text-white text-xs bg-black/60 p-1.5 rounded-full">zoom_in</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Lightbox Modal -->
        <div 
            v-if="lightboxImage" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md animate-fade-in"
            @click="closeLightbox"
        >
            <button @click.stop="closeLightbox" class="absolute top-6 right-6 text-gray-400 hover:text-white p-2 outline-none">
                <span class="material-symbols-rounded text-3xl">close</span>
            </button>
            <div class="max-w-[90vw] max-h-[85vh] p-2 flex items-center justify-center">
                <img :src="lightboxImage" class="max-w-full max-h-full rounded-lg object-contain shadow-2xl animate-scale-up" @click.stop />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@keyframes scaleUp {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.animate-scale-up {
    animation: scaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
