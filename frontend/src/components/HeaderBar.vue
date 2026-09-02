<template>
  <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4 border-b border-[#C8D0CC]">
    <div>
      <div class="flex items-center gap-3">
        <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-[#1C2B2A]">
          Job Application Tracker
        </h1>
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#ECEEEA] text-[#5B6863] border border-[#C8D0CC]">
          Logbook
        </span>
      </div>
      <p class="text-xs sm:text-sm text-[#5B6863] mt-0.5">
        Pantau perjalanan lamaran dan proses rekrutmenmu secara tenang dan teratur.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto">
      <!-- User Profile Badge & Logout -->
      <div v-if="user" class="flex items-center gap-2 px-2.5 py-1.5 bg-[#ECEEEA] border border-[#C8D0CC] rounded-md text-xs">
        <div class="w-6 h-6 rounded-full bg-[#1C2B2A] text-[#F3F4F0] flex items-center justify-center font-bold text-[11px]">
          {{ getInitials(user.name) }}
        </div>
        <div class="max-w-[140px] truncate">
          <div class="font-semibold text-[#1C2B2A] truncate leading-tight">{{ user.name }}</div>
          <div class="text-[10px] text-[#5B6863] truncate leading-tight">{{ user.email }}</div>
        </div>
        <button
          @click="$emit('logout')"
          class="ml-1 text-[#8B5A5A] hover:text-[#6D3F3F] p-1 transition-colors cursor-pointer"
          title="Keluar dari akun (Logout)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>

      <!-- Export CSV -->
      <button
        @click="$emit('export-csv')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium text-[#1C2B2A] bg-[#F3F4F0] hover:bg-[#ECEEEA] active:bg-[#E4E8E3] border border-[#C8D0CC] rounded-md transition-colors cursor-pointer"
        title="Unduh seluruh data lamaran dalam format CSV"
      >
        <svg class="w-4 h-4 text-[#5B6863]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Ekspor CSV
      </button>

      <!-- Tambah Lamaran -->
      <button
        @click="$emit('open-create')"
        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-medium text-[#F3F4F0] bg-[#1C2B2A] hover:bg-[#2B3E3C] active:bg-[#14201F] rounded-md shadow-sm transition-colors cursor-pointer"
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
import type { User } from '../types/job';

defineProps<{
  user?: User | null;
}>();

defineEmits<{
  (e: 'open-create'): void;
  (e: 'logout'): void;
  (e: 'export-csv'): void;
}>();

const getInitials = (name?: string): string => {
  if (!name) return 'U';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
};
</script>
