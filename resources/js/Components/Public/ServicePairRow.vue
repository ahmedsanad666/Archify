<script setup>
import ServiceListRow from '@/Components/Public/ServiceListRow.vue'

defineProps({
    left: {
        type: Object,
        required: true,
    },
    right: {
        type: Object,
        default: null,
    },
    startIndex: {
        type: Number,
        required: true,
    },
})
</script>

<template>
    <div
        class="relative overflow-visible"
        :class="
            right
                ? 'md:grid md:grid-cols-2 md:items-stretch md:gap-gutter'
                : ''
        "
    >
        <!--
          Arch sits behind content (z-0). Left card gets bottom padding so the
          CTA clears the bottom stroke; right card gets top padding so number/icon
          clear the top stroke. Vertical step stays in the column gutter.
        -->
        <svg
            v-if="right"
            class="pointer-events-none absolute inset-0 z-0 hidden h-full w-full md:block rtl:-scale-x-100"
            viewBox="0 0 100 100"
            preserveAspectRatio="none"
            aria-hidden="true"
        >
            <path
                d="M 0 88 H 44 C 47 88, 50 88, 50 70 V 30 C 50 12, 53 12, 56 12 H 100"
                fill="none"
                stroke="#51443a"
                stroke-width="1"
                vector-effect="non-scaling-stroke"
            />
        </svg>

        <ServiceListRow
            :service="left"
            :index="startIndex"
            align="start"
            class="relative z-10"
            :class="right ? 'md:pb-xl' : ''"
        />

        <ServiceListRow
            v-if="right"
            :service="right"
            :index="startIndex + 1"
            align="end"
            class="relative z-10 mt-xl md:mt-0 md:pt-xl"
        />
    </div>
</template>
