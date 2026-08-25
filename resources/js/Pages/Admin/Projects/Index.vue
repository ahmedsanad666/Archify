<script setup>
import { Link, router, Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { IconPencil, IconPlus, IconTrash } from "@tabler/icons-vue";

defineProps({
    projects: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const destroy = (id) => {
    if (!confirm("Delete this project?")) {
        return;
    }
    router.delete(route("admin.projects.destroy", id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout title="Projects">
        <Head title="Projects" />

        <div class="mb-xl flex flex-wrap items-end justify-between gap-md">
            <div>
                <h2 class="mb-xs text-display-md text-on-surface">Projects</h2>
                <p class="text-body-md text-on-surface-variant">
                    Manage portfolio projects and media.
                </p>
            </div>
            <Link
                :href="route('admin.projects.create')"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                New project
            </Link>
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
            <div
                v-if="!projects.data?.length"
                class="px-md py-xl text-center"
            >
                <p class="text-body-md text-on-surface-variant">
                    No projects yet. Create your first portfolio project.
                </p>
                <Link
                    :href="route('admin.projects.create')"
                    class="mt-md inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
                >
                    <IconPlus :size="18" stroke-width="1.5" />
                    New project
                </Link>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-start">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Project
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Category
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Client
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Year
                            </th>
                            <th
                                class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                            >
                                Location
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
                            v-for="project in projects.data"
                            :key="project.id"
                            class="border-b border-outline-variant last:border-b-0 hover:bg-surface-container-high"
                        >
                            <td class="px-md py-sm">
                                <div class="flex items-center gap-sm">
                                    <div
                                        class="h-12 w-12 shrink-0 overflow-hidden rounded-md border border-outline-variant bg-surface-container-low"
                                    >
                                        <img
                                            v-if="project.thumbnail_url"
                                            :src="project.thumbnail_url"
                                            :alt="project.name || 'Project'"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <span class="text-body-md text-on-surface">
                                        {{ project.name || "—" }}
                                    </span>
                                </div>
                            </td>
                            <td
                                class="px-md py-sm text-body-md text-on-surface-variant"
                            >
                                {{ project.category?.name || "—" }}
                            </td>
                            <td
                                class="px-md py-sm text-body-md text-on-surface-variant"
                            >
                                {{ project.client_name || "—" }}
                            </td>
                            <td
                                class="px-md py-sm text-body-md text-on-surface-variant"
                            >
                                {{ project.year || "—" }}
                            </td>
                            <td
                                class="px-md py-sm text-body-md text-on-surface-variant"
                            >
                                {{ project.location || "—" }}
                            </td>
                            <td class="px-md py-sm">
                                <div
                                    class="flex items-center justify-end gap-sm"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.projects.edit',
                                                project.id,
                                            )
                                        "
                                        class="text-on-surface-variant transition-colors hover:text-primary"
                                    >
                                        <IconPencil
                                            :size="18"
                                            stroke-width="1.5"
                                        />
                                    </Link>
                                    <button
                                        type="button"
                                        class="text-on-surface-variant transition-colors hover:text-error"
                                        @click="destroy(project.id)"
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
    </AdminLayout>
</template>
