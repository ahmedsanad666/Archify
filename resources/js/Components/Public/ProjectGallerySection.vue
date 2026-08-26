<script setup>
import { computed, ref } from 'vue'
import ImageLightbox from '@/Components/Public/ImageLightbox.vue'

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    images: {
        type: Array,
        default: () => [],
    },
})

const lightboxVisible = ref(false)
const lightboxIndex = ref(0)

const urls = computed(() =>
    (props.images ?? [])
        .map((item) => item?.url)
        .filter((url) => typeof url === 'string' && url !== ''),
)

function openAt(index) {
    lightboxIndex.value = index
    lightboxVisible.value = true
}

function closeLightbox() {
    lightboxVisible.value = false
}
</script>

<template>
    <section
        v-if="urls.length"
        class="flex flex-col gap-lg"
    >
        <div class="flex items-center gap-md">
            <h2
                class="shrink-0 text-label-lg uppercase tracking-widest text-primary"
            >
                {{ title }}
            </h2>
            <div class="h-px flex-grow bg-outline-variant" />
        </div>

        <div class="grid grid-cols-1 gap-gutter md:grid-cols-3">
            <button
                v-for="(image, index) in images"
                :key="image.id ?? image.url ?? index"
                type="button"
                class="group aspect-[4/3] border border-outline-variant bg-surface-container p-sm text-start transition-colors duration-300 hover:border-primary"
                @click="openAt(index)"
            >
                <img
                    v-if="image.url"
                    :src="image.url"
                    :alt="title"
                    class="h-full w-full object-contain opacity-80 transition-opacity group-hover:opacity-100"
                    loading="lazy"
                />
            </button>
        </div>

        <ImageLightbox
            :visible="lightboxVisible"
            :imgs="urls"
            :index="lightboxIndex"
            @close="closeLightbox"
            @update:index="(value) => (lightboxIndex = value)"
        />
    </section>
</template>
