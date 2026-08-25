<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';

const props = defineProps({
    settings: {
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
    robots_txt: props.settings.robots_txt ?? '',
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
        .post(route('admin.settings.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <AdminLayout title="Settings">
        <Head title="Settings" />

        <div class="mb-xl">
            <h2 class="mb-xs text-display-md text-on-surface">Site Settings</h2>
            <p class="text-body-md text-on-surface-variant">
                Brand contact, SEO, analytics, and auto-translate.
            </p>
        </div>

        <form class="flex flex-col gap-md" @submit.prevent="submit">
            <div
                v-if="page.props.flash?.success"
                class="rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
            >
                {{ page.props.flash.success }}
            </div>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-label-lg uppercase tracking-wide text-primary"
                >
                    Contact
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Email</label
                        >
                        <input v-model="form.email" type="email" :class="inputClass" />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Phone</label
                        >
                        <input v-model="form.phone" type="text" :class="inputClass" />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >WhatsApp</label
                        >
                        <input
                            v-model="form.whatsapp"
                            type="text"
                            :class="inputClass"
                        />
                    </div>
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-label-lg uppercase tracking-wide text-primary"
                >
                    Social
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Instagram</label
                        >
                        <input
                            v-model="form.instagram_url"
                            type="url"
                            :class="inputClass"
                        />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >YouTube</label
                        >
                        <input
                            v-model="form.youtube_url"
                            type="url"
                            :class="inputClass"
                        />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >X / Twitter</label
                        >
                        <input
                            v-model="form.twitter_url"
                            type="url"
                            :class="inputClass"
                        />
                    </div>
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-label-lg uppercase tracking-wide text-primary"
                >
                    Brand media
                </h3>
                <div class="grid gap-md md:grid-cols-3">
                    <MediaUploader
                        v-model="form.logo"
                        v-model:remove-existing="form.remove_logo"
                        label="Logo"
                        :existing-url="settings.media?.logo"
                    />
                    <MediaUploader
                        v-model="form.favicon"
                        v-model:remove-existing="form.remove_favicon"
                        label="Favicon"
                        :existing-url="settings.media?.favicon"
                    />
                    <MediaUploader
                        v-model="form.og_image"
                        v-model:remove-existing="form.remove_og_image"
                        label="OG Image"
                        :existing-url="settings.media?.og_image"
                    />
                </div>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-label-lg uppercase tracking-wide text-primary"
                >
                    Localized content & SEO
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
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Site name</label
                                >
                                <input
                                    v-model="form.translations[locale].name"
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Slogan</label
                                >
                                <input
                                    v-model="form.translations[locale].slogan"
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Address</label
                                >
                                <textarea
                                    v-model="form.translations[locale].address"
                                    rows="2"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Meta title</label
                                >
                                <input
                                    v-model="
                                        form.translations[locale].meta_title
                                    "
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Meta description</label
                                >
                                <textarea
                                    v-model="
                                        form.translations[locale]
                                            .meta_description
                                    "
                                    rows="3"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide"
                                    >Meta keywords</label
                                >
                                <input
                                    v-model="
                                        form.translations[locale].meta_keywords
                                    "
                                    type="text"
                                    :class="inputClass"
                                />
                            </div>
                        </div>
                    </template>
                </TranslationTabs>
            </section>

            <section
                class="rounded-lg border border-outline-variant bg-surface-container p-md"
            >
                <h3
                    class="mb-md text-label-lg uppercase tracking-wide text-primary"
                >
                    Analytics & robots
                </h3>
                <div class="grid gap-md md:grid-cols-2">
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Google Analytics ID</label
                        >
                        <input
                            v-model="form.google_analytics_id"
                            type="text"
                            :class="inputClass"
                        />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >GTM ID</label
                        >
                        <input v-model="form.gtm_id" type="text" :class="inputClass" />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Facebook Pixel</label
                        >
                        <input
                            v-model="form.facebook_pixel_id"
                            type="text"
                            :class="inputClass"
                        />
                    </div>
                    <div class="flex flex-col gap-xs">
                        <label class="text-label-md uppercase tracking-wide"
                            >Search Console verification</label
                        >
                        <input
                            v-model="form.google_site_verification"
                            type="text"
                            :class="inputClass"
                        />
                    </div>
                    <div class="flex flex-col gap-xs md:col-span-2">
                        <label class="text-label-md uppercase tracking-wide"
                            >robots.txt</label
                        >
                        <textarea
                            v-model="form.robots_txt"
                            rows="4"
                            :class="inputClass"
                        />
                    </div>
                    <label class="flex items-center gap-sm md:col-span-2">
                        <input
                            v-model="form.auto_translate_enabled"
                            type="checkbox"
                            class="rounded-sm border-outline-variant text-primary focus:ring-primary"
                        />
                        <span
                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                        >
                            Enable auto-translate (DeepL) globally
                        </span>
                    </label>
                </div>
            </section>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Save settings
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
