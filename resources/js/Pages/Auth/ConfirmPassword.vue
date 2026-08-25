<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};

const inputClass =
    'w-full rounded-md border border-outline-variant bg-surface-container-highest px-sm py-sm text-body-md text-on-surface outline-none transition-colors duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20';
</script>

<template>
    <AuthLayout title="Confirm Password">
        <Head title="Confirm Password" />

        <div
            class="flex w-full flex-col gap-md rounded-xl border border-outline-variant bg-surface-container p-lg"
        >
            <div class="mb-md text-center">
                <h2 class="mb-xs text-headline-lg text-on-surface">
                    Confirm password
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    This is a secure area. Please confirm your password before
                    continuing.
                </p>
            </div>

            <form class="flex w-full flex-col gap-md" @submit.prevent="submit">
                <div class="flex flex-col gap-xs">
                    <label
                        for="password"
                        class="text-label-md uppercase tracking-wide text-on-surface-variant"
                    >
                        Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        :class="inputClass"
                    />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <button
                    type="submit"
                    class="mt-sm w-full rounded-md bg-primary py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-200 hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Confirm
                </button>
            </form>
        </div>
    </AuthLayout>
</template>
