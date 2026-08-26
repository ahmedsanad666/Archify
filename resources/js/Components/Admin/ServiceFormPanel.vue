<script setup>
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminDrawerHeader from '@/Components/Admin/AdminDrawerHeader.vue';
import IconPicker from '@/Components/Shared/IconPicker.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import { IconPlus, IconTrash } from '@tabler/icons-vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    service: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:open']);

const page = usePage();
const { t } = useUiTranslations();
const languages = computed(() => page.props.languages ?? []);
const defaultLocale = computed(
    () =>
        languages.value.find((l) => l.is_default)?.code ??
        languages.value[0]?.code ??
        'en',
);

const isEdit = computed(() => Boolean(props.service?.id));
const title = computed(() =>
    isEdit.value ? 'Edit service' : 'Add service',
);

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);

const buildTranslations = (service = null) => {
    const bag = {};
    for (const language of languages.value) {
        const existing = service?.translations?.[language.code] ?? {};
        const items = [...(existing.included_items ?? [''])];
        bag[language.code] = {
            title: existing.title ?? '',
            short_description: existing.short_description ?? '',
            included_items: items.length ? items : [''],
        };
    }
    return bag;
};

const form = useForm({
    icon: 'home',
    order: 0,
    show_on_home: false,
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
});

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const resetForm = () => {
    sourceLocale.value = defaultLocale.value;
    autoTranslate.value = false;
    form.clearErrors();
    form.defaults({
        icon: props.service?.icon ?? 'home',
        order: props.service?.order ?? 0,
        show_on_home: props.service?.show_on_home ?? false,
        source_locale: defaultLocale.value,
        auto_translate: false,
        translations: buildTranslations(props.service),
    });
    form.reset();
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            resetForm();
        }
    },
);

const addItem = (locale) => {
    form.translations[locale].included_items.push('');
};

const removeItem = (locale, index) => {
    form.translations[locale].included_items.splice(index, 1);
    if (!form.translations[locale].included_items.length) {
        form.translations[locale].included_items.push('');
    }
};

const close = () => {
    if (form.processing) {
        return;
    }
    emit('update:open', false);
};

const submit = () => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;

    const options = {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    };

    if (isEdit.value) {
        form.put(route('admin.services.update', props.service.id), options);
        return;
    }

    form.post(route('admin.services.store'), options);
};
</script>

