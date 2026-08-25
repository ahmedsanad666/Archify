<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    IconBrandInstagram,
    IconBrandX,
    IconBrandYoutube,
    IconMail,
    IconMapPin,
    IconPhone,
} from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const page = usePage()
const { t } = useUiTranslations()
const { locale, languages, localePath, switchLocale } = useLocale()

const siteSettings = computed(() => page.props.siteSettings)

const siteName = computed(() => siteSettings.value?.name ?? 'Archify')
const slogan = computed(
    () =>
        siteSettings.value?.slogan ??
        t('public.footer.default_slogan'),
)

const exploreLinks = computed(() => [
    { label: t('nav.home'), routeName: 'home' },
    { label: t('nav.about'), routeName: 'about' },
    { label: t('nav.team'), routeName: 'team' },
    { label: t('nav.faq'), routeName: 'faqs.index' },
    { label: t('nav.services'), href: '#' },
    { label: t('nav.projects'), href: '#' },
])

const socialLinks = computed(() => {
    const settings = siteSettings.value
    if (!settings) {
        return []
    }

    return [
        {
            href: settings.instagram_url,
            label: 'Instagram',
            icon: IconBrandInstagram,
        },
        {
            href: settings.youtube_url,
            label: 'YouTube',
            icon: IconBrandYoutube,
        },
        {
            href: settings.twitter_url,
            label: 'X',
            icon: IconBrandX,
        },
    ].filter((link) => Boolean(link.href))
})

const onNewsletterSubmit = (event) => {
    event.preventDefault()
}
</script>

<template>
    <footer
        class="texture-bg relative w-full border-t border-outline-variant bg-surface-container-lowest"
    >
        <div
            class="relative z-10 mx-auto grid max-w-[1440px] grid-cols-1 gap-gutter px-margin-mobile pb-lg pt-xl md:grid-cols-4 md:px-margin-desktop"
        >
            <div class="flex flex-col gap-md">
                <h2 class="text-display-md tracking-tighter text-on-surface">
                    {{ siteName }}
                </h2>
                <p class="max-w-xs text-body-md text-on-surface-variant">
                    {{ slogan }}
                </p>
                <div
                    class="flex flex-col gap-sm pt-sm text-label-lg uppercase tracking-wide text-on-surface-variant"
                >
                    <div
                        v-if="siteSettings?.address"
                        class="flex items-center gap-sm transition-colors duration-300 hover:text-primary"
                    >
                        <IconMapPin
                            class="shrink-0 text-primary"
                            :size="18"
                            stroke-width="1.5"
                        />
                        <span>{{ siteSettings.address }}</span>
                    </div>
                    <div
                        v-if="siteSettings?.phone"
                        class="flex items-center gap-sm transition-colors duration-300 hover:text-primary"
                    >
                        <IconPhone
                            class="shrink-0 text-primary"
                            :size="18"
                            stroke-width="1.5"
                        />
                        <span>{{ siteSettings.phone }}</span>
                    </div>
                    <div
                        v-if="siteSettings?.email"
                        class="flex items-center gap-sm transition-colors duration-300 hover:text-primary"
                    >
                        <IconMail
                            class="shrink-0 text-primary"
                            :size="18"
                            stroke-width="1.5"
                        />
                        <span>{{ siteSettings.email }}</span>
                    </div>
                </div>
                <div
                    v-if="socialLinks.length"
                    class="flex gap-sm pt-sm"
                >
                    <a
                        v-for="link in socialLinks"
                        :key="link.label"
                        :href="link.href"
                        :aria-label="link.label"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant bg-surface-container text-on-surface-variant transition-all duration-300 hover:border-primary hover:text-primary"
                    >
                        <component
                            :is="link.icon"
                            :size="16"
                            stroke-width="1.5"
                        />
                    </a>
                </div>
            </div>

            <div class="flex flex-col gap-md">
                <h3
                    class="text-label-lg uppercase tracking-[0.1em] text-primary"
                >
                    {{ t('public.footer.explore') }}
                </h3>
                <nav class="flex flex-col gap-sm text-body-md text-on-surface">
                    <template
                        v-for="link in exploreLinks"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.routeName"
                            :href="localePath(link.routeName)"
                            class="group relative w-max py-1"
                        >
                            {{ link.label }}
                            <span
                                class="absolute inset-x-0 bottom-0 h-px w-0 bg-primary transition-all duration-300 group-hover:w-full"
                            />
                        </Link>
                        <span
                            v-else
                            class="py-1 text-on-surface-variant/50"
                        >
                            {{ link.label }}
                        </span>
                    </template>
                </nav>
            </div>

            <div class="flex flex-col gap-md md:col-span-2">
                <h3
                    class="text-label-lg uppercase tracking-[0.1em] text-primary"
                >
                    {{ t('public.footer.community') }}
                </h3>
                <p class="text-body-md text-on-surface-variant">
                    {{ t('public.footer.newsletter_blurb') }}
                </p>
                <form
                    class="mt-xs flex max-w-md flex-col gap-sm"
                    @submit="onNewsletterSubmit"
                >
                    <input
                        type="email"
                        :placeholder="t('public.footer.email_placeholder')"
                        class="w-full rounded-md border border-outline-variant bg-surface-container px-4 py-3 text-body-md text-on-surface outline-none transition-colors duration-300 placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-1 focus:ring-primary"
                    />
                    <button
                        type="submit"
                        class="group relative w-full overflow-hidden rounded-md bg-primary px-4 py-3 text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-300 hover:bg-secondary hover:text-on-secondary"
                    >
                        <span class="relative z-10">{{
                            t('public.footer.subscribe')
                        }}</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="relative z-10 w-full border-t border-outline-variant">
            <div
                class="mx-auto flex max-w-[1440px] flex-col items-center justify-between gap-4 px-margin-mobile py-md md:flex-row md:gap-0 md:px-margin-desktop"
            >
                <div class="text-body-md text-on-surface-variant">
                    © {{ new Date().getFullYear() }} {{ siteName }}.
                    {{ t('public.footer.rights') }}
                </div>
                <div
                    class="flex flex-wrap items-center justify-center gap-md text-label-lg uppercase tracking-wide text-on-surface-variant"
                >
                    <div
                        class="flex items-center gap-2 border-e border-outline-variant pe-md"
                    >
                        <template
                            v-for="(language, index) in languages"
                            :key="language.code"
                        >
                            <button
                                type="button"
                                class="uppercase transition-colors duration-300 hover:text-primary"
                                :class="{
                                    'text-primary':
                                        locale?.code === language.code,
                                }"
                                @click="switchLocale(language.code)"
                            >
                                {{ language.code }}
                            </button>
                            <span
                                v-if="index < languages.length - 1"
                                class="text-outline-variant"
                            >
                                /
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.texture-bg {
    background-image: radial-gradient(
        rgba(158, 142, 129, 0.05) 1px,
        transparent 1px
    );
    background-size: 20px 20px;
}
</style>
