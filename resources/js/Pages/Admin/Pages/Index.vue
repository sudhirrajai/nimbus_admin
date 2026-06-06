<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pages: Array
});

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Static Pages Management — Nimbus by VMCore" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Manage Pages</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
                    <span class="material-symbols-rounded text-lg">check_circle</span>
                    <span>{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Static Website Pages</h3>
                                <p class="text-xs text-gray-500 mt-1">Configure user-facing legal guidelines, support contents, and platform documentation entry points.</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-semibold">Page Title</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Slug / URL Path</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Last Updated</th>
                                        <th scope="col" class="px-6 py-3 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="page in pages" 
                                        :key="page.id" 
                                        class="bg-white border-b border-gray-100 hover:bg-gray-50 transition-colors"
                                    >
                                        <td class="px-6 py-4 font-semibold text-gray-900">
                                            {{ page.title }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-gray-100 border border-gray-200 rounded text-xs font-mono text-gray-650">
                                                /p/{{ page.slug }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            {{ formatDate(page.updated_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a 
                                                    :href="'/p/' + page.slug" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-semibold px-2 py-1 rounded hover:bg-emerald-50 transition-colors"
                                                >
                                                    <span class="material-symbols-rounded text-sm">open_in_new</span>
                                                    View Page
                                                </a>
                                                <Link 
                                                    :href="route('admin.pages.edit', page.id)" 
                                                    class="inline-flex items-center gap-1 text-xs bg-slate-900 text-white hover:bg-slate-800 font-semibold px-3 py-1.5 rounded-md shadow transition-colors"
                                                >
                                                    <span class="material-symbols-rounded text-sm">edit</span>
                                                    Edit Content
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
