/** @type {import('tailwindcss').Config} */
module.exports = {
content: [
'./*.php',
'./**/*.php',
'./**/*.html',
'./assets/scripts/**/*.js',
],
prefix: 'gn-',
theme: {
extend: {
colors: {
lavender: '#EBDDF9',
lavenderHover: '#D9C4F3',
gold: '#D4AF37',
goldHover: '#B8952D',
charcoal: '#1C1C1C',
},
borderRadius: {
'3xl': '1.5rem',
'full': '9999px',
},
boxShadow: {
soft: '0 10px 25px -10px rgba(0,0,0,0.15)',
},
},
},
corePlugins: {
container: false,
},
};
