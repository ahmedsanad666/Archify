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
    { label: t('public.blogs.title') },
])
</script>

<template>
    <AppLayout :title="t('public.blogs.title')">
        <InnerHero
            :title="t('public.blogs.title')"
            :eyebrow="t('public.blogs.eyebrow')"
            :breadcrumbs="breadcrumbs"
        />
        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p class="text-center text-body-lg text-on-surface-variant">
                {{ t('public.blogs.empty') }}
            </p>
        </section>
    </AppLayout>
</template>
