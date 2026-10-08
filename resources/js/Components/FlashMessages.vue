<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => (page.props.flash as any) ?? {})
</script>

<template>
  <div class="space-y-3">
    <!-- Success Banner -->
    <div v-if="flash?.success" class="p-4 pb-0 max-w-7xl mx-auto w-full">
      <div role="alert" class="alert alert-success text-success-content shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm font-medium">{{ flash.success }}</span>
        </div>
      </div>
    </div>

    <!-- Error Banner -->
    <div v-if="flash?.error" class="p-4 pb-0 max-w-7xl mx-auto w-full">
      <div role="alert" class="alert alert-error text-error-content shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm font-medium">{{ flash.error }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
