<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import TestimonialCard from '@/Components/Public/TestimonialCard.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    testimonials: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
const scroller = ref(null)
const activeIndex = ref(0)

const updateActiveIndex = () => {
    const el = scroller.value
    if (!el) {
        return
    }

    const containerRect = el.getBoundingClientRect()
    const containerStart = containerRect.left + 16
    let best = 0
    let bestDist = Infinity

    el.querySelectorAll('article').forEach((card, index) => {
        const rect = card.getBoundingClientRect()
        const dist = Math.abs(rect.left - containerStart)
        if (dist < bestDist) {
            bestDist = dist
            best = index
        }
    })

    activeIndex.value = best
}

const scrollToIndex = (index) => {
    const el = scroller.value
    if (!el) {
        return
    }
    const cards = el.querySelectorAll('article')
    const card = cards[index]
    if (!card) {
        return
    }
    card.scrollIntoView({
        behavior: 'smooth',
        inline: 'start',
        block: 'nearest',
    })
    activeIndex.value = index
}

onMounted(() => {
    const el = scroller.value
    if (!el) {
        return
    }
    el.addEventListener('scroll', updateActiveIndex, { passive: true })
    updateActiveIndex()
})

onUnmounted(() => {
    const el = scroller.value
    if (!el) {
        return
    }
    el.removeEventListener('scroll', updateActiveIndex)
})
</script>

<template>
    <section
        v-if="testimonials.length"
        class="bg-surface-container-lowest py-xl"
    >
        <div
            class="mx-auto max-w-[1440px] px-margin-mobile md:px-margin-desktop"
        >
            <div class="mb-xl text-center">
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.home.testimonials_title') }}
                </h2>
                <p
                    class="mx-auto max-w-2xl text-body-lg text-on-surface-variant"
                >
                    {{ t('public.home.testimonials_subtitle') }}
                </p>
            </div>

            <div
                ref="scroller"
                class="testimonials-scroller flex gap-gutter overflow-x-auto scroll-smooth snap-x snap-mandatory"
            >
                <TestimonialCard
                    v-for="item in testimonials"
                    :key="item.id"
                    :testimonial="item"
                />
            </div>

            <div
                v-if="testimonials.length > 1"
                class="mt-md flex flex-wrap items-center justify-center gap-2"
                role="tablist"
                :aria-label="t('public.home.testimonials_title')"
            >
                <button
                    v-for="(item, index) in testimonials"
                    :key="item.id"
                    type="button"
                    role="tab"
                    class="h-2.5 w-2.5 rounded-full transition-colors duration-200"
                    :class="
                        index === activeIndex
                            ? 'bg-primary'
                            : 'border border-primary bg-transparent hover:bg-primary/30'
                    "
                    :aria-selected="index === activeIndex"
                    :aria-label="`${t('public.home.testimonials_title')} ${index + 1}`"
                    @click="scrollToIndex(index)"
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
.testimonials-scroller {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.testimonials-scroller::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}
</style>
