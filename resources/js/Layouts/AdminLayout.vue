<script setup>
import { computed, ref, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useLocalStorage } from '@vueuse/core';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import TopBar from '@/Components/Admin/TopBar.vue';

defineProps({
    title: {
        type: String,
        default: null,
    },
});

const page = usePage();
const mobileOpen = ref(false);
const collapsed = useLocalStorage('admin-sidebar-collapsed', false);

const siteName = computed(() => page.props.siteSettings?.name ?? 'Archify');

watch(
    () => page.url,
    () => {
        mobileOpen.value = false;
    },
);
</script>

<template>
    <div class="min-h-screen bg-background text-on-surface antialiased">
        <Head
            v-if="title"
            :title="`${title} | ${siteName}`"
        />

        <Sidebar
            :mobile-open="mobileOpen"
            @close-mobile="mobileOpen = false"
        />

        <div
            class="flex min-h-screen flex-col transition-[margin] duration-200 md:ms-64"
            :class="{ 'md:ms-20': collapsed }"
        >
            <TopBar
                :sidebar-collapsed="collapsed"
                @toggle-mobile="mobileOpen = true"
            />

            <main
                class="mx-auto mt-16 w-full max-w-[1440px] flex-1 p-margin-mobile md:p-margin-desktop"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
