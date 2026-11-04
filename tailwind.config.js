// PRT362S — Eben Supply | Group KN3
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // Flux component templates: flux.css declares them with Tailwind 4's
        // @source, which Tailwind 3 ignores, so list them here or a build on
        // an empty view cache (Docker, a fresh clone) misses their classes.
        // Only the components the views use (button, dropdown, input, menu,
        // toast) and the shared parts they render.
        './vendor/livewire/flux/stubs/resources/views/flux/{button,input,menu,toast,icon}/**/*.blade.php',
        './vendor/livewire/flux/stubs/resources/views/flux/{button-or-link,button-or-div,button-or-link-pure,dropdown,separator,with-field,with-tooltip,field,label,error,description,tooltip}.blade.php',
        './vendor/livewire/flux/stubs/resources/views/flux/tooltip/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    safelist: [
        // Order status badges (dynamically injected)
        'bg-amber-100',  'text-amber-800',
        'bg-blue-100',   'text-blue-800',
        'bg-green-100',  'text-green-800',
        'bg-stone-100',  'text-stone-600',
        // Category badges
        'bg-rose-50',    'text-rose-700',
        'bg-sky-50',     'text-sky-700',
        'bg-emerald-50', 'text-emerald-700',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Roboto', 'Open Sans', ...defaultTheme.fontFamily.sans],
                heading: ['Montserrat', 'Poppins', ...defaultTheme.fontFamily.sans],
                accent:  ['Playfair Display', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    charcoal: '#333333',
                    gray:     '#F5F5F5',
                    beige:    '#D4C7B0',
                    olive:    '#A3A380',
                    white:    '#FFFFFF',
                },
            },
            borderRadius: {
                DEFAULT: '0.5rem',
                xl:      '1rem',
                '2xl':   '1.5rem',
            },
            boxShadow: {
                soft:  '0 2px 12px 0 rgba(51,51,51,0.07)',
                card:  '0 4px 24px 0 rgba(51,51,51,0.09)',
                hover: '0 8px 32px 0 rgba(51,51,51,0.13)',
            },
        },
    },

    plugins: [forms],
};