<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import IconPicker from '@/Components/Admin/IconPicker.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import { IconPlus, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    service: {
        type: Object,
        default: null,
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

const isEdit = computed(() => Boolean(props.service?.id));

const buildTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        const existing = props.service?.translations?.[language.code] ?? {};
        bag[language.code] = {
            title: existing.title ?? '',
            short_description: existing.short_description ?? '',
            included_items: [...(existing.included_items ?? [''])],
        };
        if (!bag[language.code].included_items.length) {
            bag[language.code].included_items = [''];
        }
    }
    return bag;
};

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);

const form = useForm({
    icon: props.service?.icon ?? 'home',
    order: props.service?.order ?? 0,
    show_on_home: props.service?.show_on_home ?? false,
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
});

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const addItem = (locale) => {
    form.translations[locale].included_items.push('');
};

const removeItem = (locale, index) => {
    form.translations[locale].included_items.splice(index, 1);
    if (!form.translations[locale].included_items.length) {
        form.translations[locale].included_items.push('');
    }
};

const submit = () => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;

    if (isEdit.value) {
        form.put(route('admin.services.update', props.service.id));
        return;
    }

    form.post(route('admin.services.store'));
};
</script>

<template>
    <AdminLayout :title="isEdit ? 'Edit service' : 'New service'">
        <Head :title="isEdit ? 'Edit service' : 'New service'" />

        <div class="mb-xl">
            <Link
                :href="route('admin.services.index')"
                class="mb-sm inline-block text-label-md uppercase tracking-wide text-on-surface-variant hover:text-primary"
            >
                ← Back to services
            </Link>
            <h2 class="text-display-md text-on-surface">
                {{ isEdit ? 'Edit service' : 'New service' }}
            </h2>
        </div>

        <form class="flex max-w-3xl flex-col gap-md" @submit.prevent="submit">
            <section
                class="grid gap-md rounded-lg border border-outline-variant bg-surface-container p-md md:grid-cols-2"
            >
                <IconPicker v-model="form.icon" />
                <div class="flex flex-col gap-md">
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Order</label
                        >
                        <input
                            v-model.number="form.order"
                            type="number"
                            min="0"
                            :class="inputClass"
                        />
                    </div>
                    <label class="flex items-center gap-sm">
                        <input
                            v-model="form.show_on_home"
                            type="checkbox"
                            class="rounded-sm border-outline-variant text-primary focus:ring-primary"
                        />
                        <span
                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                            >Show on homepage</span
                        >
                    </label>
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <TranslationTabs
                    v-model="form.translations"
                    v-model:source-locale="sourceLocale"
                    v-model:auto-translate="autoTranslate"
                    :languages="languages"
                    :auto-translate-enabled="
                        page.props.siteSettings?.auto_translate_enabled ?? false
                    "
                >
                    <template #default="{ locale }">
                        <div class="grid gap-md">
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Title</label
                                >
                                <input
                                    v-model="form.translations[locale].title"
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Short description</label
                                >
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .short_description
                                    "
                                    rows="3"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-sm">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-label-md uppercase tracking-wide"
                                        >Included items</span
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-xs text-label-md uppercase tracking-wide text-primary"
                                        @click="addItem(locale)"
                                    >
                                        <IconPlus
                                            :size="14"
                                            stroke-width="1.5"
                                        />
                                        Add
                                    </button>
                                </div>
                                <div
                                    v-for="(item, index) in form.translations[
                                        locale
                                    ].included_items"
                                    :key="`${locale}-item-${index}`"
                                    class="flex gap-sm"
                                >
                                    <input
                                        v-model="
                                            form.translations[locale]
                                                .included_items[index]
                                        "
                                        type="text"
                                        :class="inputClass"
                                        placeholder="Included item"
                                    />
                                    <button
                                        type="button"
                                        class="shrink-0 text-on-surface-variant hover:text-error"
                                        @click="removeItem(locale, index)"
                                    >
                                        <IconTrash
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </TranslationTabs>
            </section>

            <div class="flex justify-end gap-sm">
                <Link
                    :href="route('admin.services.index')"
                    class="rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ isEdit ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
