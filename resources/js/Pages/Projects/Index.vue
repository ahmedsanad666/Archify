<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const { t } = useUiTranslations()
const { localePath } = useLocale()
const page = usePage()
const heroBanner = computed(
    () => page.props.siteSettings?.media?.banner_projects ?? null,
)

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.projects.title') },
])
</script>

<template>
    <AppLayout :title="t('public.projects.title')">
        <InnerHero
            :title="t('public.projects.title')"
            :eyebrow="t('public.projects.eyebrow')"
            :breadcrumbs="breadcrumbs"
            :background-image="heroBanner"
        />
        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p class="text-center text-body-lg text-on-surface-variant">
                {{ t('public.projects.empty') }}
            </p>
        </section>
    </AppLayout>
</template>
