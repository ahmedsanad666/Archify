<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { moveArrayElement, useSortable } from '@vueuse/integrations/useSortable';
import CoreValueFormPanel from '@/Components/Admin/About/CoreValueFormPanel.vue';
import { resolveAppIcon } from '@/icons/appIcons';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';
import {
    IconCompass,
    IconGripVertical,
    IconPencil,
    IconPlus,
    IconTrash,
} from '@tabler/icons-vue';

const props = defineProps({
    coreValues: {
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
const editingValue = ref(null);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

const iconFor = (name) => resolveAppIcon(name, IconCompass);

const titleFor = (value) => {
    const translations = value.translations ?? {};
    const code = localeCode.value;
    return (
        translations[code]?.title
        || translations.en?.title
        || translations.tr?.title
        || translations.ar?.title
        || Object.values(translations).find((row) => row?.title)?.title
        || '—'
    );
};

const descriptionFor = (value) => {
    const translations = value.translations ?? {};
    const code = localeCode.value;
    return (
        translations[code]?.short_description
        || translations.en?.short_description
        || translations.tr?.short_description
        || translations.ar?.short_description
        || Object.values(translations).find((row) => row?.short_description)
            ?.short_description
        || ''
    );
};

const syncItems = () => {
    items.value = props.coreValues.map((value) => ({ ...value }));
};

watch(
    () => props.coreValues,
    () => syncItems(),
    { immediate: true, deep: true },
);

useSortable(listRef, items, {
    handle: '.drag-handle',
    animation: 150,
    onUpdate: (e) => {
        moveArrayElement(items, e.oldIndex, e.newIndex, e);
        nextTick(() => {
            const ids = items.value.map((v) => v.id).filter(Boolean);
            if (!ids.length || reordering.value) {
                return;
            }
            reordering.value = true;
            router.post(
                route('admin.core-values.reorder'),
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
    editingValue.value = null;
    panelOpen.value = true;
};

const openEdit = (value) => {
    editingValue.value = value;
    panelOpen.value = true;
};

const destroy = async (id) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.core_values.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }
    router.delete(route('admin.core-values.destroy', id), {
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
                {{ t('admin.core_values.add') }}
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
                                {{ t('admin.core_values.col_order') }}
                            </th>
                            <th class="w-16 px-sm py-sm" aria-label="Icon" />
                            <th
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.core_values.col_title') }}
                            </th>
                            <th
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.core_values.col_description') }}
                            </th>
                            <th
                                class="w-28 px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.core_values.col_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        ref="listRef"
                        class="divide-y divide-outline-variant"
                    >
                        <tr
                            v-for="(value, index) in items"
                            :key="value.id"
                            class="transition-colors hover:bg-surface-container-high"
                        >
                            <td class="px-md py-md text-on-surface-variant">
                                <button
                                    type="button"
                                    class="drag-handle cursor-grab hover:text-primary"
                                    :aria-label="t('admin.core_values.drag_reorder')"
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
                            <td class="px-sm py-md">
                                <component
                                    :is="iconFor(value.icon)"
                                    class="text-primary"
                                    :size="22"
                                    stroke-width="1.5"
                                />
                            </td>
                            <td
                                class="truncate px-sm py-md text-body-lg text-on-surface"
                            >
                                {{ titleFor(value) }}
                            </td>
                            <td
                                class="truncate px-sm py-md text-body-md text-on-surface-variant"
                            >
                                {{ descriptionFor(value) || '—' }}
                            </td>
                            <td class="px-md py-md text-end">
                                <div
                                    class="inline-flex items-center gap-sm"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-primary"
                                        :aria-label="t('common.edit')"
                                        @click="openEdit(value)"
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
                                        @click="destroy(value.id)"
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
                                colspan="6"
                                class="px-md py-xl text-center text-body-md text-on-surface-variant"
                            >
                                {{ t('admin.core_values.empty') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <CoreValueFormPanel
            v-model:open="panelOpen"
            :core-value="editingValue"
        />
    </div>
</template>
