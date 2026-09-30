<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    end: {
        type: Number,
        required: true,
    },
    decimals: {
        type: Number,
        default: 0,
    },
    prefix: {
        type: String,
        default: '',
    },
    suffix: {
        type: String,
        default: '',
    },
});

const metricRef = ref(null);
const displayValue = ref(0);
let frame = 0;
let observer = null;

onMounted(() => {
    const target = metricRef.value;
    if (!target) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        displayValue.value = props.end;
        return;
    }

    let startTime;
    observer = new IntersectionObserver(([entry]) => {
        if (!entry?.isIntersecting) return;
        observer.disconnect();

        const tick = (now) => {
            if (!startTime) startTime = now;
            const progress = Math.min((now - startTime) / 900, 1);
            displayValue.value = props.end * (1 - Math.pow(1 - progress, 3));
            if (progress < 1) {
                frame = requestAnimationFrame(tick);
            }
        };
        frame = requestAnimationFrame(tick);
    }, { threshold: 0.6 });

    observer.observe(target);
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    if (frame) cancelAnimationFrame(frame);
});
</script>

<template>
    <span ref="metricRef">{{ prefix }}{{ displayValue.toFixed(decimals) }}{{ suffix }}</span>
</template>
