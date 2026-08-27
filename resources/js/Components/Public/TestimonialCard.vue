<script setup>
import { computed } from 'vue'
import { IconQuote } from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    testimonial: {
        type: Object,
        required: true,
    },
})

const { localized } = useLocale()

const quote = computed(() => localized(props.testimonial, 'quote'))
const name = computed(() => props.testimonial?.client_name || '')
const avatarUrl = computed(() => props.testimonial?.avatar_url || null)

const initials = computed(() => {
    const parts = String(name.value)
        .trim()
        .split(/\s+/)
        .filter(Boolean)
    if (!parts.length) {
        return '?'
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase()
    }
    return `${parts[0][0] ?? ''}${parts[parts.length - 1][0] ?? ''}`.toUpperCase()
})
</script>

<template>
    <article
        class="flex min-h-[220px] w-[280px] shrink-0 snap-start flex-col justify-between rounded-lg border border-outline-variant bg-surface-container p-md transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high md:min-w-[340px] md:w-[340px]"
    >
        <blockquote
            class="mb-md text-start text-body-md leading-relaxed text-on-surface md:text-body-lg"
        >
            “{{ quote }}”
        </blockquote>

        <div class="flex items-end justify-between gap-sm">
            <div class="flex min-w-0 items-center gap-sm">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-primary bg-primary-container"
                >
                    <img
                        v-if="avatarUrl"
                        :src="avatarUrl"
                        :alt="name"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="text-label-md font-semibold text-on-primary-container"
                    >
                        {{ initials }}
                    </span>
                </div>
                <div class="min-w-0 text-start">
                    <p
                        class="truncate text-label-lg uppercase tracking-wide text-on-surface"
                    >
                        {{ name }}
                    </p>
                </div>
            </div>

            <IconQuote
                class="shrink-0 text-primary opacity-80"
                :size="36"
                stroke-width="1.5"
            />
        </div>
    </article>
</template>
