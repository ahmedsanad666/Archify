<script setup>
import { computed, ref } from 'vue';
import { Link, Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BlogCategoryFormPanel from '@/Components/Admin/BlogCategoryFormPanel.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';
import { IconPencil, IconPlus, IconTrash } from '@tabler/icons-vue';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();

const panelOpen = ref(false);
const editingCategory = ref(null);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

const fieldFor = (category, field) => {
    const translations = category.translations ?? {};
    const code = localeCode.value;

    return (
        translations[code]?.[field]
        || translations.en?.[field]
        || translations.tr?.[field]
        || translations.ar?.[field]
        || Object.values(translations).find((row) => row?.[field])?.[field]
        || category[field]
        || ''
    );
};

const openCreate = () => {
    editingCategory.value = null;
    panelOpen.value = true;
};

const openEdit = (category) => {
    editingCategory.value = category;
    panelOpen.value = true;
};

const destroy = async (category) => {
    if ((category.blogs_count ?? 0) > 0) {
        await confirm({
            title: t('common.confirm_title'),
            message: t('admin.blog_categories.confirm_delete_blocked'),
            variant: 'danger',
            confirmLabel: t('common.confirm'),
        });
        return;
    }

    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.blog_categories.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }

    router.delete(route('admin.blog-categories.destroy', category.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="t('admin.blog_categories.title')" />

    <AdminLayout>
        <div
            class="mb-xl flex flex-col justify-between gap-md md:flex-row md:items-end"
        >
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
                                {{ t('admin.menu.dashboard') }}
                            </Link>
                        </li>
                        <li aria-hidden="true" class="text-outline">/</li>
                        <li class="text-on-surface-variant">
                            {{ t('admin.blog_categories.breadcrumb_section') }}
                        </li>
                        <li aria-hidden="true" class="text-outline">/</li>
                        <li class="text-on-surface" aria-current="page">
                            {{ t('admin.blog_categories.title') }}
                        </li>
                    </ol>
                </nav>
                <h1
                    class="mb-xs text-[28px] font-semibold tracking-[-0.01em] text-on-surface md:text-display-md md:tracking-[-0.03em]"
                >
                    {{ t('admin.blog_categories.title') }}
                </h1>
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.blog_categories.subtitle') }}
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
                />
                {{ t('admin.blog_categories.add') }}
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
                v-if="categories.length === 0"
                class="px-md py-xl text-center"
            >
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.blog_categories.empty') }}
                </p>
                <button
                    type="button"
                    class="mt-md inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                    @click="openCreate"
                >
                    <IconPlus
                        :size="18"
                        stroke-width="1.5"
                    />
                    {{ t('admin.blog_categories.empty_cta') }}
                </button>
            </div>

            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="w-full border-collapse">
                    <thead>
                        <tr
                            class="border-b border-outline-variant bg-surface-container-low"
                        >
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_name') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_slug') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_color') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_type') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-end text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_items') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-end text-label-md uppercase tracking-wider text-on-surface-variant"
                            >
                                {{ t('admin.blog_categories.col_actions') }}
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
                            <td class="px-md py-sm text-start text-on-surface">
                                {{ fieldFor(category, 'name') || '—' }}
                            </td>
                            <td
                                class="px-md py-sm text-start font-mono text-[14px] text-on-surface-variant"
                            >
                                {{ fieldFor(category, 'slug') || '—' }}
                            </td>
                            <td class="px-md py-sm text-start">
                                <span class="inline-flex items-center gap-sm">
                                    <span
                                        class="size-4 shrink-0 rounded-sm border border-outline-variant"
                                        :style="{
                                            backgroundColor:
                                                category.color || '#f9ba7f',
                                        }"
                                        aria-hidden="true"
                                    />
                                    <span
                                        class="font-mono text-[14px] text-on-surface-variant"
                                    >
                                        {{ category.color || '—' }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-md py-sm text-start">
                                <span
                                    class="inline-flex items-center rounded-sm border border-outline-variant bg-surface-container-high px-3 py-1 text-label-md text-on-surface-variant"
                                >
                                    {{ t('admin.blog_categories.type_blog') }}
                                </span>
                            </td>
                            <td class="px-md py-sm text-end text-on-surface">
                                {{ category.blogs_count ?? 0 }}
                            </td>
                            <td class="px-md py-sm text-end">
                                <div
                                    class="inline-flex justify-end gap-sm opacity-50 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-primary"
                                        :title="t('common.edit')"
                                        @click="openEdit(category)"
                                    >
                                        <IconPencil
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                        <span class="sr-only">{{
                                            t('common.edit')
                                        }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-error disabled:cursor-not-allowed disabled:opacity-30"
                                        :title="t('common.delete')"
                                        :disabled="
                                            (category.blogs_count ?? 0) > 0
                                        "
                                        @click="destroy(category)"
                                    >
                                        <IconTrash
                                            :size="20"
                                            stroke-width="1.5"
                                        />
                                        <span class="sr-only">{{
                                            t('common.delete')
                                        }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <BlogCategoryFormPanel
            v-model:open="panelOpen"
            :category="editingCategory"
        />
    </AdminLayout>
</template>
