<script setup>
import { Link } from '@inertiajs/vue3'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const { t } = useUiTranslations()
const { localePath } = useLocale()
</script>

<template>
    <section
        class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
    >
        <div
            class="relative overflow-hidden rounded-xl border border-outline-variant p-xl text-center"
        >
            <div
                class="absolute inset-0 bg-[url('/images/contact-cta.jpg')] bg-cover bg-center bg-no-repeat"
                aria-hidden="true"
            />
            <div
                class="absolute inset-0 bg-surface/80"
                aria-hidden="true"
            />

            <div class="relative z-10">
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
        </div>
    </section>
</template>
