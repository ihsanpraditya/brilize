<script setup lang="ts">
const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="footer footer-center p-4 border-t border-base-300 bg-base-100 text-base-content/60 text-xs mt-auto">
    <aside>
      <p>© {{ currentYear }} Brilize School ERP • Sistem Informasi Akademik & Keuangan Terpadu</p>
    </aside>
  </footer>
</template>
