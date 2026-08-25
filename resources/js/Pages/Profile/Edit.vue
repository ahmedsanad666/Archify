<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <AdminLayout title="Profile">
        <Head title="Profile" />

        <div class="mb-xl">
            <h2 class="mb-xs text-display-md text-on-surface">Profile</h2>
            <p class="text-body-md text-on-surface-variant">
                Manage your account details and password.
            </p>
        </div>

        <div class="space-y-md">
            <div
                class="rounded-lg border border-outline-variant bg-surface-container p-md md:p-lg"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div
                class="rounded-lg border border-outline-variant bg-surface-container p-md md:p-lg"
            >
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div
                class="rounded-lg border border-outline-variant bg-surface-container p-md md:p-lg"
            >
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AdminLayout>
</template>
