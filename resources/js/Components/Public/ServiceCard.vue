<script setup>
import { computed } from 'vue'
import { IconBuildingArch } from '@tabler/icons-vue'
import { resolveAppIcon } from '@/icons/appIcons'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
})

const { localized } = useLocale()

const IconComponent = computed(() =>
    resolveAppIcon(props.service.icon, IconBuildingArch),
)

const title = computed(() => localized(props.service, 'title'))
const items = computed(
    () => localized(props.service, 'included_items', []) || [],
)
</script>

<template>
    <article
        class="group rounded-lg border border-outline-variant bg-surface-container p-lg transition-all duration-300 hover:border-secondary hover:bg-surface-container-high"
    >
        <div class="mb-md flex items-start justify-between">
            <component
                :is="IconComponent"
                class="text-on-surface-variant transition-colors group-hover:text-primary"
                :size="36"
                stroke-width="1.5"
            />
            <span class="text-label-md text-outline-variant">
                {{ String(index + 1).padStart(2, '0') }}
            </span>
        </div>
        <h3
            class="mb-4 text-headline-lg-mobile text-on-surface transition-colors group-hover:text-primary"
        >
            {{ title }}
        </h3>
        <p
            v-if="localized(service, 'short_description')"
            class="mb-4 text-body-md text-on-surface-variant"
        >
            {{ localized(service, 'short_description') }}
        </p>
        <ul
            v-if="Array.isArray(items) && items.length"
            class="space-y-2"
        >
            <li
                v-for="(item, i) in items"
                :key="i"
                class="flex items-center text-body-md text-on-surface-variant"
            >
                <span
                    class="me-3 h-1.5 w-1.5 shrink-0 rounded-full bg-outline"
                />
                {{ item }}
            </li>
        </ul>
    </article>
</template>
