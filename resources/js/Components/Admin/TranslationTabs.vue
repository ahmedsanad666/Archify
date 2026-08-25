<script setup>
import { computed, ref, watch } from 'vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { IconLanguage } from '@tabler/icons-vue';

const props = defineProps({
    languages: {
        type: Array,
        required: true,
    },
    /** Global site setting — when false, auto-translate checkbox is forced off/disabled */
    autoTranslateEnabled: {
        type: Boolean,
        default: false,
    },
    sourceLocale: {
        type: String,
        required: true,
    },
    /** Per-locale field bags keyed by language code, e.g. { en: { title: '' }, tr: { title: '' } } */
    modelValue: {
        type: Object,
        required: true,
    },
    /** Whether the form should request auto-translate on save */
    autoTranslate: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'update:sourceLocale',
    'update:modelValue',
    'update:autoTranslate',
]);

const selectedIndex = ref(0);

const orderedLanguages = computed(() => props.languages ?? []);

watch(
    () => props.sourceLocale,
    (code) => {
        const index = orderedLanguages.value.findIndex((l) => l.code === code);
        if (index >= 0) {
            selectedIndex.value = index;
        }
    },
    { immediate: true },
);

watch(
    () => props.autoTranslateEnabled,
    (enabled) => {
        if (!enabled && props.autoTranslate) {
            emit('update:autoTranslate', false);
        }
    },
);

const setSourceLocale = (event) => {
    emit('update:sourceLocale', event.target.value);
};

const setAutoTranslate = (event) => {
    if (!props.autoTranslateEnabled) {
        return;
    }
    emit('update:autoTranslate', event.target.checked);
};

const statusFor = (code) => {
    return props.modelValue?.[code]?.translation_status ?? null;
};

const statusClass = (status) => {
    switch (status) {
        case 'translated':
            return 'bg-primary/15 text-primary';
        case 'pending':
            return 'bg-secondary/15 text-secondary';
        case 'failed':
            return 'bg-error/15 text-error';
        case 'manual':
        default:
            return 'bg-surface-container-high text-on-surface-variant';
    }
};
</script>

<template>
    <div class="flex flex-col gap-md">
        <div
            class="flex flex-col gap-md rounded-lg border border-outline-variant bg-surface-container p-md md:flex-row md:items-end md:justify-between"
        >
            <div class="flex flex-col gap-xs">
                <label
                    for="source-locale"
                    class="text-label-md uppercase tracking-wide text-on-surface"
                >
                    Source locale
                </label>
                <div class="relative">
                    <IconLanguage
                        class="pointer-events-none absolute start-sm top-1/2 -translate-y-1/2 text-on-surface-variant"
                        :size="18"
                        stroke-width="1.5"
                    />
                    <select
                        id="source-locale"
                        :value="sourceLocale"
                        class="w-full rounded-md border border-outline bg-surface-container py-sm pe-sm ps-9 text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20 md:min-w-[200px]"
                        @change="setSourceLocale"
                    >
                        <option
                            v-for="language in orderedLanguages"
                            :key="language.code"
                            :value="language.code"
                        >
                            {{ language.name }} ({{ language.code.toUpperCase() }})
                        </option>
                    </select>
                </div>
            </div>

            <label
                class="flex cursor-pointer items-center gap-sm"
                :class="{ 'cursor-not-allowed opacity-50': !autoTranslateEnabled }"
            >
                <input
                    type="checkbox"
                    class="rounded-sm border-outline-variant bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-surface"
                    :checked="autoTranslate && autoTranslateEnabled"
                    :disabled="!autoTranslateEnabled"
                    @change="setAutoTranslate"
                />
                <span
                    class="text-label-md uppercase tracking-wide text-on-surface-variant"
                >
                    Auto-translate other locales
                </span>
            </label>
        </div>

        <p
            v-if="!autoTranslateEnabled"
            class="text-label-md text-on-surface-variant"
        >
            Auto-translate is disabled in site settings. Fill every locale tab
            manually before saving.
        </p>

        <TabGroup :selected-index="selectedIndex" @change="selectedIndex = $event">
            <TabList
                class="flex flex-wrap gap-xs border-b border-outline-variant pb-sm"
            >
                <Tab
                    v-for="language in orderedLanguages"
                    :key="language.code"
                    v-slot="{ selected }"
                    as="template"
                >
                    <button
                        type="button"
                        class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-primary"
                        :class="
                            selected
                                ? 'bg-primary text-on-primary'
                                : 'border border-outline-variant text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'
                        "
                    >
                        <span class="inline-flex items-center gap-2">
                            {{ language.code }}
                            <span
                                v-if="statusFor(language.code)"
                                class="rounded-sm px-1.5 py-0.5 text-[10px] normal-case tracking-normal"
                                :class="statusClass(statusFor(language.code))"
                            >
                                {{ statusFor(language.code) }}
                            </span>
                        </span>
                    </button>
                </Tab>
            </TabList>

            <TabPanels class="mt-md">
                <TabPanel
                    v-for="language in orderedLanguages"
                    :key="`panel-${language.code}`"
                    class="focus:outline-none"
                    :dir="language.direction ?? 'ltr'"
                >
                    <slot
                        :locale="language.code"
                        :language="language"
                        :is-source="language.code === sourceLocale"
                        :fields="modelValue[language.code] ?? {}"
                    />
                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>
