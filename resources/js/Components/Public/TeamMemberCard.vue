<script setup>
import { computed } from 'vue'
import {
    IconBrandBehance,
    IconBrandInstagram,
    IconBrandLinkedin,
} from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'

const props = defineProps({
    member: {
        type: Object,
        required: true,
    },
})

const { localized } = useLocale()

const role = computed(() => localized(props.member, 'role'))
const name = computed(() => props.member?.name || '')
const avatarUrl = computed(() => props.member?.avatar_url || null)

const initials = computed(() => {
    const parts = String(name.value)
        .trim()
        .split(/\s+/)
        .filter(Boolean)
    if (!parts.length) {
        return '?'
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase()
    }
    return `${parts[0][0] ?? ''}${parts[parts.length - 1][0] ?? ''}`.toUpperCase()
})

const socials = computed(() => {
    const links = []
    if (props.member?.linkedin_url) {
        links.push({
            href: props.member.linkedin_url,
            label: 'LinkedIn',
            icon: IconBrandLinkedin,
        })
    }
    if (props.member?.behance_url) {
        links.push({
            href: props.member.behance_url,
            label: 'Behance',
            icon: IconBrandBehance,
        })
    }
    if (props.member?.instagram_url) {
        links.push({
            href: props.member.instagram_url,
            label: 'Instagram',
            icon: IconBrandInstagram,
        })
    }
    return links
})
</script>

<template>
    <article
        class="flex w-[280px] shrink-0 snap-start flex-col items-center rounded-lg border border-outline-variant bg-surface-container p-md text-center transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high md:w-[300px] md:p-lg"
    >
        <div
            class="mb-md flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-primary bg-primary-container md:h-28 md:w-28"
        >
            <img
                v-if="avatarUrl"
                :src="avatarUrl"
                :alt="name"
                class="h-full w-full object-cover"
            />
            <span
                v-else
                class="text-label-lg font-semibold text-on-primary-container"
            >
                {{ initials }}
            </span>
        </div>

        <h3
            class="mb-xs text-headline-lg-mobile text-on-surface md:text-headline-lg"
        >
            {{ name }}
        </h3>

        <p
            v-if="role"
            class="mb-md text-label-lg uppercase tracking-wide text-primary"
        >
            {{ role }}
        </p>

        <div
            v-if="socials.length"
            class="mt-auto flex items-center justify-center gap-sm"
        >
            <a
                v-for="link in socials"
                :key="link.label"
                :href="link.href"
                :aria-label="link.label"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant text-on-surface-variant transition-colors hover:border-primary hover:text-primary"
            >
                <component
                    :is="link.icon"
                    :size="18"
                    stroke-width="1.5"
                />
            </a>
        </div>
    </article>
</template>
