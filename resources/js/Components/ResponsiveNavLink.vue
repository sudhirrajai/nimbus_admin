<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-emerald-500 dark:border-emerald-450 text-start text-base font-medium text-emerald-700 dark:text-emerald-800 bg-emerald-50 dark:bg-emerald-50/20 focus:outline-none focus:text-emerald-800 dark:focus:text-emerald-800 focus:bg-emerald-100 dark:focus:bg-emerald-100 focus:border-emerald-700 dark:focus:border-emerald-600 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-500 hover:text-gray-800 dark:hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-55 hover:border-gray-300 dark:hover:border-gray-200 focus:outline-none focus:text-gray-800 dark:focus:text-gray-700 focus:bg-gray-50 dark:focus:bg-gray-55 focus:border-gray-300 dark:focus:border-gray-200 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
