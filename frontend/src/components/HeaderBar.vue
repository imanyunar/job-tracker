<template>
  <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4 border-b border-[#C8D0CC]">
    <div>
      <div class="flex items-center gap-3">
        <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-[#1C2B2A]">
          Job Application Tracker
        </h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-[#ECEEEA] text-[#5B6863] border border-[#C8D0CC]">
          Logbook
        </span>
      </div>
      <p class="text-xs sm:text-sm text-[#5B6863] mt-1">
        Pantau perjalanan lamaran dan proses rekrutmenmu secara tenang dan teratur.
      </p>
    </div>

    <div class="flex items-center gap-2.5 self-start sm:self-auto">
      <a
        href="/api/job-applications/export"
        target="_blank"
        download
        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-medium text-[#1C2B2A] bg-[#F3F4F0] hover:bg-[#ECEEEA] active:bg-[#E4E8E3] border border-[#C8D0CC] rounded-md transition-colors"
        title="Unduh seluruh data lamaran dalam format CSV"
      >
        <svg class="w-4 h-4 text-[#5B6863]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Ekspor CSV
      </a>

      <button
        @click="$emit('open-create')"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs sm:text-sm font-medium text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-md shadow-sm transition-colors cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Lamaran
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
defineEmits<{
  (e: 'open-create'): void;
}>();
</script>
