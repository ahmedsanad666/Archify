<script setup>
import StatBlock from '@/Components/Public/StatBlock.vue'

defineProps({
    statistics: {
        type: Array,
        default: () => [],
    },
})
</script>

<template>
    <section
        v-if="statistics.length"
        class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
    >
        <div
            class="flex flex-wrap items-center justify-center gap-xl border-y border-outline-variant py-lg"
        >
            <template
                v-for="(stat, i) in statistics"
                :key="stat.id"
            >
                <StatBlock :statistic="stat" />
                <div
                    v-if="i < statistics.length - 1"
                    class="hidden h-12 w-px bg-outline-variant sm:block"
                />
            </template>
        </div>
    </section>
</template>
