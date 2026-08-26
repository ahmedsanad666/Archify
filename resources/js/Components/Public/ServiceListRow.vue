<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { IconBuildingArch } from '@tabler/icons-vue'
import { resolveAppIcon } from '@/icons/appIcons'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
    /** Visual side in a pair — padding only; text stays start-aligned for RTL. */
    align: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'end'].includes(value),
    },
    showBorder: {
        type: Boolean,
        default: false,
    },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

const IconComponent = computed(() =>
    resolveAppIcon(props.service.icon, IconBuildingArch),
)

const title = computed(() => localized(props.service, 'title'))
const description = computed(() =>
    localized(props.service, 'short_description'),
)
const items = computed(
    () => localized(props.service, 'included_items', []) || [],
)

const numberLabel = computed(() =>
    String(props.index + 1).padStart(2, '0'),
)
</script>

<template>
    <article
        class="flex min-w-0 flex-col text-start"
        :class="[
            showBorder ? 'border-b border-outline-variant pb-xl' : '',
            align === 'end' ? 'md:ps-gutter' : 'md:pe-gutter',
        ]"
    >
        <div
            class="mb-md flex w-full max-w-[7.5rem] items-center justify-between gap-md sm:max-w-[15rem]"
        >
            <span class="text-display-md leading-none text-outline-variant">
                {{ numberLabel }}
            </span>
            <component
                :is="IconComponent"
                class="shrink-0 text-primary"
                :size="28"
                stroke-width="1.5"
            />
        </div>

        <h2
            class="mb-sm text-headline-lg-mobile text-on-surface sm:text-headline-lg"
        >
            {{ title }}
        </h2>

        <p
            v-if="description"
            class="mb-md whitespace-pre-line text-body-md text-on-surface-variant"
        >
            {{ description }}
        </p>

        <ul
            v-if="Array.isArray(items) && items.length"
            class="mb-lg flex flex-col gap-sm border-s border-outline-variant py-sm ps-md"
        >
            <li
                v-for="(item, i) in items"
                :key="i"
                class="flex items-center gap-sm text-body-md text-on-surface"
            >
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                {{ item }}
            </li>
        </ul>

        <div>
            <Link
                :href="localePath('contact')"
                class="inline-flex rounded-md border border-outline px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-all hover:border-primary hover:bg-surface-container"
            >
                {{ t('common.learn_more') }}
            </Link>
        </div>
    </article>
</template>
