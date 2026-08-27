<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

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
const favicon = computed(
    () => page.props.siteSettings?.media?.favicon || '',
);
</script>

<template>
    <div
        class="flex h-screen w-full overflow-hidden bg-background font-sans text-on-surface antialiased"
    >
        <Head :title="title ? `${title} | ${siteName}` : undefined">
            <link
                v-if="favicon"
                head-key="icon"
                rel="icon"
                :href="favicon"
            />
        </Head>

        <!-- Photo brand panel (md+) -->
        <div class="relative hidden h-full md:block md:w-1/2">
            <img
                src="/images/auth-login.jpg"
                alt=""
                class="absolute inset-0 h-full w-full object-cover brightness-75 grayscale-[20%]"
            />
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-r from-background/20 to-background/90"
            />
        </div>

        <!-- Form panel -->
        <div
            class="relative z-10 flex h-full w-full flex-col items-center justify-center bg-surface p-margin-mobile md:w-1/2 md:p-margin-desktop"
        >
            <div class="flex w-full max-w-[440px] flex-col items-center">
                <slot />

                <div class="mt-lg text-center">
                    <p
                        class="text-label-md uppercase tracking-wide text-on-surface-variant"
                    >
                        © {{ new Date().getFullYear() }} {{ siteName }} Admin
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
