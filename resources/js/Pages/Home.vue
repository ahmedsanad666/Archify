<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeroSlider from '@/Components/Public/HeroSlider.vue'
import AboutStorySection from '@/Components/Public/AboutStorySection.vue'
import ProjectsSection from '@/Components/Public/ProjectsSection.vue'
import ServicesSection from '@/Components/Public/ServicesSection.vue'
import StatisticsSection from '@/Components/Public/StatisticsSection.vue'
import TestimonialsSection from '@/Components/Public/TestimonialsSection.vue'
import BlogsSection from '@/Components/Public/BlogsSection.vue'
import ContactCtaSection from '@/Components/Public/ContactCtaSection.vue'
import { useSiteSeo } from '@/Composables/useSiteSeo'

defineProps({
    sliders: { type: Array, default: () => [] },
    about: { type: Object, default: null },
    services: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    statistics: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] },
    blogs: { type: Array, default: () => [] },
})

const { headTitle, title, description, keywords } = useSiteSeo()
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ headTitle }}</title>
            <meta
                v-if="description"
                head-key="description"
                name="description"
                :content="description"
            />
            <meta
                v-if="keywords"
                head-key="keywords"
                name="keywords"
                :content="keywords"
            />
            <meta
                head-key="og:title"
                property="og:title"
                :content="title"
            />
            <meta
                v-if="description"
                head-key="og:description"
                property="og:description"
                :content="description"
            />
        </Head>

        <HeroSlider :sliders="sliders" />
        <AboutStorySection :about="about" />
        <ProjectsSection :projects="projects" />
        <ServicesSection :services="services" />
        <StatisticsSection :statistics="statistics" />
        <TestimonialsSection :testimonials="testimonials" />
        <BlogsSection :blogs="blogs" />
        <ContactCtaSection />
    </AppLayout>
</template>
