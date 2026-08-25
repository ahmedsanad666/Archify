<script setup>
import { onBeforeUnmount, watch } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import {
    IconBold,
    IconItalic,
    IconList,
    IconListNumbers,
    IconH2,
    IconH3,
} from "@tabler/icons-vue";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const editor = useEditor({
    content: props.modelValue || "",
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: "min-h-[12rem] px-sm py-sm text-body-md text-on-surface focus:outline-none",
        },
    },
    onUpdate: ({ editor: instance }) => {
        emit("update:modelValue", instance.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) {
            return;
        }
        const current = editor.value.getHTML();
        if (value !== current) {
            editor.value.commands.setContent(value || "", false);
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const run = (command) => {
    editor.value?.chain().focus()[command]().run();
};

const toggleHeading = (level) => {
    editor.value?.chain().focus().toggleHeading({ level }).run();
};

const isActive = (name, attrs = {}) =>
    editor.value?.isActive(name, attrs) ?? false;

const toolClass = (active) =>
    active
        ? "rounded-sm bg-primary px-2 py-1 text-on-primary"
        : "rounded-sm px-2 py-1 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface";
</script>

<template>
    <div
        class="overflow-hidden rounded-md border border-outline bg-surface-container"
    >
        <div
            v-if="editor"
            class="flex flex-wrap gap-xs border-b border-outline-variant bg-surface-container-low px-sm py-xs"
        >
            <button
                type="button"
                :class="toolClass(isActive('bold'))"
                title="Bold"
                @click="run('toggleBold')"
            >
                <IconBold :size="16" stroke-width="1.5" />
            </button>
            <button
                type="button"
                :class="toolClass(isActive('italic'))"
                title="Italic"
                @click="run('toggleItalic')"
            >
                <IconItalic :size="16" stroke-width="1.5" />
            </button>
            <button
                type="button"
                :class="toolClass(isActive('heading', { level: 2 }))"
                title="Heading 2"
                @click="toggleHeading(2)"
            >
                <IconH2 :size="16" stroke-width="1.5" />
            </button>
            <button
                type="button"
                :class="toolClass(isActive('heading', { level: 3 }))"
                title="Heading 3"
                @click="toggleHeading(3)"
            >
                <IconH3 :size="16" stroke-width="1.5" />
            </button>
            <button
                type="button"
                :class="toolClass(isActive('bulletList'))"
                title="Bullet list"
                @click="run('toggleBulletList')"
            >
                <IconList :size="16" stroke-width="1.5" />
            </button>
            <button
                type="button"
                :class="toolClass(isActive('orderedList'))"
                title="Ordered list"
                @click="run('toggleOrderedList')"
            >
                <IconListNumbers :size="16" stroke-width="1.5" />
            </button>
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

<style scoped>
:deep(.ProseMirror) {
    min-height: 12rem;
}
:deep(.ProseMirror p) {
    margin-bottom: 0.75rem;
}
:deep(.ProseMirror h2) {
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}
:deep(.ProseMirror h3) {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
}
:deep(.ProseMirror ul) {
    list-style: disc;
    padding-inline-start: 1.25rem;
    margin-bottom: 0.75rem;
}
:deep(.ProseMirror ol) {
    list-style: decimal;
    padding-inline-start: 1.25rem;
    margin-bottom: 0.75rem;
}
:deep(.ProseMirror:focus) {
    outline: none;
}
</style>
