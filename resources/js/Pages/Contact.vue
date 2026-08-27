<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import {
    IconBrandInstagram,
    IconBrandX,
    IconBrandYoutube,
    IconMail,
    IconMapPin,
    IconPhone,
} from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import ContactForm from '@/Components/Public/ContactForm.vue'
import FaqsSection from '@/Components/Public/FaqsSection.vue'
import { useLocale } from '@/Composables/useLocale'
import { useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    services: {
        type: Array,
        default: () => [],
    },
    faqs: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
const { localePath } = useLocale()
const page = usePage()

const settings = computed(() => page.props.siteSettings ?? {})

const heroBanner = computed(
    () => settings.value?.media?.banner_contact ?? null,
)

const { headTitle, title, description, keywords } = useSiteSeo({
    pageTitle: t('public.contact.title'),
})

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.contact.title') },
])

const hasDetails = computed(
    () =>
        Boolean(settings.value?.address) ||
        Boolean(settings.value?.email) ||
        Boolean(settings.value?.phone),
)

const mapLat = computed(() => {
    const value = settings.value?.map_lat
    return value !== null && value !== undefined && value !== ''
        ? Number(value)
        : null
})

const mapLng = computed(() => {
    const value = settings.value?.map_lng
    return value !== null && value !== undefined && value !== ''
        ? Number(value)
        : null
})

const hasMap = computed(
    () =>
        mapLat.value !== null &&
        !Number.isNaN(mapLat.value) &&
        mapLng.value !== null &&
        !Number.isNaN(mapLng.value),
)

const mapEmbedUrl = computed(() => {
    if (!hasMap.value) {
        return null
    }
    const lat = mapLat.value
    const lng = mapLng.value
    const delta = 0.02
    const bbox = [
        lng - delta,
        lat - delta,
        lng + delta,
        lat + delta,
    ].join(',')

    return `https://www.openstreetmap.org/export/embed.html?bbox=${encodeURIComponent(bbox)}&layer=mapnik&marker=${encodeURIComponent(`${lat},${lng}`)}`
})

const socialLinks = computed(() => {
    const links = []
    if (settings.value?.instagram_url) {
        links.push({
            href: settings.value.instagram_url,
            label: 'Instagram',
            icon: IconBrandInstagram,
        })
    }
    if (settings.value?.youtube_url) {
        links.push({
            href: settings.value.youtube_url,
            label: 'YouTube',
            icon: IconBrandYoutube,
        })
    }
    if (settings.value?.twitter_url) {
        links.push({
            href: settings.value.twitter_url,
            label: 'X',
            icon: IconBrandX,
        })
    }
    return links
})
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
            :title="t('public.contact.title')"
            :eyebrow="t('public.contact.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />

        <section
            class="mx-auto w-full max-w-[1440px] px-margin-mobile pt-xl md:px-margin-desktop"
        >
            <p
                class="mb-xl max-w-2xl text-body-lg text-on-surface-variant"
            >
                {{ t('public.contact.intro') }}
            </p>

            <div
                class="grid grid-cols-1 gap-gutter lg:grid-cols-12 lg:gap-xl"
            >
                <div class="lg:col-span-7">
                    <ContactForm :services="services" />
                </div>

                <div
                    class="flex flex-col gap-lg lg:col-span-5"
                >
                    <div
                        v-if="hasDetails"
                        class="rounded-lg border border-outline-variant bg-surface-container p-md md:p-lg"
                    >
                        <h2
                            class="mb-md text-label-lg uppercase tracking-widest text-on-surface"
                        >
                            {{ t('public.contact.details_title') }}
                        </h2>
                        <p
                            class="mb-lg text-body-md text-on-surface-variant"
                        >
                            {{ t('public.contact.details_blurb') }}
                        </p>

                        <div class="flex flex-col gap-md">
                            <div
                                v-if="settings.address"
                                class="flex items-start gap-md"
                            >
                                <IconMapPin
                                    class="mt-1 shrink-0 text-primary"
                                    :size="24"
                                    stroke-width="1.5"
                                />
                                <div class="text-start">
                                    <h3
                                        class="mb-xs text-label-lg uppercase tracking-wider text-primary"
                                    >
                                        {{ t('public.contact.atelier') }}
                                    </h3>
                                    <p
                                        class="whitespace-pre-line text-body-md text-on-surface-variant"
                                    >
                                        {{ settings.address }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="settings.email || settings.phone"
                                class="h-px w-full bg-outline-variant"
                            />

                            <div
                                v-if="settings.email || settings.phone"
                                class="flex items-start gap-md"
                            >
                                <IconMail
                                    class="mt-1 shrink-0 text-primary"
                                    :size="24"
                                    stroke-width="1.5"
                                />
                                <div class="text-start">
                                    <h3
                                        class="mb-xs text-label-lg uppercase tracking-wider text-primary"
                                    >
                                        {{ t('public.contact.direct_connect') }}
                                    </h3>
                                    <a
                                        v-if="settings.email"
                                        :href="`mailto:${settings.email}`"
                                        class="mb-1 block text-body-md text-on-surface-variant transition-colors hover:text-primary"
                                    >
                                        {{ settings.email }}
                                    </a>
                                    <a
                                        v-if="settings.phone"
                                        :href="`tel:${settings.phone}`"
                                        class="flex items-center gap-2 text-body-md text-on-surface-variant transition-colors hover:text-primary"
                                    >
                                        <IconPhone
                                            class="shrink-0"
                                            :size="16"
                                            stroke-width="1.5"
                                        />
                                        {{ settings.phone }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="socialLinks.length">
                        <h3
                            class="mb-md text-label-md uppercase tracking-widest text-on-surface-variant"
                        >
                            {{ t('public.contact.follow') }}
                        </h3>
                        <div class="flex flex-wrap gap-sm">
                            <a
                                v-for="link in socialLinks"
                                :key="link.label"
                                :href="link.href"
                                :aria-label="link.label"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-12 w-12 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant transition-colors hover:border-primary hover:text-primary"
                            >
                                <component
                                    :is="link.icon"
                                    :size="20"
                                    stroke-width="1.5"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="hasMap && mapEmbedUrl"
            class="mx-auto w-full max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <div
                class="relative h-[400px] overflow-hidden rounded-lg border border-outline-variant md:h-[500px]"
            >
                <iframe
                    class="h-full w-full border-0 grayscale-[20%] contrast-125"
                    :src="mapEmbedUrl"
                    :title="t('public.contact.map')"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                />
            </div>
        </section>

        <FaqsSection
            :faqs="faqs"
            :title="t('public.contact.faqs_title')"
            :subtitle="t('public.contact.faqs_subtitle')"
        />
    </AppLayout>
</template>
