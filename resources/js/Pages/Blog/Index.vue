<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import FeaturedBlogCard from '@/Components/Public/FeaturedBlogCard.vue'
import BlogCard from '@/Components/Public/BlogCard.vue'
import AppPagination from '@/Components/Shared/AppPagination.vue'
import { useLocale } from '@/Composables/useLocale'
import { useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    blogs: {
        type: Object,
        default: () => ({ data: [], meta: null }),
    },
    featured: {
        type: Object,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({ category: null }),
    },
})

const { t } = useUiTranslations()
const { locale, localePath, localized } = useLocale()
const page = usePage()

const heroBanner = computed(
    () => page.props.siteSettings?.media?.banner_blogs ?? null,
)

const blogCategories = computed(() => page.props.blogCategories ?? [])

const { headTitle, title, description, keywords } = useSiteSeo({
    pageTitle: t('public.blogs.title'),
})

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.blogs.title') },
])

const activeCategory = computed(() => props.filters?.category ?? null)

const blogItems = computed(() => props.blogs?.data ?? [])

const hasContent = computed(
    () => Boolean(props.featured) || blogItems.value.length > 0,
)

const categorySlug = (category) => {
    const code = locale.value?.code || 'en'
    return (
        category?.translations?.[code]?.slug ||
        category?.translations?.en?.slug ||
        null
    )
}

const categoryHref = (slug = null) => {
    const base = localePath('blogs.index')
    if (!slug) {
        return base
    }
    const sep = base.includes('?') ? '&' : '?'
    return `${base}${sep}category=${encodeURIComponent(slug)}`
}
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ headTitle }}</title>
            <meta
                v-if="description"
                head-key="description"
                name="description"
                :content="description"
            />
            <meta
                v-if="keywords"
                head-key="keywords"
                name="keywords"
                :content="keywords"
            />
            <meta
                head-key="og:title"
                property="og:title"
                :content="title"
            />
            <meta
                v-if="description"
                head-key="og:description"
                property="og:description"
                :content="description"
            />
        </Head>

        <InnerHero
            :title="t('public.blogs.title')"
            :eyebrow="t('public.blogs.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />

        <section
            class="mx-auto w-full max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p
                class="mb-xl max-w-3xl text-body-lg leading-relaxed text-on-surface-variant"
            >
                {{ t('public.blogs.intro') }}
            </p>

            <div
                v-if="featured"
                class="mb-xl"
            >
                <FeaturedBlogCard
                    :blog="featured"
                    :href="
                        localePath('blogs.show', {
                            slug: localized(featured, 'slug'),
                        })
                    "
                />
            </div>

            <div
                class="mb-lg flex flex-col items-start justify-between gap-md border-b border-outline-variant pb-md md:flex-row md:items-center"
            >
                <span
                    class="text-label-md uppercase tracking-wider text-outline"
                >
                    {{ t('public.blogs.filter') }}
                </span>
                <div class="flex flex-wrap gap-sm">
                    <Link
                        :href="categoryHref()"
                        class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors duration-200"
                        :class="
                            !activeCategory
                                ? 'bg-primary text-on-primary'
                                : 'border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary'
                        "
                        preserve-scroll
                    >
                        {{ t('nav.all') }}
                    </Link>
                    <Link
                        v-for="category in blogCategories"
                        :key="category.id"
                        :href="categoryHref(categorySlug(category))"
                        class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors duration-200"
                        :class="
                            activeCategory === categorySlug(category)
                                ? 'bg-primary text-on-primary'
                                : 'border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary'
                        "
                        preserve-scroll
                    >
                        {{ localized(category, 'name') }}
                    </Link>
                </div>
            </div>

            <div
                v-if="blogItems.length"
                class="mb-xl grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3"
            >
                <BlogCard
                    v-for="blog in blogItems"
                    :key="blog.id"
                    :blog="blog"
                    :href="
                        localePath('blogs.show', {
                            slug: localized(blog, 'slug'),
                        })
                    "
                />
            </div>
            <p
                v-else-if="!hasContent"
                class="mb-xl text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.blogs.empty') }}
            </p>

            <AppPagination
                :meta="blogs.meta"
                class="mt-md"
            />
        </section>
    </AppLayout>
</template>
