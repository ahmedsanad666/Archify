<script setup>
import { computed } from 'vue'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
})

const { localized } = useLocale()

const name = computed(() => localized(props.project, 'name'))
const shortDescription = computed(() =>
    localized(props.project, 'short_description'),
)
</script>

<template>
    <article
        class="group overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high"
    >
        <div class="aspect-[4/3] overflow-hidden bg-surface-container-low">
            <img
                v-if="project.thumbnail_url"
                :src="project.thumbnail_url"
                :alt="name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
        </div>
        <div class="flex flex-col gap-2 p-md">
            <div
                class="flex items-center justify-between gap-2 text-label-md uppercase tracking-wide text-on-surface-variant"
            >
                <span v-if="project.category?.name">{{
                    project.category.name
                }}</span>
                <span v-if="project.year">{{ project.year }}</span>
            </div>
            <h3 class="text-headline-lg-mobile text-on-surface">
                {{ name }}
            </h3>
            <p
                v-if="shortDescription"
                class="line-clamp-2 text-body-md text-on-surface-variant"
            >
                {{ shortDescription }}
            </p>
            <p
                v-if="project.location"
                class="text-label-md text-outline"
            >
                {{ project.location }}
            </p>
        </div>
    </article>
</template>
