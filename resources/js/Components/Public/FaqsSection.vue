<script setup>
import FaqList from '@/Components/Public/FaqList.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    faqs: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
</script>

<template>
    <section
        class="mx-auto max-w-5xl px-margin-mobile py-xl md:px-margin-desktop"
    >
        <div class="mb-xl text-center">
            <h2 class="mb-sm text-display-md text-on-surface">
                {{ t('public.home.faqs_title') }}
            </h2>
            <p class="text-body-lg text-on-surface-variant">
                {{ t('public.home.faqs_subtitle') }}
            </p>
        </div>

        <FaqList
            v-if="faqs.length"
            :faqs="faqs"
        />
        <p
            v-else
            class="text-center text-body-md text-on-surface-variant"
        >
            {{ t('public.home.empty_faqs') }}
        </p>
    </section>
</template>
