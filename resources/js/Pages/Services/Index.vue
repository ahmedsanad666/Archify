<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const { t } = useUiTranslations()
const { localePath } = useLocale()

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('public.services.title') },
])
</script>

<template>
    <AppLayout :title="t('public.services.title')">
        <InnerHero
            :title="t('public.services.title')"
            :eyebrow="t('public.services.eyebrow')"
            :breadcrumbs="breadcrumbs"
        />
        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p class="text-center text-body-lg text-on-surface-variant">
                {{ t('public.services.empty') }}
            </p>
        </section>
    </AppLayout>
</template>
