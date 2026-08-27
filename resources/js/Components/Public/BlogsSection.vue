<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
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
const { localePath, localized } = useLocale()

const scroller = ref(null)
const activeIndex = ref(0)

const updateActiveIndex = () => {
    const el = scroller.value
    if (!el) {
        return
    }

    const containerRect = el.getBoundingClientRect()
    const containerStart = containerRect.left + 16
    let best = 0
    let bestDist = Infinity

    el.querySelectorAll('[data-blog-slide]').forEach((slide, index) => {
        const rect = slide.getBoundingClientRect()
        const dist = Math.abs(rect.left - containerStart)
        if (dist < bestDist) {
            bestDist = dist
            best = index
        }
    })

    activeIndex.value = best
}

const scrollToIndex = (index) => {
    const el = scroller.value
    if (!el) {
        return
    }
    const slides = el.querySelectorAll('[data-blog-slide]')
    const slide = slides[index]
    if (!slide) {
        return
    }
    slide.scrollIntoView({
        behavior: 'smooth',
        inline: 'start',
        block: 'nearest',
    })
    activeIndex.value = index
}

onMounted(() => {
    const el = scroller.value
    if (!el) {
        return
    }
    el.addEventListener('scroll', updateActiveIndex, { passive: true })
    updateActiveIndex()
})

onUnmounted(() => {
    const el = scroller.value
    if (!el) {
        return
    }
    el.removeEventListener('scroll', updateActiveIndex)
})
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

            <template v-if="blogs.length">
                <!-- Mobile: horizontal scroll + bullets -->
                <div class="md:hidden">
                    <div
                        ref="scroller"
                        class="blogs-scroller flex gap-gutter overflow-x-auto scroll-smooth snap-x snap-mandatory"
                    >
                        <div
                            v-for="blog in blogs"
                            :key="blog.id"
                            data-blog-slide
                            class="w-[85vw] max-w-[320px] shrink-0 snap-start"
                        >
                            <BlogCard
                                :blog="blog"
                                :href="
                                    localePath('blogs.show', {
                                        slug: localized(blog, 'slug'),
                                    })
                                "
                            />
                        </div>
                    </div>

                    <div
                        v-if="blogs.length > 1"
                        class="mt-md flex flex-wrap items-center justify-center gap-2"
                        role="tablist"
                        :aria-label="t('public.home.blogs_title')"
                    >
                        <button
                            v-for="(blog, index) in blogs"
                            :key="blog.id"
                            type="button"
                            role="tab"
                            class="h-2.5 w-2.5 rounded-full transition-colors duration-200"
                            :class="
                                index === activeIndex
                                    ? 'bg-primary'
                                    : 'border border-primary bg-transparent hover:bg-primary/30'
                            "
                            :aria-selected="index === activeIndex"
                            :aria-label="`${t('public.home.blogs_title')} ${index + 1}`"
                            @click="scrollToIndex(index)"
                        />
                    </div>
                </div>

                <!-- md+: grid -->
                <div
                    class="hidden gap-gutter md:grid md:grid-cols-2 lg:grid-cols-3"
                >
                    <BlogCard
                        v-for="blog in blogs"
                        :key="`grid-${blog.id}`"
                        :blog="blog"
                        :href="
                            localePath('blogs.show', {
                                slug: localized(blog, 'slug'),
                            })
                        "
                    />
                </div>
            </template>
            <p
                v-else
                class="text-center text-body-md text-on-surface-variant"
            >
                {{ t('public.home.empty_blogs') }}
            </p>
        </div>
    </section>
</template>

<style scoped>
.blogs-scroller {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.blogs-scroller::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}
</style>
