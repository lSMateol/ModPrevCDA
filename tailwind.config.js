import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            // Mantenemos la fuente Figtree como predeterminada (sans)
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Añadimos las fuentes personalizadas para los títulos y cuerpo
                headline: ['Manrope', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
                label: ['Inter', 'sans-serif'],
            },
            // Colores de la paleta del prototipo
            colors: {
                "secondary-fixed": "#d3e4ff",
                "primary-fixed": "#ffdea8",
                "on-tertiary": "#ffffff",
                "error-container": "#ffdad6",
                "surface-container-highest": "#e0e3e5",
                "surface-container": "#ebeef0",
                "on-primary-fixed": "#271900",
                "outline-variant": "#c3c6cf",
                "on-tertiary-container": "#6b95d4",
                "on-primary": "#ffffff",
                "error": "#ba1a1a",
                "on-tertiary-fixed-variant": "#124782",
                "on-secondary-container": "#365881",
                "on-background": "#181c1e",
                "background": "#f7fafc",
                "on-surface-variant": "#43474e",
                "on-secondary-fixed": "#001c38",
                "on-error": "#ffffff",
                "on-tertiary-fixed": "#001c3b",
                "on-error-container": "#93000a",
                "outline": "#73777f",
                "secondary-fixed-dim": "#a7c9f8",
                "surface-variant": "#e0e3e5",
                "tertiary-container": "#002c59",
                "surface-dim": "#d7dadc",
                "surface-container-lowest": "#ffffff",
                "surface-bright": "#f7fafc",
                "on-secondary": "#ffffff",
                "inverse-surface": "#2d3133",
                "primary-fixed-dim": "#ffba20",
                "on-secondary-fixed-variant": "#254870",
                "on-primary-container": "#c08a00",
                "tertiary": "#001834",
                "secondary-container": "#adcefe",
                "surface-container-low": "#f1f4f6",
                "inverse-on-surface": "#eef1f3",
                "surface": "#f7fafc",
                "on-surface": "#181c1e",
                "surface-container-high": "#e5e9eb",
                "secondary": "#3f608a",
                "tertiary-fixed-dim": "#a7c8ff",
                "primary-container": "#3c2900",
                "on-primary-fixed-variant": "#5e4200",
                "surface-tint": "#7c5800",
                "primary": "#221500",
                "tertiary-fixed": "#d5e3ff",
                "inverse-primary": "#ffba20"
            },
            borderRadius: {
                DEFAULT: "0.125rem",
                lg: "0.25rem",
                xl: "0.5rem",
                full: "0.75rem"
            }
        },
    },
    plugins: [forms],
};