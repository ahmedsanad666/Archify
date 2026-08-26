<script setup>
import { computed } from 'vue';
import { IconTrendingDown, IconTrendingUp } from '@tabler/icons-vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    changePercent: {
        type: Number,
        default: null,
    },
    trend: {
        type: String,
        default: 'flat',
        validator: (value) => ['up', 'down', 'flat'].includes(value),
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
});

const { t } = useUiTranslations();

const showChange = computed(
    () =>
        props.trend !== 'flat' &&
        props.changePercent !== null &&
        props.changePercent !== undefined,
);

const changeLabel = computed(() => {
    if (!showChange.value) {
        return t('admin.dashboard.no_change');
    }

    const prefix = props.trend === 'down' ? '-' : '+';
    return `${prefix}${props.changePercent}%`;
});
</script>

<template>
    <div
        class="flex min-w-0 flex-col justify-between rounded-lg border border-outline-variant bg-surface-container p-md transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high"
    >
        <div class="mb-md flex items-start justify-between gap-sm">
            <span
                class="min-w-0 text-start text-label-md uppercase tracking-wide text-on-surface-variant line-clamp-2"
            >
                {{ label }}
            </span>
            <component
                :is="icon"
                v-if="icon"
                class="shrink-0 text-primary"
                :size="20"
                stroke-width="1.5"
            />
        </div>

        <div class="flex items-end justify-between gap-sm">
            <span
                class="min-w-0 truncate text-display-md leading-none text-on-surface"
            >
                {{ value }}
            </span>
            <span
                class="inline-flex shrink-0 items-center gap-0.5 rounded-sm bg-primary/10 px-xs py-0.5 text-[10px] text-label-md uppercase tracking-wide text-primary"
                :class="{
                    'text-error bg-error/10': trend === 'down',
                    'text-on-surface-variant bg-surface-container-high':
                        !showChange,
                }"
            >
                <IconTrendingUp
                    v-if="trend === 'up'"
                    :size="12"
                    stroke-width="1.5"
                />
                <IconTrendingDown
                    v-else-if="trend === 'down'"
                    :size="12"
                    stroke-width="1.5"
                />
                {{ changeLabel }}
            </span>
        </div>
    </div>
</template>
