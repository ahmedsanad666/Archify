import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const PUBLIC_PATH_TO_ROUTE = {
    '/': 'home',
    '/about': 'about',
    '/team': 'team',
    '/faq': 'faqs.index',
    '/services': 'services.index',
    '/blogs': 'blogs.index',
    '/contact': 'contact',
}

/**
 * Locale-aware helpers for public routes.
 *
 * Named Ziggy routes are always the unprefixed (default-language) URLs.
 * Non-default locales prepend /{code} to match the unnamed prefixed route group.
 */
export function useLocale() {
    const page = usePage()

    const locale = computed(() => page.props.locale ?? null)
    const languages = computed(() => page.props.languages ?? [])
    const localeCode = computed(() => locale.value?.code ?? 'en')
    const isDefaultLocale = computed(() => Boolean(locale.value?.is_default))

    function localized(entity, field, fallback = '') {
        if (!entity?.translations) {
            return fallback
        }

        const code = localeCode.value
        const primary = entity.translations[code]?.[field]
        if (primary !== undefined && primary !== null && primary !== '') {
            return primary
        }

        const english = entity.translations.en?.[field]
        if (english !== undefined && english !== null && english !== '') {
            return english
        }

        const first = Object.values(entity.translations).find(
            (row) => row && row[field],
        )

        return first?.[field] ?? fallback
    }

    function withLocalePrefix(path, language) {
        if (!language || language.is_default) {
            return path || '/'
        }

        if (!path || path === '/') {
            return `/${language.code}`
        }

        return `/${language.code}${path.startsWith('/') ? path : `/${path}`}`
    }

    function stripLocalePrefix(urlPath) {
        const stripped = urlPath.replace(/^\/(tr|ar)(?=\/|$)/, '')

        return stripped === '' ? '/' : stripped
    }

    function currentPublicRouteName() {
        const path = stripLocalePrefix(page.url.split('?')[0] || '/')

        return PUBLIC_PATH_TO_ROUTE[path] ?? 'home'
    }

    /**
     * Build a public URL for the current (or given) locale.
     */
    function localePath(name, params = {}, forLocale = null) {
        const target =
            forLocale ??
            languages.value.find((l) => l.code === localeCode.value) ??
            locale.value

        const path = route(name, params, false)

        return withLocalePrefix(path, target)
    }

    /**
     * Visit the same named route in another locale.
     */
    function switchLocale(code) {
        const target = languages.value.find((l) => l.code === code)
        if (!target || target.code === localeCode.value) {
            return
        }

        router.visit(localePath(currentPublicRouteName(), {}, target), {
            preserveScroll: true,
        })
    }

    return {
        locale,
        languages,
        localeCode,
        isDefaultLocale,
        localized,
        localePath,
        switchLocale,
    }
}
