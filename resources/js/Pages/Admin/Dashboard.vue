<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    IconBuildingArch,
    IconCategory,
    IconEye,
    IconFileText,
    IconHome,
    IconMail,
    IconPlus,
    IconUserPlus,
} from '@tabler/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsCard from '@/Components/Admin/StatsCard.vue';
import TrafficChart from '@/Components/Admin/TrafficChart.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    greeting: { type: Object, required: true },
    stats: { type: Object, required: true },
    traffic: { type: Object, required: true },
    recent_leads: { type: Array, required: true },
    recent_projects: { type: Array, required: true },
    pending_leads_count: { type: Number, required: true },
});

const { t } = useUiTranslations();

const welcomeTitle = computed(() =>
    t('admin.dashboard.welcome', { name: props.greeting.admin_name }),
);

const formatCompact = (value) => {
    const number = Number(value) || 0;
    if (number < 1000) {
        return String(number);
    }
    return new Intl.NumberFormat(undefined, {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(number);
};

const formatProjectDate = (iso) => {
    if (!iso) {
        return '';
    }
    return new Date(iso).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
};

const formatRelativeTime = (iso) => {
    if (!iso) {
        return '';
    }

    const then = new Date(iso).getTime();
    const now = Date.now();
    const diffSec = Math.round((then - now) / 1000);
    const abs = Math.abs(diffSec);
    const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

    if (abs < 60) {
        return rtf.format(diffSec, 'second');
    }
    if (abs < 3600) {
        return rtf.format(Math.round(diffSec / 60), 'minute');
    }
    if (abs < 86400) {
        return rtf.format(Math.round(diffSec / 3600), 'hour');
    }
    if (abs < 604800) {
        return rtf.format(Math.round(diffSec / 86400), 'day');
    }
    if (abs < 2592000) {
        return rtf.format(Math.round(diffSec / 604800), 'week');
    }
    return rtf.format(Math.round(diffSec / 2592000), 'month');
};

const trafficRange = computed({
    get: () => props.traffic.range,
    set: (value) => {
        router.get(
            route('admin.dashboard'),
            { traffic_range: value },
            {
                preserveState: true,
                preserveScroll: true,
                only: [
                    'traffic',
                    'stats',
                    'recent_leads',
                    'recent_projects',
                    'pending_leads_count',
                    'greeting',
                ],
            },
        );
    },
});

const rangeOptions = computed(() => [
    { value: '7d', label: t('admin.dashboard.range_7d') },
    { value: '30d', label: t('admin.dashboard.range_30d') },
    { value: 'year', label: t('admin.dashboard.range_year') },
]);

const quickActions = computed(() => [
    {
        key: 'project',
        href: route('admin.projects.create'),
        label: t('admin.dashboard.add_project'),
        icon: IconBuildingArch,
        primary: true,
    },
    {
        key: 'service',
        href: route('admin.services.index'),
        label: t('admin.dashboard.add_service'),
        icon: IconCategory,
        primary: false,
    },
    {
        key: 'blog',
        href: route('admin.blogs.create'),
        label: t('admin.dashboard.add_blog'),
        icon: IconFileText,
        primary: false,
    },
    {
        key: 'messages',
        href: route('admin.leads.index'),
        label: t('admin.dashboard.view_messages'),
        icon: IconMail,
        primary: false,
        badge: props.pending_leads_count,
    },
]);
</script>

<template>
    <AdminLayout :title="t('admin.dashboard.title')">
        <Head :title="t('admin.dashboard.title')" />

        <div class="min-w-0 text-start">
            <!-- Header -->
            <div class="mb-xl min-w-0">
                <p
                    class="mb-xs flex items-center gap-xs text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    <IconHome :size="14" stroke-width="1.5" class="shrink-0" />
                    <span class="truncate"
                        >/ {{ t('admin.menu.dashboard') }}</span
                    >
                </p>
                <h2
                    class="mb-xs min-w-0 truncate text-display-md text-on-surface md:text-display-lg"
                    :title="welcomeTitle"
                >
                    {{ welcomeTitle }}
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    {{ greeting.date_label }}
                </p>
            </div>

            <!-- Stats -->
            <div
                class="mb-xl grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-4"
            >
                <StatsCard
                    :label="t('admin.dashboard.stat_projects')"
                    :value="stats.projects.value"
                    :change-percent="stats.projects.change_percent"
                    :trend="stats.projects.trend"
                    :icon="IconBuildingArch"
                />
                <StatsCard
                    :label="t('admin.dashboard.stat_services')"
                    :value="stats.services.value"
                    :change-percent="stats.services.change_percent"
                    :trend="stats.services.trend"
                    :icon="IconCategory"
                />
                <StatsCard
                    :label="t('admin.dashboard.stat_leads_week')"
                    :value="stats.leads_this_week.value"
                    :change-percent="stats.leads_this_week.change_percent"
                    :trend="stats.leads_this_week.trend"
                    :icon="IconUserPlus"
                />
                <StatsCard
                    :label="t('admin.dashboard.stat_page_views')"
                    :value="formatCompact(stats.page_views.value)"
                    :change-percent="stats.page_views.change_percent"
                    :trend="stats.page_views.trend"
                    :icon="IconEye"
                />
            </div>

            <!-- Traffic + Recent leads -->
            <div class="mb-xl grid grid-cols-1 gap-md lg:grid-cols-12">
                <section
                    class="min-w-0 rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-7 xl:col-span-8"
                >
                    <div
                        class="mb-md flex flex-col gap-sm sm:flex-row sm:items-end sm:justify-between"
                    >
                        <h3
                            class="min-w-0 text-start text-label-lg uppercase tracking-wide text-primary line-clamp-2"
                        >
                            {{ t('admin.dashboard.traffic_title') }}
                        </h3>
                        <div class="w-full shrink-0 sm:max-w-[12rem]">
                            <AppSelect
                                v-model="trafficRange"
                                :options="rangeOptions"
                            />
                        </div>
                    </div>
                    <TrafficChart
                        :labels="traffic.labels"
                        :values="traffic.values"
                    />
                </section>

                <section
                    class="flex min-w-0 flex-col rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-5 xl:col-span-4"
                >
                    <div
                        class="mb-md flex items-center justify-between gap-sm"
                    >
                        <h3
                            class="min-w-0 truncate text-label-lg uppercase tracking-wide text-primary"
                        >
                            {{ t('admin.dashboard.recent_leads') }}
                        </h3>
                        <Link
                            :href="route('admin.leads.index')"
                            class="shrink-0 text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:text-primary"
                        >
                            {{ t('admin.dashboard.view_all') }}
                        </Link>
                    </div>

                    <ul
                        v-if="recent_leads.length"
                        class="flex min-w-0 flex-1 flex-col"
                    >
                        <li
                            v-for="lead in recent_leads"
                            :key="lead.id"
                            class="min-w-0 border-b border-outline py-sm last:border-b-0"
                        >
                            <div
                                class="flex min-w-0 items-start justify-between gap-sm"
                            >
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-body-md text-on-surface"
                                        :title="lead.full_name"
                                    >
                                        {{ lead.full_name }}
                                    </p>
                                    <p
                                        class="truncate text-label-md text-on-surface-variant"
                                        :title="lead.interest_label"
                                    >
                                        {{ lead.interest_label }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-label-md text-on-surface-variant"
                                >
                                    {{ formatRelativeTime(lead.created_at) }}
                                </span>
                            </div>
                        </li>
                    </ul>
                    <p
                        v-else
                        class="text-body-md text-on-surface-variant"
                    >
                        {{ t('admin.dashboard.empty_leads') }}
                    </p>
                </section>
            </div>

            <!-- Projects + Quick actions -->
            <div class="grid grid-cols-1 gap-md lg:grid-cols-12">
                <section
                    class="min-w-0 rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-8"
                >
                    <div
                        class="mb-md flex items-center justify-between gap-sm"
                    >
                        <h3
                            class="min-w-0 truncate text-label-lg uppercase tracking-wide text-primary"
                        >
                            {{ t('admin.dashboard.recent_projects') }}
                        </h3>
                        <Link
                            :href="route('admin.projects.index')"
                            class="shrink-0 text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:text-primary"
                        >
                            {{ t('admin.dashboard.manage_projects') }}
                        </Link>
                    </div>

                    <div
                        v-if="recent_projects.length"
                        class="grid grid-cols-1 gap-md md:grid-cols-2 xl:grid-cols-3"
                    >
                        <Link
                            v-for="project in recent_projects"
                            :key="project.id"
                            :href="route('admin.projects.edit', project.id)"
                            class="group min-w-0 overflow-hidden rounded-lg border border-outline-variant bg-surface-container-low transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high"
                        >
                            <div
                                class="relative aspect-[4/3] overflow-hidden bg-surface-container-high"
                            >
                                <img
                                    v-if="project.thumbnail_url"
                                    :src="project.thumbnail_url"
                                    :alt="project.name"
                                    class="size-full object-cover grayscale transition duration-300 group-hover:scale-105 group-hover:grayscale-0"
                                />
                                <div
                                    v-else
                                    class="flex size-full items-center justify-center text-on-surface-variant"
                                >
                                    <IconBuildingArch
                                        :size="32"
                                        stroke-width="1.5"
                                    />
                                </div>
                                <span
                                    v-if="project.updated_at"
                                    class="absolute end-sm top-sm rounded-sm bg-surface-container/90 px-2 py-0.5 text-label-md uppercase tracking-wide text-on-surface"
                                >
                                    {{ formatProjectDate(project.updated_at) }}
                                </span>
                            </div>
                            <div class="min-w-0 space-y-xs p-sm">
                                <span
                                    v-if="project.category_name"
                                    class="inline-block max-w-full truncate rounded-sm border border-outline-variant px-2 py-0.5 text-label-md uppercase tracking-wide text-on-surface-variant"
                                    :title="project.category_name"
                                >
                                    {{ project.category_name }}
                                </span>
                                <p
                                    class="text-body-md text-on-surface line-clamp-2"
                                    :title="project.name"
                                >
                                    {{ project.name }}
                                </p>
                            </div>
                        </Link>
                    </div>
                    <p
                        v-else
                        class="text-body-md text-on-surface-variant"
                    >
                        {{ t('admin.dashboard.empty_projects') }}
                    </p>
                </section>

                <section
                    class="min-w-0 rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-4"
                >
                    <h3
                        class="mb-md truncate text-label-lg uppercase tracking-wide text-primary"
                    >
                        {{ t('admin.dashboard.quick_actions') }}
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-sm sm:grid-cols-2 lg:grid-cols-1"
                    >
                        <Link
                            v-for="action in quickActions"
                            :key="action.key"
                            :href="action.href"
                            class="flex min-w-0 items-center gap-sm rounded-md px-sm py-sm text-start text-label-lg uppercase tracking-wide transition-colors duration-200"
                            :class="
                                action.primary
                                    ? 'bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container'
                                    : 'border border-outline-variant bg-transparent text-on-surface hover:bg-surface-container-high'
                            "
                        >
                            <component
                                :is="action.icon"
                                class="shrink-0"
                                :size="18"
                                stroke-width="1.5"
                            />
                            <span class="min-w-0 flex-1 truncate">{{
                                action.label
                            }}</span>
                            <span
                                v-if="action.badge"
                                class="ms-auto inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-on-primary"
                            >
                                {{ action.badge > 99 ? '99+' : action.badge }}
                            </span>
                            <IconPlus
                                v-else-if="action.primary"
                                class="ms-auto shrink-0 opacity-80"
                                :size="16"
                                stroke-width="1.5"
                            />
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
