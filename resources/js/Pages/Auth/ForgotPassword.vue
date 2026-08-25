<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};

const inputClass =
    'rounded-md border border-outline-variant bg-surface-container-highest px-sm py-sm text-body-md text-on-surface outline-none transition-colors duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20';
</script>

<template>
    <AuthLayout title="Forgot Password">
        <Head title="Forgot Password" />

        <div
            class="flex w-full flex-col gap-md rounded-xl border border-outline-variant bg-surface-container p-lg"
        >
            <div class="mb-md text-center">
                <h2 class="mb-xs text-headline-lg text-on-surface">
                    Forgot password?
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    Enter your email and we will send a reset link.
                </p>
            </div>

            <div
                v-if="status"
                class="text-sm font-medium text-primary"
            >
                {{ status }}
            </div>

            <form class="flex w-full flex-col gap-md" @submit.prevent="submit">
                <div class="flex flex-col gap-xs">
                    <label
                        for="email"
                        class="text-label-md uppercase tracking-wide text-on-surface-variant"
                    >
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        :class="inputClass"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <button
                    type="submit"
                    class="mt-sm w-full rounded-md bg-primary py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-200 hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </button>
            </form>
        </div>
    </AuthLayout>
</template>
