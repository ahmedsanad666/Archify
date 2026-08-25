<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { moveArrayElement, useSortable } from '@vueuse/integrations/useSortable';
import {
    IconGripVertical,
    IconPencil,
    IconPlus,
    IconTrash,
} from '@tabler/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TeamFormPanel from '@/Components/Admin/TeamFormPanel.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    members: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();
const search = ref('');
const listRef = ref(null);
const items = ref([]);
const reordering = ref(false);
const panelOpen = ref(false);
const editingMember = ref(null);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

const isSearching = computed(() => search.value.trim() !== '');

const roleFor = (member) => {
    const translations = member.translations ?? {};
    const code = localeCode.value;
    return (
        translations[code]?.role
        || translations.en?.role
        || translations.tr?.role
        || translations.ar?.role
        || Object.values(translations).find((row) => row?.role)?.role
        || ''
    );
};

const syncItems = () => {
    items.value = props.members.map((member) => ({ ...member }));
};

watch(
    () => props.members,
    () => syncItems(),
    { immediate: true, deep: true },
);

useSortable(listRef, items, {
    handle: '.drag-handle',
    animation: 150,
    onUpdate: (e) => {
        moveArrayElement(items, e.oldIndex, e.newIndex, e);
        if (isSearching.value) {
            return;
        }
        nextTick(() => {
            const ids = items.value.map((m) => m.id).filter(Boolean);
            if (!ids.length || reordering.value) {
                return;
            }
            reordering.value = true;
            router.post(
                route('admin.team-members.reorder'),
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

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) {
        return items.value;
    }

    return items.value.filter((member) => {
        const name = (member.name ?? '').toLowerCase();
        const role = roleFor(member).toLowerCase();
        return name.includes(q) || role.includes(q);
    });
});

const rows = computed(() =>
    isSearching.value ? filtered.value : items.value,
);

const openCreate = () => {
    editingMember.value = null;
    panelOpen.value = true;
};

const openEdit = (member) => {
    editingMember.value = member;
    panelOpen.value = true;
};

const destroy = async (id) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.team.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }
    router.delete(route('admin.team-members.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout :title="t('admin.team.title')">
        <Head :title="t('admin.team.title')" />

        <div class="mb-lg flex flex-wrap items-end justify-between gap-md">
            <div>
                <h1 class="mb-2 text-display-md text-on-surface">
                    {{ t('admin.team.title') }}
                </h1>
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.team.subtitle') }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-sm rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="openCreate"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                {{ t('admin.team.add') }}
            </button>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div
                class="flex items-center justify-between border-b border-outline-variant p-sm"
            >
                <div class="relative w-full max-w-xs">
                    <input
                        v-model="search"
                        type="search"
                        :placeholder="t('admin.team.search_placeholder')"
                        class="w-full rounded-md border border-outline bg-surface-container-low px-sm py-xs text-body-md text-on-surface outline-none transition-colors placeholder:text-outline-variant focus:border-primary focus:ring-1 focus:ring-primary/20"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-fixed border-collapse">
                    <colgroup>
                        <col class="w-12" />
                        <col class="w-14" />
                        <col />
                        <col />
                        <col class="w-16" />
                        <col class="w-28" />
                    </colgroup>
                    <thead
                        class="border-b border-outline-variant bg-surface-container-low"
                    >
                        <tr>
                            <th
                                scope="col"
                                class="px-md py-sm"
                                aria-label="Reorder"
                            />
                            <th
                                scope="col"
                                class="px-sm py-sm"
                                aria-label="Avatar"
                            />
                            <th
                                scope="col"
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.team.col_name') }}
                            </th>
                            <th
                                scope="col"
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.team.col_role') }}
                            </th>
                            <th
                                scope="col"
                                class="px-sm py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.team.col_order') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.team.col_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        ref="listRef"
                        class="divide-y divide-outline-variant"
                    >
                        <tr
                            v-for="(member, index) in rows"
                            :key="member.id"
                            class="group transition-colors hover:bg-surface-container-high"
                        >
                            <td class="px-md py-md align-middle text-on-surface-variant">
                                <button
                                    v-if="!isSearching"
                                    type="button"
                                    class="drag-handle cursor-grab text-on-surface-variant transition-colors hover:text-primary"
                                    aria-label="Drag to reorder"
                                >
                                    <IconGripVertical
                                        :size="18"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </td>
                            <td class="px-sm py-md align-middle">
                                <div
                                    class="size-10 overflow-hidden rounded-full border border-outline-variant bg-surface"
                                >
                                    <img
                                        v-if="member.avatar_url"
                                        :src="member.avatar_url"
                                        :alt="member.name"
                                        class="size-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center text-label-md text-on-surface-variant"
                                    >
                                        {{ member.name?.charAt(0) ?? '?' }}
                                    </div>
                                </div>
                            </td>
                            <td
                                class="truncate px-sm py-md align-middle text-start text-label-lg text-on-surface"
                            >
                                {{ member.name }}
                            </td>
                            <td
                                class="truncate px-sm py-md align-middle text-start text-body-md text-on-surface-variant"
                            >
                                {{ roleFor(member) || '—' }}
                            </td>
                            <td
                                class="px-sm py-md align-middle text-start text-body-md text-on-surface-variant"
                            >
                                {{ index + 1 }}
                            </td>
                            <td class="px-md py-md align-middle text-end">
                                <div
                                    class="inline-flex items-center justify-end gap-sm"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-primary"
                                        :aria-label="t('common.edit')"
                                        @click="openEdit(member)"
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
                                        @click="destroy(member.id)"
                                    >
                                        <IconTrash
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td
                                colspan="6"
                                class="px-md py-xl text-center text-body-md text-on-surface-variant"
                            >
                                {{ t('admin.team.empty') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <TeamFormPanel
            v-model:open="panelOpen"
            :member="editingMember"
        />
    </AdminLayout>
</template>
