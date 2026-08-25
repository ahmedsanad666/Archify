<script setup>
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    IconArrowRight,
    IconEye,
    IconEyeOff,
} from '@tabler/icons-vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout title="Log in">
        <Head title="Log in" />

        <div
            class="flex w-full flex-col gap-md rounded-xl border border-outline-variant bg-surface-container p-lg"
        >
            <div class="mb-md text-center">
                <h2 class="mb-xs text-headline-lg text-on-surface">
                    Welcome back
                </h2>
                <p class="text-body-md text-on-surface-variant">
                    Sign in to manage your website
                </p>
            </div>

            <div
                v-if="status"
                class="mb-2 text-sm font-medium text-primary"
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
                        placeholder="admin@archify.com"
                        class="rounded-md border border-outline-variant bg-surface-container-highest px-sm py-sm text-body-md text-on-surface outline-none transition-colors duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20"
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
                    <div class="relative w-full">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-md border border-outline-variant bg-surface-container-highest px-sm py-sm pe-12 text-body-md text-on-surface outline-none transition-colors duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 end-0 flex items-center justify-center px-sm text-on-surface-variant transition-colors hover:text-on-surface"
                            aria-label="Toggle password visibility"
                            @click="showPassword = !showPassword"
                        >
                            <IconEyeOff
                                v-if="showPassword"
                                :size="20"
                                stroke-width="1.5"
                            />
                            <IconEye
                                v-else
                                :size="20"
                                stroke-width="1.5"
                            />
                        </button>
                    </div>
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <div class="mt-xs flex w-full items-center justify-between">
                    <label class="group flex cursor-pointer items-center gap-sm">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            name="remember"
                            class="rounded-sm border-outline-variant bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-surface"
                        />
                        <span
                            class="text-label-md uppercase tracking-wide text-on-surface-variant transition-colors group-hover:text-on-surface"
                        >
                            Remember me
                        </span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-label-md uppercase tracking-wide text-on-surface-variant underline-offset-4 transition-colors hover:text-primary hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <button
                    type="submit"
                    class="group relative mt-sm flex w-full items-center justify-center gap-sm overflow-hidden rounded-md bg-primary py-sm text-label-lg uppercase tracking-wide text-on-primary transition-all duration-200 hover:bg-primary-container hover:text-on-primary-container disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-white/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    />
                    <span>Sign in</span>
                    <IconArrowRight :size="18" stroke-width="1.5" />
                </button>
            </form>
        </div>
    </AuthLayout>
</template>
