<script setup>
import { computed, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { onKeyStroke, useScrollLock, useWindowScroll } from '@vueuse/core'
import {
    IconChevronLeft,
    IconChevronRight,
    IconMenu2,
    IconX,
} from '@tabler/icons-vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const FLAG_SRC = {
    en: '/images/svgs/en.svg',
    tr: '/images/svgs/tr.svg',
    ar: '/images/svgs/ar.svg',
}

const page = usePage()
const mobileOpen = ref(false)
const drawerView = ref('main')
const { t } = useUiTranslations()
const { locale, languages, localePath, switchLocale, localized } = useLocale()
const { y } = useWindowScroll()

const isLocked = useScrollLock(
    typeof document !== 'undefined' ? document.body : null,
)

watch(mobileOpen, (open) => {
    isLocked.value = open
    if (!open) {
        drawerView.value = 'main'
    }
})

onKeyStroke('Escape', () => {
    if (mobileOpen.value) {
        closeMobile()
    }
})

const siteName = computed(
    () => page.props.siteSettings?.name ?? 'Archify',
)
const slogan = computed(() => {
    const value = page.props.siteSettings?.slogan
    return typeof value === 'string' && value.trim() !== '' ? value.trim() : null
})
const logoUrl = computed(() => page.props.siteSettings?.media?.logo ?? null)

const projectCategories = computed(() => page.props.projectCategories ?? [])
const blogCategories = computed(() => page.props.blogCategories ?? [])

const isScrolled = computed(() => y.value > 8)
const showFrosted = computed(() => isScrolled.value || mobileOpen.value)

const navLinks = computed(() => [
    { label: t('nav.home'), routeName: 'home' },
    { label: t('nav.about'), routeName: 'about' },
    { label: t('nav.services'), routeName: 'services.index' },
    { label: t('nav.blog'), routeName: 'blogs.index' },
    { label: t('nav.contact'), routeName: 'contact' },
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
        'services.index': '/services',
        'projects.index': '/projects',
        'blogs.index': '/blogs',
        contact: '/contact',
    }

    return map[routeName] === normalized
}

const openMobile = () => {
    drawerView.value = 'main'
    mobileOpen.value = true
}

const closeMobile = () => {
    mobileOpen.value = false
    drawerView.value = 'main'
}

const toggleMobile = () => {
    if (mobileOpen.value) {
        closeMobile()
    } else {
        openMobile()
    }
}

const categoryHref = (routeName, slug = null) => {
    const base = localePath(routeName)
    if (!slug) {
        return base
    }

    const sep = base.includes('?') ? '&' : '?'
    return `${base}${sep}category=${encodeURIComponent(slug)}`
}

const categorySlug = (category) => {
    const code = locale.value?.code || 'en'
    return (
        category?.translations?.[code]?.slug ||
        category?.translations?.en?.slug ||
        null
    )
}

const onNavClick = () => {
    closeMobile()
}

const flagSrc = (code) => FLAG_SRC[code] ?? null

const currentFlagSrc = computed(() => flagSrc(locale.value?.code))
</script>

