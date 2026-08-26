<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useUiTranslations } from '@/Composables/useUiTranslations';

const props = defineProps({
    meta: {
        type: Object,
        default: null,
    },
    preserveScroll: {
        type: Boolean,
        default: true,
    },
});

const { t } = useUiTranslations();

const links = computed(() => {
    const items = props.meta?.links ?? [];

    return items.filter((link) => {
        const label = String(link.label ?? '')
            .replace(/&laquo;|&raquo;/gi, '')
            .trim()
            .toLowerCase();

        if (label.includes('previous') || label.includes('next')) {
            return false;
        }

        return true;
    });
});

const prevUrl = computed(() => {
    const first = props.meta?.links?.[0];
    return first?.url ?? null;
});

const nextUrl = computed(() => {
    const all = props.meta?.links ?? [];
    const last = all[all.length - 1];
    return last?.url ?? null;
});

const show = computed(() => (props.meta?.last_page ?? 1) > 1);

const go = (url) => {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveScroll: props.preserveScroll,
            preserveState: true,
        },
    );
};

const isEllipsis = (label) => String(label ?? '').includes('...');
</script>

<template>
    <nav
        v-if="show"
        class="rounded-md border border-outline-variant bg-surface-container px-md py-sm"
        aria-label="Pagination"
    >
        <div class="flex flex-wrap items-center justify-start gap-md">
            <button
                type="button"
                class="text-label-md tracking-wide transition-colors"
                :class="
                    prevUrl
                        ? 'text-on-surface hover:text-primary'
                        : 'cursor-not-allowed text-on-surface-variant/50'
                "
                :disabled="!prevUrl"
                @click="go(prevUrl)"
            >
                {{ t('common.previous_page') }}
            </button>

            <template
                v-for="(link, index) in links"
                :key="`page-${index}-${link.label}`"
            >
                <span
                    v-if="isEllipsis(link.label)"
                    class="px-1 text-label-md text-on-surface-variant"
                >
                    …
                </span>
                <button
                    v-else
                    type="button"
                    class="min-w-8 rounded-md px-2 py-1 text-center text-label-md transition-colors"
                    :class="
                        link.active
                            ? 'bg-primary text-on-primary'
                            : 'text-on-surface hover:text-primary'
                    "
                    :disabled="!link.url || link.active"
                    @click="go(link.url)"
                >
                    {{ link.label }}
                </button>
            </template>

            <button
                type="button"
                class="text-label-md tracking-wide transition-colors"
                :class="
                    nextUrl
                        ? 'text-on-surface hover:text-primary'
                        : 'cursor-not-allowed text-on-surface-variant/50'
                "
                :disabled="!nextUrl"
                @click="go(nextUrl)"
            >
                {{ t('common.next_page') }}
            </button>
        </div>
    </nav>
</template>
