<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    about: {
        type: Object,
        default: null,
    },
    showLearnMore: {
        type: Boolean,
        default: true,
    },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

const storyTitle = computed(() => localized(props.about, 'story_title'))
const storyDescription = computed(() =>
    localized(props.about, 'story_description'),
)

const hasContent = computed(
    () =>
        Boolean(storyTitle.value) ||
        Boolean(storyDescription.value) ||
        Boolean(props.about?.story_image_url),
)
</script>

<template>
    <section
        v-if="hasContent"
        class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
    >
        <div class="grid grid-cols-1 items-center gap-gutter md:grid-cols-12">
            <div
                v-if="about?.story_image_url"
                class="relative min-w-0 md:col-span-5"
            >
                <div
                    class="pointer-events-none absolute inset-0 translate-x-4 translate-y-4 rounded-lg border border-primary-container rtl:-translate-x-4"
                />
                <img
                    :src="about.story_image_url"
                    :alt="storyTitle"
                    class="relative z-10 aspect-[4/5] w-full rounded-lg border border-outline-variant object-cover"
                />
            </div>
            <div
                class="mt-lg min-w-0 space-y-6 text-start md:col-span-6 md:col-start-7 md:mt-0"
                :class="{
                    'md:col-span-12 md:col-start-1': !about?.story_image_url,
                }"
            >
                <span
                    v-if="showLearnMore"
                    class="mb-4 block text-label-lg uppercase tracking-widest text-secondary"
                >
                    {{ t('public.about.eyebrow') }}
                </span>
                <h2
                    v-if="storyTitle"
                    class="text-headline-lg-mobile text-on-surface sm:text-headline-lg"
                >
                    {{ storyTitle }}
                </h2>
                <p
                    v-if="storyDescription"
                    class="whitespace-pre-line text-body-md text-on-surface-variant"
                    :class="{ 'mb-lg': showLearnMore }"
                >
                    {{ storyDescription }}
                </p>
                <Link
                    v-if="showLearnMore"
                    :href="localePath('about')"
                    class="inline-flex text-label-lg uppercase tracking-wide text-primary transition-colors hover:text-secondary"
                >
                    {{ t('common.learn_more') }}
                </Link>
            </div>
        </div>
    </section>
</template>
