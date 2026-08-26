<script setup>
import { DialogTitle } from '@headlessui/vue';
import { IconX } from '@tabler/icons-vue';
import { useUiTranslations } from '@/Composables/useUiTranslations';

defineProps({
    title: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const { t } = useUiTranslations();

const close = () => {
    emit('close');
};
</script>

<template>
    <div
        class="sticky top-0 z-10 border-b border-outline-variant bg-surface-container-low px-md py-sm"
    >
        <DialogTitle
            class="px-8 text-center text-body-lg font-semibold text-on-surface"
        >
            {{ title }}
        </DialogTitle>
        <button
            type="button"
            class="absolute end-md top-1/2 -translate-y-1/2 rounded-md p-1 text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
            :disabled="disabled"
            @click="close"
        >
            <IconX
                :size="20"
                stroke-width="1.5"
            />
            <span class="sr-only">{{ t('common.cancel') }}</span>
        </button>
    </div>
</template>
