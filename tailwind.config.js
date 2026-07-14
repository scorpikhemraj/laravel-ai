import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
                display: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                surface: {
                    900: '#08090c',
                    800: '#0c0d12',
                    700: '#0f1117',
                    600: '#13151c',
                    500: '#1a1d26',
                },
                brand: {
                    DEFAULT: '#f59e0b',
                    light: '#fbbf24',
                    dark: '#d97706',
                },
                accent: {
                    DEFAULT: '#06b6d4',
                    light: '#22d3ee',
                    dark: '#0891b2',
                },
            },
        },
    },

    plugins: [forms, typography],
};
