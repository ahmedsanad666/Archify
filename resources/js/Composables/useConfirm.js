import { reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * @typedef {'default' | 'danger'} ConfirmVariant
 */

/**
 * @typedef {Object} ConfirmOptions
 * @property {string} message
 * @property {string} [title]
 * @property {string} [confirmLabel]
 * @property {string} [cancelLabel]
 * @property {ConfirmVariant} [variant]
 */

const state = reactive({
    open: false,
    title: '',
    message: '',
    confirmLabel: '',
    cancelLabel: '',
    variant: 'default',
});

/** @type {((value: boolean) => void) | null} */
let resolveFn = null;

/**
 * @param {Record<string, unknown>} ui
 * @param {string} key
 * @param {string} [fallback]
 */
function translate(ui, key, fallback = '') {
    if (!ui || !key) {
        return fallback;
    }

    const value = key.split('.').reduce((carry, segment) => {
        if (carry && typeof carry === 'object' && segment in carry) {
            return carry[segment];
        }

        return undefined;
    }, ui);

    return typeof value === 'string' ? value : fallback;
}

function reset() {
    state.open = false;
    state.title = '';
    state.message = '';
    state.confirmLabel = '';
    state.cancelLabel = '';
    state.variant = 'default';
    resolveFn = null;
}

function accept() {
    resolveFn?.(true);
    reset();
}

function dismiss() {
    resolveFn?.(false);
    reset();
}

/**
 * @param {ConfirmOptions} options
 * @param {Record<string, unknown>} ui
 * @returns {Promise<boolean>}
 */
function openConfirm(options, ui) {
    if (resolveFn) {
        dismiss();
    }

    const variant = options.variant ?? 'default';
    const isDanger = variant === 'danger';

    state.title =
        options.title
        ?? translate(ui, 'common.confirm_title', 'Are you sure?');
    state.message = options.message ?? '';
    state.confirmLabel =
        options.confirmLabel
        ?? (isDanger
            ? translate(ui, 'common.delete', 'Delete')
            : translate(ui, 'common.confirm', 'Confirm'));
    state.cancelLabel =
        options.cancelLabel
        ?? translate(ui, 'common.cancel', 'Cancel');
    state.variant = variant;
    state.open = true;

    return new Promise((resolve) => {
        resolveFn = resolve;
    });
}

export function useConfirm() {
    const page = usePage();

    /**
     * @param {ConfirmOptions} options
     * @returns {Promise<boolean>}
     */
    function confirm(options) {
        const ui = page.props.ui ?? {};

        return openConfirm(options, ui);
    }

    return {
        state,
        confirm,
        accept,
        dismiss,
    };
}
