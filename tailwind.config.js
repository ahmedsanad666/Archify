import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                surface: {
                    DEFAULT: '#161310',
                    dim: '#161310',
                    bright: '#3d3835',
                    'container-lowest': '#100e0b',
                    'container-low': '#1e1b18',
                    container: '#221f1c',
                    'container-high': '#2d2926',
                    'container-highest': '#383430',
                },
                'on-surface': '#e9e1db',
                'on-surface-variant': '#d5c3b6',
                'inverse-surface': '#e9e1db',
                'inverse-on-surface': '#34302c',
                outline: '#9e8e81',
                'outline-variant': '#51443a',
                primary: {
                    DEFAULT: '#f9ba7f',
                    container: '#bd854f',
                    fixed: '#ffdcbf',
                    'fixed-dim': '#f9ba7f',
                },
                'on-primary': '#4b2800',
                'on-primary-container': '#412200',
                'inverse-primary': '#835422',
                secondary: {
                    DEFAULT: '#f1bc8c',
                    container: '#66411b',
                },
                'on-secondary': '#492905',
                'on-secondary-container': '#e2ae7f',
                tertiary: {
                    DEFAULT: '#d0c5b6',
                    container: '#998f82',
                },
                'on-tertiary': '#363025',
                'on-tertiary-container': '#2f291f',
                error: {
                    DEFAULT: '#ffb4ab',
                    container: '#93000a',
                },
                'on-error': '#690005',
                'on-error-container': '#ffdad6',
                background: '#161310',
                'on-background': '#e9e1db',
                'surface-variant': '#383430',
            },
            fontFamily: {
                sans: ['Manrope', 'sans-serif'],
            },
            borderRadius: {
                sm: '0.5rem', // 8px — chips, small badges
                DEFAULT: '0.75rem', // 12px — buttons, inputs
                md: '0.75rem', // 12px — buttons, inputs (alias)
                lg: '1rem', // 16px — cards, table containers, modals
                xl: '1.25rem', // 20px — large feature cards, hero panels
                full: '9999px', // avatars, status dots, circular icon buttons only
            },
            spacing: {
                xs: '4px',
                sm: '12px',
                md: '24px',
                lg: '48px',
                xl: '80px',
                gutter: '24px',
                'margin-mobile': '16px',
                'margin-desktop': '64px',
            },
            fontSize: {
                'display-lg': [
                    '64px',
                    { lineHeight: '1.1', letterSpacing: '-0.04em', fontWeight: '700' },
                ],
                'display-md': [
                    '48px',
                    { lineHeight: '1.2', letterSpacing: '-0.03em', fontWeight: '600' },
                ],
                'headline-lg': [
                    '32px',
                    { lineHeight: '1.3', letterSpacing: '-0.02em', fontWeight: '600' },
                ],
                'headline-lg-mobile': [
                    '28px',
                    { lineHeight: '1.3', letterSpacing: '-0.01em', fontWeight: '600' },
                ],
                'body-lg': ['18px', { lineHeight: '1.6', letterSpacing: '0', fontWeight: '400' }],
                'body-md': ['16px', { lineHeight: '1.6', letterSpacing: '0', fontWeight: '400' }],
                'label-lg': [
                    '14px',
                    { lineHeight: '1.4', letterSpacing: '0.05em', fontWeight: '600' },
                ],
                'label-md': [
                    '12px',
                    { lineHeight: '1.4', letterSpacing: '0.02em', fontWeight: '500' },
                ],
            },
        },
    },

    plugins: [forms],
};
