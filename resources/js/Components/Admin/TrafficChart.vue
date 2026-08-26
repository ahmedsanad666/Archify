<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    labels: {
        type: Array,
        default: () => [],
    },
    values: {
        type: Array,
        default: () => [],
    },
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.values,
            backgroundColor: '#f9ba7f',
            hoverBackgroundColor: '#bd854f',
            borderRadius: 4,
            borderSkipped: false,
            maxBarThickness: 28,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#2d2926',
            titleColor: '#e9e1db',
            bodyColor: '#d5c3b6',
            borderColor: '#51443a',
            borderWidth: 1,
            displayColors: false,
            padding: 10,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: {
                color: '#d5c3b6',
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 8,
                font: { size: 11 },
            },
            border: { color: '#51443a' },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(81, 68, 58, 0.45)',
            },
            ticks: {
                color: '#d5c3b6',
                precision: 0,
                font: { size: 11 },
            },
            border: { display: false },
        },
    },
}));
</script>

<template>
    <div class="relative min-h-[240px] min-w-0 w-full overflow-hidden">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
