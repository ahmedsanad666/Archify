<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        type: [String, null],
        default: '',
    },
    label: {
        type: String,
        default: null,
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
    rows: {
        type: Number,
        default: 5,
    },
})

const emit = defineEmits(['update:modelValue'])

const textareaId = computed(
    () =>
        props.id ??
        (props.label
            ? `textarea-${props.label.toLowerCase().replace(/\s+/g, '-')}`
            : undefined),
)

const onInput = (event) => {
    emit('update:modelValue', event.target.value)
}
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="textareaId"
            class="mb-xs block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
        >
            {{ label }}
        </label>

        <textarea
            :id="textareaId"
            :value="modelValue ?? ''"
            :placeholder="placeholder"
            :disabled="disabled"
            :rows="rows"
            class="w-full resize-none rounded-md border border-outline bg-surface-container px-sm py-sm text-body-md text-on-surface outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
            :class="error ? 'border-error focus:border-error focus:ring-error/20' : ''"
            @input="onInput"
        />

        <p
            v-if="error"
            class="mt-xs text-start text-label-md text-error"
        >
            {{ error }}
        </p>
    </div>
</template>
