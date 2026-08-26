<script setup>
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppPagination from '@/Components/Shared/AppPagination.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    leads: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ status: null }),
    },
});

const page = usePage();
const { t } = useUiTranslations();
const updatingId = ref(null);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

const rows = computed(() => props.leads?.data ?? []);

const filterChips = computed(() => [
    { value: null, label: t('admin.leads.filter_all') },
    { value: 'pending', label: t('admin.leads.filter_new') },
    { value: 'contacted', label: t('admin.leads.filter_contacted') },
]);

const isFilterActive = (value) => {
    const current = props.filters?.status ?? null;
    return current === value;
};

const chipClass = (value) =>
    isFilterActive(value)
        ? 'bg-primary text-on-primary'
        : 'border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface';

const setFilter = (status) => {
    router.get(
        route('admin.leads.index'),
        status ? { status } : {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const sourceFor = (lead) =>
    lead.service?.title
    || lead.interest_other
    || t('admin.leads.source_contact_form');

const statusLabel = (status) =>
    status === 'contacted'
        ? t('admin.leads.status_contacted')
        : t('admin.leads.status_new');

const statusClass = (status) =>
    status === 'contacted'
        ? 'bg-primary/15 text-primary'
        : 'bg-secondary/15 text-secondary';

const formatReceived = (iso) => {
    if (!iso) {
        return '—';
    }

    try {
        return new Intl.DateTimeFormat(localeCode.value, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).format(new Date(iso));
    } catch {
        return iso;
    }
};

const toggleStatus = (lead) => {
    if (updatingId.value) {
        return;
    }

    const nextStatus = lead.status === 'pending' ? 'contacted' : 'pending';
    updatingId.value = lead.id;

    router.patch(
        route('admin.leads.update-status', lead.id),
        { status: nextStatus },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingId.value = null;
            },
        },
    );
};
</script>

<template>
    <AdminLayout :title="t('admin.leads.title')">
        <Head :title="t('admin.leads.title')" />

        <div class="mb-lg">
            <h1 class="mb-2 text-display-md text-on-surface">
                {{ t('admin.leads.title') }}
            </h1>
            <p class="text-body-md text-on-surface-variant">
                {{ t('admin.leads.subtitle') }}
            </p>
        </div>

        <div class="mb-md flex flex-wrap gap-sm">
            <button
                v-for="chip in filterChips"
                :key="String(chip.value)"
                type="button"
                class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors duration-200"
                :class="chipClass(chip.value)"
                @click="setFilter(chip.value)"
            >
                {{ chip.label }}
            </button>
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div
                v-if="!rows.length"
                class="px-md py-xl text-center text-body-md text-on-surface-variant"
            >
                {{ t('admin.leads.empty') }}
            </div>

            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full min-w-[720px] border-collapse">
                    <thead class="border-b border-outline-variant bg-surface-container-low">
                        <tr>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_name') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_email') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_phone') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_source') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_status') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_received') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.leads.col_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(lead, index) in rows"
                            :key="lead.id"
                            class="transition-colors hover:bg-surface-container"
                            :class="
                                index < rows.length - 1
                                    ? 'border-b border-outline-variant'
                                    : ''
                            "
                        >
                            <td class="px-md py-sm text-start text-body-md font-semibold text-on-surface">
                                {{ lead.full_name }}
                            </td>
                            <td class="px-md py-sm text-start text-body-md text-on-surface-variant">
                                {{ lead.email }}
                            </td>
                            <td class="px-md py-sm text-start text-body-md text-on-surface-variant">
                                {{ lead.phone || '—' }}
                            </td>
                            <td class="px-md py-sm text-start text-body-md text-on-surface-variant">
                                {{ sourceFor(lead) }}
                            </td>
                            <td class="px-md py-sm text-start">
                                <span
                                    class="inline-flex rounded-sm px-3 py-1 text-label-md uppercase tracking-wide"
                                    :class="statusClass(lead.status)"
                                >
                                    {{ statusLabel(lead.status) }}
                                </span>
                            </td>
                            <td class="px-md py-sm text-start text-body-md text-on-surface-variant">
                                {{ formatReceived(lead.created_at) }}
                            </td>
                            <td class="px-md py-sm text-end">
                                <button
                                    type="button"
                                    class="text-label-md uppercase tracking-wide text-primary transition-colors hover:text-secondary disabled:opacity-50"
                                    :disabled="updatingId === lead.id"
                                    @click="toggleStatus(lead)"
                                >
                                    {{
                                        lead.status === 'pending'
                                            ? t('admin.leads.mark_contacted')
                                            : t('admin.leads.back_to_pending')
                                    }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AppPagination
            class="mt-md"
            :meta="leads.meta"
        />
    </AdminLayout>
</template>
