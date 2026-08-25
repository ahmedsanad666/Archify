<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeroSlider from '@/Components/Public/HeroSlider.vue'
import ProjectCard from '@/Components/Public/ProjectCard.vue'
import ServiceCard from '@/Components/Public/ServiceCard.vue'
import StatBlock from '@/Components/Public/StatBlock.vue'
import TestimonialCarousel from '@/Components/Public/TestimonialCarousel.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    about: { type: Object, default: null },
    services: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    statistics: { type: Array, default: () => [] },
    coreValues: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

const storyTitle = computed(() => localized(props.about, 'story_title'))
const storyDescription = computed(() =>
    localized(props.about, 'story_description'),
)
</script>

<template>
    <AppLayout :title="t('nav.home')">
        <HeroSlider :sliders="sliders" />

        <section
            v-if="storyTitle || storyDescription || statistics.length"
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div
                class="grid grid-cols-1 items-center gap-gutter md:grid-cols-12"
            >
                <div
                    v-if="about?.story_image_url"
                    class="relative md:col-span-5"
                >
                    <div
                        class="pointer-events-none absolute inset-0 translate-x-4 translate-y-4 border border-outline rtl:-translate-x-4"
                    />
                    <img
                        :src="about.story_image_url"
                        :alt="storyTitle"
                        class="relative z-10 aspect-[3/4] w-full object-cover"
                    />
                </div>
                <div
                    class="mt-lg md:col-span-6 md:col-start-7 md:mt-0"
                    :class="{ 'md:col-span-12 md:col-start-1': !about?.story_image_url }"
                >
                    <span
                        class="mb-4 block text-label-lg uppercase tracking-widest text-secondary"
                    >
                        {{ t('public.about.eyebrow') }}
                    </span>
                    <h2
                        v-if="storyTitle"
                        class="mb-md text-display-md text-on-surface"
                    >
                        {{ storyTitle }}
                    </h2>
                    <p
                        v-if="storyDescription"
                        class="mb-lg whitespace-pre-line text-body-md text-on-surface-variant"
                    >
                        {{ storyDescription }}
                    </p>
                    <div
                        v-if="statistics.length"
                        class="flex flex-wrap items-center gap-xl border-t border-outline-variant pt-md"
                    >
                        <template
                            v-for="(stat, i) in statistics"
                            :key="stat.id"
                        >
                            <StatBlock :statistic="stat" />
                            <div
                                v-if="i < statistics.length - 1"
                                class="hidden h-12 w-px bg-outline-variant sm:block"
                            />
                        </template>
                    </div>
                    <Link
                        :href="localePath('about')"
                        class="mt-lg inline-flex text-label-lg uppercase tracking-wide text-primary transition-colors hover:text-secondary"
                    >
                        {{ t('common.learn_more') }}
                    </Link>
                </div>
            </div>
        </section>

        <section
            v-if="services.length"
            class="bg-surface-container-lowest py-xl"
        >
            <div
                class="mx-auto max-w-[1440px] px-margin-mobile md:px-margin-desktop"
            >
                <div class="mb-xl text-center">
                    <h2 class="mb-sm text-display-md text-on-surface">
                        {{ t('public.home.services_title') }}
                    </h2>
                    <p
                        class="mx-auto max-w-2xl text-body-lg text-on-surface-variant"
                    >
                        {{ t('public.home.services_subtitle') }}
                    </p>
                </div>
                <div
                    class="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3"
                >
                    <ServiceCard
                        v-for="(service, i) in services"
                        :key="service.id"
                        :service="service"
                        :index="i"
                    />
                </div>
            </div>
        </section>

        <section
            v-if="projects.length"
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div class="mb-xl text-center">
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.home.projects_title') }}
                </h2>
                <p
                    class="mx-auto max-w-2xl text-body-lg text-on-surface-variant"
                >
                    {{ t('public.home.projects_subtitle') }}
                </p>
            </div>
            <div
                class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3"
            >
                <ProjectCard
                    v-for="project in projects"
                    :key="project.id"
                    :project="project"
                />
            </div>
        </section>

        <section
            v-if="testimonials.length"
            class="bg-surface-container-lowest py-xl"
        >
            <div
                class="mx-auto max-w-[1440px] px-margin-mobile md:px-margin-desktop"
            >
                <h2 class="mb-xl text-center text-display-md text-on-surface">
                    {{ t('public.home.testimonials_title') }}
                </h2>
                <TestimonialCarousel :testimonials="testimonials" />
            </div>
        </section>

        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div
                class="rounded-xl border border-outline-variant bg-surface-container-high p-xl text-center"
            >
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.home.cta_title') }}
                </h2>
                <p
                    class="mx-auto mb-lg max-w-2xl text-body-lg text-on-surface-variant"
                >
                    {{ t('public.home.cta_body') }}
                </p>
                <span
                    class="inline-flex cursor-default rounded-md bg-primary px-8 py-4 text-label-lg uppercase tracking-wider text-on-primary opacity-80"
                >
                    {{ t('public.home.cta_button') }}
                </span>
            </div>
        </section>
    </AppLayout>
</template>
