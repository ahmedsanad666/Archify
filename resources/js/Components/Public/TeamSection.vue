<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import TeamMemberCard from '@/Components/Public/TeamMemberCard.vue'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    members: {
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
        v-if="members.length"
        class="bg-surface-container-lowest py-xl"
    >
        <div
            class="mx-auto max-w-[1440px] px-margin-mobile md:px-margin-desktop"
        >
            <div class="mb-xl text-center">
                <h2 class="mb-sm text-display-md text-on-surface">
                    {{ t('public.team.title') }}
                </h2>
                <p
                    class="mx-auto max-w-2xl text-body-lg text-on-surface-variant"
                >
                    {{ t('public.team.subtitle') }}
                </p>
            </div>

            <div
                ref="scroller"
                class="team-scroller flex justify-start gap-gutter overflow-x-auto scroll-smooth snap-x snap-mandatory md:justify-center"
            >
                <TeamMemberCard
                    v-for="member in members"
                    :key="member.id"
                    :member="member"
                />
            </div>

            <div
                v-if="members.length > 1"
                class="mt-md flex flex-wrap items-center justify-center gap-2"
                role="tablist"
                :aria-label="t('public.team.title')"
            >
                <button
                    v-for="(member, index) in members"
                    :key="member.id"
                    type="button"
                    role="tab"
                    class="h-2.5 w-2.5 rounded-full transition-colors duration-200"
                    :class="
                        index === activeIndex
                            ? 'bg-primary'
                            : 'border border-primary bg-transparent hover:bg-primary/30'
                    "
                    :aria-selected="index === activeIndex"
                    :aria-label="`${t('public.team.title')} ${index + 1}`"
                    @click="scrollToIndex(index)"
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
.team-scroller {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.team-scroller::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}
</style>
