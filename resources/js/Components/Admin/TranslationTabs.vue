<script setup>
import { computed, ref, watch } from "vue";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import { IconCheck, IconLanguage } from "@tabler/icons-vue";
import AppSelect from "@/Components/Shared/AppSelect.vue";
import { useUiTranslations } from "@/Composables/useUiTranslations";

const { t } = useUiTranslations();

const props = defineProps({
    languages: {
        type: Array,
        required: true,
    },
    /** Global site setting — used for soft notice only; form checkbox stays usable */
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
    "update:sourceLocale",
    "update:modelValue",
    "update:autoTranslate",
]);

const selectedIndex = ref(0);

const orderedLanguages = computed(() => props.languages ?? []);

const localeOptions = computed(() =>
    orderedLanguages.value.map((language) => ({
        value: language.code,
        label: `${language.name} (${language.code.toUpperCase()})`,
    })),
);

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

const setSourceLocale = (code) => {
    emit("update:sourceLocale", code);
};

const setAutoTranslate = (event) => {
    emit("update:autoTranslate", event.target.checked);
};

const statusFor = (code) => {
    return props.modelValue?.[code]?.translation_status ?? null;
};

const statusClass = (status) => {
    switch (status) {
        case "translated":
            return "bg-primary/15 text-primary";
        case "pending":
            return "bg-secondary/15 text-secondary";
        case "failed":
            return "bg-error/15 text-error";
        case "manual":
        default:
            return "bg-surface-container-high text-on-surface-variant";
    }
};
</script>

<template>
    <div class="flex flex-col gap-sm text-start">
        <div
            class="flex flex-col gap-sm rounded-lg border border-outline-variant bg-surface-container p-sm md:flex-row md:items-end md:justify-between"
        >
            <div class="min-w-0 flex-1 md:max-w-xs">
                <AppSelect
                    id="source-locale"
                    :model-value="sourceLocale"
                    :options="localeOptions"
                    :label="t('common.source_locale')"
                    :leading-icon="IconLanguage"
                    @update:model-value="setSourceLocale"
                />
            </div>

            <label
                class="flex cursor-pointer items-center gap-sm pb-0.5 md:ms-auto"
            >
                <span
                    class="relative inline-flex size-4 shrink-0 items-center justify-center"
                >
                    <input
                        type="checkbox"
                        class="size-4 shrink-0 cursor-pointer appearance-none rounded-[3px] border-2 border-outline bg-surface-container transition-colors checked:border-primary checked:bg-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        :checked="autoTranslate"
                        @change="setAutoTranslate"
                    />
                    <IconCheck
                        v-if="autoTranslate"
                        class="pointer-events-none absolute text-on-primary"
                        :size="11"
                        stroke-width="3"
                    />
                </span>
                <span class="text-label-md text-on-surface-variant">
                    {{ t("common.auto_translate") }}
                </span>
            </label>
        </div>

        <p
            v-if="!autoTranslateEnabled"
            class="text-start text-label-md leading-snug text-on-surface-variant"
        >
            {{ t("common.auto_translate_notice") }}
        </p>

        <TabGroup
            :selected-index="selectedIndex"
            @change="selectedIndex = $event"
        >
            <TabList
                class="flex flex-wrap justify-start gap-xs border-b border-outline-variant pb-sm"
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

            <TabPanels class="mt-sm">
                <TabPanel
                    v-for="language in orderedLanguages"
                    :key="`panel-${language.code}`"
                    class="focus:outline-none"
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
