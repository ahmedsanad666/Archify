<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout title="Email Verification">
        <Head title="Email Verification" />

        <div
            class="flex w-full flex-col gap-md rounded-xl border border-outline-variant bg-surface-container p-lg"
        >
            <div class="mb-md text-center">
                <h2 class="mb-xs text-headline-lg text-on-surface">
                    Verify your email
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    Thanks for signing up! Click the link we emailed you, or
                    resend a new verification email below.
                </p>
            </div>

            <div
                v-if="verificationLinkSent"
                class="text-sm font-medium text-primary"
            >
                A new verification link has been sent to your email address.
            </div>

            <form class="flex flex-col gap-md" @submit.prevent="submit">
                <button
                    type="submit"
                    class="w-full rounded-md bg-primary py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-200 hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-center text-label-md uppercase tracking-wide text-on-surface-variant underline-offset-4 transition-colors hover:text-primary hover:underline"
                >
                    Log Out
                </Link>
            </form>
        </div>
    </AuthLayout>
</template>
