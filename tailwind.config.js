/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './blocks/**/*.php',
    './blocks/**/*.js',
    './blocks/**/*.jsx',
    './blocks/**/*.css',
    './template-parts/**/*.php',
    './resources/**/*.css',
  ],
  theme: {
    extend: {
      colors: {
        cream: '#F9F5EE',
        'cream-light': '#FFF6E5',
        'gold-light': '#FCE5B0',
        gold: '#B89352',
        'gold-dark': '#6B4502',
        'dark-text': '#25272B',
        'footer-bg': '#191919',
        'paragraph-gray': '#B2B2B2',
        'card-cream': '#F5F1E8',
        'card-gold': '#FFF4D9',
        'card-beige': '#F8F5F0',
        'check-green': '#7CAA6D',
        'divider-gold': '#CEB270',
        'card-label': '#3A3A3A',
        'intro-bg': '#EFEBE3',
        'mission-text': 'rgba(0, 0, 0, 0.20)',
      },
      fontFamily: {
        sofia: ['"Sofia Sans"', 'sans-serif'],
        poppins: ['"Poppins"', 'sans-serif'],
        inter: ['"Inter"', 'sans-serif'],
      },
      letterSpacing: {
        'hero': '-0.74px',        // For h1 hero headings
        'heading': '-0.56px',     // For large h2 headings
        'subheading': '-0.28px',  // For medium h4 subheadings
        'body': '-0.18px',        // For body text/paragraphs
        'mission': '-0.4px',      // For mission section text
        'display': '-0.8px',      // For extra large display text
        'label': '-0.24px',       // For small labels
        'title': '-0.32px',       // For medium titles
      },
    },
  },
  plugins: [],
};
