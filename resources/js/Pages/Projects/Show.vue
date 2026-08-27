<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import ProjectGallerySection from '@/Components/Public/ProjectGallerySection.vue'
import { useLocale } from '@/Composables/useLocale'
import { mergeKeywords, useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

const name = computed(() => localized(props.project, 'name'))
const shortDescription = computed(() =>
    localized(props.project, 'short_description'),
)
const longDescription = computed(() => localized(props.project, 'description'))
const categoryName = computed(() => props.project?.category?.name ?? null)

const pageTitle = computed(
    () =>
        localized(props.project, 'meta_title') ||
        name.value ||
        t('public.projects.title'),
)

const seoDescriptionValue = computed(
    () =>
        localized(props.project, 'meta_description') ||
        shortDescription.value ||
        '',
)

const seoKeywordsValue = computed(() =>
    mergeKeywords(
        localized(props.project, 'meta_keywords'),
        categoryName.value,
    ),
)

const ogImageValue = computed(() => props.project?.thumbnail_url || null)

const {
    headTitle,
    title,
    description: siteDescription,
    keywords: siteKeywords,
    ogImage,
} = useSiteSeo({
    pageTitle: pageTitle.value,
    description: seoDescriptionValue.value,
    keywords: seoKeywordsValue.value,
    ogImage: ogImageValue.value,
})

const seoDescription = computed(
    () => seoDescriptionValue.value || siteDescription.value,
)

const seoKeywords = computed(
    () => seoKeywordsValue.value || siteKeywords.value,
)

const seoOgImage = computed(() => ogImage.value || ogImageValue.value || '')

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.projects.title'), href: localePath('projects.index') },
    { label: name.value || t('public.projects.title') },
])

const hasMeta = computed(
    () =>
        Boolean(props.project?.client_name) ||
        Boolean(props.project?.location) ||
        Boolean(props.project?.year),
)

const hasIntro = computed(
    () => Boolean(shortDescription.value) || Boolean(longDescription.value),
)

const images2d = computed(() => props.project?.images_2d ?? [])
const images3d = computed(() => props.project?.images_3d ?? [])
const imagesOutdoor = computed(() => props.project?.images_outdoor ?? [])

const youtubeId = computed(() => {
    const url = props.project?.video_url
    if (!url || typeof url !== 'string') {
        return null
    }

    try {
        const parsed = new URL(url)
        const host = parsed.hostname.replace(/^www\./, '')

        if (host === 'youtu.be') {
            const id = parsed.pathname.split('/').filter(Boolean)[0]
            return id || null
        }

        if (host === 'youtube.com' || host === 'm.youtube.com') {
            const v = parsed.searchParams.get('v')
            if (v) {
                return v
            }
            const parts = parsed.pathname.split('/').filter(Boolean)
            if (parts[0] === 'embed' || parts[0] === 'shorts') {
                return parts[1] || null
            }
        }
    } catch {
        return null
    }

    return null
})

const youtubeEmbedUrl = computed(() =>
    youtubeId.value
        ? `https://www.youtube.com/embed/${youtubeId.value}`
        : null,
)
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ headTitle }}</title>
            <meta
                v-if="seoDescription"
                head-key="description"
                name="description"
                :content="seoDescription"
            />
            <meta
                v-if="seoKeywords"
                head-key="keywords"
                name="keywords"
                :content="seoKeywords"
            />
            <meta
                head-key="og:title"
                property="og:title"
                :content="title"
            />
            <meta
                v-if="seoDescription"
                head-key="og:description"
                property="og:description"
                :content="seoDescription"
            />
            <meta
                v-if="seoOgImage"
                head-key="og:image"
                property="og:image"
                :content="seoOgImage"
            />
        </Head>

        <InnerHero
            :title="name || t('public.projects.title')"
            :breadcrumbs="breadcrumbs"
            :category="categoryName"
            :background-image="project.thumbnail_url"
        />

        <main
            class="mx-auto flex w-full max-w-[1440px] flex-col gap-[80px] px-margin-mobile py-xl md:gap-[120px] md:px-margin-desktop"
        >
            <section
                v-if="hasMeta"
                class="flex flex-wrap items-center gap-md border-t border-outline-variant pt-md text-label-lg text-on-surface-variant"
            >
                <div
                    v-if="project.client_name"
                    class="flex items-center gap-2"
                >
                    <span class="text-outline"
                        >{{ t('public.projects.client') }}:</span
                    >
                    <span>{{ project.client_name }}</span>
                </div>
                <div
                    v-if="
                        project.client_name &&
                        (project.location || project.year)
                    "
                    class="hidden h-4 w-px bg-outline-variant sm:block"
                    aria-hidden="true"
                />
                <div
                    v-if="project.location"
                    class="flex items-center gap-2"
                >
                    <span class="text-outline"
                        >{{ t('public.projects.location') }}:</span
                    >
                    <span>{{ project.location }}</span>
                </div>
                <div
                    v-if="project.location && project.year"
                    class="hidden h-4 w-px bg-outline-variant sm:block"
                    aria-hidden="true"
                />
                <div
                    v-if="project.year"
                    class="flex items-center gap-2"
                >
                    <span class="text-outline"
                        >{{ t('public.projects.year') }}:</span
                    >
                    <span>{{ project.year }}</span>
                </div>
            </section>

            <section
                v-if="hasIntro"
                class="grid grid-cols-1 gap-gutter md:grid-cols-12"
            >
                <div
                    v-if="shortDescription"
                    class="md:col-span-4"
                >
                    <p
                        class="pe-md text-headline-lg-mobile leading-snug text-on-surface-variant"
                    >
                        {{ shortDescription }}
                    </p>
                </div>
                <div
                    v-if="longDescription"
                    class="flex flex-col gap-sm text-body-lg text-on-surface-variant"
                    :class="
                        shortDescription ? 'md:col-span-8' : 'md:col-span-12'
                    "
                >
                    <p class="whitespace-pre-line">
                        {{ longDescription }}
                    </p>
                </div>
            </section>

            <section
                v-if="project.preview_video_url"
                class="w-full"
                :aria-label="t('public.projects.preview_video')"
            >
                <div
                    class="aspect-[21/9] rounded-lg border border-outline-variant bg-surface-container-lowest p-1"
                >
                    <div class="relative h-full w-full overflow-hidden">
                        <video
                            class="h-full w-full object-cover"
                            :src="project.preview_video_url"
                            autoplay
                            muted
                            loop
                            playsinline
                        />
                    </div>
                </div>
            </section>

            <ProjectGallerySection
                :title="t('public.projects.gallery_2d')"
                :images="images2d"
            />

            <ProjectGallerySection
                :title="t('public.projects.gallery_3d')"
                :images="images3d"
            />

            <ProjectGallerySection
                :title="t('public.projects.gallery_landscape')"
                :images="imagesOutdoor"
            />

            <section
                v-if="youtubeEmbedUrl"
                class="flex flex-col gap-lg"
            >
                <div class="flex items-center gap-md">
                    <h2
                        class="shrink-0 text-label-lg uppercase tracking-widest text-primary"
                    >
                        {{ t('public.projects.youtube') }}
                    </h2>
                    <div class="h-px flex-grow bg-outline-variant" />
                </div>
                <div
                    class="aspect-video overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest"
                >
                    <iframe
                        class="h-full w-full"
                        :src="youtubeEmbedUrl"
                        :title="name || t('public.projects.youtube')"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                    />
                </div>
            </section>
        </main>
    </AppLayout>
</template>
