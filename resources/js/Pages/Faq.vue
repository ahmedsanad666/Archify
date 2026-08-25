<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { IconChevronDown } from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    faqs: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
const { localized } = useLocale()
</script>

<template>
    <AppLayout :title="t('nav.faq')">
        <section
            class="mx-auto max-w-3xl px-margin-mobile py-xl md:px-margin-desktop"
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

            <div
                v-else
                class="divide-y divide-outline-variant rounded-lg border border-outline-variant overflow-hidden"
            >
                <Disclosure
                    v-for="faq in faqs"
                    :key="faq.id"
                    v-slot="{ open }"
                    as="div"
                    class="bg-surface-container"
                >
                    <DisclosureButton
                        class="flex w-full items-center justify-between gap-4 px-md py-6 text-start transition-colors hover:bg-surface-container-high"
                    >
                        <span class="text-headline-lg-mobile text-on-surface">
                            {{ localized(faq, 'question') }}
                        </span>
                        <IconChevronDown
                            class="shrink-0 text-on-surface-variant transition-transform duration-200"
                            :class="{ 'rotate-180 text-primary': open }"
                            :size="22"
                            stroke-width="1.5"
                        />
                    </DisclosureButton>
                    <DisclosurePanel
                        class="border-t border-outline-variant px-md pb-6 pt-4 text-body-md text-on-surface-variant"
                    >
                        {{ localized(faq, 'answer') }}
                    </DisclosurePanel>
                </Disclosure>
            </div>
        </section>
    </AppLayout>
</template>
