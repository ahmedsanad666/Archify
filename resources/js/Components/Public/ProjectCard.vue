<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { IconArrowUpRight } from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    href: {
        type: String,
        default: null,
    },
})

const { localized } = useLocale()

const name = computed(() => localized(props.project, 'name'))
const categoryName = computed(() => props.project?.category?.name ?? null)
const isLinkable = computed(() => Boolean(props.href))
</script>

<template>
    <component
        :is="isLinkable ? Link : 'article'"
        v-bind="isLinkable ? { href } : {}"
        class="group relative aspect-[4/5] overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors duration-500 hover:border-secondary"
        :class="isLinkable ? 'cursor-pointer' : ''"
    >
        <img
            v-if="project.thumbnail_url"
            :src="project.thumbnail_url"
            :alt="name || ''"
            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
        />

        <div
            class="absolute inset-0 bg-gradient-to-t from-background via-background/40 to-transparent opacity-80"
        />

        <div
            class="absolute end-md top-md translate-y-2 opacity-0 transition-all duration-500 ltr:-translate-x-2 rtl:translate-x-2 group-hover:translate-x-0 group-hover:translate-y-0 group-hover:opacity-100"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant bg-surface/80 text-primary backdrop-blur-sm"
            >
                <IconArrowUpRight
                    :size="20"
                    stroke-width="1.5"
                />
            </div>
        </div>

        <div class="absolute inset-x-0 bottom-0 w-full p-lg text-start">
            <span
                v-if="categoryName"
                class="mb-sm inline-block rounded-sm bg-primary-container px-3 py-1 text-label-md text-on-primary-container"
            >
                {{ categoryName }}
            </span>
            <h3
                class="text-headline-lg-mobile text-on-surface transition-colors duration-300 group-hover:text-primary md:text-headline-lg"
            >
                {{ name }}
            </h3>
        </div>
    </component>
</template>
