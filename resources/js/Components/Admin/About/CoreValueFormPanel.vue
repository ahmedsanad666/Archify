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
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    coreValue: {
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

const isEdit = computed(() => Boolean(props.coreValue?.id));
const title = computed(() =>
    isEdit.value ? t('admin.core_values.edit') : t('admin.core_values.add'),
);

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);

const buildTranslations = (coreValue = null) => {
    const bag = {};
    for (const language of languages.value) {
        const existing = coreValue?.translations?.[language.code] ?? {};
        bag[language.code] = {
            title: existing.title ?? '',
            short_description: existing.short_description ?? '',
        };
    }
    return bag;
};

const form = useForm({
    icon: 'compass',
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
        icon: props.coreValue?.icon ?? 'compass',
        source_locale: defaultLocale.value,
        auto_translate: false,
        translations: buildTranslations(props.coreValue),
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
        form.put(route('admin.core-values.update', props.coreValue.id), options);
        return;
    }

    form.post(route('admin.core-values.store'), options);
};
</script>

<template>
    <TransitionRoot appear :show="open" as="template">
        <Dialog as="div" class="relative z-50" @close="close">
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
                    class="fixed inset-0 bg-surface/70 backdrop-blur-sm"
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

                                        <TranslationTabs
                                            v-model="form.translations"
                                            v-model:source-locale="sourceLocale"
                                            v-model:auto-translate="autoTranslate"
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
                                                            {{
                                                                t(
                                                                    'admin.core_values.title',
                                                                )
                                                            }}
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
                                                    </div>
                                                    <div
                                                        class="flex flex-col gap-xs"
                                                    >
                                                        <label
                                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                                        >
                                                            {{
                                                                t(
                                                                    'admin.core_values.description',
                                                                )
                                                            }}
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
                                                </div>
                                            </template>
                                        </TranslationTabs>
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
                                            {{ t('common.save') }}
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
