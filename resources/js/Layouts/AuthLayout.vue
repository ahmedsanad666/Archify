<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { IconBuildingArch } from '@tabler/icons-vue';

defineProps({
    title: {
        type: String,
        default: null,
    },
});

const page = usePage();

const siteName = computed(
    () => page.props.siteSettings?.name ?? 'Archify',
);
const slogan = computed(
    () =>
        page.props.siteSettings?.slogan ??
        'Sign in to manage your website',
);
</script>

<template>
    <div
        class="flex h-screen w-full overflow-hidden bg-surface text-on-surface antialiased"
    >
        <Head
            v-if="title"
            :title="`${title} | ${siteName}`"
        />

        <!-- Brand panel -->
        <div
            class="relative hidden h-full flex-col justify-between bg-surface-container p-margin-desktop md:flex md:w-1/2"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent"
            />
            <div class="relative z-10">
                <div class="mb-md flex items-center gap-sm text-primary">
                    <IconBuildingArch :size="32" stroke-width="1.5" />
                </div>
                <h1
                    class="text-display-md uppercase tracking-tighter text-on-surface"
                >
                    {{ siteName }}
                </h1>
                <p class="mt-md max-w-md text-body-lg text-on-surface-variant">
                    {{ slogan }}
                </p>
            </div>
            <p
                class="relative z-10 text-label-md uppercase tracking-wide text-on-surface-variant"
            >
                © {{ new Date().getFullYear() }} {{ siteName }} Admin
            </p>
        </div>

        <!-- Form panel -->
        <div
            class="relative z-10 flex h-full w-full flex-col items-center justify-center bg-surface p-margin-mobile md:w-1/2 md:p-margin-desktop"
        >
            <div class="flex w-full max-w-[440px] flex-col items-center">
                <div class="mb-lg text-center md:hidden">
                    <IconBuildingArch
                        class="mx-auto text-primary"
                        :size="40"
                        stroke-width="1.5"
                    />
                    <h1
                        class="mt-sm text-headline-lg uppercase tracking-tighter text-on-surface"
                    >
                        {{ siteName }}
                    </h1>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
