<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    IconBrandInstagram,
    IconBrandX,
    IconBrandYoutube,
    IconMail,
} from '@tabler/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import AppInput from '@/Components/Shared/AppInput.vue';
import AppPhoneInput from '@/Components/Shared/AppPhoneInput.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const { t } = useUiTranslations();
const page = usePage();
const languages = computed(() => page.props.languages ?? []);
const defaultLocale = computed(
    () =>
        languages.value.find((l) => l.is_default)?.code ??
        languages.value[0]?.code ??
        'en',
);

const emptyTranslation = () => ({
    name: '',
    slogan: '',
    address: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

const buildTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        bag[language.code] = {
            ...emptyTranslation(),
            ...(props.settings.translations?.[language.code] ?? {}),
        };
    }
    return bag;
};

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);

const form = useForm({
    email: props.settings.email ?? '',
    phone: props.settings.phone ?? '',
    whatsapp: props.settings.whatsapp ?? '',
    map_lat: props.settings.map_lat ?? '',
    map_lng: props.settings.map_lng ?? '',
    instagram_url: props.settings.instagram_url ?? '',
    youtube_url: props.settings.youtube_url ?? '',
    twitter_url: props.settings.twitter_url ?? '',
    google_analytics_id: props.settings.google_analytics_id ?? '',
    gtm_id: props.settings.gtm_id ?? '',
    facebook_pixel_id: props.settings.facebook_pixel_id ?? '',
    google_site_verification: props.settings.google_site_verification ?? '',
    robots_txt: props.settings.robots_txt || '',
    auto_translate_enabled: props.settings.auto_translate_enabled ?? false,
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
    logo: null,
    favicon: null,
    og_image: null,
    remove_logo: false,
    remove_favicon: false,
    remove_og_image: false,
    banner_about: null,
    banner_services: null,
    banner_projects: null,
    banner_blogs: null,
    banner_contact: null,
    remove_banner_about: false,
    remove_banner_services: false,
    remove_banner_projects: false,
    remove_banner_blogs: false,
    remove_banner_contact: false,
});

