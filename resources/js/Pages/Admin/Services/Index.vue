<script setup>
import { Link, router, Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { IconPencil, IconPlus, IconTrash } from '@tabler/icons-vue';

defineProps({
    services: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const destroy = (id) => {
    if (!confirm('Delete this service?')) {
        return;
    }
    router.delete(route('admin.services.destroy', id));
};
</script>

<template>
    <AdminLayout title="Services">
        <Head title="Services" />

        <div class="mb-xl flex flex-wrap items-end justify-between gap-md">
            <div>
                <h2 class="mb-xs text-display-md text-on-surface">Services</h2>
                <p class="text-body-md text-on-surface-variant">
                    Manage consultancy service offerings.
                </p>
            </div>
            <Link
                :href="route('admin.services.create')"
                class="inline-flex items-center gap-xs rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors hover:bg-primary-container hover:text-on-primary-container"
            >
                <IconPlus :size="18" stroke-width="1.5" />
                New service
            </Link>
        </div>

        <div
            v-if="page.props.flash?.success"
            class="mb-md rounded-md bg-primary/15 px-md py-sm text-body-md text-primary"
        >
            {{ page.props.flash.success }}
        </div>

        <div class="overflow-hidden rounded-lg border border-outline-variant">
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
                            Items
                        </th>
                        <th
                            class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                        >
                            Order
                        </th>
                        <th
                            class="px-md py-sm text-label-md uppercase tracking-wide text-on-surface-variant"
                        >
                            Home
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
                        v-for="service in services.data"
                        :key="service.id"
                        class="border-b border-outline-variant last:border-b-0 hover:bg-surface-container"
                    >
                        <td
                            class="px-md py-sm text-label-md text-on-surface-variant"
                        >
                            {{ service.icon || '—' }}
                        </td>
                        <td class="px-md py-sm text-body-md text-on-surface">
                            {{ service.title || '—' }}
                        </td>
                        <td
                            class="px-md py-sm text-body-md text-on-surface-variant"
                        >
                            {{ service.items_count }}
                        </td>
                        <td
                            class="px-md py-sm text-body-md text-on-surface-variant"
                        >
                            {{ service.order }}
                        </td>
                        <td class="px-md py-sm">
                            <span
                                class="rounded-sm px-3 py-1 text-label-md uppercase tracking-wide"
                                :class="
                                    service.show_on_home
                                        ? 'bg-primary/15 text-primary'
                                        : 'bg-surface-container-high text-on-surface-variant'
                                "
                            >
                                {{ service.show_on_home ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-md py-sm">
                            <div class="flex items-center justify-end gap-sm">
                                <Link
                                    :href="
                                        route('admin.services.edit', service.id)
                                    "
                                    class="text-on-surface-variant transition-colors hover:text-primary"
                                >
                                    <IconPencil :size="18" stroke-width="1.5" />
                                </Link>
                                <button
                                    type="button"
                                    class="text-on-surface-variant transition-colors hover:text-error"
                                    @click="destroy(service.id)"
                                >
                                    <IconTrash :size="18" stroke-width="1.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!services.data?.length">
                        <td
                            colspan="6"
                            class="px-md py-lg text-center text-body-md text-on-surface-variant"
                        >
                            No services yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
