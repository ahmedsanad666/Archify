<script setup>
import { computed, ref, watch } from 'vue';
import { IconPhoto, IconTrash, IconUpload } from '@tabler/icons-vue';

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null,
    },
    existingUrl: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: 'Image',
    },
    accept: {
        type: String,
        default: 'image/jpeg,image/png,image/webp,image/gif,image/svg+xml',
    },
    hint: {
        type: String,
        default: 'JPEG, PNG, WEBP up to 5MB',
    },
    removeExisting: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'update:removeExisting']);

const inputRef = ref(null);
const previewUrl = ref(null);

const displayUrl = computed(() => {
    if (previewUrl.value) {
        return previewUrl.value;
    }
    if (props.removeExisting) {
        return null;
    }
    return props.existingUrl;
});

watch(
    () => props.modelValue,
    (file) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        if (file instanceof File) {
            previewUrl.value = URL.createObjectURL(file);
        }
    },
);

const onFileChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    emit('update:removeExisting', false);
    emit('update:modelValue', file);
};

const clear = () => {
    if (inputRef.value) {
        inputRef.value.value = '';
    }
    emit('update:modelValue', null);
    if (props.existingUrl) {
        emit('update:removeExisting', true);
    }
};

const openPicker = () => {
    inputRef.value?.click();
};
</script>

<template>
    <div class="flex flex-col gap-xs">
        <span class="text-label-md uppercase tracking-wide text-on-surface">
            {{ label }}
        </span>

        <div
            class="relative flex min-h-[160px] flex-col items-center justify-center gap-sm overflow-hidden rounded-lg border border-dashed border-outline-variant bg-surface-container-low p-md transition-colors duration-200 hover:border-secondary"
            :class="{ 'border-primary': displayUrl }"
        >
            <img
                v-if="displayUrl"
                :src="displayUrl"
                :alt="label"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div
                v-if="displayUrl"
                class="absolute inset-0 bg-surface/50"
            />

            <template v-if="!displayUrl">
                <IconUpload
                    class="text-on-surface-variant"
                    :size="28"
                    stroke-width="1.5"
                />
                <p class="text-center text-body-md text-on-surface-variant">
                    Drop an image or click to browse
                </p>
                <p class="text-label-md text-on-surface-variant/70">
                    {{ hint }}
                </p>
            </template>

            <div
                class="relative z-10 flex flex-wrap items-center justify-center gap-sm"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-xs rounded-md bg-primary px-sm py-xs text-label-md uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                    @click="openPicker"
                >
                    <IconPhoto :size="16" stroke-width="1.5" />
                    {{ displayUrl ? 'Replace' : 'Choose file' }}
                </button>
                <button
                    v-if="displayUrl"
                    type="button"
                    class="inline-flex items-center gap-xs rounded-md border border-outline-variant bg-transparent px-sm py-xs text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-error"
                    @click="clear"
                >
                    <IconTrash :size="16" stroke-width="1.5" />
                    Remove
                </button>
            </div>

            <input
                ref="inputRef"
                type="file"
                class="hidden"
                :accept="accept"
                @change="onFileChange"
            />
        </div>
    </div>
</template>
