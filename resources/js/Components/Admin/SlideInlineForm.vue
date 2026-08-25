<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import { IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    slider: {
        type: Object,
        required: true,
    },
    languages: {
        type: Array,
        required: true,
    },
    defaultLocale: {
        type: String,
        required: true,
    },
    autoTranslateEnabled: {
        type: Boolean,
        default: false,
    },
    isDraft: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['cancel', 'delete']);

const buildTranslations = () => {
    const bag = {};
    for (const language of props.languages) {
        bag[language.code] = {
            title: '',
            description: '',
            ...(props.slider.translations?.[language.code] ?? {}),
        };
    }
    return bag;
};

const sourceLocale = ref(props.defaultLocale);
const autoTranslate = ref(false);

const form = useForm({
    is_active: props.slider.is_active ?? true,
    source_locale: props.defaultLocale,
    auto_translate: false,
    translations: buildTranslations(),
    image: null,
    remove_image: false,
});

watch(
    () => props.slider,
    () => {
        form.is_active = props.slider.is_active ?? true;
        form.translations = buildTranslations();
        form.image = null;
        form.remove_image = false;
        form.clearErrors();
    },
    { deep: true },
);

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const submit = () => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;

    if (props.isDraft) {
        form.post(route('admin.sliders.store'), {
            forceFormData: true,
            preserveScroll: true,
        });
        return;
    }

    form
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(route('admin.sliders.update', props.slider.id), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <form class="flex flex-col gap-lg bg-surface p-lg" @submit.prevent="submit">
        <div>
            <h5 class="mb-sm text-label-lg uppercase tracking-wide text-on-surface">
                Slide Background
            </h5>
            <MediaUploader
                v-model="form.image"
                v-model:remove-existing="form.remove_image"
                label=""
                :existing-url="slider.image_url"
                hint="Recommended size: 1920×1080px (JPEG or WEBP)"
            />
        </div>

        <div>
            <h5
                class="mb-md border-b border-outline-variant pb-sm text-label-lg uppercase tracking-wide text-on-surface"
            >
                Slide Content
            </h5>
            <TranslationTabs
                v-model="form.translations"
                v-model:source-locale="sourceLocale"
                v-model:auto-translate="autoTranslate"
                :languages="languages"
                :auto-translate-enabled="autoTranslateEnabled"
            >
                <template #default="{ locale }">
                    <div class="grid gap-md">
                        <div class="flex flex-col gap-xs">
                            <label class="text-label-md uppercase tracking-wide text-on-surface-variant">
                                Title
                            </label>
                            <input
                                v-model="form.translations[locale].title"
                                type="text"
                                :class="inputClass"
                            />
                        </div>
                        <div class="flex flex-col gap-xs">
                            <label class="text-label-md uppercase tracking-wide text-on-surface-variant">
                                Description
                            </label>
                            <textarea
                                v-model="form.translations[locale].description"
                                rows="3"
                                :class="inputClass"
                            />
                        </div>
                    </div>
                </template>
            </TranslationTabs>
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-md border-t border-outline-variant pt-md"
        >
            <label class="flex items-center gap-sm">
                <span class="text-label-md uppercase tracking-wide text-on-surface">
                    Active
                </span>
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded-sm border-outline-variant text-primary focus:ring-primary"
                />
            </label>

            <div class="flex flex-wrap items-center gap-sm">
                <button
                    v-if="!isDraft"
                    type="button"
                    class="inline-flex items-center gap-xs rounded-md px-sm py-sm text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:text-error"
                    @click="emit('delete')"
                >
                    <IconTrash :size="16" stroke-width="1.5" />
                    Delete
                </button>
                <button
                    type="button"
                    class="rounded-md border border-outline-variant px-md py-sm text-label-md uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high"
                    @click="emit('cancel')"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-md bg-primary px-md py-sm text-label-md uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Save slide
                </button>
            </div>
        </div>
    </form>
</template>
