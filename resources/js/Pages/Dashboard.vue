<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AdminLayout title="Dashboard">
        <Head title="Dashboard" />

        <div class="mb-xl">
            <p
                class="mb-xs flex items-center gap-xs text-label-md uppercase tracking-wide text-tertiary"
            >
                / Dashboard
            </p>
            <h2
                class="mb-xs text-display-md text-on-surface md:text-display-lg"
            >
                Overview
            </h2>
            <p class="text-body-md text-on-surface-variant">
                Admin shell is ready. Charts and CMS modules land in later
                phases.
            </p>
        </div>

        <div
            class="rounded-lg border border-outline-variant bg-surface-container p-md"
        >
            <p class="text-body-md text-on-surface">You're logged in.</p>
        </div>
    </AdminLayout>
</template>
