<script setup lang="ts">
import Header from '../Components/Header.vue'
import Sidebar from '../Components/Sidebar.vue'
import Footer from '../Components/Footer.vue'
import FlashMessages from '../Components/FlashMessages.vue'
</script>

<template>
  <div class="drawer lg:drawer-open min-h-screen bg-base-200 text-base-content font-sans">
    <input id="erp-drawer" type="checkbox" class="drawer-toggle" />

    <!-- Area Konten Utama -->
    <div class="drawer-content flex flex-col min-h-screen">
      
      <!-- Top Navbar / Header Component -->
      <Header />

      <!-- Alert Notifikasi Flash Component -->
      <FlashMessages />

      <!-- Main Content Slot -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <slot />
      </main>

      <!-- Footer Component -->
      <Footer />

    </div>

    <!-- Sidebar Navigation Drawer Component -->
    <Sidebar />

  </div>
</template>
