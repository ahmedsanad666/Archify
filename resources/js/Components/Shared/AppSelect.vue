<script setup>
import { computed } from 'vue';
import {
    Listbox,
    ListboxButton,
    ListboxOption,
    ListboxOptions,
} from '@headlessui/vue';
import { IconChevronDown } from '@tabler/icons-vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    options: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Select…',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    leadingIcon: {
        type: [Object, Function],
        default: null,
    },
    id: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedOption = computed(() =>
    props.options.find((option) => option.value === props.modelValue) ?? null,
);

const displayLabel = computed(
    () => selectedOption.value?.label ?? props.placeholder,
);

const onChange = (value) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <Listbox
        :model-value="modelValue"
        :disabled="disabled"
        @update:model-value="onChange"
    >
        <div class="relative w-full">
            <label
                v-if="label"
                :for="id ?? undefined"
                class="mb-xs block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
            >
                {{ label }}
            </label>

            <ListboxButton
                v-slot="{ open }"
                :id="id ?? undefined"
                class="relative flex w-full items-center gap-sm rounded-md border border-outline bg-surface-container py-1.5 pe-9 text-start text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
                :class="leadingIcon ? 'ps-9' : 'ps-sm'"
            >
                <component
                    :is="leadingIcon"
                    v-if="leadingIcon"
                    class="pointer-events-none absolute start-sm top-1/2 -translate-y-1/2 text-on-surface-variant"
                    :size="18"
                    stroke-width="1.5"
                />
                <span
                    class="block truncate"
                    :class="
                        selectedOption
                            ? 'text-on-surface'
                            : 'text-on-surface-variant'
                    "
                >
                    {{ displayLabel }}
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
            </ListboxButton>

            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions
                    class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-outline-variant bg-surface-container-high py-1 focus:outline-none"
                >
                    <ListboxOption
                        v-for="option in options"
                        :key="String(option.value)"
                        v-slot="{ active, selected }"
                        :value="option.value"
                        as="template"
                    >
                        <li
                            class="relative cursor-pointer select-none px-sm py-1.5 text-body-md transition-colors"
                            :class="[
                                active || selected
                                    ? 'bg-primary/10 text-on-surface'
                                    : 'text-on-surface',
                                selected
                                    ? 'border-s-2 border-primary'
                                    : 'border-s-2 border-transparent',
                            ]"
                        >
                            <span
                                class="block truncate"
                                :class="selected ? 'font-semibold' : 'font-normal'"
                            >
                                {{ option.label }}
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
