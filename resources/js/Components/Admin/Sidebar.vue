<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { useLocalStorage } from "@vueuse/core";
import {
    IconArticle,
    IconBuildingArch,
    IconCategory,
    IconHelp,
    IconHome,
    IconInfoCircle,
    IconLayoutDashboard,
    IconLayoutSidebarLeftCollapse,
    IconLayoutSidebarLeftExpand,
    IconLogout,
    IconMessages,
    IconPhoto,
    IconQuote,
    IconSettings,
    IconSparkles,
    IconTags,
    IconUsersGroup,
    IconWorld,
} from "@tabler/icons-vue";
import { useUiTranslations } from "@/Composables/useUiTranslations";

defineProps({
    mobileOpen: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["close-mobile"]);

const page = usePage();
const { t } = useUiTranslations();
const collapsed = useLocalStorage("admin-sidebar-collapsed", false);

const siteName = computed(() => page.props.siteSettings?.name ?? "Archifyr");

const navSections = computed(() => [
    {
        label: t("admin.sections.overview"),
        items: [
            {
                label: t("admin.menu.dashboard"),
                href: route("admin.dashboard"),
                icon: IconLayoutDashboard,
                routeName: "admin.dashboard",
            },
            {
                label: t("admin.menu.leads"),
                href: route("admin.leads.index"),
                icon: IconMessages,
                routeName: "admin.leads.*",
            },
        ],
    },
    {
        label: t("admin.sections.content"),
        items: [
            {
                label: t("admin.menu.home_page"),
                href: route("admin.sliders.index"),
                icon: IconHome,
                routeName: "admin.sliders.*",
            },
            {
                label: t("admin.menu.about"),
                href: route("admin.about.edit"),
                icon: IconInfoCircle,
                routeName: "admin.about.*",
            },
            {
                label: t("admin.menu.services"),
                href: route("admin.services.index"),
                icon: IconCategory,
                routeName: "admin.services.*",
            },
            {
                label: t("admin.menu.team"),
                href: route("admin.team-members.index"),
                icon: IconUsersGroup,
                routeName: "admin.team-members.*",
            },
        ],
    },
    {
        label: t("admin.sections.blogs"),
        items: [
            {
                label: t("admin.menu.blog_categories"),
                href: route("admin.blog-categories.index"),
                icon: IconTags,
                routeName: "admin.blog-categories.*",
            },
            {
                label: t("admin.menu.blogs"),
                href: route("admin.blogs.index"),
                icon: IconArticle,
                routeName: "admin.blogs.*",
            },
        ],
    },
    {
        label: t("admin.sections.projects"),
        items: [
            {
                label: t("admin.menu.categories"),
                href: route("admin.project-categories.index"),
                icon: IconTags,
                routeName: "admin.project-categories.*",
            },
            {
                label: t("admin.menu.concepts"),
                href: route("admin.concepts.index"),
                icon: IconSparkles,
                routeName: "admin.concepts.*",
            },
            {
                label: t("admin.menu.projects"),
                href: route("admin.projects.index"),
                icon: IconBuildingArch,
                routeName: "admin.projects.*",
            },
        ],
    },
    {
        label: t("admin.sections.marketing"),
        items: [
            {
                label: t("admin.menu.testimonials"),
                href: route("admin.testimonials.index"),
                icon: IconQuote,
                routeName: "admin.testimonials.*",
            },
            { label: t("admin.menu.faq"), href: route("admin.faqs.index"), icon: IconHelp, routeName: "admin.faqs.*" },
        ],
    },
    {
        label: t("admin.sections.system"),
        items: [
            {
                label: t("admin.menu.media"),
                href: route("admin.media.index"),
                icon: IconPhoto,
                routeName: "admin.media.*",
            },
            {
                label: t("admin.menu.translations"),
                href: route("admin.translations.index"),
                icon: IconWorld,
                routeName: "admin.translations.*",
            },
            {
                label: t("admin.menu.settings"),
                href: route("admin.settings.edit"),
                icon: IconSettings,
                routeName: "admin.settings.*",
            },
        ],
    },
]);

const isActive = (item) => {
    if (!item.routeName) {
        return false;
    }

    try {
        return route().current(item.routeName);
    } catch {
        return false;
    }
};

const itemClass = (item) =>
    isActive(item)
        ? "border-e-2 border-primary bg-surface-container-low font-bold text-primary"
        : "font-medium text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface";

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <!-- Desktop sidebar -->
    <aside
        class="fixed inset-y-0 start-0 z-20 hidden h-screen flex-col border-e border-outline-variant bg-surface py-md px-md transition-all duration-200 md:flex"
        :class="collapsed ? 'w-20' : 'w-64'"
    >
        <div
            class="mb-md flex items-start gap-sm border-b border-outline-variant pb-md"
            :class="collapsed ? 'flex-col items-center' : 'justify-between'"
        >
            <div :class="{ 'text-center': collapsed }">
                <h1
                    class="tracking-tighter text-on-surface"
                    :class="
                        collapsed
                            ? 'text-label-md uppercase'
                            : 'text-headline-lg'
                    "
                >
                    {{ collapsed ? "CMS" : siteName }}
                </h1>
                <p
                    v-if="!collapsed"
                    class="mt-xs text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    
                </p>
            </div>
            <button
                type="button"
                class="shrink-0 rounded-md p-xs text-on-surface-variant transition-colors duration-200 hover:bg-surface-container-high hover:text-primary"
                :title="collapsed ? 'Expand' : 'Collapse'"
                @click="collapsed = !collapsed"
            >
                <IconLayoutSidebarLeftExpand
                    v-if="collapsed"
                    :size="20"
                    stroke-width="1.5"
                />
                <IconLayoutSidebarLeftCollapse
                    v-else
                    :size="20"
                    stroke-width="1.5"
                />
            </button>
        </div>

        <nav class="flex flex-grow flex-col gap-md overflow-y-auto pe-xs">
            <div
                v-for="section in navSections"
                :key="section.label"
                class="flex flex-col gap-xs"
            >
                <p
                    v-if="!collapsed"
                    class="px-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    {{ section.label }}
                </p>
                <ul class="flex flex-col gap-xs">
                    <li
                        v-for="item in section.items"
                        :key="`${section.label}-${item.label}`"
                    >
                        <Link
                            v-if="item.href !== '#'"
                            :href="item.href"
                            class="flex items-center gap-sm rounded-md px-sm py-sm text-label-lg uppercase tracking-wide transition-colors duration-200"
                            :class="[
                                itemClass(item),
                                collapsed ? 'justify-center' : '',
                            ]"
                            :title="item.label"
                        >
                            <component
                                :is="item.icon"
                                class="shrink-0"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <span v-if="!collapsed">{{ item.label }}</span>
                        </Link>
                        <a
                            v-else
                            :href="item.href"
                            class="flex items-center gap-sm rounded-md px-sm py-sm text-label-lg uppercase tracking-wide transition-colors duration-200"
                            :class="[
                                itemClass(item),
                                collapsed ? 'justify-center' : '',
                            ]"
                            :title="item.label"
                        >
                            <component
                                :is="item.icon"
                                class="shrink-0"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <span v-if="!collapsed">{{ item.label }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- Mobile drawer -->
    <Teleport to="body">
        <div v-show="mobileOpen" class="fixed inset-0 z-40 md:hidden">
            <div
                class="absolute inset-0 bg-surface/80"
                @click="$emit('close-mobile')"
            />
            <aside
                class="absolute inset-y-0 start-0 flex h-full w-64 flex-col border-e border-outline-variant bg-surface py-md px-md"
            >
                <div class="mb-md border-b border-outline-variant pb-md">
                    <!-- <h3
                        class="text-headline-md tracking-tighter text-on-surface"
                    >
                        {{ siteName }} s
                    </h3>
                     -->
                </div>

                <nav class="flex flex-grow flex-col gap-md overflow-y-auto">
                    <div
                        v-for="section in navSections"
                        :key="`mobile-${section.label}`"
                        class="flex flex-col gap-xs"
                    >
                        <p
                            class="px-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                        >
                            {{ section.label }}
                        </p>
                        <ul class="flex flex-col gap-xs">
                            <li
                                v-for="item in section.items"
                                :key="`mobile-${section.label}-${item.label}`"
                            >
                                <Link
                                    v-if="item.href !== '#'"
                                    :href="item.href"
                                    class="flex items-center gap-sm rounded-md px-sm py-sm text-label-lg uppercase tracking-wide transition-colors duration-200"
                                    :class="itemClass(item)"
                                    @click="$emit('close-mobile')"
                                >
                                    <component
                                        :is="item.icon"
                                        :size="20"
                                        stroke-width="1.5"
                                    />
                                    {{ item.label }}
                                </Link>
                                <a
                                    v-else
                                    :href="item.href"
                                    class="flex items-center gap-sm rounded-md px-sm py-sm text-label-lg uppercase tracking-wide transition-colors duration-200"
                                    :class="itemClass(item)"
                                    @click="$emit('close-mobile')"
                                >
                                    <component
                                        :is="item.icon"
                                        :size="20"
                                        stroke-width="1.5"
                                    />
                                    {{ item.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <button
                    type="button"
                    class="mt-auto flex w-full items-center gap-sm rounded-md border-t border-outline-variant px-sm py-sm pt-md text-label-lg font-medium uppercase tracking-wide text-on-surface-variant transition-colors duration-200 hover:bg-surface-container-high hover:text-on-surface"
                    @click="logout"
                >
                    <IconLogout :size="20" stroke-width="1.5" />
                    {{ t("admin.menu.logout") }}
                </button>
            </aside>
        </div>
    </Teleport>
</template>
