/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Noto Sans Arabic', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
