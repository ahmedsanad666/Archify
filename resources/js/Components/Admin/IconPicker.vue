<script setup>
import { computed, ref } from "vue";
import { IconLayout } from "@tabler/icons-vue";
import { appIconEntries, resolveAppIcon } from "@/icons/appIcons";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    variant: {
        type: String,
        default: "dropdown",
        validator: (value) => ["dropdown", "grid"].includes(value),
    },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const icons = appIconEntries();

const selected = computed(() =>
    icons.find((icon) => icon.name === props.modelValue) ?? null,
);

const SelectedIcon = computed(() =>
    resolveAppIcon(props.modelValue, IconLayout),
);

const pick = (name) => {
    emit("update:modelValue", name);
    open.value = false;
};

const cellClass = (name) =>
    props.modelValue === name
        ? "border-primary bg-primary/15 text-primary"
        : "border-transparent text-on-surface-variant hover:bg-surface-container-high hover:text-primary";
</script>

<template>
    <div class="flex flex-col gap-xs">
        <span class="text-label-md uppercase tracking-wide text-on-surface">
            Icon
        </span>

        <template v-if="variant === 'grid'">
            <div
                class="grid grid-cols-4 gap-xs rounded-lg border border-outline-variant bg-surface-container p-sm sm:grid-cols-6"
            >
                <button
                    v-for="icon in icons"
                    :key="icon.name"
                    type="button"
                    class="flex aspect-square items-center justify-center rounded-md border transition-colors"
                    :class="cellClass(icon.name)"
                    :title="icon.name"
                    @click="pick(icon.name)"
                >
                    <component
                        :is="icon.component"
                        :size="22"
                        stroke-width="1.5"
                    />
                </button>
            </div>
        </template>

        <template v-else>
            <button
                type="button"
                class="flex items-center gap-sm rounded-md border border-outline bg-surface-container px-sm py-sm text-start text-body-md text-on-surface transition-colors hover:border-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                @click="open = !open"
            >
                <component
                    :is="SelectedIcon"
                    class="text-primary"
                    :size="20"
                    stroke-width="1.5"
                />
                <span>{{ modelValue || "Select an icon" }}</span>
            </button>

            <div
                v-if="open"
                class="grid grid-cols-5 gap-xs rounded-lg border border-outline-variant bg-surface-container p-sm sm:grid-cols-6"
            >
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
                    <span class="text-[10px] uppercase tracking-wide">{{
                        icon.name
                    }}</span>
                </button>
            </div>
        </template>
    </div>
</template>
