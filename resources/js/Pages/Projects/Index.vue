<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import ProjectCard from '@/Components/Public/ProjectCard.vue'
import AppPagination from '@/Components/Shared/AppPagination.vue'
import { useLocale } from '@/Composables/useLocale'
import { useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    projects: {
        type: Object,
        default: () => ({ data: [], meta: null }),
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
    () => page.props.siteSettings?.media?.banner_projects ?? null,
)

const projectCategories = computed(() => page.props.projectCategories ?? [])

const { headTitle, title, description, keywords } = useSiteSeo({
    pageTitle: t('public.projects.title'),
})

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.projects.title') },
])

const activeCategory = computed(() => props.filters?.category ?? null)

const projectItems = computed(() => props.projects?.data ?? [])

const categorySlug = (category) => {
    const code = locale.value?.code || 'en'
    return (
        category?.translations?.[code]?.slug ||
        category?.translations?.en?.slug ||
        null
    )
}

const categoryHref = (slug = null) => {
    const base = localePath('projects.index')
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
            :title="t('public.projects.title')"
            :eyebrow="t('public.projects.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />

        <section
            class="mx-auto w-full max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div
                class="mb-xl flex flex-col items-center justify-between gap-md border-b border-outline-variant pb-md md:flex-row"
            >
                <span
                    class="text-label-md uppercase tracking-wider text-outline"
                >
                    {{ t('public.projects.filter') }}
                </span>
                <div class="flex flex-wrap justify-center gap-sm">
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
                        v-for="category in projectCategories"
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
                v-if="projectItems.length"
                class="mb-xl grid grid-cols-1 gap-gutter md:grid-cols-2"
            >
                <ProjectCard
                    v-for="(project, index) in projectItems"
                    :key="project.id"
                    :project="project"
                    :href="
                        localePath('projects.show', {
                            slug: localized(project, 'slug'),
                        })
                    "
                    :class="index % 2 === 1 ? 'md:mt-xl' : ''"
                />
            </div>
            <p
                v-else
                class="mb-xl text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.projects.empty') }}
            </p>

            <AppPagination
                :meta="projects.meta"
                class="mt-md"
            />
        </section>
    </AppLayout>
</template>
