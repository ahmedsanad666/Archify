<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { IconChevronRight } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useLocale } from '@/Composables/useLocale'
import { mergeKeywords, useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    blog: {
        type: Object,
        required: true,
    },
})

const { t } = useUiTranslations()
const { localized, localePath, locale } = useLocale()

const title = computed(() => localized(props.blog, 'title') || props.blog.title)
const content = computed(() => localized(props.blog, 'content') || '')
const categoryName = computed(() => props.blog?.category?.name ?? null)
const readTime = computed(
    () => localized(props.blog, 'read_time') ?? props.blog.read_time,
)
const coverUrl = computed(
    () => props.blog.cover_url || props.blog.thumbnail_url || null,
)

const metaKeywordsRaw = computed(() =>
    String(localized(props.blog, 'meta_keywords') || '').trim(),
)

const keywordTags = computed(() =>
    metaKeywordsRaw.value
        ? metaKeywordsRaw.value.split(/\s+/).filter(Boolean)
        : [],
)

const pageTitle = computed(
    () =>
        localized(props.blog, 'meta_title') ||
        title.value ||
        t('public.blogs.title'),
)

const seoDescription = computed(
    () =>
        localized(props.blog, 'meta_description') ||
        props.blog.excerpt ||
        '',
)

const seoKeywords = computed(() =>
    mergeKeywords(metaKeywordsRaw.value, categoryName.value),
)

const ogImage = computed(() => coverUrl.value || null)

const { headTitle, title: seoTitle, description, keywords, ogImage: resolvedOg } =
    useSiteSeo({
        pageTitle: pageTitle.value,
        description: seoDescription.value,
        keywords: seoKeywords.value,
        ogImage: ogImage.value,
    })

const headDescription = computed(
    () => seoDescription.value || description.value,
)
const headKeywords = computed(() => seoKeywords.value || keywords.value)
const headOgImage = computed(() => resolvedOg.value || ogImage.value || '')

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

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.blogs.title'), href: localePath('blogs.index') },
    { label: title.value || t('public.blogs.title') },
])
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ headTitle }}</title>
            <meta
                v-if="headDescription"
                head-key="description"
                name="description"
                :content="headDescription"
            />
            <meta
                v-if="headKeywords"
                head-key="keywords"
                name="keywords"
                :content="headKeywords"
            />
            <meta
                head-key="og:title"
                property="og:title"
                :content="seoTitle"
            />
            <meta
                v-if="headDescription"
                head-key="og:description"
                property="og:description"
                :content="headDescription"
            />
            <meta
                v-if="headOgImage"
                head-key="og:image"
                property="og:image"
                :content="headOgImage"
            />
        </Head>

        <main class="pb-xl pt-28 md:pb-[120px] md:pt-40">
            <header
                class="mx-auto max-w-[1440px] px-margin-mobile text-center md:px-margin-desktop"
            >
                <nav
                    aria-label="Breadcrumb"
                    class="mb-lg flex flex-wrap items-center justify-center gap-2 text-label-md text-on-surface-variant"
                >
                    <template
                        v-for="(crumb, index) in breadcrumbs"
                        :key="`${crumb.label}-${index}`"
                    >
                        <Link
                            v-if="crumb.href"
                            :href="crumb.href"
                            class="transition-colors hover:text-primary"
                        >
                            {{ crumb.label }}
                        </Link>
                        <span
                            v-else
                            class="text-on-surface"
                            aria-current="page"
                        >
                            {{ crumb.label }}
                        </span>
                        <IconChevronRight
                            v-if="index < breadcrumbs.length - 1"
                            class="shrink-0 opacity-50 rtl:rotate-180"
                            :size="16"
                            stroke-width="1.5"
                        />
                    </template>
                </nav>

                <div
                    v-if="categoryName"
                    class="mb-md"
                >
                    <span
                        class="inline-block rounded-md border border-primary/20 bg-primary-container/10 px-4 py-1.5 text-label-md uppercase tracking-widest text-primary"
                    >
                        {{ categoryName }}
                    </span>
                </div>

                <h1
                    class="mx-auto mb-md max-w-4xl text-headline-lg text-on-surface sm:text-display-md md:text-display-lg"
                >
                    {{ title }}
                </h1>

                <div
                    v-if="formattedDate || readTimeLabel"
                    class="mt-lg flex flex-wrap items-center justify-center gap-sm text-label-md text-on-surface-variant"
                >
                    <span v-if="formattedDate">{{ formattedDate }}</span>
                    <span
                        v-if="formattedDate && readTimeLabel"
                        class="hidden h-3 w-px bg-outline-variant sm:block"
                        aria-hidden="true"
                    />
                    <span v-if="readTimeLabel">{{ readTimeLabel }}</span>
                </div>
            </header>

            <div
                v-if="coverUrl"
                class="mx-auto mt-xl max-w-[1440px] px-margin-mobile md:px-margin-desktop"
            >
                <div
                    class="group relative h-[512px] overflow-hidden rounded-lg border border-outline-variant md:h-[716px]"
                >
                    <img
                        :src="coverUrl"
                        :alt="title || ''"
                        class="h-full w-full object-cover transition-transform duration-[2s] group-hover:scale-[1.02]"
                    />
                </div>
            </div>

            <article
                class="mx-auto mt-xl max-w-3xl px-margin-mobile md:px-0"
            >
                <div
                    v-if="content"
                    class="blog-prose text-body-lg leading-[1.8] text-on-surface-variant"
                    v-html="content"
                />

                <div
                    v-if="keywordTags.length"
                    class="mb-xl mt-xl flex flex-wrap gap-sm border-b border-outline-variant/30 pb-xl"
                >
                    <span
                        v-for="tag in keywordTags"
                        :key="tag"
                        class="inline-flex items-center justify-center rounded-sm border border-outline px-4 py-2 text-label-md text-on-surface-variant"
                    >
                        {{ tag }}
                    </span>
                </div>
            </article>
        </main>
    </AppLayout>
</template>

<style scoped>
.blog-prose :deep(p) {
    margin-bottom: 1.5rem;
}

.blog-prose :deep(h2) {
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    font-size: 1.75rem;
    font-weight: 600;
    line-height: 1.3;
    letter-spacing: -0.01em;
    color: #e9e1db;
}

.blog-prose :deep(h3) {
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: #e9e1db;
}

.blog-prose :deep(blockquote) {
    margin: 2.5rem 0;
    border-inline-start: 2px solid #f9ba7f;
    padding-inline-start: 1.5rem;
}

.blog-prose :deep(blockquote p) {
    font-size: 1.5rem;
    font-style: italic;
    font-weight: 600;
    line-height: 1.3;
    color: #e9e1db;
}

.blog-prose :deep(img) {
    margin: 2.5rem 0;
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #51443a;
}

.blog-prose :deep(a) {
    color: #f9ba7f;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.blog-prose :deep(ul),
.blog-prose :deep(ol) {
    margin-bottom: 1.5rem;
    padding-inline-start: 1.25rem;
}

.blog-prose :deep(li) {
    margin-bottom: 0.5rem;
}
</style>
