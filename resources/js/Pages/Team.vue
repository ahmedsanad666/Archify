<script setup>
import { computed } from 'vue'
import {
    IconBrandBehance,
    IconBrandInstagram,
    IconBrandLinkedin,
} from '@tabler/icons-vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import InnerHero from '@/Components/Public/InnerHero.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

defineProps({
    members: { type: Array, default: () => [] },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()

const breadcrumbs = computed(() => [
    { label: t('nav.home'), href: localePath('home') },
    { label: t('nav.team') },
])
</script>

<template>
    <AppLayout :title="t('nav.team')">
        <InnerHero
            :title="t('public.team.title')"
            :eyebrow="t('public.team.subtitle')"
            :breadcrumbs="breadcrumbs"
        />

        <section
            class="mx-auto max-w-[1440px] px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p
                v-if="!members.length"
                class="text-center text-body-lg text-on-surface-variant"
            >
                {{ t('public.team.empty') }}
            </p>

            <div
                v-else
                class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3"
            >
                <article
                    v-for="member in members"
                    :key="member.id"
                    class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors hover:border-secondary hover:bg-surface-container-high"
                >
                    <div class="aspect-[3/4] bg-surface-container-low">
                        <img
                            v-if="member.avatar_url"
                            :src="member.avatar_url"
                            :alt="member.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="flex flex-col gap-2 p-md">
                        <h2 class="text-headline-lg-mobile text-on-surface">
                            {{ member.name }}
                        </h2>
                        <p
                            class="text-label-lg uppercase tracking-wide text-primary"
                        >
                            {{ localized(member, 'role') }}
                        </p>
                        <div class="mt-2 flex gap-sm">
                            <a
                                v-if="member.linkedin_url"
                                :href="member.linkedin_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-on-surface-variant hover:text-primary"
                                aria-label="LinkedIn"
                            >
                                <IconBrandLinkedin
                                    :size="20"
                                    stroke-width="1.5"
                                />
                            </a>
                            <a
                                v-if="member.behance_url"
                                :href="member.behance_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-on-surface-variant hover:text-primary"
                                aria-label="Behance"
                            >
                                <IconBrandBehance
                                    :size="20"
                                    stroke-width="1.5"
                                />
                            </a>
                            <a
                                v-if="member.instagram_url"
                                :href="member.instagram_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-on-surface-variant hover:text-primary"
                                aria-label="Instagram"
                            >
                                <IconBrandInstagram
                                    :size="20"
                                    stroke-width="1.5"
                                />
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AppLayout>
</template>