<template>
    <header
        class="fixed top-0 z-50 w-full border-b transition-colors duration-300"
        :class="
            showFrosted
                ? 'border-outline-variant/60 bg-surface/80 backdrop-blur-md'
                : 'border-transparent bg-transparent'
        "
    >
        <nav
            class="mx-auto flex w-full max-w-[1440px] items-center justify-between px-margin-mobile py-3 md:px-margin-desktop"
        >
            <Link
                :href="localePath('home')"
                class="flex min-w-0 max-w-[14rem] items-center gap-sm md:max-w-xs"
            >
                <img
                    v-if="logoUrl"
                    :src="logoUrl"
                    :alt="siteName"
                    class="h-8 w-auto shrink-0 object-contain md:h-10"
                />
                <span class="flex min-w-0 flex-col text-start">
                    <span
                        class="truncate text-[23px] font-semibold leading-tight tracking-tight text-primary"
                    >
                        {{ siteName }}
                    </span>
                    <span
                        v-if="slogan"
                        class="truncate text-[10px] font-normal leading-snug text-on-surface-variant sm:text-label-md"
                    >
                        {{ slogan }}
                    </span>
                </span>
            </Link>

            <div class="hidden items-center gap-8 lg:flex">
                <Link
                    v-for="link in navLinks"
                    :key="link.routeName"
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
            </div>

            <div class="flex items-center gap-4">
                <!-- Desktop: horizontal locale codes (active chip) -->
                <div
                    class="hidden items-center gap-2 lg:flex"
                    role="group"
                    :aria-label="t('nav.language')"
                >
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        type="button"
                        class="rounded-md px-2.5 py-1 text-label-md uppercase tracking-wide transition-colors duration-200"
                        :class="
                            locale?.code === language.code
                                ? 'bg-primary/20 text-primary'
                                : 'text-on-surface-variant hover:text-primary'
                        "
                        :aria-current="
                            locale?.code === language.code ? 'true' : undefined
                        "
                        @click="switchLocale(language.code)"
                    >
                        {{ language.code }}
                    </button>
                </div>

                <!-- Mobile / tablet: current flag + dropdown with flags -->
                <Menu
                    as="div"
                    class="relative lg:hidden"
                >
                    <MenuButton
                        class="inline-flex items-center justify-center overflow-hidden rounded-[0.2rem] border border-outline-variant transition-colors duration-200 hover:border-secondary"
                        :aria-label="t('nav.language')"
                    >
                        <img
                            v-if="currentFlagSrc"
                            :src="currentFlagSrc"
                            :alt="locale?.code ?? ''"
                            class="h-4 w-5 object-cover"
                        />
                        <span
                            v-else
                            class="px-1.5 py-0.5 text-label-md uppercase text-on-surface-variant"
                        >
                            {{ locale?.code }}
                        </span>
                    </MenuButton>
                    <MenuItems
                        class="absolute end-0 z-50 mt-2 min-w-[7.5rem] overflow-hidden rounded-lg border border-outline-variant bg-surface-container shadow-none focus:outline-none"
                    >
                        <MenuItem
                            v-for="(language, index) in languages"
                            :key="language.code"
                            v-slot="{ active }"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 px-3 py-2 text-label-md uppercase tracking-wide transition-colors"
                                :class="[
                                    active || locale?.code === language.code
                                        ? 'bg-surface-container-high text-primary'
                                        : 'text-on-surface',
                                    index === 0 ? 'rounded-t-lg' : '',
                                    index === languages.length - 1
                                        ? 'rounded-b-lg'
                                        : '',
                                ]"
                                @click="switchLocale(language.code)"
                            >
                                <span>{{ language.code }}</span>
                                <img
                                    v-if="flagSrc(language.code)"
                                    :src="flagSrc(language.code)"
                                    :alt="language.code"
                                    class="h-3.5 w-5 shrink-0 rounded-[0.2rem] object-cover"
                                />
                            </button>
                        </MenuItem>
                    </MenuItems>
                </Menu>

                <button
                    type="button"
                    class="text-on-surface-variant transition-colors duration-200 hover:text-primary lg:hidden"
                    :aria-expanded="mobileOpen"
                    :aria-label="t('nav.menu')"
                    @click="toggleMobile"
                >
                    <IconMenu2
                        :size="22"
                        stroke-width="1.5"
                    />
                </button>
            </div>
        </nav>
    </header>

    <!-- Mobile drawer -->
    <Teleport to="body">
        <div
            v-show="mobileOpen"
            class="lg:hidden"
            :dir="locale?.direction ?? 'ltr'"
        >
            <button
                type="button"
                class="fixed inset-0 z-[60] bg-surface/70"
                aria-label="Close menu"
                @click="closeMobile"
            />

            <aside
                class="fixed inset-y-0 start-0 z-[70] flex w-[min(100%,20rem)] flex-col border-e border-outline-variant bg-surface-container-lowest"
                role="dialog"
                aria-modal="true"
                :aria-label="t('nav.menu')"
            >
                <!-- Main menu -->
                <template v-if="drawerView === 'main'">
                    <div
                        class="relative flex items-center justify-center border-b border-outline-variant px-md py-4"
                    >
                        <button
                            type="button"
                            class="absolute start-md text-on-surface transition-colors hover:text-primary"
                            :aria-label="t('common.cancel')"
                            @click="closeMobile"
                        >
                            <IconX
                                :size="22"
                                stroke-width="1.5"
                            />
                        </button>
                        <span
                            class="text-label-lg uppercase tracking-widest text-on-surface"
                        >
                            {{ t('nav.menu') }}
                        </span>
                    </div>

                    <nav class="flex flex-1 flex-col overflow-y-auto">
                        <Link
                            :href="localePath('home')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide transition-colors"
                            :class="
                                isActive('home')
                                    ? 'text-primary'
                                    : 'text-on-surface hover:text-primary'
                            "
                            @click="onNavClick"
                        >
                            {{ t('nav.home') }}
                        </Link>

                        <button
                            type="button"
                            class="flex w-full items-center justify-between border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="drawerView = 'work'"
                        >
                            <span class="flex items-center gap-sm">
                                {{ t('nav.work') }}
                                <span
                                    v-if="projectCategories.length"
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-surface-container-high px-1.5 text-label-md text-on-surface-variant"
                                >
                                    {{ projectCategories.length }}
                                </span>
                            </span>
                            <IconChevronRight
                                class="shrink-0 rtl:rotate-180"
                                :size="18"
                                stroke-width="1.5"
                            />
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center justify-between border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="drawerView = 'blog'"
                        >
                            <span class="flex items-center gap-sm">
                                {{ t('nav.blog') }}
                                <span
                                    v-if="blogCategories.length"
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-surface-container-high px-1.5 text-label-md text-on-surface-variant"
                                >
                                    {{ blogCategories.length }}
                                </span>
                            </span>
                            <IconChevronRight
                                class="shrink-0 rtl:rotate-180"
                                :size="18"
                                stroke-width="1.5"
                            />
                        </button>

                        <Link
                            :href="localePath('about')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide transition-colors"
                            :class="
                                isActive('about')
                                    ? 'text-primary'
                                    : 'text-on-surface hover:text-primary'
                            "
                            @click="onNavClick"
                        >
                            {{ t('nav.about') }}
                        </Link>

                        <Link
                            :href="localePath('services.index')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide transition-colors"
                            :class="
                                isActive('services.index')
                                    ? 'text-primary'
                                    : 'text-on-surface hover:text-primary'
                            "
                            @click="onNavClick"
                        >
                            {{ t('nav.services') }}
                        </Link>

                        <Link
                            :href="localePath('contact')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-primary transition-colors hover:text-secondary"
                            @click="onNavClick"
                        >
                            {{ t('nav.contact') }}
                        </Link>
                    </nav>
                </template>

                <!-- Work submenu -->
                <template v-else-if="drawerView === 'work'">
                    <div
                        class="relative flex items-center justify-center border-b border-outline-variant px-md py-4"
                    >
                        <button
                            type="button"
                            class="absolute start-md text-on-surface transition-colors hover:text-primary"
                            :aria-label="t('common.back')"
                            @click="drawerView = 'main'"
                        >
                            <IconChevronLeft
                                class="rtl:rotate-180"
                                :size="22"
                                stroke-width="1.5"
                            />
                        </button>
                        <span
                            class="text-label-lg uppercase tracking-widest text-on-surface"
                        >
                            {{ t('nav.work') }}
                        </span>
                    </div>
                    <nav class="flex flex-1 flex-col overflow-y-auto">
                        <Link
                            :href="categoryHref('projects.index')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="onNavClick"
                        >
                            {{ t('nav.all') }}
                        </Link>
                        <Link
                            v-for="category in projectCategories"
                            :key="category.id"
                            :href="
                                categoryHref(
                                    'projects.index',
                                    categorySlug(category),
                                )
                            "
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="onNavClick"
                        >
                            {{ localized(category, 'name') }}
                        </Link>
                    </nav>
                </template>

                <!-- Blog submenu -->
                <template v-else-if="drawerView === 'blog'">
                    <div
                        class="relative flex items-center justify-center border-b border-outline-variant px-md py-4"
                    >
                        <button
                            type="button"
                            class="absolute start-md text-on-surface transition-colors hover:text-primary"
                            :aria-label="t('common.back')"
                            @click="drawerView = 'main'"
                        >
                            <IconChevronLeft
                                class="rtl:rotate-180"
                                :size="22"
                                stroke-width="1.5"
                            />
                        </button>
                        <span
                            class="text-label-lg uppercase tracking-widest text-on-surface"
                        >
                            {{ t('nav.blog') }}
                        </span>
                    </div>
                    <nav class="flex flex-1 flex-col overflow-y-auto">
                        <Link
                            :href="categoryHref('blogs.index')"
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="onNavClick"
                        >
                            {{ t('nav.all') }}
                        </Link>
                        <Link
                            v-for="category in blogCategories"
                            :key="category.id"
                            :href="
                                categoryHref(
                                    'blogs.index',
                                    categorySlug(category),
                                )
                            "
                            class="border-b border-outline-variant px-md py-5 text-start text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:text-primary"
                            @click="onNavClick"
                        >
                            {{ localized(category, 'name') }}
                        </Link>
                    </nav>
                </template>
            </aside>
        </div>
    </Teleport>
</template>
