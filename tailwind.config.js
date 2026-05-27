/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        'alt-red':     '#dc2626',
        'alt-red-dim': '#b91c1c',
        'brand':       '#FCDB56',
        'brand-dim':   '#f0c932',
        'hero-base':   '#080117',
        'canvas':      '#f5f5f0',
        'surface':     '#ffffff',
        'surface-2':   '#f0f0eb',
        'surface-3':   '#e8e8e3',
        'border':      'rgba(0,0,0,0.07)',
        'border-strong':'rgba(0,0,0,0.14)',
        'text-primary':  '#111111',
        'text-secondary':'#555555',
        'text-muted':    '#9b9b93',
      },
      fontFamily: {
        mono:  ['JetBrains Mono', 'ui-monospace', 'monospace'],
        sans:  ['Inter', 'system-ui', 'sans-serif'],
        serif: ['Lora', 'Georgia', 'serif'],
      },
      borderRadius: {
        '2xl': '16px',
        '3xl': '24px',
      },
      boxShadow: {
        'card':        '0 1px 0 rgba(255,255,255,0.9) inset, 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04)',
        'card-hover':  '0 1px 0 rgba(255,255,255,0.9) inset, 0 4px 12px rgba(0,0,0,0.10), 0 12px 32px rgba(0,0,0,0.07)',
        'neo-inset':   'inset 2px 2px 5px rgba(0,0,0,0.08), inset -2px -2px 5px rgba(255,255,255,0.9)',
        'capsule':     '0 1px 0 rgba(255,255,255,0.8) inset, 0 4px 24px rgba(0,0,0,0.06)',
        'hero-text':   '0 2px 16px rgba(0,0,0,0.5)',
      },
    },
  },
  plugins: [],
};
