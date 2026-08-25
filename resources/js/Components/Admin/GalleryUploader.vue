<script setup>
import { computed, onUnmounted, ref, watch } from "vue";
import { IconPlus, IconTrash } from "@tabler/icons-vue";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    existing: {
        type: Array,
        default: () => [],
    },
    removeIds: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: "Images",
    },
    hint: {
        type: String,
        default: "JPEG, PNG, WEBP up to 5MB each",
    },
    accept: {
        type: String,
        default: "image/jpeg,image/png,image/webp,image/gif",
    },
});

const emit = defineEmits(["update:modelValue", "update:removeIds"]);

const inputRef = ref(null);
const localPreviews = ref([]);

watch(
    () => props.modelValue,
    (files) => {
        localPreviews.value.forEach((url) => URL.revokeObjectURL(url));
        localPreviews.value = (files ?? []).map((file) =>
            file instanceof File ? URL.createObjectURL(file) : "",
        );
    },
    { immediate: true, deep: true },
);

onUnmounted(() => {
    localPreviews.value.forEach((url) => URL.revokeObjectURL(url));
});

const visibleExisting = computed(() =>
    (props.existing ?? []).filter(
        (item) => !props.removeIds.includes(item.id),
    ),
);

const openPicker = () => {
    inputRef.value?.click();
};

const onFileChange = (event) => {
    const picked = Array.from(event.target.files ?? []);
    if (!picked.length) {
        return;
    }
    emit("update:modelValue", [...(props.modelValue ?? []), ...picked]);
    if (inputRef.value) {
        inputRef.value.value = "";
    }
};

const removeNew = (index) => {
    const next = [...(props.modelValue ?? [])];
    next.splice(index, 1);
    emit("update:modelValue", next);
};

const removeExisting = (id) => {
    if (props.removeIds.includes(id)) {
        return;
    }
    emit("update:removeIds", [...props.removeIds, id]);
};
</script>

<template>
    <div class="flex flex-col gap-xs">
        <div class="flex flex-wrap items-end justify-between gap-sm">
            <div>
                <span
                    class="text-label-md uppercase tracking-wide text-on-surface"
                >
                    {{ label }}
                </span>
                <p class="text-label-md text-on-surface-variant/70">
                    {{ hint }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-xs rounded-md border border-outline-variant px-sm py-xs text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
                @click="openPicker"
            >
                <IconPlus :size="16" stroke-width="1.5" />
                Add
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-sm">
            <div
                v-for="item in visibleExisting"
                :key="`existing-${item.id}`"
                class="group relative h-16 w-16 shrink-0 overflow-hidden rounded-md border border-outline-variant bg-surface-container-low"
            >
                <img
                    :src="item.url"
                    :alt="label"
                    class="h-full w-full object-cover"
                />
                <button
                    type="button"
                    class="absolute inset-0 flex items-center justify-center bg-surface/70 text-error opacity-0 transition-opacity group-hover:opacity-100"
                    title="Remove"
                    @click="removeExisting(item.id)"
                >
                    <IconTrash :size="16" stroke-width="1.5" />
                </button>
            </div>

            <div
                v-for="(preview, index) in localPreviews"
                :key="`new-${index}`"
                class="group relative h-16 w-16 shrink-0 overflow-hidden rounded-md border border-outline-variant bg-surface-container-low"
            >
                <img
                    v-if="preview"
                    :src="preview"
                    :alt="`${label} new ${index + 1}`"
                    class="h-full w-full object-cover"
                />
                <button
                    type="button"
                    class="absolute inset-0 flex items-center justify-center bg-surface/70 text-error opacity-0 transition-opacity group-hover:opacity-100"
                    title="Remove"
                    @click="removeNew(index)"
                >
                    <IconTrash :size="16" stroke-width="1.5" />
                </button>
            </div>

            <button
                type="button"
                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-md border border-dashed border-outline-variant bg-surface-container-low text-on-surface-variant transition-colors hover:border-secondary hover:text-on-surface"
                title="Add images"
                @click="openPicker"
            >
                <IconPlus :size="20" stroke-width="1.5" />
            </button>
        </div>

        <input
            ref="inputRef"
            type="file"
            class="hidden"
            :accept="accept"
            multiple
            @change="onFileChange"
        />
    </div>
</template>
