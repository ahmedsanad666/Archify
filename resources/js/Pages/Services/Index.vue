<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import ServiceListRow from '@/Components/Public/ServiceListRow.vue'
import ServicePairRow from '@/Components/Public/ServicePairRow.vue'
import ContactCtaSection from '@/Components/Public/ContactCtaSection.vue'
import { useLocale } from '@/Composables/useLocale'
import { useSiteSeo } from '@/Composables/useSiteSeo'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
const { localePath } = useLocale()
const page = usePage()
const heroBanner = computed(
    () => page.props.siteSettings?.media?.banner_services ?? null,
)

const { headTitle, title, description, keywords } = useSiteSeo({
    pageTitle: t('public.services.title'),
})

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.services.title') },
])

const servicePairs = computed(() => {
    const pairs = []
    for (let i = 0; i < props.services.length; i += 2) {
        pairs.push({
            left: props.services[i],
            right: props.services[i + 1] ?? null,
            startIndex: i,
        })
    }
    return pairs
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
            :title="t('public.services.title')"
            :eyebrow="t('public.services.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />

        <section
            class="mx-auto flex w-full max-w-[1440px] flex-col gap-xl px-margin-mobile pb-xl pt-lg md:px-margin-desktop"
        >
            <p
                class="max-w-2xl text-start text-body-md text-on-surface-variant sm:text-body-lg"
            >
                {{ t('public.services.intro') }}
            </p>

            <!-- Mobile: stacked list with bottom borders -->
            <div
                v-if="services.length"
                class="flex flex-col gap-xl md:hidden"
            >
                <ServiceListRow
                    v-for="(service, i) in services"
                    :key="service.id"
                    :service="service"
                    :index="i"
                    :show-border="i < services.length - 1"
                />
            </div>

            <!-- md+: paired rows with thin S-curve arch -->
            <div
                v-if="servicePairs.length"
                class="hidden flex-col gap-xl md:flex"
            >
                <ServicePairRow
                    v-for="pair in servicePairs"
                    :key="pair.left.id"
                    :left="pair.left"
                    :right="pair.right"
                    :start-index="pair.startIndex"
                />
            </div>

            <p
                v-if="!services.length"
                class="text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.services.empty') }}
            </p>
        </section>

        <ContactCtaSection />
    </AppLayout>
</template>
