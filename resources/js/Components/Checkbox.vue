<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-slate-700 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:focus:ring-emerald-500 dark:focus:ring-offset-slate-900"
    />
</template>
