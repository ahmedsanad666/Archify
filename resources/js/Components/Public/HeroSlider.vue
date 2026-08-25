<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { IconChevronLeft, IconChevronRight } from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    sliders: {
        type: Array,
        default: () => [],
    },
})

const { localized } = useLocale()
const { t } = useUiTranslations()

const index = ref(0)
let timer = null

const current = computed(() => props.sliders[index.value] ?? null)
const total = computed(() => props.sliders.length)

const go = (delta) => {
    if (!total.value) {
        return
    }
    index.value = (index.value + delta + total.value) % total.value
}

const start = () => {
    stop()
    if (total.value < 2) {
        return
    }
    timer = window.setInterval(() => go(1), 7000)
}

const stop = () => {
    if (timer) {
        window.clearInterval(timer)
        timer = null
    }
}

watch(
    () => props.sliders.length,
    () => {
        index.value = 0
        start()
    },
)

onMounted(start)
onUnmounted(stop)
</script>

<template>
    <section
        class="relative flex min-h-[600px] w-full items-center justify-center md:h-[90vh]"
    >
        <div class="absolute inset-0 h-full w-full">
            <img
                v-if="current?.image_url"
                :src="current.image_url"
                :alt="localized(current, 'title') || 'Hero'"
                class="h-full w-full object-cover object-center"
            />
            <div
                v-else
                class="h-full w-full bg-surface-container"
            />
            <div class="absolute inset-0 bg-surface/60 mix-blend-multiply" />
            <div
                class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-surface/30"
            />
        </div>

        <div
            class="relative z-10 mx-auto flex h-full w-full max-w-[1440px] flex-col justify-center px-margin-mobile pt-xl md:px-margin-desktop"
        >
            <div class="max-w-3xl">
                <h1
                    class="mb-sm text-display-md font-semibold tracking-tight text-on-surface md:text-display-lg"
                >
                    {{
                        localized(current, 'title') ||
                        t('public.home.empty_hero')
                    }}
                </h1>
                <p
                    v-if="localized(current, 'description')"
                    class="max-w-xl text-body-lg text-secondary"
                >
                    {{ localized(current, 'description') }}
                </p>
            </div>
        </div>

        <div
            v-if="total > 1"
            class="absolute bottom-8 start-margin-mobile z-10 flex items-center gap-md md:start-margin-desktop"
        >
            <div class="flex gap-sm">
                <button
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant transition-all hover:border-primary hover:bg-surface-container-high hover:text-primary"
                    @click="go(-1)"
                >
                    <IconChevronLeft
                        :size="20"
                        stroke-width="1.5"
                        class="rtl:rotate-180"
                    />
                </button>
                <button
                    type="button"
                    class="flex h-12 w-12 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant transition-all hover:border-primary hover:bg-surface-container-high hover:text-primary"
                    @click="go(1)"
                >
                    <IconChevronRight
                        :size="20"
                        stroke-width="1.5"
                        class="rtl:rotate-180"
                    />
                </button>
            </div>
            <div
                class="flex items-center text-label-lg text-on-surface-variant"
            >
                <span class="text-primary">{{
                    String(index + 1).padStart(2, '0')
                }}</span>
                <span class="mx-2 h-px w-8 bg-outline-variant" />
                <span>{{ String(total).padStart(2, '0') }}</span>
            </div>
        </div>
    </section>
</template>
