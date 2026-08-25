<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import GalleryUploader from "@/Components/Admin/GalleryUploader.vue";
import MediaUploader from "@/Components/Admin/MediaUploader.vue";
import RichTextEditor from "@/Components/Admin/RichTextEditor.vue";
import TranslationTabs from "@/Components/Admin/TranslationTabs.vue";

const props = defineProps({
    project: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    concepts: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const languages = computed(() => page.props.languages ?? []);
const defaultLocale = computed(
    () =>
        languages.value.find((l) => l.is_default)?.code ??
        languages.value[0]?.code ??
        "en",
);

const isEdit = computed(() => Boolean(props.project?.id));
const pageTitle = computed(() =>
    isEdit.value ? "Edit project" : "New project",
);

const buildTranslations = () => {
    const bag = {};
    for (const language of languages.value) {
        const existing = props.project?.translations?.[language.code] ?? {};
        bag[language.code] = {
            name: existing.name ?? "",
            slug: existing.slug ?? "",
            short_description: existing.short_description ?? "",
            description: existing.description ?? "",
            meta_title: existing.meta_title ?? "",
            meta_description: existing.meta_description ?? "",
            meta_keywords: existing.meta_keywords ?? "",
            translation_status: existing.translation_status ?? null,
        };
    }
    return bag;
};

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);
const removeImages2d = ref([]);
const removeImages3d = ref([]);
const removeImagesOutdoor = ref([]);
const uploadError = ref("");

const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const VIDEO_MAX_BYTES = 50 * 1024 * 1024;
const TOTAL_MAX_BYTES = 100 * 1024 * 1024;

const fileSize = (file) => (file instanceof File ? file.size : 0);

const collectUploadFiles = () => {
    const images = [
        form.thumbnail,
        ...(form.images_2d ?? []),
        ...(form.images_3d ?? []),
        ...(form.images_outdoor ?? []),
    ].filter((file) => file instanceof File);

    const videos = [form.preview_video].filter((file) => file instanceof File);

    return { images, videos };
};

const validateUploadSizes = () => {
    const { images, videos } = collectUploadFiles();

    for (const file of images) {
        if (file.size > IMAGE_MAX_BYTES) {
            return `"${file.name}" exceeds the 5MB image limit.`;
        }
    }

    for (const file of videos) {
        if (file.size > VIDEO_MAX_BYTES) {
            return `"${file.name}" exceeds the 50MB video limit.`;
        }
    }

    const total = [...images, ...videos].reduce(
        (sum, file) => sum + fileSize(file),
        0,
    );

    if (total > TOTAL_MAX_BYTES) {
        return "Total uploads exceed 100MB. Remove some gallery images or the preview video and try again.";
    }

    return "";
};

const form = useForm({
    project_category_id: props.project?.project_category_id ?? "",
    client_name: props.project?.client_name ?? "",
    location: props.project?.location ?? "",
    year: props.project?.year ?? new Date().getFullYear(),
    video_url: props.project?.video_url ?? "",
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
    concept_ids: [...(props.project?.concept_ids ?? [])],
    thumbnail: null,
    preview_video: null,
    remove_thumbnail: false,
    remove_preview_video: false,
    images_2d: [],
    images_3d: [],
    images_outdoor: [],
    remove_media_ids: [],
});

const inputClass =
    "w-full rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20";

const slugify = (value) =>
    value
        .toString()
        .normalize("NFKD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\u0600-\u06FF]+/g, "-")
        .replace(/^-+|-+$/g, "");

const onTitleInput = (locale, event) => {
    const name = event.target.value;
    form.translations[locale].name = name;
    form.translations[locale].slug = slugify(name);
};

const toggleConcept = (id) => {
    const index = form.concept_ids.indexOf(id);
    if (index >= 0) {
        form.concept_ids.splice(index, 1);
        return;
    }
    form.concept_ids.push(id);
};

const submit = () => {
    uploadError.value = validateUploadSizes();
    if (uploadError.value) {
        return;
    }

    form.source_locale = sourceLocale.value;
    form.auto_translate = autoTranslate.value;
    form.remove_media_ids = [
        ...removeImages2d.value,
        ...removeImages3d.value,
        ...removeImagesOutdoor.value,
    ];

    const options = { forceFormData: true };

    if (isEdit.value) {
        form
            .transform((data) => ({
                ...data,
                _method: "put",
            }))
            .post(route("admin.projects.update", props.project.id), options);
        return;
    }

    form.post(route("admin.projects.store"), options);
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
                                    :href="route('admin.projects.index')"
                                    class="transition-colors hover:text-on-surface"
                                >
                                    Projects
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
                        {{
                            isEdit ? "Edit project" : "Add new project"
                        }}
                    </h1>
                </div>

                <div class="flex flex-wrap gap-sm">
                    <Link
                        :href="route('admin.projects.index')"
                        class="inline-flex items-center justify-center rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Saving…" : "Save" }}
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
                Please fix the highlighted fields and try again.
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
                                                Project title
                                            </label>
                                            <input
                                                :value="
                                                    form.translations[locale]
                                                        .name
                                                "
                                                type="text"
                                                :class="inputClass"
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
                                                        `translations.${locale}.name`
                                                    ]
                                                "
                                                class="text-label-md text-error"
                                            >
                                                {{
                                                    form.errors[
                                                        `translations.${locale}.name`
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
                                                Slug
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
                                                Auto-generated from title
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-xs">
                                        <label
                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                        >
                                            Short description
                                        </label>
                                        <textarea
                                            v-model="
                                                form.translations[locale]
                                                    .short_description
                                            "
                                            rows="2"
                                            :class="inputClass"
                                        />
                                    </div>

                                    <div class="flex flex-col gap-xs">
                                        <label
                                            class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                        >
                                            Full description
                                        </label>
                                        <RichTextEditor
                                            :key="locale"
                                            v-model="
                                                form.translations[locale]
                                                    .description
                                            "
                                        />
                                        <p
                                            class="text-label-md text-on-surface-variant/70"
                                        >
                                            Saved as HTML for the public project
                                            page
                                        </p>
                                    </div>

                                    <div
                                        class="grid gap-md border-t border-outline-variant pt-md sm:grid-cols-2"
                                    >
                                        <p
                                            class="text-label-md uppercase tracking-wide text-on-surface sm:col-span-2"
                                        >
                                            SEO
                                        </p>
                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                Meta title
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
                                                Meta keywords
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
                                                Meta description
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

                    <section
                        class="flex flex-col gap-md rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <h2
                            class="text-label-lg uppercase tracking-wide text-on-surface"
                        >
                            Media assets
                        </h2>

                        <MediaUploader
                            v-model="form.thumbnail"
                            v-model:remove-existing="form.remove_thumbnail"
                            label="Featured image"
                            :existing-url="project?.thumbnail_url"
                            hint="JPEG, PNG, WEBP — max 5MB each; keep total uploads under 100MB"
                        />

                        <GalleryUploader
                            v-model="form.images_2d"
                            v-model:remove-ids="removeImages2d"
                            label="2D images"
                            :existing="project?.images_2d ?? []"
                            hint="JPEG, PNG, WEBP — max 5MB each"
                        />

                        <GalleryUploader
                            v-model="form.images_3d"
                            v-model:remove-ids="removeImages3d"
                            label="3D images"
                            :existing="project?.images_3d ?? []"
                            hint="JPEG, PNG, WEBP — max 5MB each"
                        />

                        <GalleryUploader
                            v-model="form.images_outdoor"
                            v-model:remove-ids="removeImagesOutdoor"
                            label="Outdoor images"
                            :existing="project?.images_outdoor ?? []"
                            hint="JPEG, PNG, WEBP — max 5MB each"
                        />

                        <MediaUploader
                            v-model="form.preview_video"
                            v-model:remove-existing="form.remove_preview_video"
                            label="Preview video"
                            :existing-url="project?.preview_video_url"
                            accept="video/mp4,video/webm,video/quicktime"
                            hint="MP4 or WEBM — max 50MB (~30s loop); keep total uploads under 100MB"
                        />
                    </section>
                </div>

                <div class="flex flex-col gap-md lg:col-span-4 lg:sticky lg:top-md lg:self-start">
                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <h2
                            class="mb-md text-label-lg uppercase tracking-wide text-on-surface"
                        >
                            Project details
                        </h2>
                        <div class="grid grid-cols-1 gap-md sm:grid-cols-2">
                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    Category
                                </label>
                                <select
                                    v-model="form.project_category_id"
                                    :class="inputClass"
                                >
                                    <option value="" disabled>
                                        Select category
                                    </option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{
                                            category.name ||
                                            `Category #${category.id}`
                                        }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.project_category_id"
                                    class="text-label-md text-error"
                                >
                                    {{ form.errors.project_category_id }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    Client
                                </label>
                                <input
                                    v-model="form.client_name"
                                    type="text"
                                    :class="inputClass"
                                />
                                <p
                                    v-if="form.errors.client_name"
                                    class="text-label-md text-error"
                                >
                                    {{ form.errors.client_name }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    Location
                                </label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    :class="inputClass"
                                />
                                <p
                                    v-if="form.errors.location"
                                    class="text-label-md text-error"
                                >
                                    {{ form.errors.location }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-xs">
                                <label
                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    Year
                                </label>
                                <input
                                    v-model="form.year"
                                    type="number"
                                    min="1900"
                                    max="2100"
                                    :class="inputClass"
                                />
                                <p
                                    v-if="form.errors.year"
                                    class="text-label-md text-error"
                                >
                                    {{ form.errors.year }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-xs sm:col-span-2">
                                <label
                                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                                >
                                    YouTube URL
                                </label>
                                <input
                                    v-model="form.video_url"
                                    type="url"
                                    placeholder="https://youtube.com/..."
                                    :class="inputClass"
                                />
                                <p
                                    v-if="form.errors.video_url"
                                    class="text-label-md text-error"
                                >
                                    {{ form.errors.video_url }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-lg border border-outline-variant bg-surface-container p-md"
                    >
                        <h2
                            class="mb-md text-label-lg uppercase tracking-wide text-on-surface"
                        >
                            Concepts
                        </h2>
                        <p
                            v-if="!concepts.length"
                            class="text-body-md text-on-surface-variant"
                        >
                            No concepts yet. Add concepts later from the Concepts
                            admin.
                        </p>
                        <div v-else class="flex flex-wrap gap-sm">
                            <button
                                v-for="concept in concepts"
                                :key="concept.id"
                                type="button"
                                class="rounded-sm border px-3 py-1 text-label-md uppercase tracking-wide transition-colors"
                                :class="
                                    form.concept_ids.includes(concept.id)
                                        ? 'border-primary bg-primary text-on-primary'
                                        : 'border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'
                                "
                                @click="toggleConcept(concept.id)"
                            >
                                {{ concept.title || `Concept #${concept.id}` }}
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
