<script setup>
import { useLocale } from '@/Composables/useLocale'

defineProps({
    statistic: {
        type: Object,
        required: true,
    },
})

const { localized } = useLocale()
</script>

<template>
    <div>
        <span class="block text-headline-lg text-primary">
            {{ statistic.count }}
        </span>
        <span
            class="text-label-md uppercase tracking-wider text-on-surface-variant"
        >
            {{ localized(statistic, 'label') }}
        </span>
    </div>
</template>
