<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import FaqList from '@/Components/Public/FaqList.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    faqs: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
</script>

<template>
    <AppLayout :title="t('nav.faq')">
        <section
            class="mx-auto max-w-5xl px-margin-mobile pb-xl pt-24 md:px-margin-desktop md:pt-28"
        >
            <div class="mb-xl text-center">
                <h1 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.faq.title') }}
                </h1>
                <p class="text-body-lg text-on-surface-variant">
                    {{ t('public.faq.subtitle') }}
                </p>
            </div>

            <p
                v-if="!faqs.length"
                class="text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.faq.empty') }}
            </p>

            <FaqList
                v-else
                :faqs="faqs"
            />
        </section>
    </AppLayout>
</template>
