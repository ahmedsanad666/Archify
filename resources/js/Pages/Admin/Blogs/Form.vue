<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploader from '@/Components/Admin/MediaUploader.vue';
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    blog: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
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

const isEdit = computed(() => Boolean(props.blog?.id));
const pageTitle = computed(() =>
    isEdit.value ? t('admin.blogs.edit') : t('admin.blogs.add'),
);

const categoryOptions = computed(() =>
    props.categories.map((category) => ({
        value: category.id,
        label: category.name || `Category #${category.id}`,
    })),
);

const buildTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        const existing = props.blog?.translations?.[language.code] ?? {};
        bag[language.code] = {
            title: existing.title ?? '',
            slug: existing.slug ?? '',
            content: existing.content ?? '',
            meta_title: existing.meta_title ?? '',
            meta_description: existing.meta_description ?? '',
            meta_keywords: existing.meta_keywords ?? '',
            translation_status: existing.translation_status ?? null,
        };
    }
    return bag;
};

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);
const uploadError = ref('');

const IMAGE_MAX_BYTES = 5 * 1024 * 1024;

const form = useForm({
    blog_category_id: props.blog?.blog_category_id ?? '',
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
    thumbnail: null,
    cover: null,
    remove_thumbnail: false,
    remove_cover: false,
});

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const slugify = (value) =>
    value
        .toString()
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\u0600-\u06FF]+/g, '-')
        .replace(/^-+|-+$/g, '');

const onTitleInput = (locale, event) => {
    const title = event.target.value;
    form.translations[locale].title = title;
    form.translations[locale].slug = slugify(title);
};

const validateUploadSizes = () => {
    const images = [form.thumbnail, form.cover].filter(
        (file) => file instanceof File,
    );

    for (const file of images) {
        if (file.size > IMAGE_MAX_BYTES) {
            return `"${file.name}" exceeds the 5MB image limit.`;
        }
    }

    return '';
};

const submit = () => {
    uploadError.value = validateUploadSizes();
    if (uploadError.value) {
        return;
    }

    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;

    const options = { forceFormData: true };

    if (isEdit.value) {
        form
            .transform((data) => ({
                ...data,
                _method: 'put',
            }))
            .post(route('admin.blogs.update', props.blog.id), options);
        return;
    }

    form.post(route('admin.blogs.store'), options);
};
</script>

