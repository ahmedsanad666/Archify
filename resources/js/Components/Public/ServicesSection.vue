<script setup>
import ServiceCard from '@/Components/Public/ServiceCard.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    services: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
</script>

<template>
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
</template>
