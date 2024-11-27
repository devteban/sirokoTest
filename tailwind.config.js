/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        'node_modules/preline/dist/*.js',
    ],
    theme: {
        extend: {},
    },
    safelist: [
        // 'bg-neutral-800',
    ],
    darkMode: 'class',
    plugins: [
        require('preline/plugin'),
    ],
}