<template>
    <AdminLayout :title="pageTitle">
        <Head :title="pageTitle" />

        <form @submit.prevent="submit">
            <div
                class="mb-xl flex flex-col justify-between gap-md md:flex-row md:items-end"
            >
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="mb-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                    >
                        <ol class="inline-flex flex-wrap items-center gap-xs">
                            <li>
                                <Link
                                    :href="route('admin.blogs.index')"
                                    class="transition-colors hover:text-on-surface"
                                >
                                    {{ t('admin.blogs.title') }}
                                </Link>
                            </li>
                            <li aria-hidden="true" class="text-outline">/</li>
                            <li class="text-on-surface" aria-current="page">
                                {{ pageTitle }}
                            </li>
                        </ol>
                    </nav>
                    <h1
                        class="text-[28px] font-semibold tracking-[-0.01em] text-on-surface md:text-display-md md:tracking-[-0.03em]"
                    >
                        {{ pageTitle }}
                    </h1>
                </div>

                <div class="flex flex-wrap gap-sm">
                    <Link
                        :href="route('admin.blogs.index')"
                        class="inline-flex items-center justify-center rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high"
                    >
                        {{ t('common.cancel') }}
                    </Link>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('common.loading')
                                : t('common.save')
                        }}
                    </button>
                </div>
            </div>

            <div
                v-if="uploadError"
                class="mb-md rounded-md bg-error/15 px-md py-sm text-body-md text-error"
            >
                {{ uploadError }}
            </div>

            <div
                v-if="Object.keys(form.errors).length"
                class="mb-md rounded-md bg-error/15 px-md py-sm text-body-md text-error"
            >
                {{ t('admin.blogs.fix_errors') }}
            </div>

            <div class="grid gap-md lg:grid-cols-12">
                <div class="flex flex-col gap-md lg:col-span-8">
                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <TranslationTabs
                            v-model="form.translations"
                            v-model:source-locale="sourceLocale"
                            v-model:auto-translate="autoTranslate"
                            :languages="languages"
                            :auto-translate-enabled="
                                page.props.siteSettings
                                    ?.auto_translate_enabled ?? false
                            "
                        >
                            <template #default="{ locale }">
                                <div class="grid gap-md">
                                    <div
                                        class="flex flex-col gap-md sm:flex-row"
                                    >
                                        <div
                                            class="flex w-full flex-col gap-xs sm:w-1/2"
                                        >
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{ t('admin.blogs.field_title') }}
                                            </label>
                                            <input
                                                :value="
                                                    form.translations[locale]
                                                        .title
                                                "
                                                type="text"
                                                :class="inputClass"
                                                :placeholder="
                                                    t(
                                                        'admin.blogs.title_placeholder',
                                                    )
                                                "
                                                @input="
                                                    onTitleInput(
                                                        locale,
                                                        $event,
                                                    )
                                                "
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
                                            class="flex w-full flex-col gap-xs sm:w-1/2"
                                        >
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{ t('admin.blogs.field_slug') }}
                                            </label>
                                            <input
                                                :value="
                                                    form.translations[locale]
                                                        .slug
                                                "
                                                type="text"
                                                disabled
                                                class="w-full cursor-not-allowed rounded-md border border-outline bg-surface-container-low px-sm py-sm font-mono text-body-md text-on-surface-variant opacity-80 outline-none"
                                            />
                                            <p
                                                class="text-label-md text-on-surface-variant/70"
                                            >
                                                {{ t('admin.blogs.slug_hint') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-xs">
                                        <label
                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                        >
                                            {{ t('admin.blogs.field_content') }}
                                        </label>
                                        <RichTextEditor
                                            :key="locale"
                                            v-model="
                                                form.translations[locale]
                                                    .content
                                            "
                                        />
                                        <p
                                            v-if="
                                                form.errors[
                                                    `translations.${locale}.content`
                                                ]
                                            "
                                            class="text-label-md text-error"
                                        >
                                            {{
                                                form.errors[
                                                    `translations.${locale}.content`
                                                ]
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="grid gap-md border-t border-outline-variant pt-md sm:grid-cols-2"
                                    >
                                        <p
                                            class="text-label-md uppercase tracking-wide text-on-surface sm:col-span-2"
                                        >
                                            {{ t('admin.blogs.seo') }}
                                        </p>
                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.blogs.meta_title',
                                                    )
                                                }}
                                            </label>
                                            <input
                                                v-model="
                                                    form.translations[locale]
                                                        .meta_title
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
                                                        'admin.blogs.meta_keywords',
                                                    )
                                                }}
                                            </label>
                                            <input
                                                v-model="
                                                    form.translations[locale]
                                                        .meta_keywords
                                                "
                                                type="text"
                                                :class="inputClass"
                                            />
                                        </div>
                                        <div
                                            class="flex flex-col gap-xs sm:col-span-2"
                                        >
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{
                                                    t(
                                                        'admin.blogs.meta_description',
                                                    )
                                                }}
                                            </label>
                                            <textarea
                                                v-model="
                                                    form.translations[locale]
                                                        .meta_description
                                                "
                                                rows="2"
                                                :class="inputClass"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </TranslationTabs>
                    </section>
                </div>

                <div
                    class="flex flex-col gap-md lg:col-span-4 lg:sticky lg:top-md lg:self-start"
                >
                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <MediaUploader
                            v-model="form.thumbnail"
                            v-model:remove-existing="form.remove_thumbnail"
                            :label="t('admin.blogs.thumbnail')"
                            :existing-url="blog?.thumbnail_url"
                            :hint="t('admin.blogs.media_hint')"
                        />
                    </section>

                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <MediaUploader
                            v-model="form.cover"
                            v-model:remove-existing="form.remove_cover"
                            :label="t('admin.blogs.cover')"
                            :existing-url="blog?.cover_url"
                            :hint="t('admin.blogs.media_hint')"
                        />
                    </section>

                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <h2
                            class="mb-md text-label-lg uppercase tracking-wide text-on-surface"
                        >
                            {{ t('admin.blogs.details') }}
                        </h2>
                        <div class="flex flex-col gap-xs">
                            <AppSelect
                                v-model="form.blog_category_id"
                                :options="categoryOptions"
                                :label="t('admin.blogs.field_category')"
                                :placeholder="
                                    t('admin.blogs.category_placeholder')
                                "
                            />
                            <p
                                v-if="form.errors.blog_category_id"
                                class="text-label-md text-error"
                            >
                                {{ form.errors.blog_category_id }}
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
