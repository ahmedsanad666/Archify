<script setup>
import { ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ConceptFormPanel from "@/Components/Admin/ConceptFormPanel.vue";
import { resolveAppIcon } from "@/icons/appIcons";
import { IconLayout, IconPlus, IconTrash } from "@tabler/icons-vue";

defineProps({
    concepts: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const panelOpen = ref(false);
const editingConcept = ref(null);

const iconFor = (name) => resolveAppIcon(name, IconLayout);

const openCreate = () => {
    editingConcept.value = null;
    panelOpen.value = true;
};

const openEdit = (concept) => {
    editingConcept.value = concept;
    panelOpen.value = true;
};

const destroy = (id) => {
    if (!confirm("Delete this concept?")) {
        return;
    }
    router.delete(route("admin.concepts.destroy", id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Concepts" />

    <AdminLayout title="Concepts">
        <div class="mb-xl flex flex-wrap items-end justify-between gap-md">
            <div>
                <h1 class="mb-xs text-display-md text-on-surface">Concepts</h1>
                <p class="text-body-md text-on-surface-variant">
                    Tags and themes attached to portfolio projects.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                @click="openCreate"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                Add new concept
            </button>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div
            class="overflow-hidden rounded-lg border border-outline-variant bg-surface-container"
        >
            <div v-if="!concepts.length" class="px-md py-xl text-center">
                <p class="text-body-md text-on-surface-variant">
                    No concepts yet. Add your first tag for project pages.
                </p>
                <button
                    type="button"
                    class="mt-md inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary"
                    @click="openCreate"
                >
                    <IconPlus :size="18" stroke-width="1.5" />
                    Add new concept
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Icon
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Title
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Short description
                            </th>
                            <th
                                class="px-md py-sm text-end text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="concept in concepts"
                            :key="concept.id"
                            class="border-b border-outline-variant last:border-b-0 hover:bg-surface-container-high"
                        >
                            <td class="px-md py-sm">
                                <component
                                    :is="iconFor(concept.icon)"
                                    class="text-primary"
                                    :size="22"
                                    stroke-width="1.5"
                                />
                            </td>
                            <td class="px-md py-sm text-body-md text-on-surface">
                                {{ concept.title || "—" }}
                            </td>
                            <td
                                class="max-w-md px-md py-sm text-body-md text-on-surface-variant"
                            >
                                {{ concept.short_description || "—" }}
                            </td>
                            <td class="px-md py-sm">
                                <div
                                    class="flex items-center justify-end gap-sm"
                                >
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-primary"
                                        @click="openEdit(concept)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-error"
                                        @click="destroy(concept.id)"
                                    >
                                        <IconTrash
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ConceptFormPanel
            v-model:open="panelOpen"
            :concept="editingConcept"
        />
    </AdminLayout>
</template>
