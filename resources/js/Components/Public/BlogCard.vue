<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    blog: {
        type: Object,
        required: true,
    },
    href: {
        type: String,
        default: null,
    },
})

const { localized, locale } = useLocale()
const { t } = useUiTranslations()

const title = computed(() => localized(props.blog, 'title') || props.blog.title)
const excerpt = computed(() => props.blog.excerpt || null)
const readTime = computed(
    () => localized(props.blog, 'read_time') ?? props.blog.read_time,
)
const imageUrl = computed(
    () => props.blog.thumbnail_url || props.blog.cover_url || null,
)
const categoryName = computed(() => props.blog.category?.name ?? null)

const formattedDate = computed(() => {
    if (!props.blog.created_at) {
        return null
    }

    const date = new Date(props.blog.created_at)
    if (Number.isNaN(date.getTime())) {
        return null
    }

    const localeCode = locale.value?.code || 'en'

    try {
        return new Intl.DateTimeFormat(localeCode, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        }).format(date)
    } catch {
        return date.toLocaleDateString()
    }
})

const readTimeLabel = computed(() => {
    if (!readTime.value) {
        return null
    }

    return t('public.blog.min_read', { n: readTime.value })
})
</script>

<template>
    <component
        :is="href ? Link : 'article'"
        :href="href || undefined"
        class="group flex flex-col overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-all duration-300 hover:border-secondary hover:bg-surface-container-high"
        :class="href ? 'cursor-pointer' : ''"
    >
        <div class="relative h-64 overflow-hidden bg-surface-dim">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="title || ''"
                class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
            />
        </div>

        <div class="flex flex-grow flex-col p-md text-start">
            <span
                v-if="categoryName"
                class="mb-sm text-label-md text-primary"
            >
                {{ categoryName }}
            </span>

            <h3
                class="mb-sm text-headline-lg-mobile text-on-surface transition-colors duration-300 group-hover:text-primary md:text-headline-lg"
            >
                {{ title }}
            </h3>

            <p
                v-if="excerpt"
                class="mb-md line-clamp-2 flex-grow text-body-md text-on-surface-variant"
            >
                {{ excerpt }}
            </p>

            <div
                v-if="formattedDate || readTimeLabel"
                class="mt-auto flex items-center justify-between gap-sm border-t border-outline-variant pt-sm text-label-md text-on-surface-variant"
            >
                <span v-if="formattedDate">{{ formattedDate }}</span>
                <span v-if="readTimeLabel">{{ readTimeLabel }}</span>
            </div>
        </div>
    </component>
</template>
