<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    IconBell,
    IconLogout,
    IconMenu2,
} from '@tabler/icons-vue';

defineProps({
    sidebarCollapsed: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['toggle-mobile']);

const page = usePage();

const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const userEmail = computed(() => page.props.auth?.user?.email ?? '');
const languages = computed(() => page.props.languages ?? []);

const activeLocaleCode = computed(() => {
    return (
        page.props.locale?.code ||
        languages.value.find((l) => l.is_default)?.code ||
        'en'
    );
});

const selectLocale = (code) => {
    if (code === activeLocaleCode.value) {
        return;
    }

    router.put(
        route('admin.locale.update'),
        { locale: code },
        { preserveScroll: true },
    );
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-10 flex h-16 items-center justify-between border-b border-outline-variant bg-surface px-md transition-[inset-inline-start] duration-200 md:start-64 md:px-lg"
        :class="{ 'md:start-20': sidebarCollapsed }"
    >
        <div class="flex items-center">
            <button
                type="button"
                class="rounded-md p-xs text-on-surface-variant transition-colors duration-200 hover:text-primary focus:outline-none focus:ring-1 focus:ring-primary md:hidden"
                aria-label="Open menu"
                @click="$emit('toggle-mobile')"
            >
                <IconMenu2 :size="22" stroke-width="1.5" />
            </button>
        </div>

        <div class="flex items-center gap-md">
            <div
                class="flex items-center gap-1 rounded-md border border-outline-variant p-0.5"
                role="group"
                aria-label="Language"
            >
                <button
                    v-for="language in languages"
                    :key="language.code"
                    type="button"
                    class="rounded-sm px-2.5 py-1 text-label-md uppercase tracking-wide transition-colors duration-200"
                    :class="
                        activeLocaleCode === language.code
                            ? 'bg-primary text-on-primary'
                            : 'text-on-surface-variant hover:text-primary'
                    "
                    @click="selectLocale(language.code)"
                >
                    {{ language.code }}
                </button>
            </div>

            <button
                type="button"
                class="rounded-md p-xs text-on-surface-variant transition-colors duration-200 hover:text-primary focus:outline-none focus:ring-1 focus:ring-primary"
                aria-label="Notifications"
            >
                <IconBell :size="22" stroke-width="1.5" />
            </button>

            <div class="hidden items-center gap-sm border-s border-outline-variant ps-md sm:flex">
                <div class="text-end">
                    <p class="text-label-md uppercase tracking-wide text-on-surface">
                        {{ userName }}
                    </p>
                    <p
                        v-if="userEmail"
                        class="text-label-md normal-case tracking-normal text-on-surface-variant"
                    >
                        {{ userEmail }}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-md p-xs text-on-surface-variant transition-colors duration-200 hover:text-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    aria-label="Log out"
                    @click="logout"
                >
                    <IconLogout :size="22" stroke-width="1.5" />
                </button>
            </div>

            <button
                type="button"
                class="rounded-md p-xs text-on-surface-variant transition-colors duration-200 hover:text-primary sm:hidden"
                aria-label="Log out"
                @click="logout"
            >
                <IconLogout :size="22" stroke-width="1.5" />
            </button>
        </div>
    </header>
</template>
