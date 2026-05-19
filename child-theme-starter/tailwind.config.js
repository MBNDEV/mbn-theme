/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./blocks/**/*.{js,jsx,ts,tsx,php}',
		'./template-parts/**/*.php',
		'./page-templates/**/*.php',
		'./*.php',
	],
	theme: {
		extend: {
			colors: {
				// Add your custom colors here
				// 'brand-primary': '#0066cc',
				// 'brand-secondary': '#ff6600',
			},
			fontFamily: {
				// Add custom fonts here
				// 'heading': ['Your Heading Font', 'sans-serif'],
				// 'body': ['Your Body Font', 'sans-serif'],
			},
		},
	},
	plugins: [],
};
