<script setup>
import { computed, ref } from 'vue';
import {
    IconBuildingArch,
    IconBrush,
    IconHome,
    IconLamp,
    IconLayout,
    IconPencil,
    IconRuler,
    IconSofa,
    IconTools,
    IconWorld,
} from '@tabler/icons-vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);

const icons = [
    { name: 'home', component: IconHome },
    { name: 'building-arch', component: IconBuildingArch },
    { name: 'sofa', component: IconSofa },
    { name: 'lamp', component: IconLamp },
    { name: 'ruler', component: IconRuler },
    { name: 'pencil', component: IconPencil },
    { name: 'brush', component: IconBrush },
    { name: 'layout', component: IconLayout },
    { name: 'tools', component: IconTools },
    { name: 'world', component: IconWorld },
];

const selected = computed(
    () => icons.find((icon) => icon.name === props.modelValue) ?? null,
);

const pick = (name) => {
    emit('update:modelValue', name);
    open.value = false;
};
</script>

<template>
    <div class="flex flex-col gap-xs">
        <span class="text-label-md uppercase tracking-wide text-on-surface">
            Icon
        </span>
        <button
            type="button"
            class="flex items-center gap-sm rounded-md border border-outline bg-surface-container px-sm py-sm text-start text-body-md text-on-surface transition-colors hover:border-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
            @click="open = !open"
        >
            <component
                :is="selected?.component ?? IconLayout"
                class="text-primary"
                :size="20"
                stroke-width="1.5"
            />
            <span>{{ modelValue || 'Select an icon' }}</span>
        </button>

        <div
            v-if="open"
            class="grid grid-cols-5 gap-xs rounded-lg border border-outline-variant bg-surface-container p-sm"
        >
            <button
                v-for="icon in icons"
                :key="icon.name"
                type="button"
                class="flex flex-col items-center gap-1 rounded-md p-sm text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary"
                :class="{
                    'bg-primary/15 text-primary': modelValue === icon.name,
                }"
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
    </div>
</template>
