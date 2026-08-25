<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AboutCoreValuesTab from '@/Components/Admin/About/AboutCoreValuesTab.vue';
import AboutStatisticsTab from '@/Components/Admin/About/AboutStatisticsTab.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const TAB_KEYS = ['story', 'vision', 'mission', 'statistics', 'core_values'];

const props = defineProps({
    about: {
        type: Object,
        required: true,
    },
    statistics: {
        type: Array,
        default: () => [],
    },
    coreValues: {
        type: Array,
        default: () => [],
    },
    tab: {
        type: String,
        default: 'story',
    },
});

const page = usePage();
const { t } = useUiTranslations();
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

const tabIndex = (key) => {
    const index = TAB_KEYS.indexOf(key);
    return index >= 0 ? index : 0;
};

const selectedIndex = ref(tabIndex(props.tab));

watch(
    () => props.tab,
    (value) => {
        selectedIndex.value = tabIndex(value);
    },
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

const tabClass = (selected) =>
    selected
        ? 'border-b-2 border-secondary pb-3 text-secondary'
        : 'pb-3 text-on-surface-variant transition-colors hover:text-on-surface';

const submitAbout = (activeTab) => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;

    form
        .transform((data) => ({
            ...data,
            active_tab: activeTab,
            auto_translate: data.auto_translate ? '1' : '0',
            remove_story_image: data.remove_story_image ? '1' : '0',
            _method: 'put',
        }))
        .post(route('admin.about.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <AdminLayout :title="t('admin.about.title')">
        <Head :title="t('admin.about.title')" />

        <div class="mb-xl">
            <h2 class="mb-xs text-display-md text-on-surface">
                {{ t('admin.about.title') }}
            </h2>
            <p class="text-body-md text-on-surface-variant">
                {{ t('admin.about.subtitle') }}
            </p>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <TabGroup
            :selected-index="selectedIndex"
            @change="selectedIndex = $event"
        >
            <TabList
                class="mb-xl flex flex-wrap gap-x-lg gap-y-2 border-b border-outline-variant font-label-lg uppercase tracking-wide"
            >
                <Tab v-slot="{ selected }" as="template">
                    <button
                        type="button"
                        :class="tabClass(selected)"
                    >
                        {{ t('admin.about.tab_story') }}
                    </button>
                </Tab>
                <Tab v-slot="{ selected }" as="template">
                    <button
                        type="button"
                        :class="tabClass(selected)"
                    >
                        {{ t('admin.about.tab_vision') }}
                    </button>
                </Tab>
                <Tab v-slot="{ selected }" as="template">
                    <button
                        type="button"
                        :class="tabClass(selected)"
                    >
                        {{ t('admin.about.tab_mission') }}
                    </button>
                </Tab>
                <Tab v-slot="{ selected }" as="template">
                    <button
                        type="button"
                        :class="tabClass(selected)"
                    >
                        {{ t('admin.about.tab_statistics') }}
                    </button>
                </Tab>
                <Tab v-slot="{ selected }" as="template">
                    <button
                        type="button"
                        :class="tabClass(selected)"
                    >
                        {{ t('admin.about.tab_core_values') }}
                    </button>
                </Tab>
            </TabList>

            <TabPanels>
                <!-- Our Story -->
                <TabPanel>
                    <form
                        class="grid grid-cols-1 gap-gutter lg:grid-cols-12"
                        @submit.prevent="submitAbout('story')"
                    >
                        <section
                            class="flex flex-col gap-md rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-8"
                        >
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
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.story_title',
                                                    )
                                                }}
                                            </label>
                                            <input
                                                v-model="
                                                    form.translations[locale]
                                                        .story_title
                                                "
                                                type="text"
                                                :class="inputClass"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.story_description',
                                                    )
                                                }}
                                            </label>
                                            <textarea
                                                v-model="
                                                    form.translations[locale]
                                                        .story_description
                                                "
                                                rows="6"
                                                :class="inputClass"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </TranslationTabs>

                            <div class="flex justify-end border-t border-outline-variant pt-md">
                                <button
                                    type="submit"
                                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ t('admin.about.save_story') }}
                                </button>
                            </div>
                        </section>

                        <section
                            class="rounded-lg border border-outline-variant bg-surface-container p-md lg:col-span-4"
                        >
                            <h3
                                class="mb-sm text-label-lg uppercase tracking-wide text-on-surface"
                            >
                                {{ t('admin.about.story_image') }}
                            </h3>
                            <MediaUploader
                                v-model="form.story_image"
                                v-model:remove-existing="form.remove_story_image"
                                label=""
                                :existing-url="about.story_image_url"
                            />
                        </section>
                    </form>
                </TabPanel>

                <!-- Vision -->
                <TabPanel>
                    <form @submit.prevent="submitAbout('vision')">
                        <section
                            class="max-w-3xl rounded-lg border border-outline-variant bg-surface-container p-md"
                        >
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
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.vision_title',
                                                    )
                                                }}
                                            </label>
                                            <input
                                                v-model="
                                                    form.translations[locale]
                                                        .vision_title
                                                "
                                                type="text"
                                                :class="inputClass"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.vision_description',
                                                    )
                                                }}
                                            </label>
                                            <textarea
                                                v-model="
                                                    form.translations[locale]
                                                        .vision_description
                                                "
                                                rows="5"
                                                :class="inputClass"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </TranslationTabs>

                            <div class="mt-md flex justify-end border-t border-outline-variant pt-md">
                                <button
                                    type="submit"
                                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ t('admin.about.save_vision') }}
                                </button>
                            </div>
                        </section>
                    </form>
                </TabPanel>

                <!-- Mission -->
                <TabPanel>
                    <form @submit.prevent="submitAbout('mission')">
                        <section
                            class="max-w-3xl rounded-lg border border-outline-variant bg-surface-container p-md"
                        >
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
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.mission_title',
                                                    )
                                                }}
                                            </label>
                                            <input
                                                v-model="
                                                    form.translations[locale]
                                                        .mission_title
                                                "
                                                type="text"
                                                :class="inputClass"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.about.mission_description',
                                                    )
                                                }}
                                            </label>
                                            <textarea
                                                v-model="
                                                    form.translations[locale]
                                                        .mission_description
                                                "
                                                rows="5"
                                                :class="inputClass"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </TranslationTabs>

                            <div class="mt-md flex justify-end border-t border-outline-variant pt-md">
                                <button
                                    type="submit"
                                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ t('admin.about.save_mission') }}
                                </button>
                            </div>
                        </section>
                    </form>
                </TabPanel>

                <!-- Statistics -->
                <TabPanel>
                    <AboutStatisticsTab :statistics="statistics" />
                </TabPanel>

                <!-- Core Values -->
                <TabPanel>
                    <AboutCoreValuesTab :core-values="coreValues" />
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </AdminLayout>
</template>
