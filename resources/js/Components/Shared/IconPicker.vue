<script setup>
import { computed, ref } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { IconChevronDown, IconLayout } from '@tabler/icons-vue';
import { appIconEntries, resolveAppIcon } from '@/icons/appIcons';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const { t } = useUiTranslations();
const open = ref(false);
const rootRef = ref(null);
const icons = appIconEntries();

const labelText = computed(() => props.label ?? t('common.icon'));
const placeholderText = computed(
    () => props.placeholder ?? t('common.select_icon'),
);

const SelectedIcon = computed(() =>
    resolveAppIcon(props.modelValue, IconLayout),
);

const displayName = computed(() => props.modelValue || placeholderText.value);

const toggle = () => {
    if (props.disabled) {
        return;
    }
    open.value = !open.value;
};

const pick = (name) => {
    emit('update:modelValue', name);
    open.value = false;
};

onClickOutside(rootRef, () => {
    open.value = false;
});

const cellClass = (name) =>
    props.modelValue === name
        ? 'border-primary bg-primary/15 text-primary'
        : 'border-transparent text-on-surface-variant hover:bg-surface-container-high hover:text-primary';
</script>

<template>
    <div ref="rootRef" class="relative flex w-full flex-col gap-xs">
        <span class="text-label-md uppercase tracking-wide text-on-surface-variant">
            {{ labelText }}
        </span>

        <div class="relative">
            <button
                type="button"
                class="relative flex w-full items-center gap-sm rounded-md border border-outline bg-surface-container py-1.5 pe-9 ps-sm text-start text-body-md text-on-surface outline-none transition-colors hover:border-primary focus:border-primary focus:ring-1 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="disabled"
                @click="toggle"
            >
                <component
                    :is="SelectedIcon"
                    class="shrink-0 text-primary"
                    :size="20"
                    stroke-width="1.5"
                />
                <span
                    class="block truncate"
                    :class="
                        modelValue
                            ? 'text-on-surface'
                            : 'text-on-surface-variant'
                    "
                >
                    {{ displayName }}
                </span>
                <span
                    class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-sm"
                >
                    <IconChevronDown
                        class="text-primary transition-transform duration-150"
                        :class="{ 'rotate-180': open }"
                        :size="18"
                        stroke-width="1.5"
                        aria-hidden="true"
                    />
                </span>
            </button>

            <div
                v-if="open"
                class="absolute top-full z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-outline-variant bg-surface-container-high p-sm"
            >
                <div class="grid grid-cols-5 gap-xs sm:grid-cols-6">
                    <button
                        v-for="icon in icons"
                        :key="icon.name"
                        type="button"
                        class="flex flex-col items-center gap-1 rounded-md border p-sm transition-colors"
                        :class="cellClass(icon.name)"
                        :title="icon.name"
                        @click="pick(icon.name)"
                    >
                        <component
                            :is="icon.component"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <span class="text-[10px] uppercase tracking-wide">
                            {{ icon.name }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
