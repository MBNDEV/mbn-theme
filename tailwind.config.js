/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './inc/**/*.php',
    './src/**/*.php',
    './src/**/*.js',
    './template-parts/**/*.php',
    './page-templates/**/*.php',
    './assets/js/**/*.js',
    './resources/**/*.css',
  ],
  theme: {
    extend: {
      colors: {
        'scheme-1': 'var(--mbn-color-scheme-1)',
        'scheme-2': 'var(--mbn-color-scheme-2)',
        'scheme-3': 'var(--mbn-color-scheme-3)',
        'scheme-4': 'var(--mbn-color-scheme-4)',
        'scheme-5': 'var(--mbn-color-scheme-5)',
        'scheme-6': 'var(--mbn-color-scheme-6)',
        'scheme-7': 'var(--mbn-color-scheme-7)',
        'scheme-8': 'var(--mbn-color-scheme-8)',
        'mbn-primary': 'var(--mbn-color-primary, var(--mbn-color-scheme-1))',
        'mbn-secondary': 'var(--mbn-color-secondary, var(--mbn-color-scheme-2))',
      },
      fontFamily: {
        primary: 'var(--mbn-font-primary)',
        secondary: 'var(--mbn-font-secondary)',
      },
      fontSize: {
        'mbn-h1': 'var(--mbn-size-h1)',
        'mbn-h2': 'var(--mbn-size-h2)',
        'mbn-h3': 'var(--mbn-size-h3)',
        'mbn-h4': 'var(--mbn-size-h4)',
        'mbn-h5': 'var(--mbn-size-h5)',
        'mbn-h6': 'var(--mbn-size-h6)',
        'mbn-body': 'var(--mbn-size-body)',
      },
      borderRadius: {
        mbn: 'var(--mbn-radius)',
      },
      maxWidth: {
        'mbn-container': 'var(--mbn-container-width)',
      },
    },
  },
  // Scheme colors / fonts are used from post_content (which Tailwind does not
  // scan), so keep their utilities (+ common variants) always compiled.
  safelist: [
    {
      pattern: /^(bg|text|border)-scheme-[1-8]$/,
      variants: [ 'hover', 'focus', 'group-hover', 'md', 'lg' ],
    },
    {
      pattern: /^(bg|text|border)-(mbn-primary|mbn-secondary)$/,
      variants: [ 'hover', 'focus', 'group-hover' ],
    },
    {
      pattern: /^(font-primary|font-secondary)$/,
    },
    {
      pattern: /^text-mbn-(h[1-6]|body)$/,
    },
    'rounded-mbn',
    'max-w-mbn-container',
  ],
  plugins: [],
};
