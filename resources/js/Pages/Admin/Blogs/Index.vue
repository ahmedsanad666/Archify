<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppPagination from '@/Components/Shared/AppPagination.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';
import { IconPencil, IconPlus, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    blogs: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ category: null }),
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();

const rows = computed(() => props.blogs?.data ?? []);

const localeCode = computed(
    () => page.props.locale?.code ?? page.props.locale ?? 'en',
);

/** Prefer current admin UI locale, then EN / any filled translation. */
const fieldFor = (blog, field) => {
    const translations = blog.translations ?? {};
    const code = localeCode.value;

    return (
        translations[code]?.[field]
        || translations.en?.[field]
        || translations.tr?.[field]
        || translations.ar?.[field]
        || Object.values(translations).find((row) => row?.[field])?.[field]
        || blog[field]
        || null
    );
};

const titleFor = (blog) => fieldFor(blog, 'title') || '—';

const readTimeFor = (blog) => fieldFor(blog, 'read_time') ?? blog.read_time ?? null;

const categoryFilter = computed({
    get: () => props.filters?.category ?? null,
    set: (value) => {
        router.get(
            route('admin.blogs.index'),
            value ? { category: value } : {},
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    },
});

const categoryOptions = computed(() => [
    { value: null, label: t('admin.blogs.filter_all') },
    ...props.categories.map((category) => ({
        value: category.id,
        label: category.name || `Category #${category.id}`,
    })),
]);

const destroy = async (id) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.blogs.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }
    router.delete(route('admin.blogs.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout :title="t('admin.blogs.title')">
        <Head :title="t('admin.blogs.title')" />

        <div class="mb-xl flex flex-wrap items-end justify-between gap-md">
            <div>
                <h2 class="mb-xs text-display-md text-on-surface">
                    {{ t('admin.blogs.title') }}
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.blogs.subtitle') }}
                </p>
            </div>
            <Link
                :href="route('admin.blogs.create')"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                {{ t('admin.blogs.add') }}
            </Link>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div class="mb-md max-w-xs">
            <AppSelect
                v-model="categoryFilter"
                :options="categoryOptions"
                :label="t('admin.blogs.field_category')"
                :placeholder="t('admin.blogs.filter_all')"
            />
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div v-if="!rows.length" class="px-md py-xl text-center">
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.blogs.empty') }}
                </p>
                <Link
                    :href="route('admin.blogs.create')"
                    class="mt-md inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                >
                    <IconPlus :size="18" stroke-width="1.5" />
                    {{ t('admin.blogs.add') }}
                </Link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full border-collapse text-start">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.blogs.col_post') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.blogs.col_category') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.blogs.col_views') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.blogs.col_read_time') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('common.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="blog in rows"
                            :key="blog.id"
                            class="border-b border-outline-variant last:border-b-0 hover:bg-surface-container-high"
                        >
                            <td class="px-md py-sm text-start">
                                <div class="flex items-center gap-sm">
                                    <div
                                        class="h-12 w-12 shrink-0 overflow-hidden rounded-md border border-outline-variant bg-surface-container-low"
                                    >
                                        <img
                                            v-if="blog.thumbnail_url"
                                            :src="blog.thumbnail_url"
                                            :alt="titleFor(blog)"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <span class="text-body-md text-on-surface">
                                        {{ titleFor(blog) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-md py-sm text-start">
                                <span
                                    v-if="blog.category"
                                    class="inline-flex items-center gap-xs rounded-sm border border-outline-variant px-3 py-1 text-label-md text-on-surface"
                                >
                                    <span
                                        class="h-2 w-2 shrink-0 rounded-full"
                                        :style="{
                                            backgroundColor:
                                                blog.category.color ||
                                                '#f9ba7f',
                                        }"
                                    />
                                    {{ blog.category.name || '—' }}
                                </span>
                                <span
                                    v-else
                                    class="text-body-md text-on-surface-variant"
                                >
                                    —
                                </span>
                            </td>
                            <td
                                class="px-md py-sm text-start text-body-md text-on-surface-variant"
                            >
                                {{ blog.views_count ?? 0 }}
                            </td>
                            <td
                                class="px-md py-sm text-start text-body-md text-on-surface-variant"
                            >
                                <template v-if="readTimeFor(blog)">
                                    {{ readTimeFor(blog) }}
                                    {{ t('admin.blogs.read_time_unit') }}
                                </template>
                                <template v-else>—</template>
                            </td>
                            <td class="px-md py-sm text-end">
                                <div
                                    class="inline-flex items-center justify-end gap-sm"
                                >
                                    <Link
                                        :href="
                                            route('admin.blogs.edit', blog.id)
                                        "
                                        class="text-on-surface-variant transition-colors hover:text-primary"
                                    >
                                        <IconPencil
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </Link>
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-error"
                                        @click="destroy(blog.id)"
                                    >
                                        <IconTrash
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AppPagination :meta="blogs.meta" class="mt-md" />
    </AdminLayout>
</template>
