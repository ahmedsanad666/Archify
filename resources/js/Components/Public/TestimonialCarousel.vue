<script setup>
import { computed, ref } from 'vue'
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    testimonials: {
        type: Array,
        default: () => [],
    },
})

const { localized } = useLocale()
const index = ref(0)

const current = computed(() => props.testimonials[index.value] ?? null)

const go = (delta) => {
    if (!props.testimonials.length) {
        return
    }
    index.value =
        (index.value + delta + props.testimonials.length) %
        props.testimonials.length
}
</script>

<template>
    <div
        v-if="testimonials.length"
        class="relative mx-auto max-w-3xl text-center"
    >
        <div
            v-if="current?.avatar_url"
            class="mx-auto mb-md h-16 w-16 overflow-hidden rounded-full border border-outline-variant"
        >
            <img
                :src="current.avatar_url"
                :alt="current.client_name"
                class="h-full w-full object-cover"
            />
        </div>
        <blockquote class="mb-md text-body-lg text-on-surface">
            “{{ localized(current, 'quote') }}”
        </blockquote>
        <p class="text-label-lg uppercase tracking-wide text-primary">
            {{ current?.client_name }}
        </p>
        <div
            v-if="testimonials.length > 1"
            class="mt-lg flex items-center justify-center gap-sm"
        >
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary"
                @click="go(-1)"
            >
                <IconChevronLeft
                    :size="18"
                    stroke-width="1.5"
                    class="rtl:rotate-180"
                />
            </button>
            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary"
                @click="go(1)"
            >
                <IconChevronRight
                    :size="18"
                    stroke-width="1.5"
                    class="rtl:rotate-180"
                />
            </button>
        </div>
    </div>
</template>
