<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/Public/Footer.vue';
import Navbar from '@/Components/Public/Navbar.vue';

defineProps({
    title: {
        type: String,
        default: null,
    },
});

const page = usePage();

const direction = computed(() => page.props.locale?.direction ?? 'ltr');
const siteName = computed(() => page.props.siteSettings?.name ?? 'Archify');
const favicon = computed(
    () => page.props.siteSettings?.media?.favicon || '',
);
</script>

<template>
    <div
        class="min-h-screen bg-surface text-on-surface antialiased"
        :dir="direction"
    >
        <Head :title="title ? `${title} | ${siteName}` : undefined">
            <link
                v-if="favicon"
                head-key="icon"
                rel="icon"
                :href="favicon"
            />
        </Head>

        <Navbar />
        <main>
            <slot />
        </main>
        <Footer />
    </div>
</template>
