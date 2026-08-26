<script setup>
import BlogCard from '@/Components/Public/BlogCard.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    blogs: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
const { localePath } = useLocale()
</script>

<template>
    <section class="bg-surface-container-lowest py-xl">
        <div
            class="mx-auto max-w-[1440px] px-margin-mobile md:px-margin-desktop"
        >
            <div class="mb-xl text-center">
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.home.blogs_title') }}
                </h2>
                <p
                    class="mx-auto max-w-2xl text-body-lg text-on-surface-variant"
                >
                    {{ t('public.home.blogs_subtitle') }}
                </p>
            </div>
            <div
                v-if="blogs.length"
                class="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3"
            >
                <BlogCard
                    v-for="blog in blogs"
                    :key="blog.id"
                    :blog="blog"
                    :href="localePath('blogs.index')"
                />
            </div>
            <p
                v-else
                class="text-center text-body-md text-on-surface-variant"
            >
                {{ t('public.home.empty_blogs') }}
            </p>
        </div>
    </section>
</template>
