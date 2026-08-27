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
const isLinkable = computed(() => Boolean(props.href))

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
            month: 'long',
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
        :is="isLinkable ? Link : 'article'"
        v-bind="isLinkable ? { href } : {}"
        class="group relative block min-h-[400px] w-full overflow-hidden rounded-lg border border-outline-variant transition-colors duration-300 hover:border-secondary md:h-[614px]"
        :class="isLinkable ? 'cursor-pointer' : ''"
    >
        <div class="absolute inset-0 z-0 bg-surface-container">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="title || ''"
                class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
            />
        </div>

        <div
            class="absolute inset-0 z-10 bg-gradient-to-t from-surface via-surface/40 to-transparent"
        />

        <div
            class="absolute inset-x-0 bottom-0 z-20 flex h-full w-full flex-col justify-end p-md text-start md:p-lg"
        >
            <span
                v-if="categoryName"
                class="mb-md inline-block self-start rounded-sm bg-primary-container px-sm py-xs text-label-md text-on-primary-container"
            >
                {{ categoryName }}
            </span>

            <h2
                class="mb-sm max-w-4xl text-headline-lg text-on-surface transition-colors duration-300 group-hover:text-primary md:text-display-md"
            >
                {{ title }}
            </h2>

            <p
                v-if="excerpt"
                class="mb-sm hidden max-w-2xl text-body-md text-on-surface-variant md:block"
            >
                {{ excerpt }}
            </p>

            <div
                v-if="formattedDate || readTimeLabel"
                class="flex flex-wrap items-center gap-sm text-label-md text-on-surface-variant"
            >
                <span v-if="formattedDate">{{ formattedDate }}</span>
                <span
                    v-if="formattedDate && readTimeLabel"
                    class="h-1 w-1 rounded-full bg-outline-variant"
                    aria-hidden="true"
                />
                <span v-if="readTimeLabel">{{ readTimeLabel }}</span>
            </div>
        </div>
    </component>
</template>
