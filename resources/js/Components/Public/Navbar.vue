<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { IconLanguage, IconMenu2, IconX } from '@tabler/icons-vue';

const page = usePage();
const mobileOpen = ref(false);

const siteName = computed(
    () => page.props.siteSettings?.name ?? 'Archify',
);

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'Projects', href: '#' },
    { label: 'About', href: '#' },
    { label: 'Services', href: '#' },
    { label: 'Blog', href: '#' },
    { label: 'Contact', href: '#' },
];

const isActive = (href) => {
    if (href === '#') {
        return false;
    }

    return page.url === href || page.url.startsWith(`${href}/`);
};

const toggleMobile = () => {
    mobileOpen.value = !mobileOpen.value;
};
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full border-b border-outline-variant bg-surface/90 backdrop-blur-md"
    >
        <nav
            class="mx-auto flex w-full max-w-[1440px] items-center justify-between px-margin-mobile py-3 md:px-margin-desktop"
        >
            <Link
                href="/"
                class="text-headline-lg-mobile font-semibold tracking-tight text-primary md:text-headline-lg"
            >
                {{ siteName }}
            </Link>

            <div class="hidden items-center gap-8 md:flex">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="text-label-lg uppercase tracking-wide transition-colors duration-300"
                    :class="
                        isActive(link.href)
                            ? 'border-b-2 border-primary pb-1 text-primary'
                            : 'text-on-surface-variant hover:text-primary'
                    "
                >
                    {{ link.label }}
                </a>
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="button"
                    class="text-on-surface-variant transition-colors duration-200 hover:text-primary"
                    aria-label="Language"
                >
                    <IconLanguage :size="22" stroke-width="1.5" />
                </button>
                <button
                    type="button"
                    class="text-on-surface-variant transition-colors duration-200 hover:text-primary md:hidden"
                    :aria-expanded="mobileOpen"
                    aria-label="Toggle menu"
                    @click="toggleMobile"
                >
                    <IconX v-if="mobileOpen" :size="22" stroke-width="1.5" />
                    <IconMenu2 v-else :size="22" stroke-width="1.5" />
                </button>
            </div>
        </nav>

        <div
            v-show="mobileOpen"
            class="border-t border-outline-variant bg-surface-container px-margin-mobile py-md md:hidden"
        >
            <div class="flex flex-col gap-sm">
                <a
                    v-for="link in navLinks"
                    :key="`mobile-${link.label}`"
                    :href="link.href"
                    class="py-2 text-label-lg uppercase tracking-wide text-on-surface-variant transition-colors duration-200 hover:text-primary"
                    :class="{ 'text-primary': isActive(link.href) }"
                    @click="mobileOpen = false"
                >
                    {{ link.label }}
                </a>
            </div>
        </div>
    </header>
</template>
