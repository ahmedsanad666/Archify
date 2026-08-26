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
        class="relative flex min-h-[400px] w-full flex-col justify-end overflow-hidden"
        style="height: 40vh"
    >
        <div class="absolute inset-0 z-0">
            <div
                v-if="backgroundImage"
                class="absolute inset-0 bg-cover bg-center"
                :style="{ backgroundImage: `url('${backgroundImage}')` }"
            />
            <div
                v-else
                class="absolute inset-0 bg-surface-container-high"
            />
            <div class="scrim-overlay absolute inset-0 z-10 opacity-90" />
        </div>

        <div
            class="relative z-20 mx-auto flex h-full w-full max-w-[1440px] flex-col justify-end px-margin-mobile pb-margin-desktop pt-24 text-start md:px-margin-desktop md:pt-28"
        >
            <div class="max-w-2xl min-w-0">
                <nav
                    v-if="breadcrumbs.length"
                    aria-label="Breadcrumb"
                    class="mb-md flex flex-wrap items-center gap-xs text-label-md text-on-surface-variant opacity-80"
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
                    class="mb-sm flex items-center gap-sm"
                >
                    <div class="h-[2px] w-8 shrink-0 bg-primary" />
                    <span
                        class="text-label-lg uppercase tracking-widest text-primary"
                    >
                        {{ eyebrow }}
                    </span>
                </div>

                <h1
                    class="mb-lg min-w-0 text-display-md text-on-surface md:text-display-lg"
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
    background: linear-gradient(
        to bottom,
        rgba(22, 19, 16, 0.4) 0%,
        rgba(22, 19, 16, 0.9) 100%
    );
}
</style>
