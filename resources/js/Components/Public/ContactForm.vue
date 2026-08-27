<script setup>
import { computed, ref, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { IconArrowRight, IconBrandWhatsapp, IconCheck } from '@tabler/icons-vue'
import AppInput from '@/Components/Shared/AppInput.vue'
import AppPhoneInput from '@/Components/Shared/AppPhoneInput.vue'
import AppSelect from '@/Components/Shared/AppSelect.vue'
import AppTextarea from '@/Components/Shared/AppTextarea.vue'
import { useLocale } from '@/Composables/useLocale'
import { useUiTranslations } from '@/Composables/useUiTranslations'

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
})

const { t } = useUiTranslations()
const { localized, localePath } = useLocale()
const page = usePage()

const showSuccess = ref(false)

watch(
    () => page.props.flash?.contact_success,
    (value) => {
        if (value) {
            showSuccess.value = true
        }
    },
    { immediate: true },
)

const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    interest: null,
    interest_other: '',
    message: '',
})

const interestOptions = computed(() => {
    const options = props.services.map((service) => ({
        value: String(service.id),
        label: localized(service, 'title') || service.title || `#${service.id}`,
    }))

    options.push({
        value: 'other',
        label: t('public.contact.other'),
    })

    return options
})

const isOther = computed(() => form.interest === 'other')

const whatsappUrl = computed(() => {
    const raw = page.props.siteSettings?.whatsapp
    if (!raw) {
        return null
    }
    const digits = String(raw).replace(/\D/g, '')
    return digits ? `https://wa.me/${digits}` : null
})

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            interest: data.interest || null,
            interest_other: data.interest === 'other' ? data.interest_other : null,
        }))
        .post(localePath('contact.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                showSuccess.value = true
            },
        })
}

const dismissSuccess = () => {
    showSuccess.value = false
}
</script>

<template>
    <div
        class="relative overflow-hidden rounded-lg border border-outline-variant bg-surface-container p-md transition-colors duration-300 hover:border-primary md:p-lg"
    >
        <div
            v-if="showSuccess"
            class="mb-md flex items-start gap-sm rounded-md border border-primary/30 bg-primary/10 p-md text-start"
            role="status"
        >
            <IconCheck
                class="mt-0.5 shrink-0 text-primary"
                :size="20"
                stroke-width="1.5"
            />
            <div class="min-w-0 flex-1">
                <p class="text-label-lg uppercase tracking-wide text-primary">
                    {{ t('public.contact.success_title') }}
                </p>
                <p class="mt-xs text-body-md text-on-surface-variant">
                    {{ t('public.contact.success_body') }}
                </p>
            </div>
            <button
                type="button"
                class="shrink-0 text-label-md uppercase tracking-wide text-on-surface-variant transition-colors hover:text-primary"
                @click="dismissSuccess"
            >
                {{ t('public.contact.dismiss') }}
            </button>
        </div>

        <form
            class="relative z-10 flex flex-col gap-md"
            @submit.prevent="submit"
        >
            <div class="grid grid-cols-1 gap-md md:grid-cols-2">
                <AppInput
                    v-model="form.full_name"
                    :label="t('public.contact.full_name')"
                    :placeholder="t('public.contact.full_name_placeholder')"
                    autocomplete="name"
                    :error="form.errors.full_name"
                    :disabled="form.processing"
                />
                <AppInput
                    v-model="form.email"
                    type="email"
                    :label="t('public.contact.email')"
                    :placeholder="t('public.contact.email_placeholder')"
                    autocomplete="email"
                    :error="form.errors.email"
                    :disabled="form.processing"
                />
            </div>

            <div class="grid grid-cols-1 gap-md md:grid-cols-2">
                <AppPhoneInput
                    v-model="form.phone"
                    :label="t('public.contact.phone')"
                    :placeholder="t('public.contact.phone_placeholder')"
                    :error="form.errors.phone"
                    :disabled="form.processing"
                />
                <AppSelect
                    v-model="form.interest"
                    :options="interestOptions"
                    :label="t('public.contact.interest')"
                    :placeholder="t('public.contact.interest_placeholder')"
                    :disabled="form.processing"
                />
            </div>

            <p
                v-if="form.errors.interest"
                class="text-start text-label-md text-error"
            >
                {{ form.errors.interest }}
            </p>

            <AppInput
                v-if="isOther"
                v-model="form.interest_other"
                :label="t('public.contact.interest_other')"
                :placeholder="t('public.contact.interest_other_placeholder')"
                :error="form.errors.interest_other"
                :disabled="form.processing"
            />

            <AppTextarea
                v-model="form.message"
                :label="t('public.contact.message')"
                :placeholder="t('public.contact.message_placeholder')"
                :error="form.errors.message"
                :disabled="form.processing"
                :rows="5"
            />

            <div class="mt-sm flex flex-col gap-sm sm:flex-row sm:flex-wrap">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-lg py-sm text-label-lg uppercase tracking-wider text-on-primary transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? t('public.contact.sending')
                            : t('public.contact.send')
                    }}
                    <IconArrowRight
                        class="rtl:rotate-180"
                        :size="18"
                        stroke-width="1.5"
                    />
                </button>

                <a
                    v-if="whatsappUrl"
                    :href="whatsappUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-outline-variant px-lg py-sm text-label-lg uppercase tracking-wider text-on-surface transition-colors hover:border-primary hover:text-primary"
                >
                    <IconBrandWhatsapp
                        :size="18"
                        stroke-width="1.5"
                    />
                    {{ t('public.contact.whatsapp_cta') }}
                </a>
            </div>
        </form>
    </div>
</template>
