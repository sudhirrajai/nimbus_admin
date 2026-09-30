import defaultTheme from 'tailwindcss/defaultTheme';
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
            fontFamily: {
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                rook: {
                    page: 'var(--page, #0b100d)',
                    surface: 'var(--surface, #101713)',
                    'surface-raised': 'var(--surface-raised, #151e19)',
                    'surface-deep': 'var(--surface-deep, #0b100d)',
                    line: 'var(--line, #27352d)',
                    'line-strong': 'var(--line-strong, #34483b)',
                    green: 'var(--green, #87d7a0)',
                    'green-bright': 'var(--green-bright, #a0e7b4)',
                    'green-ink': 'var(--green-ink, #112218)',
                    ink: 'var(--ink, #e7eee9)',
                    'ink-soft': 'var(--ink-soft, #b1beb7)',
                    'ink-muted': 'var(--ink-muted, #85948b)',
                },
            },
            borderRadius: {
                'rook': 'var(--radius, 14px)',
            },
        },
    },

    plugins: [forms],
};
