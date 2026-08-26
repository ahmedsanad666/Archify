<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { IconPlus, IconX } from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'

defineProps({
    faqs: {
        type: Array,
        default: () => [],
    },
})

const { localized } = useLocale()
</script>

<template>
    <div class="flex flex-col gap-sm">
        <Disclosure
            v-for="faq in faqs"
            :key="faq.id"
            v-slot="{ open }"
            as="div"
            class="rounded-lg border border-outline-variant bg-surface-container transition-colors duration-200 hover:bg-surface-container-high"
        >
            <DisclosureButton
                class="flex w-full items-center justify-between gap-4 px-md py-6 text-start"
            >
                <span class="min-w-0 text-body-lg font-semibold text-on-surface">
                    {{ localized(faq, 'question') }}
                </span>
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-primary text-primary"
                    aria-hidden="true"
                >
                    <IconX
                        v-if="open"
                        :size="18"
                        stroke-width="1.5"
                    />
                    <IconPlus
                        v-else
                        :size="18"
                        stroke-width="1.5"
                    />
                </span>
            </DisclosureButton>
            <DisclosurePanel
                class="px-md pb-6 text-start text-body-md text-on-surface-variant"
            >
                {{ localized(faq, 'answer') }}
            </DisclosurePanel>
        </Disclosure>
    </div>
</template>
