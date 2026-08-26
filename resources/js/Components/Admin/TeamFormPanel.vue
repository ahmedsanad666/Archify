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
import TranslationTabs from '@/Components/Admin/TranslationTabs.vue';
import { IconBrandBehance, IconBrandInstagram, IconBrandLinkedin, IconUpload } from '@tabler/icons-vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    member: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:open']);

const page = usePage();
const { t } = useUiTranslations();
const languages = computed(() => page.props.languages ?? []);
const direction = computed(() => page.props.locale?.direction ?? 'ltr');
const defaultLocale = computed(
    () =>
        languages.value.find((l) => l.is_default)?.code ??
        languages.value[0]?.code ??
        'en',
);

const isEdit = computed(() => Boolean(props.member?.id));
const title = computed(() =>
    isEdit.value ? t('admin.team.edit') : t('admin.team.add'),
);

const sourceLocale = ref(defaultLocale.value);
const autoTranslate = ref(false);
const avatarInputRef = ref(null);
const avatarPreview = ref(null);

const buildTranslations = (member = null) => {
    const bag = {};
    for (const language of languages.value) {
        const existing = member?.translations?.[language.code] ?? {};
        bag[language.code] = {
            role: existing.role ?? '',
        };
    }
    return bag;
};

const form = useForm({
    name: '',
    linkedin_url: '',
    behance_url: '',
    instagram_url: '',
    order: 0,
    avatar: null,
    remove_avatar: false,
    source_locale: defaultLocale.value,
    auto_translate: false,
    translations: buildTranslations(),
});

const inputClass =
    'w-full rounded-md border border-outline bg-surface-container px-sm py-1.5 text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const socialInputClass =
    'w-full rounded-md border border-outline bg-surface-container py-1.5 pe-sm ps-xl text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20';

const displayAvatarUrl = computed(() => {
    if (avatarPreview.value) {
        return avatarPreview.value;
    }
    if (form.remove_avatar) {
        return null;
    }
    return props.member?.avatar_url ?? null;
});

const clearAvatarPreview = () => {
    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
        avatarPreview.value = null;
    }
};

