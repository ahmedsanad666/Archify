<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useSortable } from '@vueuse/integrations/useSortable';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SlideInlineForm from '@/Components/Admin/SlideInlineForm.vue';
import {
    IconChevronDown,
    IconChevronUp,
    IconGripVertical,
    IconPlus,
} from '@tabler/icons-vue';

const props = defineProps({
    sliders: {
        type: Array,
        required: true,
    },
});

const page = usePage();
const languages = computed(() => page.props.languages ?? []);
const defaultLocale = computed(
    () =>
        languages.value.find((l) => l.is_default)?.code ??
        languages.value[0]?.code ??
        'en',
);
const autoTranslateEnabled = computed(
    () => page.props.siteSettings?.auto_translate_enabled ?? false,
);

const items = ref([]);
const listRef = ref(null);
const expandedId = ref(null);
const draft = ref(null);
const reordering = ref(false);

const emptyTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        bag[language.code] = { title: '', description: '' };
    }
    return bag;
};

const syncItems = () => {
    items.value = props.sliders.map((slider) => ({ ...slider }));
};

watch(
    () => props.sliders,
    () => {
        syncItems();
        draft.value = null;
        if (
            expandedId.value !== null &&
            expandedId.value !== 'draft' &&
            !items.value.some((s) => s.id === expandedId.value)
        ) {
            expandedId.value = null;
        }
    },
    { immediate: true, deep: true },
);

useSortable(listRef, items, {
    handle: '.drag-handle',
    animation: 150,
    onUpdate: () => {
        const ids = items.value.map((s) => s.id).filter(Boolean);
        if (!ids.length || reordering.value) {
            return;
        }
        reordering.value = true;
        router.post(
            route('admin.sliders.reorder'),
            { ids },
            {
                preserveScroll: true,
                onFinish: () => {
                    reordering.value = false;
                },
            },
        );
    },
});

const displayTitle = (slider) => {
    const translations = slider.translations ?? {};
    const preferred =
        translations[defaultLocale.value] ??
        Object.values(translations)[0] ??
        {};
    return preferred.title || 'Untitled slide';
};

const toggleExpand = (id) => {
    if (expandedId.value === id) {
        expandedId.value = null;
        return;
    }
    expandedId.value = id;
    draft.value = null;
};

const startDraft = async () => {
    draft.value = {
        id: null,
        is_active: true,
        image_url: null,
        translations: emptyTranslations(),
    };
    expandedId.value = 'draft';
    await nextTick();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelExpand = () => {
    if (expandedId.value === 'draft') {
        draft.value = null;
    }
    expandedId.value = null;
};

const destroy = (id) => {
    if (!confirm('Delete this slider?')) {
        return;
    }
    router.delete(route('admin.sliders.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            if (expandedId.value === id) {
                expandedId.value = null;
            }
        },
    });
};
</script>

<template>
    <AdminLayout title="Home page">
        <Head title="Home page" />

        <div
            class="mb-xl flex flex-wrap items-end justify-between gap-md border-b border-outline-variant pb-md"
        >
            <div>
                <h2 class="mb-xs text-display-md text-on-surface">Home page</h2>
                <p class="text-body-lg text-on-surface-variant">
                    Manage hero slides and homepage section order
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="startDraft"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                Add new slide
            </button>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div class="flex flex-col gap-md">
            <!-- Draft (new slide) -->
            <div
                v-if="draft"
                class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
            >
                <div
                    class="flex items-center justify-between border-b border-outline-variant bg-surface-container p-md"
                >
                    <div class="flex items-center gap-md">
                        <div
                            class="h-12 w-16 rounded-md border border-outline-variant bg-surface-container-high"
                        />
                        <div>
                            <h4 class="text-label-lg uppercase tracking-wide text-on-surface">
                                New slide
                            </h4>
                            <span
                                class="mt-1 inline-block rounded-sm bg-secondary/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-secondary"
                            >
                                Draft
                            </span>
                        </div>
                    </div>
                </div>
                <SlideInlineForm
                    :slider="draft"
                    :languages="languages"
                    :default-locale="defaultLocale"
                    :auto-translate-enabled="autoTranslateEnabled"
                    is-draft
                    @cancel="cancelExpand"
                />
            </div>

            <!-- Existing slides -->
            <div ref="listRef" class="flex flex-col gap-md">
                <div
                    v-for="slider in items"
                    :key="slider.id"
                    class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container transition-colors"
                    :class="{
                        'border-secondary': expandedId === slider.id,
                    }"
                >
                    <div
                        class="flex cursor-pointer items-center justify-between bg-surface-container p-md transition-colors hover:bg-surface-container-high"
                        :class="{
                            'border-b border-outline-variant':
                                expandedId === slider.id,
                        }"
                        @click="toggleExpand(slider.id)"
                    >
                        <div class="flex flex-1 items-center gap-md">
                            <button
                                type="button"
                                class="drag-handle cursor-grab text-outline transition-colors hover:text-primary active:cursor-grabbing"
                                aria-label="Drag to reorder"
                                @click.stop
                            >
                                <IconGripVertical
                                    :size="22"
                                    stroke-width="1.5"
                                />
                            </button>
                            <div
                                class="h-12 w-16 overflow-hidden rounded-md border border-outline-variant bg-surface-dim"
                            >
                                <img
                                    v-if="slider.image_url"
                                    :src="slider.image_url"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div>
                                <h4
                                    class="text-label-lg uppercase tracking-wide text-on-surface transition-colors group-hover:text-primary"
                                >
                                    {{ displayTitle(slider) }}
                                </h4>
                                <span
                                    class="mt-1 inline-block rounded-sm px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                                    :class="
                                        slider.is_active
                                            ? 'bg-primary/15 text-primary'
                                            : 'bg-surface-container-high text-on-surface-variant'
                                    "
                                >
                                    {{
                                        slider.is_active
                                            ? 'Published'
                                            : 'Draft'
                                    }}
                                </span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="rounded-md p-2 text-on-surface-variant transition-colors hover:bg-surface-container-highest hover:text-primary"
                            :class="{
                                'bg-surface-variant text-primary':
                                    expandedId === slider.id,
                            }"
                            @click.stop="toggleExpand(slider.id)"
                        >
                            <IconChevronUp
                                v-if="expandedId === slider.id"
                                :size="18"
                                stroke-width="1.5"
                            />
                            <IconChevronDown
                                v-else
                                :size="18"
                                stroke-width="1.5"
                            />
                        </button>
                    </div>

                    <SlideInlineForm
                        v-if="expandedId === slider.id"
                        :slider="slider"
                        :languages="languages"
                        :default-locale="defaultLocale"
                        :auto-translate-enabled="autoTranslateEnabled"
                        @cancel="cancelExpand"
                        @delete="destroy(slider.id)"
                    />
                </div>
            </div>

            <div
                v-if="!items.length && !draft"
                class="rounded-lg border border-dashed border-outline-variant px-md py-xl text-center text-body-md text-on-surface-variant"
            >
                No slides yet. Click “Add new slide” to create your first hero
                slide.
            </div>
        </div>
    </AdminLayout>
</template>
