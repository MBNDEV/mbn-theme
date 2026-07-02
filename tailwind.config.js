/**
 * Runtime-built arbitrary-value safelist.
 *
 * Some block render.php files build Tailwind classes by concatenating *dynamic*
 * values (a thickness, an opacity, a design-token hex). Tailwind only emits an
 * arbitrary value (`bg-[#e50b07]`, `h-[3px]`) when it sees the EXACT literal in a
 * scanned file — and safelist *regex patterns* do NOT match arbitrary values. So we
 * enumerate the bounded ranges + the design palette here as explicit class strings
 * (which the safelist DOES compile), guaranteeing every runtime-built arbitrary value
 * resolves. Add a hex to MBN_ARBITRARY_PALETTE and it works everywhere automatically.
 */
const MBN_ARBITRARY_PALETTE = [
  '#006dab', '#084c74', '#082f49', '#00a9c8', '#f8fcff', '#eaf8fc',
  '#d8ebf2', '#9bc7d7', '#536476', '#748494', '#111827', '#f4c542',
  '#0057a4', '#4cd0fc', '#fca14c', '#000000', '#ffffff',
];
const MBN_COLOR_UTILS = [ 'bg', 'text', 'border', 'from', 'via', 'to', 'fill', 'stroke', 'ring', 'decoration', 'outline' ];

const mbnArbitrarySafelist = ( () => {
  const out = [];
  // Arbitrary colours: <util>-[#hex] for every palette colour.
  MBN_ARBITRARY_PALETTE.forEach( ( hex ) =>
    MBN_COLOR_UTILS.forEach( ( util ) => out.push( `${ util }-[${ hex }]` ) )
  );
  // Arbitrary pixel sizes 1–24px for height/width/inset/translate-ish utilities.
  for ( let i = 1; i <= 24; i++ ) {
    [ 'h', 'w', 'min-h', 'min-w', 'max-w', 'top', 'bottom', 'left', 'right', 'gap', 'rounded', 'border' ].forEach(
      ( u ) => out.push( `${ u }-[${ i }px]` )
    );
  }
  // Arbitrary opacity 0–1 in 0.05 steps.
  for ( let i = 0; i <= 100; i += 5 ) {
    out.push( `opacity-[${ i / 100 }]` );
  }
  return out;
} )();

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
    // Runtime-built arbitrary values from component render.php — enumerated as
    // explicit strings (safelist regex patterns can't match arbitrary values).
    ...mbnArbitrarySafelist,
  ],
  plugins: [],
};
