<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';

const props = defineProps({
    about: {
        type: Object,
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

const emptyTranslation = () => ({
    story_title: '',
    story_description: '',
    vision_title: '',
    vision_description: '',
    mission_title: '',
    mission_description: '',
});

const buildTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        bag[language.code] = {
            ...emptyTranslation(),
            ...(props.about.translations?.[language.code] ?? {}),
        };
    }
    return bag;
};

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);

const form = useForm({
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
    story_image: null,
    remove_story_image: false,
});

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const submit = () => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;
    form
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(route('admin.about.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <AdminLayout title="About">
        <Head title="About" />

        <div class="mb-xl">
            <h2 class="mb-xs text-display-md text-on-surface">About page</h2>
            <p class="text-body-md text-on-surface-variant">
                Story, vision, and mission content.
            </p>
        </div>

        <form class="flex max-w-3xl flex-col gap-md" @submit.prevent="submit">
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
            >
                {{ page.props.flash.success }}
            </div>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <MediaUploader
                    v-model="form.story_image"
                    v-model:remove-existing="form.remove_story_image"
                    label="Story image"
                    :existing-url="about.story_image_url"
                />
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
                                    >Story title</label
                                >
                                <input
                                    v-model="
                                        form.translations[locale].story_title
                                    "
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Story description</label
                                >
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .story_description
                                    "
                                    rows="5"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Vision title</label
                                >
                                <input
                                    v-model="
                                        form.translations[locale].vision_title
                                    "
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Vision description</label
                                >
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .vision_description
                                    "
                                    rows="4"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Mission title</label
                                >
                                <input
                                    v-model="
                                        form.translations[locale].mission_title
                                    "
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Mission description</label
                                >
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .mission_description
                                    "
                                    rows="4"
                                    :class="inputClass"
                                />
                            </div>
                        </div>
                    </template>
                </TranslationTabs>
            </section>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Save about page
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
