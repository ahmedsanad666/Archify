<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    locales: {
        type: Array,
        default: () => [],
    },
    activeLocale: {
        type: String,
        required: true,
    },
    translations: {
        type: Object,
        default: () => ({}),
    },
    groups: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useUiTranslations();

const search = ref('');
const groupFilter = ref(null);

const form = useForm({
    locale: props.activeLocale,
    translations: { ...props.translations },
});

watch(
    () => [props.activeLocale, props.translations],
    () => {
        form.locale = props.activeLocale;
        form.translations = { ...props.translations };
        form.clearErrors();
        search.value = '';
        groupFilter.value = null;
    },
);

const groupChips = computed(() => [
    { value: null, label: t('admin.translations.group_all') },
    ...props.groups.map((group) => ({
        value: group,
        label: group,
    })),
]);

const entries = computed(() => {
    const q = search.value.trim().toLowerCase();
    const group = groupFilter.value;

    return Object.entries(form.translations)
        .filter(([key, value]) => {
            if (group && !key.startsWith(`${group}.`)) {
                return false;
            }
            if (!q) {
                return true;
            }
            return (
                key.toLowerCase().includes(q)
                || String(value ?? '').toLowerCase().includes(q)
            );
        })
        .sort(([a], [b]) => a.localeCompare(b));
});

const setLocale = (code) => {
    if (code === props.activeLocale) {
        return;
    }
    router.get(
        route('admin.translations.index'),
        { locale: code },
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
};

const chipClass = (active) =>
    active
        ? 'bg-primary text-on-primary'
        : 'border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface';

const submit = () => {
    form.put(route('admin.translations.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout :title="t('admin.translations.title')">
        <Head :title="t('admin.translations.title')" />

        <div class="mb-xl flex flex-wrap items-end justify-between gap-md">
            <div>
                <h1 class="mb-xs text-display-md text-on-surface">
                    {{ t('admin.translations.title') }}
                </h1>
                <p class="text-body-md text-on-surface-variant">
                    {{ t('admin.translations.subtitle') }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing"
                @click="submit"
            >
                {{
                    form.processing
                        ? t('common.loading')
                        : t('admin.translations.save')
                }}
            </button>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div
            v-if="Object.keys(form.errors).length"
            class="mb-md rounded-md bg-error/15 px-md py-sm text-body-md text-error"
        >
            {{ t('admin.translations.form_errors') }}
        </div>

        <div class="mb-md flex flex-wrap gap-xs">
            <button
                v-for="locale in locales"
                :key="locale.code"
                type="button"
                class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors"
                :class="chipClass(locale.code === activeLocale)"
                @click="setLocale(locale.code)"
            >
                {{ locale.code }}
            </button>
        </div>

        <div
            class="mb-md flex flex-col gap-md rounded-lg border border-outline-variant bg-surface-container p-md lg:flex-row lg:items-end"
        >
            <div class="min-w-0 flex-1">
                <label
                    class="mb-xs block text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    {{ t('common.search') }}
                </label>
                <input
                    v-model="search"
                    type="search"
                    :placeholder="t('admin.translations.search_placeholder')"
                    class="w-full rounded-md border border-outline bg-surface-container px-sm py-1.5 text-body-md text-on-surface outline-none transition-colors placeholder:text-on-surface-variant focus:border-primary focus:ring-1 focus:ring-primary/20"
                />
            </div>
            <div class="flex flex-wrap gap-xs">
                <button
                    v-for="chip in groupChips"
                    :key="String(chip.value)"
                    type="button"
                    class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors"
                    :class="chipClass(groupFilter === chip.value)"
                    @click="groupFilter = chip.value"
                >
                    {{ chip.label }}
                </button>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div
                v-if="!entries.length"
                class="px-md py-xl text-center text-body-md text-on-surface-variant"
            >
                {{ t('admin.translations.empty') }}
            </div>

            <div
                v-else
                class="max-h-[70vh] overflow-y-auto"
            >
                <table class="w-full border-collapse text-start">
                    <thead
                        class="sticky top-0 border-b border-outline-variant bg-surface-container-low"
                    >
                        <tr>
                            <th
                                scope="col"
                                class="w-2/5 px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.translations.col_key') }}
                            </th>
                            <th
                                scope="col"
                                class="px-md py-sm text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                {{ t('admin.translations.col_value') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="[key] in entries"
                            :key="key"
                            class="border-b border-outline-variant last:border-b-0 hover:bg-surface-container-high"
                        >
                            <td
                                class="px-md py-sm align-middle font-mono text-[13px] text-on-surface-variant"
                            >
                                {{ key }}
                            </td>
                            <td class="px-md py-sm align-middle">
                                <input
                                    v-model="form.translations[key]"
                                    type="text"
                                    class="w-full rounded-md border border-outline bg-surface-container px-sm py-1.5 text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
