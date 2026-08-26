<script setup>
import { computed } from 'vue'
import { IconCompass } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import StatBlock from '@/Components/Public/StatBlock.vue'
import { resolveAppIcon } from '@/icons/appIcons'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    about: { type: Object, default: null },
    statistics: { type: Array, default: () => [] },
    coreValues: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

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

const hasContent = computed(
    () =>
        storyTitle.value ||
        visionTitle.value ||
        missionTitle.value ||
        props.coreValues.length ||
        props.statistics.length,
)

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('nav.about') },
])

const heroTitle = computed(() => storyTitle.value || t('nav.about'))
</script>

<template>
    <AppLayout :title="t('nav.about')">
        <InnerHero
            :title="heroTitle"
            :eyebrow="t('public.about.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="about?.story_image_url"
        />

        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p
                v-if="!hasContent"
                class="text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.about.empty') }}
            </p>

            <template v-else>
                <div
                    v-if="storyDescription"
                    class="mx-auto mb-xl max-w-3xl text-start"
                >
                    <p
                        class="whitespace-pre-line text-body-lg text-on-surface-variant"
                    >
                        {{ storyDescription }}
                    </p>
                </div>

                <div
                    v-if="statistics.length"
                    class="flex flex-wrap items-center justify-center gap-xl border-y border-outline-variant py-lg"
                >
                    <StatBlock
                        v-for="stat in statistics"
                        :key="stat.id"
                        :statistic="stat"
                    />
                </div>

                <div
                    v-if="visionTitle || missionTitle"
                    class="mt-xl grid grid-cols-1 gap-gutter md:grid-cols-2"
                >
                    <article
                        v-if="visionTitle"
                        class="rounded-lg border border-outline-variant bg-surface-container p-lg"
                    >
                        <h2 class="mb-sm text-headline-lg text-on-surface">
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
                        class="rounded-lg border border-outline-variant bg-surface-container p-lg"
                    >
                        <h2 class="mb-sm text-headline-lg text-on-surface">
                            {{ missionTitle }}
                        </h2>
                        <p
                            class="whitespace-pre-line text-body-md text-on-surface-variant"
                        >
                            {{ missionDescription }}
                        </p>
                    </article>
                </div>

                <div
                    v-if="coreValues.length"
                    class="mt-xl"
                >
                    <h2 class="mb-lg text-center text-display-md text-on-surface">
                        {{ t('public.about.values_title') }}
                    </h2>
                    <div
                        class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="value in coreValues"
                            :key="value.id"
                            class="rounded-lg border border-outline-variant bg-surface-container p-lg transition-colors hover:border-secondary hover:bg-surface-container-high"
                        >
                            <component
                                :is="resolveIcon(value.icon)"
                                class="mb-md text-primary"
                                :size="32"
                                stroke-width="1.5"
                            />
                            <h3 class="mb-2 text-headline-lg-mobile text-on-surface">
                                {{ localized(value, 'title') }}
                            </h3>
                            <p class="text-body-md text-on-surface-variant">
                                {{ localized(value, 'short_description') }}
                            </p>
                        </article>
                    </div>
                </div>
            </template>
        </section>
    </AppLayout>
</template>
