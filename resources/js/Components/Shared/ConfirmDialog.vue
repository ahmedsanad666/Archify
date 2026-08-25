<script setup>
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconAlertTriangle } from '@tabler/icons-vue';
import { useConfirm } from '@/Composables/useConfirm';

const { state, accept, dismiss } = useConfirm();
const confirmButtonRef = ref(null);

const isDanger = computed(() => state.variant === 'danger');

const panelAccentClass = computed(() =>
    isDanger.value ? 'border-t-error' : 'border-t-primary',
);

watch(
    () => state.open,
    (open) => {
        if (open) {
            requestAnimationFrame(() => {
                confirmButtonRef.value?.focus();
            });
        }
    },
);
</script>

<template>
    <TransitionRoot appear :show="state.open" as="template">
        <Dialog as="div" class="relative z-[100]" @close="dismiss">
            <TransitionChild
                as="template"
                enter="duration-200 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-150 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-surface/80 backdrop-blur-sm"
                    aria-hidden="true"
                />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <TransitionChild
                        as="template"
                        enter="duration-200 ease-out"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="duration-150 ease-in"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="relative w-full max-w-md transform overflow-hidden rounded-xl border border-outline-variant bg-surface-container text-start shadow-none transition-all sm:my-8"
                            :class="['border-t-4', panelAccentClass]"
                        >
                            <div class="px-md py-lg sm:p-lg">
                                <div class="mb-md flex items-start gap-sm">
                                    <div
                                        v-if="isDanger"
                                        class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-md bg-error/10 text-error"
                                    >
                                        <IconAlertTriangle
                                            :size="22"
                                            stroke-width="1.5"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <DialogTitle
                                            class="text-headline-lg-mobile font-semibold tracking-tight text-on-surface md:text-headline-lg"
                                        >
                                            {{ state.title }}
                                        </DialogTitle>
                                        <p
                                            v-if="state.message"
                                            class="mt-sm text-body-md text-on-surface-variant"
                                        >
                                            {{ state.message }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col-reverse gap-sm sm:flex-row sm:justify-end"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-md border border-outline-variant px-md py-sm text-label-lg uppercase tracking-wide text-on-surface transition-colors hover:bg-surface-container-high sm:w-auto"
                                        @click="dismiss"
                                    >
                                        {{ state.cancelLabel }}
                                    </button>
                                    <button
                                        ref="confirmButtonRef"
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-md px-md py-sm text-label-lg uppercase tracking-wide transition-colors sm:w-auto"
                                        :class="
                                            isDanger
                                                ? 'border border-error/40 text-error hover:bg-error/10'
                                                : 'bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container'
                                        "
                                        @click="accept"
                                    >
                                        {{ state.confirmLabel }}
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
