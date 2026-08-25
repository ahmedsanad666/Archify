<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { moveArrayElement, useSortable } from '@vueuse/integrations/useSortable';
import StatisticFormPanel from '@/Components/Admin/About/StatisticFormPanel.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';
import {
    IconGripVertical,
    IconPencil,
    IconPlus,
    IconTrash,
} from '@tabler/icons-vue';

const props = defineProps({
    statistics: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();

const listRef = ref(null);
const items = ref([]);
const reordering = ref(false);
const panelOpen = ref(false);
const editingStatistic = ref(null);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

const labelFor = (stat) => {
    const translations = stat.translations ?? {};
    const code = localeCode.value;
    return (
        translations[code]?.label
        || translations.en?.label
        || translations.tr?.label
        || translations.ar?.label
        || Object.values(translations).find((row) => row?.label)?.label
        || '—'
    );
};

const syncItems = () => {
    items.value = props.statistics.map((stat) => ({ ...stat }));
};

watch(
    () => props.statistics,
    () => syncItems(),
    { immediate: true, deep: true },
);

useSortable(listRef, items, {
    handle: '.drag-handle',
    animation: 150,
    onUpdate: (e) => {
        moveArrayElement(items, e.oldIndex, e.newIndex, e);
        nextTick(() => {
            const ids = items.value.map((s) => s.id).filter(Boolean);
            if (!ids.length || reordering.value) {
                return;
            }
            reordering.value = true;
            router.post(
                route('admin.statistics.reorder'),
                { ids },
                {
                    preserveScroll: true,
                    onFinish: () => {
                        reordering.value = false;
                    },
                },
            );
        });
    },
});

const openCreate = () => {
    editingStatistic.value = null;
    panelOpen.value = true;
};

const openEdit = (stat) => {
    editingStatistic.value = stat;
    panelOpen.value = true;
};

const destroy = async (id) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.statistics.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }
    router.delete(route('admin.statistics.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <div class="mb-md flex justify-end">
            <button
                type="button"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="openCreate"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                {{ t('admin.statistics.add') }}
            </button>
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div class="overflow-x-auto">
                <table class="w-full table-fixed border-collapse">
                    <thead
                        class="border-b border-outline-variant bg-surface-container-low"
                    >
                        <tr>
                            <th class="w-12 px-md py-sm" aria-label="Reorder" />
                            <th
                                class="w-16 px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.statistics.col_order') }}
                            </th>
                            <th
                                class="w-28 px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.statistics.col_count') }}
                            </th>
                            <th
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.statistics.col_label') }}
                            </th>
                            <th
                                class="w-28 px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.statistics.col_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        ref="listRef"
                        class="divide-y divide-outline-variant"
                    >
                        <tr
                            v-for="(stat, index) in items"
                            :key="stat.id"
                            class="transition-colors hover:bg-surface-container-high"
                        >
                            <td class="px-md py-md text-on-surface-variant">
                                <button
                                    type="button"
                                    class="drag-handle cursor-grab hover:text-primary"
                                    :aria-label="t('admin.statistics.drag_reorder')"
                                >
                                    <IconGripVertical
                                        :size="18"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </td>
                            <td
                                class="px-sm py-md text-body-md text-on-surface-variant"
                            >
                                {{ index + 1 }}
                            </td>
                            <td
                                class="px-sm py-md text-body-lg font-semibold text-on-surface"
                            >
                                {{ stat.count }}
                            </td>
                            <td
                                class="truncate px-sm py-md text-body-md text-on-surface-variant"
                            >
                                {{ labelFor(stat) }}
                            </td>
                            <td class="px-md py-md text-end">
                                <div
                                    class="inline-flex items-center gap-sm"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-primary"
                                        :aria-label="t('common.edit')"
                                        @click="openEdit(stat)"
                                    >
                                        <IconPencil
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-error"
                                        :aria-label="t('common.delete')"
                                        @click="destroy(stat.id)"
                                    >
                                        <IconTrash
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!items.length">
                            <td
                                colspan="5"
                                class="px-md py-xl text-center text-body-md text-on-surface-variant"
                            >
                                {{ t('admin.statistics.empty') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <StatisticFormPanel
            v-model:open="panelOpen"
            :statistic="editingStatistic"
        />
    </div>
</template>
