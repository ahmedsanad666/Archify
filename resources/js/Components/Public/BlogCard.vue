<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useLocale } from '@/Composables/useLocale'

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

const { localized } = useLocale()

const title = computed(() => localized(props.blog, 'title') || props.blog.title)
const readTime = computed(
    () => localized(props.blog, 'read_time') ?? props.blog.read_time,
)
</script>

<template>
    <component
        :is="href ? Link : 'article'"
        :href="href || undefined"
        class="group flex min-w-0 flex-col overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high"
    >
        <div class="aspect-[16/10] overflow-hidden bg-surface-container-low">
            <img
                v-if="blog.thumbnail_url || blog.cover_url"
                :src="blog.thumbnail_url || blog.cover_url"
                :alt="title"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
        </div>
        <div class="flex min-w-0 flex-1 flex-col gap-2 p-md text-start">
            <div
                class="flex items-center justify-between gap-2 text-label-md uppercase tracking-wide text-on-surface-variant"
            >
                <span
                    v-if="blog.category?.name"
                    class="min-w-0 truncate"
                    :style="
                        blog.category.color
                            ? { color: blog.category.color }
                            : undefined
                    "
                >
                    {{ blog.category.name }}
                </span>
                <span
                    v-if="readTime"
                    class="shrink-0"
                >
                    {{ readTime }} min
                </span>
            </div>
            <h3
                class="text-headline-lg-mobile text-on-surface line-clamp-2"
                :title="title"
            >
                {{ title }}
            </h3>
        </div>
    </component>
</template>
