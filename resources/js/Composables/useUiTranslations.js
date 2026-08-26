import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Resolve a dotted key from shared `ui` JSON (lang/{locale}.json).
 *
 * @example t('admin.menu.dashboard') // "Dashboard"
 * @example t('admin.dashboard.welcome', { name: 'Elias' })
 */
export function useUiTranslations() {
    const page = usePage()

    const ui = computed(() => page.props.ui ?? {})

    function t(key, replacementsOrFallback = null) {
        if (!key) {
            return typeof replacementsOrFallback === 'string'
                ? replacementsOrFallback
                : ''
        }

        const value = key
            .split('.')
            .reduce((carry, segment) => {
                if (carry && typeof carry === 'object' && segment in carry) {
                    return carry[segment]
                }

                return undefined
            }, ui.value)

        let result =
            typeof value === 'string'
                ? value
                : typeof replacementsOrFallback === 'string'
                  ? replacementsOrFallback
                  : key

        if (
            typeof result === 'string' &&
            replacementsOrFallback &&
            typeof replacementsOrFallback === 'object'
        ) {
            result = result.replace(/\{(\w+)\}/g, (_, name) => {
                const replacement = replacementsOrFallback[name]
                return replacement === undefined || replacement === null
                    ? `{${name}}`
                    : String(replacement)
            })
        }

        return result
    }

    return { t, ui }
}
