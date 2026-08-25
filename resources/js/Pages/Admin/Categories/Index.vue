<script setup>
import { Link, Head, router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import CategoryFormModal from "@/Components/Admin/CategoryFormModal.vue";
import { useConfirm } from "@/Composables/useConfirm";
import { useUiTranslations } from "@/Composables/useUiTranslations";
import {
    IconPencil,
    IconPlus,
    IconTrash,
} from "@tabler/icons-vue";

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();

const modalOpen = ref(false);
const editingCategory = ref(null);

const openCreate = () => {
    editingCategory.value = null;
    modalOpen.value = true;
};

const openEdit = (category) => {
    editingCategory.value = category;
    modalOpen.value = true;
};

const destroy = async (id) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.categories.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }

    router.delete(route("admin.project-categories.destroy", id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Project categories" />

    <AdminLayout>
        <div class="mb-xl flex flex-col justify-between gap-md md:flex-row md:items-end">
            <div>
                <nav
                    aria-label="Breadcrumb"
                    class="mb-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    <ol class="inline-flex items-center gap-xs">
                        <li>
                            <Link
                                :href="route('admin.dashboard')"
                                class="transition-colors hover:text-on-surface"
                            >
                                Dashboard
                            </Link>
                        </li>
                        <li aria-hidden="true" class="text-outline">/</li>
                        <li class="text-on-surface-variant">Projects</li>
                        <li aria-hidden="true" class="text-outline">/</li>
                        <li class="text-on-surface" aria-current="page">
                            Project categories
                        </li>
                    </ol>
                </nav>
                <h1
                    class="mb-xs text-[28px] font-semibold tracking-[-0.01em] text-on-surface md:text-display-md md:tracking-[-0.03em]"
                >
                    Project categories
                </h1>
                <p class="text-body-md text-on-surface-variant">
                    Manage project categories used across the site
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="openCreate"
            >
                <IconPlus
                    :size="20"
                    stroke-width="1.5"
                    class="transition-transform duration-300 group-hover:rotate-90"
                />
                Add new category
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
            <div v-if="categories.length === 0" class="px-md py-xl text-center">
                <p class="text-body-md text-on-surface-variant">
                    No categories yet. Add your first project category to
                    organize the portfolio.
                </p>
                <button
                    type="button"
                    class="mt-md inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                    @click="openCreate"
                >
                    <IconPlus :size="18" stroke-width="1.5" />
                    Add new category
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full border-collapse text-start">
                    <thead>
                        <tr
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                Name
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                Slug
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                Type
                            </th>
                            <th
                                class="px-md py-sm text-end text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                Items
                            </th>
                            <th
                                class="px-md py-sm text-end text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-body-md">
                        <tr
                            v-for="(category, index) in categories"
                            :key="category.id"
                            class="group border-b border-outline-variant transition-colors last:border-b-0 hover:border-s-2 hover:border-s-primary hover:bg-surface-container-high"
                            :class="
                                index % 2 === 1
                                    ? 'bg-surface-container-low/50'
                                    : ''
                            "
                        >
                            <td class="px-md py-sm text-on-surface">
                                {{ category.name || "—" }}
                            </td>
                            <td
                                class="px-md py-sm font-mono text-[14px] text-on-surface-variant"
                            >
                                {{ category.slug || "—" }}
                            </td>
                            <td class="px-md py-sm">
                                <span
                                    class="inline-flex items-center rounded-sm border border-outline-variant bg-surface-container-high px-3 py-1 text-label-md capitalize text-on-surface-variant"
                                >
                                    {{ category.type || "project" }}
                                </span>
                            </td>
                            <td
                                class="px-md py-sm text-end text-on-surface"
                            >
                                {{ category.projects_count ?? 0 }}
                            </td>
                            <td class="px-md py-sm">
                                <div
                                    class="flex justify-end gap-sm opacity-50 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-primary"
                                        title="Edit"
                                        @click="openEdit(category)"
                                    >
                                        <IconPencil
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                        <span class="sr-only">Edit</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-error"
                                        title="Delete"
                                        @click="destroy(category.id)"
                                    >
                                        <IconTrash
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                        <span class="sr-only">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <CategoryFormModal
            v-model:open="modalOpen"
            :category="editingCategory"
        />
    </AdminLayout>
</template>
