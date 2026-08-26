<script setup>
import ProjectCard from '@/Components/Public/ProjectCard.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
</script>

<template>
    <section
        class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
    >
        <div class="mb-xl text-center">
            <h2 class="mb-sm text-display-md text-on-surface">
                {{ t('public.home.projects_title') }}
            </h2>
            <p class="mx-auto max-w-2xl text-body-lg text-on-surface-variant">
                {{ t('public.home.projects_subtitle') }}
            </p>
        </div>
        <div
            v-if="projects.length"
            class="grid grid-cols-1 gap-gutter md:grid-cols-2"
        >
            <ProjectCard
                v-for="(project, index) in projects"
                :key="project.id"
                :project="project"
                :class="index % 2 === 1 ? 'md:mt-xl' : ''"
            />
        </div>
        <p
            v-else
            class="text-center text-body-md text-on-surface-variant"
        >
            {{ t('public.home.empty_projects') }}
        </p>
    </section>
</template>
