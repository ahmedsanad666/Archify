<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import {
    IconChevronDown,
    IconLanguage,
    IconMenu2,
    IconX,
} from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const page = usePage()
const mobileOpen = ref(false)
const { t } = useUiTranslations()
const { locale, languages, localePath, switchLocale } = useLocale()

const siteName = computed(
    () => page.props.siteSettings?.name ?? 'Archify',
)

const navLinks = computed(() => [
    { label: t('nav.home'), routeName: 'home' },
    { label: t('nav.about'), routeName: 'about' },
    { label: t('nav.team'), routeName: 'team' },
    { label: t('nav.faq'), routeName: 'faqs.index' },
    { label: t('nav.services'), href: '#' },
    { label: t('nav.projects'), href: '#' },
    { label: t('nav.blog'), href: '#' },
    { label: t('nav.contact'), href: '#' },
])

const isActive = (routeName) => {
    if (!routeName) {
        return false
    }

    const path = (page.url.split('?')[0] || '/').replace(
        /^\/(tr|ar)(?=\/|$)/,
        '',
    )
    const normalized = path === '' ? '/' : path
    const map = {
        home: '/',
        about: '/about',
        team: '/team',
        'faqs.index': '/faq',
    }

    return map[routeName] === normalized
}

const toggleMobile = () => {
    mobileOpen.value = !mobileOpen.value
}
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full border-b border-outline-variant bg-surface/90 backdrop-blur-md"
    >
        <nav
            class="mx-auto flex w-full max-w-[1440px] items-center justify-between px-margin-mobile py-3 md:px-margin-desktop"
        >
            <Link
                :href="localePath('home')"
                class="text-headline-lg-mobile font-semibold tracking-tight text-primary md:text-headline-lg"
            >
                {{ siteName }}
            </Link>

            <div class="hidden items-center gap-8 lg:flex">
                <template
                    v-for="link in navLinks"
                    :key="link.label"
                >
                    <Link
                        v-if="link.routeName"
                        :href="localePath(link.routeName)"
                        class="text-label-lg uppercase tracking-wide transition-colors duration-300"
                        :class="
                            isActive(link.routeName)
                                ? 'border-b-2 border-primary pb-1 text-primary'
                                : 'text-on-surface-variant hover:text-primary'
                        "
                    >
                        {{ link.label }}
                    </Link>
                    <span
                        v-else
                        class="cursor-default text-label-lg uppercase tracking-wide text-on-surface-variant/50"
                    >
                        {{ link.label }}
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-4">
                <Menu
                    as="div"
                    class="relative"
                >
                    <MenuButton
                        class="inline-flex items-center gap-1 text-on-surface-variant transition-colors duration-200 hover:text-primary"
                        :aria-label="t('nav.language')"
                    >
                        <IconLanguage
                            :size="22"
                            stroke-width="1.5"
                        />
                        <span class="hidden text-label-md uppercase sm:inline">
                            {{ locale?.code }}
                        </span>
                        <IconChevronDown
                            :size="16"
                            stroke-width="1.5"
                        />
                    </MenuButton>
                    <MenuItems
                        class="absolute end-0 z-50 mt-2 min-w-[8rem] rounded-lg border border-outline-variant bg-surface-container py-1 shadow-none focus:outline-none"
                    >
                        <MenuItem
                            v-for="language in languages"
                            :key="language.code"
                            v-slot="{ active }"
                        >
                            <button
                                type="button"
                                class="flex w-full px-4 py-2 text-start text-label-md uppercase tracking-wide transition-colors"
                                :class="[
                                    active ? 'bg-surface-container-high text-primary' : 'text-on-surface-variant',
                                    locale?.code === language.code ? 'text-primary' : '',
                                ]"
                                @click="switchLocale(language.code)"
                            >
                                {{ language.name }}
                            </button>
                        </MenuItem>
                    </MenuItems>
                </Menu>

                <button
                    type="button"
                    class="text-on-surface-variant transition-colors duration-200 hover:text-primary lg:hidden"
                    :aria-expanded="mobileOpen"
                    aria-label="Toggle menu"
                    @click="toggleMobile"
                >
                    <IconX
                        v-if="mobileOpen"
                        :size="22"
                        stroke-width="1.5"
                    />
                    <IconMenu2
                        v-else
                        :size="22"
                        stroke-width="1.5"
                    />
                </button>
            </div>
        </nav>

        <div
            v-show="mobileOpen"
            class="border-t border-outline-variant bg-surface-container px-margin-mobile py-md lg:hidden"
        >
            <div class="flex flex-col gap-sm">
                <template
                    v-for="link in navLinks"
                    :key="`mobile-${link.label}`"
                >
                    <Link
                        v-if="link.routeName"
                        :href="localePath(link.routeName)"
                        class="py-2 text-label-lg uppercase tracking-wide transition-colors duration-200"
                        :class="
                            isActive(link.routeName)
                                ? 'text-primary'
                                : 'text-on-surface-variant hover:text-primary'
                        "
                        @click="mobileOpen = false"
                    >
                        {{ link.label }}
                    </Link>
                    <span
                        v-else
                        class="py-2 text-label-lg uppercase tracking-wide text-on-surface-variant/50"
                    >
                        {{ link.label }}
                    </span>
                </template>
            </div>
        </div>
    </header>
</template>
