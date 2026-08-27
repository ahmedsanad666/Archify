<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { IconCompass, IconEye, IconFlag } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import AboutStorySection from '@/Components/Public/AboutStorySection.vue'
import StatisticsSection from '@/Components/Public/StatisticsSection.vue'
import TeamSection from '@/Components/Public/TeamSection.vue'
import ContactCtaSection from '@/Components/Public/ContactCtaSection.vue'
import { resolveAppIcon } from '@/icons/appIcons'
import { useLocale } from '@/Composables/useLocale'
import { useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    about: { type: Object, default: null },
    statistics: { type: Array, default: () => [] },
    coreValues: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()
const page = usePage()
const heroBanner = computed(
    () => page.props.siteSettings?.media?.banner_about ?? null,
)
const { headTitle, title, description, keywords } = useSiteSeo({
    pageTitle: t('nav.about'),
})

const resolveIcon = (name) => resolveAppIcon(name, IconCompass)

const storyTitle = computed(() => localized(props.about, 'story_title'))
const storyDescription = computed(() =>
    localized(props.about, 'story_description'),
)
const visionTitle = computed(() => localized(props.about, 'vision_title'))
const visionDescription = computed(() =>
    localized(props.about, 'vision_description'),
)
const missionTitle = computed(() => localized(props.about, 'mission_title'))
const missionDescription = computed(() =>
    localized(props.about, 'mission_description'),
)

const hasStory = computed(
    () =>
        Boolean(storyTitle.value) ||
        Boolean(storyDescription.value) ||
        Boolean(props.about?.story_image_url),
)

const hasContent = computed(
    () =>
        hasStory.value ||
        visionTitle.value ||
        missionTitle.value ||
        props.coreValues.length ||
        props.statistics.length ||
        props.members.length,
)

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('nav.about') },
])
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
            :title="t('nav.about')"
            :eyebrow="t('public.about.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />

        <template v-if="!hasContent">
            <section
                class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
            >
                <p class="text-center text-body-lg text-on-surface-variant">
                    {{ t('public.about.empty') }}
                </p>
            </section>
        </template>

        <template v-else>
            <AboutStorySection
                v-if="hasStory"
                :about="about"
                :show-learn-more="false"
            />

            <StatisticsSection :statistics="statistics" />

            <section
                v-if="visionTitle || missionTitle"
                class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
            >
                <div class="grid grid-cols-1 gap-gutter md:grid-cols-2">
                    <article
                        v-if="visionTitle"
                        class="group rounded-lg border border-outline-variant bg-surface-container p-md transition-all duration-300 hover:border-secondary hover:bg-surface-container-high sm:p-lg md:p-xl"
                    >
                        <IconEye
                            class="mb-6 block text-primary-container transition-transform duration-300 group-hover:scale-110"
                            :size="40"
                            stroke-width="1.5"
                        />
                        <h2
                            class="mb-4 text-headline-lg-mobile text-on-surface sm:text-headline-lg"
                        >
                            {{ visionTitle }}
                        </h2>
                        <p
                            class="whitespace-pre-line text-body-md text-on-surface-variant"
                        >
                            {{ visionDescription }}
                        </p>
                    </article>
                    <article
                        v-if="missionTitle"
                        class="group rounded-lg border border-outline-variant bg-surface-container p-md transition-all duration-300 hover:border-secondary hover:bg-surface-container-high sm:p-lg md:p-xl"
                    >
                        <IconFlag
                            class="mb-6 block text-primary-container transition-transform duration-300 group-hover:scale-110"
                            :size="40"
                            stroke-width="1.5"
                        />
                        <h2
                            class="mb-4 text-headline-lg-mobile text-on-surface sm:text-headline-lg"
                        >
                            {{ missionTitle }}
                        </h2>
                        <p
                            class="whitespace-pre-line text-body-md text-on-surface-variant"
                        >
                            {{ missionDescription }}
                        </p>
                    </article>
                </div>
            </section>

            <section
                v-if="coreValues.length"
                class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
            >
                <div
                    class="mb-8 flex items-center justify-center gap-4 sm:mb-12"
                >
                    <div class="h-px w-8 bg-primary-container sm:w-12" />
                    <span
                        class="text-label-md uppercase tracking-widest text-primary-container sm:text-label-lg"
                    >
                        {{ t('public.about.values_title') }}
                    </span>
                    <div class="h-px w-8 bg-primary-container sm:w-12" />
                </div>
                <div
                    class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-4"
                >
                    <article
                        v-for="value in coreValues"
                        :key="value.id"
                        class="flex flex-col items-center rounded-lg border border-outline-variant bg-surface p-4 text-center transition-colors duration-300 hover:bg-surface-container sm:p-6 md:p-8"
                    >
                        <component
                            :is="resolveIcon(value.icon)"
                            class="mb-3 text-primary-container sm:mb-4"
                            :size="28"
                            stroke-width="1.5"
                        />
                        <h3
                            class="mb-1 text-body-md font-semibold text-on-surface sm:mb-2 sm:text-body-lg"
                        >
                            {{ localized(value, 'title') }}
                        </h3>
                        <p
                            class="text-label-md leading-relaxed text-on-surface-variant sm:text-body-md"
                        >
                            {{ localized(value, 'short_description') }}
                        </p>
                    </article>
                </div>
            </section>

            <TeamSection :members="members" />

            <ContactCtaSection />
        </template>
    </AppLayout>
</template>
