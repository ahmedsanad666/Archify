<script setup>
import { computed } from 'vue';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

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
        default: 'Enter phone number',
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
    defaultCountry: {
        type: String,
        default: 'TR',
    },
});

const emit = defineEmits(['update:modelValue']);

const inputId = computed(
    () =>
        props.id ??
        (props.label
            ? `phone-${props.label.toLowerCase().replace(/\s+/g, '-')}`
            : undefined),
);

const dropdownOptions = {
    showDialCodeInList: true,
    showDialCodeInSelection: true,
    showFlags: true,
    showSearchBox: true,
    searchBoxPlaceholder: 'Search country…',
};

const inputOptions = computed(() => ({
    id: inputId.value ?? '',
    placeholder: props.placeholder,
    showDialCode: true,
    type: 'tel',
    autocomplete: 'tel',
    styleClasses: 'app-phone-input__field',
}));

/**
 * Prefer E.164 when the number is valid; otherwise keep raw / empty
 * so the form can still save drafts without fighting the control.
 */
const onInput = (number, phoneObject) => {
    if (!number || !String(number).trim()) {
        emit('update:modelValue', '');
        return;
    }

    if (phoneObject?.isValid && phoneObject?.number) {
        emit('update:modelValue', phoneObject.number);
        return;
    }

    emit('update:modelValue', number);
};
</script>

<template>
    <div class="app-phone-input w-full" :class="{ 'has-error': !!error }">
        <label
            v-if="label"
            :for="inputId"
            class="mb-xs block text-start text-label-md uppercase tracking-wide text-on-surface-variant"
        >
            {{ label }}
        </label>

        <VueTelInput
            :model-value="modelValue ?? ''"
            :disabled="disabled"
            :default-country="defaultCountry"
            :auto-default-country="false"
            mode="international"
            :auto-format="true"
            :valid-characters-only="true"
            :dropdown-options="dropdownOptions"
            :input-options="inputOptions"
            style-classes="app-phone-input__wrap"
            @on-input="onInput"
        />

        <p v-if="error" class="mt-xs text-start text-label-md text-error">
            {{ error }}
        </p>
    </div>
</template>

<style scoped>
/* Token hexes from system-design-rule — third-party deep overrides */
.app-phone-input :deep(.app-phone-input__wrap) {
    display: flex;
    position: relative;
    overflow: visible;
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid #9e8e81; /* outline */
    background-color: #221f1c; /* surface-container */
    direction: inherit;
    transition:
        border-color 150ms ease,
        box-shadow 150ms ease;
}

.app-phone-input :deep(.app-phone-input__wrap:focus-within) {
    border-color: #f9ba7f; /* primary */
    box-shadow: 0 0 0 1px rgb(249 186 127 / 0.2);
}

.app-phone-input.has-error :deep(.app-phone-input__wrap) {
    border-color: #ffb4ab; /* error */
}

.app-phone-input.has-error :deep(.app-phone-input__wrap:focus-within) {
    box-shadow: 0 0 0 1px rgb(255 180 171 / 0.2);
}

.app-phone-input :deep(.app-phone-input__wrap.disabled) {
    cursor: not-allowed;
    opacity: 0.5;
}

.app-phone-input :deep(.vti__dropdown) {
    position: relative;
    align-self: stretch;
    background-color: transparent;
    border-inline-end: 1px solid #51443a; /* outline-variant */
    border-start-start-radius: 0.75rem;
    border-end-start-radius: 0.75rem;
    padding-inline: 0.5rem;
}

.app-phone-input :deep(.vti__dropdown:hover),
.app-phone-input :deep(.vti__dropdown.open) {
    background-color: #2d2926; /* surface-container-high */
}

.app-phone-input :deep(.vti__selection) {
    color: #e9e1db; /* on-surface */
    font-size: 0.875rem;
}

.app-phone-input :deep(.vti__dropdown-arrow) {
    color: #f9ba7f;
}

/* Library defaults top:33px — too short for our taller control */
.app-phone-input :deep(.vti__dropdown-list),
.app-phone-input :deep(.vti__dropdown-list.below) {
    top: 100% !important;
    bottom: auto !important;
    inset-inline-start: 0;
    left: auto;
    margin-top: 0.25rem;
    z-index: 50;
    max-height: 16rem;
    width: max(100%, 18rem);
    border: 1px solid #51443a;
    border-radius: 0.75rem;
    background-color: #2d2926;
    color: #e9e1db;
    box-shadow: none;
}

.app-phone-input :deep(.vti__dropdown-list.above) {
    top: auto !important;
    bottom: 100% !important;
    margin-top: 0;
    margin-bottom: 0.25rem;
}

.app-phone-input :deep(.vti__search_box) {
    margin: 0.5rem;
    width: calc(100% - 1rem);
    border-radius: 0.75rem;
    border: 1px solid #9e8e81;
    background-color: #221f1c;
    color: #e9e1db;
    padding: 0.5rem 0.75rem;
    outline: none;
}

.app-phone-input :deep(.vti__search_box:focus) {
    border-color: #f9ba7f;
}

.app-phone-input :deep(.vti__dropdown-item) {
    color: #e9e1db;
    padding-block: 0.5rem;
    border-inline-start: 2px solid transparent;
}

.app-phone-input :deep(.vti__dropdown-item.highlighted),
.app-phone-input :deep(.vti__dropdown-item:hover) {
    background-color: rgb(249 186 127 / 0.1);
    border-inline-start-color: #f9ba7f;
}

.app-phone-input :deep(.vti__dropdown-item strong) {
    color: #d5c3b6; /* on-surface-variant */
    font-weight: 500;
}

.app-phone-input :deep(.app-phone-input__field),
.app-phone-input :deep(.vti__input) {
    flex: 1;
    min-width: 0;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #e9e1db;
    font-size: 1rem;
    line-height: 1.6;
    padding-block: 0.75rem;
    padding-inline: 0.75rem;
    outline: none !important;
}

.app-phone-input :deep(.app-phone-input__field::placeholder),
.app-phone-input :deep(.vti__input::placeholder) {
    color: #d5c3b6;
}
</style>
