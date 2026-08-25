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
import ServiceFormPanel from '@/Components/Admin/ServiceFormPanel.vue';
import { resolveAppIcon } from '@/icons/appIcons';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const search = ref('');
const listRef = ref(null);
const items = ref([]);
const reordering = ref(false);
const panelOpen = ref(false);
const editingService = ref(null);

const isSearching = computed(() => search.value.trim() !== '');

const syncItems = () => {
    items.value = props.services.map((service) => ({ ...service }));
};

watch(
    () => props.services,
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
            const ids = items.value.map((s) => s.id).filter(Boolean);
            if (!ids.length || reordering.value) {
                return;
            }
            reordering.value = true;
            router.post(
                route('admin.services.reorder'),
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

    return items.value.filter((service) => {
        const title = (service.title ?? '').toLowerCase();
        const icon = (service.icon ?? '').toLowerCase();
        return title.includes(q) || icon.includes(q);
    });
});

const rows = computed(() =>
    isSearching.value ? filtered.value : items.value,
);

const openCreate = () => {
    editingService.value = null;
    panelOpen.value = true;
};

const openEdit = (service) => {
    editingService.value = service;
    panelOpen.value = true;
};

const destroy = (id) => {
    if (!confirm('Delete this service?')) {
        return;
    }
    router.delete(route('admin.services.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout :title="t('admin.menu.services')">
        <Head :title="t('admin.menu.services')" />

        <div class="mb-lg flex flex-wrap items-end justify-between gap-md">
            <div>
                <h1 class="mb-2 text-display-md text-on-surface">
                    {{ t('admin.menu.services') }}
                </h1>
                <p class="text-body-md text-outline">
                    Manage the services displayed on your website
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-sm rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="openCreate"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                Add new service
            </button>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container-low"
        >
            <div
                class="flex items-center justify-between border-b border-outline-variant p-sm"
            >
                <div class="relative w-full max-w-xs">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search services..."
                        class="w-full rounded-md border border-outline bg-surface-container px-sm py-xs text-body-md text-on-surface outline-none transition-colors placeholder:text-outline-variant focus:border-primary focus:ring-1 focus:ring-primary/20"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-start">
                    <thead
                        class="border-b border-outline-variant bg-surface-container"
                    >
                        <tr>
                            <th class="w-12 px-md py-sm" />
                            <th
                                class="w-16 px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Icon
                            </th>
                            <th
                                class="px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Title
                            </th>
                            <th
                                class="px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Items
                            </th>
                            <th
                                class="px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Status
                            </th>
                            <th
                                class="w-24 px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Order
                            </th>
                            <th
                                class="px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        ref="listRef"
                        class="divide-y divide-outline-variant"
                    >
                        <tr
                            v-for="(service, index) in rows"
                            :key="service.id"
                            class="group transition-colors hover:bg-surface-container-high"
                        >
                            <td class="px-md py-md text-on-surface-variant">
                                <button
                                    v-if="!isSearching"
                                    type="button"
                                    class="drag-handle cursor-grab text-on-surface-variant hover:text-on-surface"
                                    aria-label="Drag to reorder"
                                >
                                    <IconGripVertical
                                        :size="18"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </td>
                            <td class="px-sm py-md">
                                <component
                                    :is="resolveAppIcon(service.icon)"
                                    class="text-outline transition-colors group-hover:text-primary"
                                    :size="24"
                                    stroke-width="1.5"
                                />
                            </td>
                            <td class="px-sm py-md text-body-md text-on-surface">
                                {{ service.title || 'Untitled' }}
                            </td>
                            <td
                                class="px-sm py-md text-body-md text-on-surface-variant"
                            >
                                {{ service.items_count ?? 0 }}
                            </td>
                            <td class="px-sm py-md">
                                <span
                                    class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide"
                                    :class="
                                        service.show_on_home
                                            ? 'bg-primary/15 text-primary'
                                            : 'border border-outline-variant text-on-surface-variant'
                                    "
                                >
                                    {{
                                        service.show_on_home
                                            ? 'On home'
                                            : 'Hidden'
                                    }}
                                </span>
                            </td>
                            <td
                                class="px-sm py-md text-body-md text-on-surface-variant"
                            >
                                {{ index + 1 }}
                            </td>
                            <td class="px-md py-md">
                                <div
                                    class="flex items-center justify-end gap-sm"
                                >
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-primary"
                                        aria-label="Edit"
                                        @click="openEdit(service)"
                                    >
                                        <IconPencil
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md p-xs text-on-surface-variant transition-colors hover:text-error"
                                        aria-label="Delete"
                                        @click="destroy(service.id)"
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
                                colspan="7"
                                class="px-md py-xl text-center text-body-md text-on-surface-variant"
                            >
                                {{ t('common.empty') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ServiceFormPanel
            v-model:open="panelOpen"
            :service="editingService"
        />
    </AdminLayout>
</template>
