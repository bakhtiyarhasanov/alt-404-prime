<template>
  <router-view />
</template>

<script>
import { onMounted } from 'vue'

export default {
  name: 'App',
  setup() {
    onMounted(() => {
      const savedTheme = localStorage.getItem('admin-theme') || 'midnight'
      document.documentElement.className = `theme-${savedTheme}`
    })
  }
}
</script>

<style>
/* Global styles for modern, dark-themed admin dashboard */
:root, :root.theme-midnight {
  --color-bg: #0b0f19;
  --color-surface: #111827;
  --color-surface-hover: #1f2937;
  --color-border: #1f2937;
  --color-primary: #FCDB56;
  --color-primary-hover: #f0c932;
  --color-text-primary: #f3f4f6;
  --color-text-secondary: #9ca3af;
  --color-text-muted: #6b7280;
  --color-red: #ef4444;
  --color-green: #10b981;
}

:root.theme-light {
  --color-bg: #f9fafb;
  --color-surface: #ffffff;
  --color-surface-hover: #f3f4f6;
  --color-border: #e5e7eb;
  --color-primary: #2563eb;
  --color-primary-hover: #1d4ed8;
  --color-text-primary: #1f2937;
  --color-text-secondary: #4b5563;
  --color-text-muted: #9ca3af;
  --color-red: #ef4444;
  --color-green: #10b981;
}

:root.theme-cyberpunk {
  --color-bg: #0c0017;
  --color-surface: #16002c;
  --color-surface-hover: #29004a;
  --color-border: #ff007f;
  --color-primary: #00ffff;
  --color-primary-hover: #00cccc;
  --color-text-primary: #ffffff;
  --color-text-secondary: #00ffff;
  --color-text-muted: #ff007f;
  --color-red: #ef4444;
  --color-green: #00ffff;
}

:root.theme-forest {
  --color-bg: #060f0b;
  --color-surface: #0c1c14;
  --color-surface-hover: #173627;
  --color-border: #1e4531;
  --color-primary: #10b981;
  --color-primary-hover: #059669;
  --color-text-primary: #f0fdf4;
  --color-text-secondary: #a7f3d0;
  --color-text-muted: #6ee7b7;
  --color-red: #ef4444;
  --color-green: #10b981;
}

:root.theme-sunset {
  --color-bg: #140b12;
  --color-surface: #241221;
  --color-surface-hover: #3d1c37;
  --color-border: #5c2452;
  --color-primary: #ec4899;
  --color-primary-hover: #db2777;
  --color-text-primary: #fdf2f8;
  --color-text-secondary: #fbcfe8;
  --color-text-muted: #f472b6;
  --color-red: #ef4444;
  --color-green: #10b981;
}

body {
  margin: 0;
  background-color: var(--color-bg);
  color: var(--color-text-primary);
  font-family: 'Outfit', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Custom scrollbars */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: var(--color-bg);
}
::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 99px;
}
::-webkit-scrollbar-thumb:hover {
  background: var(--color-text-muted);
}
</style>