<template>
    <TransitionRoot
        appear
        :show="open"
        as="template"
    >
        <Dialog
            as="div"
            class="relative z-50"
            @close="close"
        >
            <TransitionChild
                as="template"
                enter="duration-200 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-150 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-surface-dim/80 backdrop-blur-sm"
                    aria-hidden="true"
                />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div
                        class="pointer-events-none fixed inset-y-0 end-0 flex max-w-full ps-4"
                    >
                        <TransitionChild
                            as="template"
                            enter="transform transition duration-200 ease-out"
                            enter-from="translate-x-full rtl:-translate-x-full"
                            enter-to="translate-x-0"
                            leave="transform transition duration-150 ease-in"
                            leave-from="translate-x-0"
                            leave-to="translate-x-full rtl:-translate-x-full"
                        >
                            <DialogPanel
                                class="pointer-events-auto flex h-full w-screen max-w-md flex-col border-s border-outline-variant bg-surface-container shadow-none"
                            >
                                <form
                                    class="flex h-full flex-col"
                                    @submit.prevent="submit"
                                >
                                    <AdminDrawerHeader
                                        :title="title"
                                        :disabled="form.processing"
                                        @close="close"
                                    />

                                    <div
                                        class="flex-1 space-y-md overflow-y-auto px-md py-md"
                                    >
                                        <IconPicker v-model="form.icon" />
                                        <p
                                            v-if="form.errors.icon"
                                            class="text-label-md text-error"
                                        >
                                            {{ form.errors.icon }}
                                        </p>

                                        <TranslationTabs
                                            v-model="form.translations"
                                            v-model:source-locale="
                                                sourceLocale
                                            "
                                            v-model:auto-translate="
                                                autoTranslate
                                            "
                                            :languages="languages"
                                            :auto-translate-enabled="
                                                page.props.siteSettings
                                                    ?.auto_translate_enabled ??
                                                false
                                            "
                                        >
                                            <template #default="{ locale }">
                                                <div class="grid gap-md">
                                                    <div
                                                        class="flex flex-col gap-xs"
                                                    >
                                                        <label
                                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                                        >
                                                            Title
                                                        </label>
                                                        <input
                                                            v-model="
                                                                form
                                                                    .translations[
                                                                    locale
                                                                ].title
                                                            "
                                                            type="text"
                                                            :class="inputClass"
                                                        />
                                                        <p
                                                            v-if="
                                                                form.errors[
                                                                    `translations.${locale}.title`
                                                                ]
                                                            "
                                                            class="text-label-md text-error"
                                                        >
                                                            {{
                                                                form.errors[
                                                                    `translations.${locale}.title`
                                                                ]
                                                            }}
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="flex flex-col gap-xs"
                                                    >
                                                        <label
                                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                                        >
                                                            Short description
                                                        </label>
                                                        <textarea
                                                            v-model="
                                                                form
                                                                    .translations[
                                                                    locale
                                                                ]
                                                                    .short_description
                                                            "
                                                            rows="3"
                                                            :class="inputClass"
                                                        />
                                                    </div>
                                                    <div
                                                        class="flex flex-col gap-sm"
                                                    >
                                                        <div
                                                            class="flex items-center justify-between"
                                                        >
                                                            <span
                                                                class="text-label-md uppercase tracking-widest text-on-surface-variant"
                                                            >
                                                                Included items
                                                            </span>
                                                            <button
                                                                type="button"
                                                                class="inline-flex items-center gap-1 text-label-md uppercase tracking-wide text-primary transition-colors hover:text-secondary"
                                                                @click="
                                                                    addItem(
                                                                        locale,
                                                                    )
                                                                "
                                                            >
                                                                <IconPlus
                                                                    :size="16"
                                                                    stroke-width="1.5"
                                                                />
                                                                Add Item
                                                            </button>
                                                        </div>
                                                        <div
                                                            v-for="(
                                                                item, index
                                                            ) in form
                                                                .translations[
                                                                locale
                                                            ].included_items"
                                                            :key="`${locale}-item-${index}`"
                                                            class="group flex items-center gap-sm rounded-sm border border-outline-variant bg-surface-container p-2"
                                                        >
                                                            <input
                                                                v-model="
                                                                    form
                                                                        .translations[
                                                                        locale
                                                                    ]
                                                                        .included_items[
                                                                        index
                                                                    ]
                                                                "
                                                                type="text"
                                                                class="flex-1 border-none bg-transparent p-0 text-body-md text-on-surface focus:ring-0"
                                                                placeholder="Included item"
                                                            />
                                                            <button
                                                                type="button"
                                                                class="shrink-0 text-outline opacity-0 transition-opacity hover:text-error group-hover:opacity-100"
                                                                @click="
                                                                    removeItem(
                                                                        locale,
                                                                        index,
                                                                    )
                                                                "
                                                            >
                                                                <IconTrash
                                                                    :size="16"
                                                                    stroke-width="1.5"
                                                                />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </TranslationTabs>

                                        <div
                                            class="grid gap-md border-t border-outline-variant pt-md sm:grid-cols-2"
                                        >
                                            <div class="flex flex-col gap-xs">
                                                <label
                                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                                >
                                                    Display order
                                                </label>
                                                <input
                                                    v-model.number="form.order"
                                                    type="number"
                                                    min="0"
                                                    :class="inputClass"
                                                />
                                            </div>
                                            <label
                                                class="flex items-center gap-sm self-end pb-sm"
                                            >
                                                <input
                                                    v-model="form.show_on_home"
                                                    type="checkbox"
                                                    class="rounded-sm border-outline-variant text-primary focus:ring-primary"
                                                />
                                                <span
                                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                                >
                                                    Show on homepage
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="flex flex-col-reverse gap-sm border-t border-outline-variant bg-surface-container-low px-md py-sm sm:flex-row sm:justify-end"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-center rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high sm:w-auto"
                                            :disabled="form.processing"
                                            @click="close"
                                        >
                                            {{ t('common.cancel') }}
                                        </button>
                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:opacity-60 sm:w-auto"
                                            :disabled="form.processing"
                                        >
                                            {{
                                                form.processing
                                                    ? t('common.loading')
                                                    : t('common.save')
                                            }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
