<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const inputClass =
    'w-full rounded-md border border-outline-variant bg-surface-container-highest px-sm py-sm text-body-md text-on-surface outline-none transition-colors duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20';
</script>

<template>
    <AuthLayout title="Reset Password">
        <Head title="Reset Password" />

        <div
            class="flex w-full flex-col gap-md rounded-xl border border-outline-variant bg-surface-container p-lg"
        >
            <div class="mb-md text-center">
                <h2 class="mb-xs text-headline-lg text-on-surface">
                    Reset password
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    Choose a new password for your account.
                </p>
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
                        autocomplete="new-password"
                        :class="inputClass"
                    />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <div class="flex flex-col gap-xs">
                    <label
                        for="password_confirmation"
                        class="text-label-md uppercase tracking-wide text-on-surface-variant"
                    >
                        Confirm Password
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        :class="inputClass"
                    />
                    <InputError
                        class="mt-1"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <button
                    type="submit"
                    class="mt-sm w-full rounded-md bg-primary py-sm text-label-lg uppercase tracking-wide text-on-primary transition-colors duration-200 hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50"
                    :disabled="form.processing"
                >
                    Reset Password
                </button>
            </form>
        </div>
    </AuthLayout>
</template>
