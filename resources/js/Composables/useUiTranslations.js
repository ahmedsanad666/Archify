import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Resolve a dotted key from shared `ui` JSON (lang/{locale}.json).
 *
 * @example t('admin.menu.dashboard') // "Dashboard"
 */
export function useUiTranslations() {
    const page = usePage()

    const ui = computed(() => page.props.ui ?? {})

    function t(key, fallback = null) {
        if (!key) {
            return fallback ?? ''
        }

        const value = key
            .split('.')
            .reduce((carry, segment) => {
                if (carry && typeof carry === 'object' && segment in carry) {
                    return carry[segment]
                }

                return undefined
            }, ui.value)

        if (typeof value === 'string') {
            return value
        }

        return fallback ?? key
    }

    return { t, ui }
}
