<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { IconFile, IconTrash, IconVideo } from '@tabler/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppPagination from '@/Components/Shared/AppPagination.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            model_type: null,
            collection: null,
            q: null,
        }),
    },
    modelTypes: {
        type: Array,
        default: () => [],
    },
    collections: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();
const { confirm } = useConfirm();

const search = ref(props.filters?.q ?? '');

watch(
    () => props.filters?.q,
    (value) => {
        search.value = value ?? '';
    },
);

const rows = computed(() => props.media?.data ?? []);

const modelTypeFilter = computed({
    get: () => props.filters?.model_type ?? null,
    set: (value) => applyFilters({ model_type: value }),
});

const collectionFilter = computed({
    get: () => props.filters?.collection ?? null,
    set: (value) => applyFilters({ collection: value }),
});

const modelTypeOptions = computed(() => [
    { value: null, label: t('admin.media.filter_all_models') },
    ...props.modelTypes,
]);

const collectionOptions = computed(() => [
    { value: null, label: t('admin.media.filter_all_collections') },
    ...props.collections,
]);

const applyFilters = (overrides = {}) => {
    const next = {
        model_type: props.filters?.model_type ?? null,
        collection: props.filters?.collection ?? null,
        q: props.filters?.q ?? null,
        ...overrides,
    };

    const params = {};
    if (next.model_type) {
        params.model_type = next.model_type;
    }
    if (next.collection) {
        params.collection = next.collection;
    }
    if (next.q) {
        params.q = next.q;
    }

    router.get(route('admin.media.index'), params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

let searchTimer = null;
const onSearchInput = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const q = search.value.trim();
        applyFilters({ q: q !== '' ? q : null });
    }, 300);
};

const destroy = async (item) => {
    const ok = await confirm({
        title: t('common.confirm_title'),
        message: t('admin.media.confirm_delete'),
        variant: 'danger',
    });
    if (!ok) {
        return;
    }

    router.delete(route('admin.media.destroy', item.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout :title="t('admin.media.title')">
        <Head :title="t('admin.media.title')" />

        <div class="mb-xl">
            <h1 class="mb-xs text-display-md text-on-surface">
                {{ t('admin.media.title') }}
            </h1>
            <p class="text-body-md text-on-surface-variant">
                {{ t('admin.media.subtitle') }}
            </p>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div
            class="mb-md grid gap-md rounded-lg border border-outline-variant bg-surface-container p-md sm:grid-cols-2 lg:grid-cols-3"
        >
            <AppSelect
                v-model="modelTypeFilter"
                :options="modelTypeOptions"
                :label="t('admin.media.filter_model')"
                :placeholder="t('admin.media.filter_all_models')"
            />
            <AppSelect
                v-model="collectionFilter"
                :options="collectionOptions"
                :label="t('admin.media.filter_collection')"
                :placeholder="t('admin.media.filter_all_collections')"
            />
            <div class="flex flex-col gap-xs">
                <label
                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    {{ t('common.search') }}
                </label>
                <input
                    v-model="search"
                    type="search"
                    :placeholder="t('admin.media.search_placeholder')"
                    class="w-full rounded-md border border-outline bg-surface-container px-sm py-1.5 text-body-md text-on-surface outline-none transition-colors placeholder:text-on-surface-variant focus:border-primary focus:ring-1 focus:ring-primary/20"
                    @input="onSearchInput"
                />
            </div>
        </div>

        <div
            v-if="!rows.length"
            class="rounded-lg border border-outline-variant bg-surface-container px-md py-xl text-center"
        >
            <p class="text-body-md text-on-surface-variant">
                {{ t('admin.media.empty') }}
            </p>
        </div>

        <div
            v-else
            class="grid gap-md sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <article
                v-for="item in rows"
                :key="item.id"
                class="flex flex-col overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors duration-200 hover:border-secondary hover:bg-surface-container-high"
            >
                <div
                    class="relative flex aspect-[4/3] items-center justify-center bg-surface-container-low"
                >
                    <img
                        v-if="item.is_image"
                        :src="item.url"
                        :alt="item.name || item.file_name"
                        class="h-full w-full object-cover"
                    />
                    <IconVideo
                        v-else-if="item.is_video"
                        class="text-on-surface-variant"
                        :size="40"
                        stroke-width="1.5"
                    />
                    <IconFile
                        v-else
                        class="text-on-surface-variant"
                        :size="40"
                        stroke-width="1.5"
                    />
                    <button
                        type="button"
                        class="absolute end-sm top-sm rounded-md bg-surface-container/90 p-xs text-on-surface-variant transition-colors hover:text-error"
                        :aria-label="t('common.delete')"
                        @click="destroy(item)"
                    >
                        <IconTrash :size="18" stroke-width="1.5" />
                    </button>
                </div>

                <div class="flex flex-1 flex-col gap-xs p-sm">
                    <p
                        class="truncate text-label-lg text-on-surface"
                        :title="item.file_name"
                    >
                        {{ item.file_name }}
                    </p>
                    <div class="flex flex-wrap gap-xs">
                        <span
                            class="rounded-sm border border-outline-variant px-2 py-0.5 text-label-md text-on-surface-variant"
                        >
                            {{ item.model_label }}
                        </span>
                        <span
                            class="rounded-sm border border-outline-variant px-2 py-0.5 text-label-md text-on-surface-variant"
                        >
                            {{ item.collection_name }}
                        </span>
                    </div>
                    <p class="mt-auto text-label-md text-on-surface-variant">
                        {{ item.human_size }}
                    </p>
                </div>
            </article>
        </div>

        <AppPagination
            :meta="media.meta"
            class="mt-md"
        />
    </AdminLayout>
</template>