const bannerHint = computed(() => t('admin.settings.banners_hint'));
const bannerFields = [
    'banner_about',
    'banner_services',
    'banner_projects',
    'banner_blogs',
    'banner_contact',
];

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const submit = () => {
    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;
    form
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(route('admin.settings.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <AdminLayout :title="t('admin.settings.title')">
        <Head :title="t('admin.settings.title')" />

        <div class="mb-xl text-start">
            <h2 class="mb-xs text-display-md text-on-surface">
                {{ t('admin.settings.title') }}
            </h2>
            <p class="text-body-md text-on-surface-variant">
                {{ t('admin.settings.subtitle') }}
            </p>
        </div>

        <form class="flex flex-col gap-md" @submit.prevent="submit">
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-primary/15 px-md py-sm text-start text-body-md text-primary"
            >
                {{ page.props.flash.success }}
            </div>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_contact') }}
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <AppInput
                        v-model="form.email"
                        type="email"
                        :label="t('admin.settings.email')"
                        autocomplete="email"
                        :leading-icon="IconMail"
                        :error="form.errors.email"
                    />
                    <AppPhoneInput
                        v-model="form.phone"
                        :label="t('admin.settings.phone')"
                        :error="form.errors.phone"
                    />
                    <AppPhoneInput
                        v-model="form.whatsapp"
                        :label="t('admin.settings.whatsapp')"
                        :error="form.errors.whatsapp"
                    />
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_social') }}
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <AppInput
                        v-model="form.instagram_url"
                        type="url"
                        :label="t('admin.settings.instagram')"
                        :placeholder="t('admin.settings.instagram_placeholder')"
                        :leading-icon="IconBrandInstagram"
                        :error="form.errors.instagram_url"
                    />
                    <AppInput
                        v-model="form.youtube_url"
                        type="url"
                        :label="t('admin.settings.youtube')"
                        :placeholder="t('admin.settings.youtube_placeholder')"
                        :leading-icon="IconBrandYoutube"
                        :error="form.errors.youtube_url"
                    />
                    <AppInput
                        v-model="form.twitter_url"
                        type="url"
                        :label="t('admin.settings.twitter')"
                        :placeholder="t('admin.settings.twitter_placeholder')"
                        :leading-icon="IconBrandX"
                        :error="form.errors.twitter_url"
                    />
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_media') }}
                </h3>
                <div class="grid gap-md md:grid-cols-3">
                    <MediaUploader
                        v-model="form.logo"
                        v-model:remove-existing="form.remove_logo"
                        :label="t('admin.settings.logo')"
                        :existing-url="settings.media?.logo"
                    />
                    <MediaUploader
                        v-model="form.favicon"
                        v-model:remove-existing="form.remove_favicon"
                        :label="t('admin.settings.favicon')"
                        :existing-url="settings.media?.favicon"
                    />
                    <MediaUploader
                        v-model="form.og_image"
                        v-model:remove-existing="form.remove_og_image"
                        :label="t('admin.settings.og_image')"
                        :existing-url="settings.media?.og_image"
                    />
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-xs text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_banners') }}
                </h3>
                <p class="mb-md text-start text-body-md text-on-surface-variant">
                    {{ t('admin.settings.banners_intro') }}
                </p>
                <div class="grid gap-md sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="field in bannerFields"
                        :key="field"
                    >
                        <MediaUploader
                            v-model="form[field]"
                            v-model:remove-existing="form[`remove_${field}`]"
                            :label="t(`admin.settings.${field}`)"
                            :hint="bannerHint"
                            accept="image/jpeg,image/png,image/webp,image/jpg"
                            :existing-url="settings.media?.[field]"
                        />
                        <p
                            v-if="form.errors[field]"
                            class="mt-xs text-start text-label-md text-error"
                        >
                            {{ form.errors[field] }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_localized') }}
                </h3>
                <TranslationTabs
                    v-model="form.translations"
                    v-model:source-locale="sourceLocale"
                    v-model:auto-translate="autoTranslate"
                    :languages="languages"
                    :auto-translate-enabled="form.auto_translate_enabled"
                >
                    <template #default="{ locale }">
                        <div class="grid gap-md">
                            <div class="grid gap-md md:grid-cols-2">
                                <AppInput
                                    v-model="form.translations[locale].name"
                                    :label="t('admin.settings.site_name')"
                                    :error="
                                        form.errors[
                                            `translations.${locale}.name`
                                        ]
                                    "
                                />
                                <AppInput
                                    v-model="form.translations[locale].slogan"
                                    :label="t('admin.settings.slogan')"
                                    :error="
                                        form.errors[
                                            `translations.${locale}.slogan`
                                        ]
                                    "
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    {{ t('admin.settings.address') }}
                                </label>
                                <textarea
                                    v-model="form.translations[locale].address"
                                    rows="2"
                                    :class="inputClass"
                                />
                            </div>
                            <AppInput
                                v-model="form.translations[locale].meta_title"
                                :label="t('admin.settings.meta_title')"
                                :error="
                                    form.errors[
                                        `translations.${locale}.meta_title`
                                    ]
                                "
                            />
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    {{ t('admin.settings.meta_description') }}
                                </label>
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .meta_description
                                    "
                                    rows="3"
                                    :class="inputClass"
                                />
                            </div>
                            <AppInput
                                v-model="
                                    form.translations[locale].meta_keywords
                                "
                                :label="t('admin.settings.meta_keywords')"
                                :error="
                                    form.errors[
                                        `translations.${locale}.meta_keywords`
                                    ]
                                "
                            />
                        </div>
                    </template>
                </TranslationTabs>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-start text-label-lg uppercase tracking-wide text-primary"
                >
                    {{ t('admin.settings.section_analytics') }}
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <AppInput
                        v-model="form.google_analytics_id"
                        :label="t('admin.settings.ga_id')"
                        :error="form.errors.google_analytics_id"
                    />
                    <AppInput
                        v-model="form.gtm_id"
                        :label="t('admin.settings.gtm_id')"
                        :error="form.errors.gtm_id"
                    />
                    <AppInput
                        v-model="form.facebook_pixel_id"
                        :label="t('admin.settings.facebook_pixel')"
                        :error="form.errors.facebook_pixel_id"
                    />
                    <AppInput
                        v-model="form.google_site_verification"
                        :label="t('admin.settings.search_console')"
                        :error="form.errors.google_site_verification"
                    />
                    <div class="flex flex-col gap-xs md:col-span-2">
                        <label
                            class="text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                        >
                            {{ t('admin.settings.robots_txt') }}
                        </label>
                        <textarea
                            v-model="form.robots_txt"
                            rows="4"
                            :class="inputClass"
                        />
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ t('admin.settings.save') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
