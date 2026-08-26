<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: '',
    },
    label: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    leadingIcon: {
        type: [Object, Function],
        default: null,
    },
    autocomplete: {
        type: String,
        default: undefined,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputId = computed(
    () => props.id ?? (props.label ? `input-${props.label.toLowerCase().replace(/\s+/g, '-')}` : undefined),
);

const onInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="inputId"
            class="mb-xs block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
        >
            {{ label }}
        </label>

        <div class="relative">
            <component
                :is="leadingIcon"
                v-if="leadingIcon"
                class="pointer-events-none absolute start-sm top-1/2 z-10 -translate-y-1/2 text-on-surface-variant"
                :size="18"
                stroke-width="1.5"
            />
            <input
                :id="inputId"
                :type="type"
                :value="modelValue ?? ''"
                :placeholder="placeholder"
                :disabled="disabled"
                :autocomplete="autocomplete"
                class="w-full rounded-md border border-outline bg-surface-container py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
                :class="[
                    leadingIcon ? 'ps-9 pe-sm' : 'px-sm',
                    error ? 'border-error focus:border-error focus:ring-error/20' : '',
                ]"
                @input="onInput"
            />
        </div>

        <p v-if="error" class="mt-xs text-start text-label-md text-error">
            {{ error }}
        </p>
    </div>
</template>
