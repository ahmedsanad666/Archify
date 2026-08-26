<script setup>
import VueEasyLightbox from 'vue-easy-lightbox'

defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    imgs: {
        type: Array,
        default: () => [],
    },
    index: {
        type: Number,
        default: 0,
    },
})

const emit = defineEmits(['close', 'update:index'])

function onHide() {
    emit('close')
}

function onIndexChange(_oldIndex, newIndex) {
    emit('update:index', newIndex)
}
</script>

<template>
    <VueEasyLightbox
        :visible="visible"
        :imgs="imgs"
        :index="index"
        teleport="body"
        @hide="onHide"
        @on-index-change="onIndexChange"
    />
</template>
