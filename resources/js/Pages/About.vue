<script setup>
import { computed } from 'vue'
import { IconCompass } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
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
const { localized } = useLocale()

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
</script>

<template>
    <AppLayout :title="t('nav.about')">
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
                    class="grid grid-cols-1 items-center gap-gutter md:grid-cols-12"
                >
                    <div
                        v-if="about?.story_image_url"
                        class="relative md:col-span-5"
                    >
                        <img
                            :src="about.story_image_url"
                            :alt="storyTitle"
                            class="aspect-[3/4] w-full rounded-lg object-cover"
                        />
                    </div>
                    <div
                        class="md:col-span-6 md:col-start-7"
                        :class="{
                            'md:col-span-12 md:col-start-1':
                                !about?.story_image_url,
                        }"
                    >
                        <span
                            class="mb-4 block text-label-lg uppercase tracking-widest text-secondary"
                        >
                            {{ t('public.about.eyebrow') }}
                        </span>
                        <h1
                            v-if="storyTitle"
                            class="mb-md text-display-md text-on-surface"
                        >
                            {{ storyTitle }}
                        </h1>
                        <p
                            v-if="storyDescription"
                            class="whitespace-pre-line text-body-md text-on-surface-variant"
                        >
                            {{ storyDescription }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="statistics.length"
                    class="mt-xl flex flex-wrap items-center justify-center gap-xl border-y border-outline-variant py-lg"
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
