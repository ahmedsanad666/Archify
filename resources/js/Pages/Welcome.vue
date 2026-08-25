<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <AppLayout title="Welcome">
        <Head title="Welcome" />

        <section
            class="mx-auto flex min-h-[60vh] max-w-[1440px] flex-col justify-center px-margin-mobile py-xl md:px-margin-desktop"
        >
            <p
                class="mb-sm text-label-md uppercase tracking-wide text-on-surface-variant"
            >
                Layout shell
            </p>
            <h1 class="mb-md text-display-md text-on-surface md:text-display-lg">
                Public chrome is live
            </h1>
            <p class="mb-lg max-w-xl text-body-lg text-on-surface-variant">
                Navbar and Footer are wired from the Theme. Page body content
                (hero, portfolio, etc.) comes in later phases.
            </p>
            <div class="flex flex-wrap gap-sm">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-md bg-primary px-md py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-200 hover:bg-primary-container hover:text-on-primary-container"
                >
                    Admin login
                </Link>
                <Link
                    v-if="canLogin"
                    :href="route('admin.dashboard')"
                    class="rounded-md border border-outline-variant bg-transparent px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors duration-200 hover:bg-surface-container-high"
                >
                    Dashboard
                </Link>
            </div>
            <p class="mt-lg text-label-md text-on-surface-variant">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </p>
        </section>
    </AppLayout>
</template>
