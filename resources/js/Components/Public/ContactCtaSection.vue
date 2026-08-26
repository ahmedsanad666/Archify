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
            class="relative overflow-hidden rounded-xl border border-outline-variant p-md text-center sm:p-lg md:p-xl"
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
                <h2
                    class="mb-sm text-headline-lg-mobile text-on-surface sm:text-headline-lg md:text-display-md"
                >
                    {{ t('public.home.cta_title') }}
                </h2>
                <p
                    class="mx-auto mb-md max-w-2xl text-body-md text-on-surface-variant sm:mb-lg sm:text-body-lg"
                >
                    {{ t('public.home.cta_body') }}
                </p>
                <Link
                    :href="localePath('contact')"
                    class="inline-flex rounded-md bg-primary px-6 py-3 text-label-md uppercase tracking-wider text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container sm:px-8 sm:py-4 sm:text-label-lg"
                >
                    {{ t('public.home.cta_button') }}
                </Link>
            </div>
        </div>
    </section>
</template>
