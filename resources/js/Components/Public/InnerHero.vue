<script setup>
import { Link } from '@inertiajs/vue3'
import { IconChevronRight } from '@tabler/icons-vue'

defineProps({
    title: {
        type: String,
        required: true,
    },
    eyebrow: {
        type: String,
        default: null,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    category: {
        type: String,
        default: null,
    },
    backgroundImage: {
        type: String,
        default: null,
    },
})
</script>

<template>
    <header
        class="relative flex min-h-[280px] w-full flex-col justify-end overflow-hidden sm:min-h-[340px] md:min-h-[400px]"
        style="height: 40vh"
    >
        <div class="absolute inset-0 z-0">
            <div
                v-if="backgroundImage"
                class="absolute inset-0 bg-cover bg-center bg-scroll md:bg-fixed"
                :style="{ backgroundImage: `url('${backgroundImage}')` }"
            />
            <div
                v-else
                class="absolute inset-0 bg-surface-container-high"
            />
            <div class="scrim-overlay absolute inset-0 z-10" />
        </div>

        <div
            class="relative z-20 mx-auto flex h-full w-full max-w-[1440px] flex-col justify-end px-margin-mobile pb-lg pt-20 text-start sm:pb-margin-desktop sm:pt-24 md:px-margin-desktop md:pt-28"
        >
            <div class="max-w-2xl min-w-0">
                <nav
                    v-if="breadcrumbs.length"
                    aria-label="Breadcrumb"
                    class="mb-sm flex flex-wrap items-center gap-xs text-label-md text-on-surface-variant opacity-80 sm:mb-md"
                >
                    <template
                        v-for="(crumb, index) in breadcrumbs"
                        :key="`${crumb.label}-${index}`"
                    >
                        <Link
                            v-if="crumb.href"
                            :href="crumb.href"
                            class="truncate transition-colors hover:text-primary"
                        >
                            {{ crumb.label }}
                        </Link>
                        <span
                            v-else
                            class="truncate font-medium text-primary"
                            aria-current="page"
                        >
                            {{ crumb.label }}
                        </span>
                        <IconChevronRight
                            v-if="index < breadcrumbs.length - 1"
                            class="shrink-0 opacity-50 rtl:rotate-180"
                            :size="16"
                            stroke-width="1.5"
                        />
                    </template>
                </nav>

                <div
                    v-if="eyebrow"
                    class="mb-xs flex items-center gap-sm sm:mb-sm"
                >
                    <div class="h-[2px] w-6 shrink-0 bg-primary sm:w-8" />
                    <span
                        class="text-label-md uppercase tracking-widest text-primary sm:text-label-lg"
                    >
                        {{ eyebrow }}
                    </span>
                </div>

                <h1
                    class="mb-md min-w-0 text-headline-lg leading-tight text-on-surface sm:mb-lg sm:text-display-md md:text-display-lg"
                >
                    {{ title }}
                </h1>

                <div
                    v-if="category"
                    class="inline-flex max-w-full items-center rounded-lg border border-primary bg-surface/30 px-sm py-xs text-label-md text-primary backdrop-blur-sm"
                >
                    <span class="truncate">{{ category }}</span>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.scrim-overlay {
    /* Dual scrim: dark top for navbar contrast + dark bottom for title */
    background: linear-gradient(
        to bottom,
        rgba(22, 19, 16, 0.88) 0%,
        rgba(22, 19, 16, 0.45) 32%,
        rgba(22, 19, 16, 0.7) 68%,
        rgba(22, 19, 16, 0.92) 100%
    );
    box-shadow: inset 0 100px 70px -30px rgba(22, 19, 16, 0.95);
}
</style>
