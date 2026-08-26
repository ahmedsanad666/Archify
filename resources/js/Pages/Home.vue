<script setup>
import { computed } from 'vue'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { Link } from '@inertiajs/vue3'
import { IconChevronDown } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeroSlider from '@/Components/Public/HeroSlider.vue'
import ProjectCard from '@/Components/Public/ProjectCard.vue'
import ServiceCard from '@/Components/Public/ServiceCard.vue'
import StatBlock from '@/Components/Public/StatBlock.vue'
import BlogCard from '@/Components/Public/BlogCard.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    sliders: { type: Array, default: () => [] },
    about: { type: Object, default: null },
    services: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    statistics: { type: Array, default: () => [] },
    blogs: { type: Array, default: () => [] },
    faqs: { type: Array, default: () => [] },
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

        <!-- Our story -->
        <section
            v-if="storyTitle || storyDescription || about?.story_image_url"
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div
                class="grid grid-cols-1 items-center gap-gutter md:grid-cols-12"
            >
                <div
                    v-if="about?.story_image_url"
                    class="relative min-w-0 md:col-span-5"
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
                    class="mt-lg min-w-0 text-start md:col-span-6 md:col-start-7 md:mt-0"
                    :class="{
                        'md:col-span-12 md:col-start-1': !about?.story_image_url,
                    }"
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
                    <Link
                        :href="localePath('about')"
                        class="inline-flex text-label-lg uppercase tracking-wide text-primary transition-colors hover:text-secondary"
                    >
                        {{ t('common.learn_more') }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Projects (2) -->
        <section
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
                v-if="projects.length"
                class="mx-auto grid max-w-4xl grid-cols-1 gap-gutter sm:grid-cols-2"
            >
                <ProjectCard
                    v-for="project in projects"
                    :key="project.id"
                    :project="project"
                />
            </div>
            <p
                v-else
                class="text-center text-body-md text-on-surface-variant"
            >
                {{ t('public.home.empty_projects') }}
            </p>
        </section>

        <!-- Services (3) -->
        <section class="bg-surface-container-lowest py-xl">
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
                    v-if="services.length"
                    class="grid grid-cols-1 gap-gutter md:grid-cols-2 lg:grid-cols-3"
                >
                    <ServiceCard
                        v-for="(service, i) in services"
                        :key="service.id"
                        :service="service"
                        :index="i"
                    />
                </div>
                <p
                    v-else
                    class="text-center text-body-md text-on-surface-variant"
                >
                    {{ t('public.home.empty_services') }}
                </p>
            </div>
        </section>

        <!-- Statistics -->
        <section
            v-if="statistics.length"
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <h2 class="mb-lg text-center text-display-md text-on-surface">
                {{ t('public.home.stats_title') }}
            </h2>
            <div
                class="flex flex-wrap items-center justify-center gap-xl border-y border-outline-variant py-lg"
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
        </section>

        <!-- Blogs -->
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

        <!-- FAQs -->
        <section
            class="mx-auto max-w-3xl px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div class="mb-xl text-center">
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.home.faqs_title') }}
                </h2>
                <p class="text-body-lg text-on-surface-variant">
                    {{ t('public.home.faqs_subtitle') }}
                </p>
            </div>

            <div
                v-if="faqs.length"
                class="overflow-hidden rounded-lg border border-outline-variant divide-y divide-outline-variant"
            >
                <Disclosure
                    v-for="faq in faqs"
                    :key="faq.id"
                    v-slot="{ open }"
                    as="div"
                    class="bg-surface-container"
                >
                    <DisclosureButton
                        class="flex w-full items-center justify-between gap-4 px-md py-6 text-start transition-colors hover:bg-surface-container-high"
                    >
                        <span
                            class="min-w-0 text-headline-lg-mobile text-on-surface"
                        >
                            {{ localized(faq, 'question') }}
                        </span>
                        <IconChevronDown
                            class="shrink-0 text-on-surface-variant transition-transform duration-200"
                            :class="{ 'rotate-180 text-primary': open }"
                            :size="22"
                            stroke-width="1.5"
                        />
                    </DisclosureButton>
                    <DisclosurePanel
                        class="border-t border-outline-variant px-md pb-6 pt-4 text-start text-body-md text-on-surface-variant"
                    >
                        {{ localized(faq, 'answer') }}
                    </DisclosurePanel>
                </Disclosure>
            </div>
            <p
                v-else
                class="text-center text-body-md text-on-surface-variant"
            >
                {{ t('public.home.empty_faqs') }}
            </p>
        </section>

        <!-- CTA -->
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
                <Link
                    :href="localePath('contact')"
                    class="inline-flex rounded-md bg-primary px-8 py-4 text-label-lg uppercase tracking-wider text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                >
                    {{ t('public.home.cta_button') }}
                </Link>
            </div>
        </section>
    </AppLayout>
</template>
