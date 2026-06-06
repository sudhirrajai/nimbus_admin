<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    page: Object
});

const form = useForm({
    title: props.page.title,
    content: props.page.content,
});

const submit = () => {
    form.put(route('admin.pages.update', props.page.id));
};
</script>

<template>
    <Head :title="'Edit Page: ' + page.title + ' — Nimbus by VMCore'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">Edit Page: {{ page.title }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Page Content Editor</h3>
                                <p class="text-xs text-gray-500 mt-1">Updates will be rendered dynamically to end-users visiting <span class="font-mono bg-gray-100 px-1 rounded">/p/{{ page.slug }}</span>.</p>
                            </div>
                            <Link 
                                :href="route('admin.pages.index')"
                                class="text-xs text-gray-650 hover:text-gray-900 font-semibold flex items-center gap-1 transition-colors"
                            >
                                <span class="material-symbols-rounded text-sm">arrow_back</span>
                                Back to list
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-semibold text-gray-700">Page Title</label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    v-model="form.title"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                    required
                                />
                                <div v-if="form.errors.title" class="text-xs text-red-600 mt-1">{{ form.errors.title }}</div>
                            </div>

                            <div>
                                <label for="content" class="block text-sm font-semibold text-gray-700">HTML / Markdown Content</label>
                                <p class="text-xs text-gray-400 mb-2">You can use standard HTML structure, links, paragraphs, and headings to construct custom content layout.</p>
                                <textarea 
                                    id="content" 
                                    v-model="form.content"
                                    rows="16"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono"
                                    required
                                ></textarea>
                                <div v-if="form.errors.content" class="text-xs text-red-600 mt-1">{{ form.errors.content }}</div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <Link 
                                    :href="route('admin.pages.index')"
                                    class="inline-flex items-center justify-center font-semibold text-sm text-gray-700 bg-white hover:bg-gray-50 border border-gray-350 px-4 py-2 rounded-lg transition-colors"
                                >
                                    Cancel
                                </Link>
                                <button 
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center justify-center font-semibold text-sm bg-emerald-600 text-white hover:bg-emerald-700 px-5 py-2 rounded-lg shadow-sm transition-colors disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Saving Changes...</span>
                                    <span v-else>Save Page Updates</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
