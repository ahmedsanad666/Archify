import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Mirror SiteSettingService::documentSeo() for Inertia <Head> after SPA navigations.
 *
 * @param {{ pageTitle?: string }} [options]
 */
export function useSiteSeo(options = {}) {
    const page = usePage()

    const settings = computed(() => page.props.siteSettings ?? {})

    const siteName = computed(
        () => settings.value?.name || import.meta.env.VITE_APP_NAME || 'Archify',
    )
    const slogan = computed(() => settings.value?.slogan || '')
    const titleTemplate = computed(() =>
        (settings.value?.seo?.title_template ?? settings.value?.meta_title ?? '').trim(),
    )
    const description = computed(() =>
        (
            settings.value?.seo?.description ??
            settings.value?.meta_description ??
            ''
        ).trim(),
    )
    const keywords = computed(() =>
        (settings.value?.seo?.keywords ?? settings.value?.meta_keywords ?? '').trim(),
    )

    const pageTitle = computed(() => (options.pageTitle ?? '').trim())

    const resolveTitleTemplate = (template, replacements) => {
        const map = {
            '%site_name%': replacements.site_name ?? '',
            '%tagline%': replacements.tagline ?? replacements.slogan ?? '',
            '%slogan%': replacements.slogan ?? replacements.tagline ?? '',
            '%page_title%': replacements.page_title ?? '',
        }

        let resolved = template
        for (const [key, value] of Object.entries(map)) {
            resolved = resolved.replaceAll(new RegExp(key, 'gi'), value)
        }

        resolved = resolved.replace(/%[a-z0-9_]+%/gi, '')
        resolved = resolved.replace(/\s+/g, ' ')
        resolved = resolved.replace(/\s*[|\-–—]\s*[|\-–—]+\s*/gu, ' | ')
        resolved = resolved.replace(/^[\s|\-–—]+|[\s|\-–—]+$/gu, '')

        return resolved.trim()
    }

    const title = computed(() => {
        const name = siteName.value
        const tag = slogan.value
        const template = titleTemplate.value
        const short = pageTitle.value
        const merge = {
            site_name: name,
            tagline: tag,
            slogan: tag,
            page_title: short,
        }

        let resolved = ''

        if (template) {
            if (short && !template.toLowerCase().includes('%page_title%')) {
                resolved = `${short} - ${name}`.trim()
            } else {
                resolved = resolveTitleTemplate(template, merge)
            }
        } else if (short) {
            resolved = `${short} - ${name}`.trim()
        } else if (tag) {
            resolved = `${name} | ${tag}`.trim()
        } else {
            resolved = name
        }

        return resolved || name
    })

    /** Full document title for <Head>; already includes site name when from template. */
    const headTitle = computed(() => title.value)

    return {
        headTitle,
        title,
        description,
        keywords,
        siteName,
    }
}