const resetForm = () => {
    sourceLocale.value = defaultLocale.value;
    autoTranslate.value = false;
    clearAvatarPreview();
    if (avatarInputRef.value) {
        avatarInputRef.value.value = '';
    }
    form.transform((data) => data);
    form.clearErrors();
    form.defaults({
        name: props.member?.name ?? '',
        linkedin_url: props.member?.linkedin_url ?? '',
        behance_url: props.member?.behance_url ?? '',
        instagram_url: props.member?.instagram_url ?? '',
        order: props.member?.order ?? 0,
        avatar: null,
        remove_avatar: false,
        source_locale: defaultLocale.value,
        auto_translate: false,
        translations: buildTranslations(props.member),
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

const onAvatarChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    clearAvatarPreview();
    form.remove_avatar = false;
    form.avatar = file;
    if (file instanceof File) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const removeAvatar = () => {
    clearAvatarPreview();
    form.avatar = null;
    if (avatarInputRef.value) {
        avatarInputRef.value.value = '';
    }
    if (props.member?.avatar_url) {
        form.remove_avatar = true;
    }
};

const openAvatarPicker = () => {
    avatarInputRef.value?.click();
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
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    };

    if (isEdit.value) {
        form
            .transform((data) => ({
                ...data,
                _method: 'put',
            }))
            .post(route('admin.team-members.update', props.member.id), options);
        return;
    }

    form.post(route('admin.team-members.store'), options);
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
                                class="pointer-events-auto flex h-full w-screen max-w-md flex-col border-s border-primary-container bg-surface-container text-start shadow-none"
                                :dir="direction"
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
                                        class="flex-1 space-y-sm overflow-y-auto px-md py-sm"
                                    >
                                        <div class="flex flex-col items-center gap-xs">
                                            <button
                                                type="button"
                                                class="group relative flex size-20 flex-col items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-outline-variant transition-colors hover:border-primary hover:bg-surface-container-high sm:size-24"
                                                @click="openAvatarPicker"
                                            >
                                                <img
                                                    v-if="displayAvatarUrl"
                                                    :src="displayAvatarUrl"
                                                    :alt="form.name || t('admin.team.avatar')"
                                                    class="absolute inset-0 size-full object-cover opacity-60 transition-opacity group-hover:opacity-40"
                                                />
                                                <IconUpload
                                                    class="relative z-10 text-on-surface-variant transition-colors group-hover:text-primary"
                                                    :size="22"
                                                    stroke-width="1.5"
                                                />
                                                <span
                                                    class="relative z-10 mt-0.5 text-[10px] uppercase tracking-wide text-on-surface-variant transition-colors group-hover:text-primary"
                                                >
                                                    {{ t('admin.team.change_photo') }}
                                                </span>
                                            </button>
                                            <input
                                                ref="avatarInputRef"
                                                type="file"
                                                class="hidden"
                                                accept="image/jpeg,image/png,image/webp,image/gif"
                                                @change="onAvatarChange"
                                            />
                                            <button
                                                v-if="displayAvatarUrl"
                                                type="button"
                                                class="text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:text-error"
                                                @click="removeAvatar"
                                            >
                                                {{ t('common.delete') }}
                                            </button>
                                            <p
                                                v-if="form.errors.avatar"
                                                class="text-label-md text-error"
                                            >
                                                {{ form.errors.avatar }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col gap-xs">
                                            <label
                                                class="block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{ t('admin.team.name') }}
                                            </label>
                                            <input
                                                v-model="form.name"
                                                type="text"
                                                :class="inputClass"
                                            />
                                            <p
                                                v-if="form.errors.name"
                                                class="text-label-md text-error"
                                            >
                                                {{ form.errors.name }}
                                            </p>
                                        </div>

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
                                            <template #default="{ locale, language }">
                                                <div class="flex flex-col gap-xs">
                                                    <label
                                                        class="block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                                                    >
                                                        {{ t('admin.team.role') }}
                                                    </label>
                                                    <input
                                                        v-model="
                                                            form.translations[locale].role
                                                        "
                                                        type="text"
                                                        :dir="language?.direction ?? 'ltr'"
                                                        :class="inputClass"
                                                    />
                                                    <p
                                                        v-if="
                                                            form.errors[
                                                                `translations.${locale}.role`
                                                            ]
                                                        "
                                                        class="text-label-md text-error"
                                                    >
                                                        {{
                                                            form.errors[
                                                                `translations.${locale}.role`
                                                            ]
                                                        }}
                                                    </p>
                                                </div>
                                            </template>
                                        </TranslationTabs>

                                        <div
                                            class="space-y-xs border-t border-outline-variant pt-sm"
                                        >
                                            <h4
                                                class="text-start text-label-md uppercase tracking-wide text-on-surface"
                                            >
                                                {{ t('admin.team.social_links') }}
                                            </h4>
                                            <div class="relative">
                                                <IconBrandLinkedin
                                                    class="pointer-events-none absolute start-sm top-1/2 -translate-y-1/2 text-on-surface-variant"
                                                    :size="16"
                                                    stroke-width="1.5"
                                                />
                                                <input
                                                    v-model="form.linkedin_url"
                                                    type="url"
                                                    :placeholder="t('admin.team.linkedin')"
                                                    :class="socialInputClass"
                                                />
                                            </div>
                                            <p
                                                v-if="form.errors.linkedin_url"
                                                class="text-label-md text-error"
                                            >
                                                {{ form.errors.linkedin_url }}
                                            </p>
                                            <div class="relative">
                                                <IconBrandBehance
                                                    class="pointer-events-none absolute start-sm top-1/2 -translate-y-1/2 text-on-surface-variant"
                                                    :size="16"
                                                    stroke-width="1.5"
                                                />
                                                <input
                                                    v-model="form.behance_url"
                                                    type="url"
                                                    :placeholder="t('admin.team.behance')"
                                                    :class="socialInputClass"
                                                />
                                            </div>
                                            <p
                                                v-if="form.errors.behance_url"
                                                class="text-label-md text-error"
                                            >
                                                {{ form.errors.behance_url }}
                                            </p>
                                            <div class="relative">
                                                <IconBrandInstagram
                                                    class="pointer-events-none absolute start-sm top-1/2 -translate-y-1/2 text-on-surface-variant"
                                                    :size="16"
                                                    stroke-width="1.5"
                                                />
                                                <input
                                                    v-model="form.instagram_url"
                                                    type="url"
                                                    :placeholder="t('admin.team.instagram')"
                                                    :class="socialInputClass"
                                                />
                                            </div>
                                            <p
                                                v-if="form.errors.instagram_url"
                                                class="text-label-md text-error"
                                            >
                                                {{ form.errors.instagram_url }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col gap-xs border-t border-outline-variant pt-sm">
                                            <label
                                                class="block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
                                            >
                                                {{ t('admin.team.display_order') }}
                                            </label>
                                            <input
                                                v-model.number="form.order"
                                                type="number"
                                                min="0"
                                                class="w-24 rounded-md border border-outline bg-surface-container px-sm py-1.5 text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="flex flex-col-reverse gap-sm border-t border-outline-variant bg-surface-container-low px-md py-sm sm:flex-row sm:justify-start"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-center rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high sm:w-auto"
                                            :disabled="form.processing"
                                            @click="close"
                                        >
                                            {{ t('admin.team.cancel') }}
                                        </button>
                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container disabled:opacity-60 sm:w-auto"
                                            :disabled="form.processing"
                                        >
                                            {{
                                                form.processing
                                                    ? t('admin.team.saving')
                                                    : t('admin.team.save')
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
